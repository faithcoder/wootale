<?php
/**
 * Plugin Name: WooTale
 * Plugin URI: https://yourwebsite.com/wootale
 * Description: Dynamic product description templates using WordPress Pages and Elementor Templates for WooCommerce products.
 * Version: 1.0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: wootale
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WOOTALE_VERSION', '1.0.1');
define('WOOTALE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOTALE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WOOTALE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WooTale Plugin Class
 */
class WooTale {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->wootale_init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function wootale_init_hooks() {
        add_action('plugins_loaded', array($this, 'wootale_check_dependencies'));
        add_action('init', array($this, 'wootale_load_textdomain'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'wootale_activate'));
        register_deactivation_hook(__FILE__, array($this, 'wootale_deactivate'));
    }
    
    /**
     * Check if dependencies are met
     */
    public function wootale_dependencies_met() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Check dependencies and show admin notice if not met
     */
    public function wootale_check_dependencies() {
        if (!$this->wootale_dependencies_met()) {
            add_action('admin_notices', array($this, 'wootale_dependency_notice'));
            return;
        }
        
        // Initialize plugin only after WooCommerce is confirmed active
        $this->wootale_init_plugin();
    }
    
    /**
     * Show dependency notice
     */
    public function wootale_dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WooTale requires WooCommerce to be installed and activated.', 'wootale'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Load plugin textdomain
     */
    public function wootale_load_textdomain() {
        load_plugin_textdomain('wootale', false, dirname(WOOTALE_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Initialize plugin functionality
     */
    private function wootale_init_plugin() {
        // Product meta field hooks
        add_action('woocommerce_product_options_general_product_data', array($this, 'wootale_add_product_template_field'));
        add_action('woocommerce_process_product_meta', array($this, 'wootale_save_product_template_field'));
        
        // Template rendering
        add_action('wp_head', array($this, 'wootale_template_styles'));
        
        // Page meta box for template identification
        add_action('add_meta_boxes', array($this, 'wootale_add_template_meta_box'));
        add_action('save_post', array($this, 'wootale_save_template_meta_box'));
        
        // Shortcode registration
        add_shortcode('wootale_product_data', array($this, 'wootale_product_data_shortcode'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'wootale_add_admin_menu'));
        
        // Settings link in plugins page
        add_filter('plugin_action_links_' . WOOTALE_PLUGIN_BASENAME, array($this, 'wootale_add_settings_link'));
        
        // AUTOMATIC INTEGRATION - Higher priority to ensure proper hook order
        add_action('template_redirect', array($this, 'wootale_setup_product_hooks'));
    }
    
    /**
 * Setup product page hooks at the right time
 */
    public function wootale_setup_product_hooks() {
        if (!is_product()) {
            return;
        }
        
        global $product;
        if (!$product || !is_object($product)) {
            // Try to get product from query
            global $wp_query;
            if (isset($wp_query->post) && $wp_query->post) {
                $product = wc_get_product($wp_query->post->ID);
            }
        }
        
        // Final check to ensure we have a valid product object
        if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }
        
        // Check if this product has a custom template
        $selected_template = get_post_meta($product->get_id(), '_wootale_product_template', true);
        
        // if (!empty($selected_template)) {
        // // Modify the description tab to use custom template
        // add_filter('woocommerce_product_tabs', array($this, 'wootale_modify_description_tab'), 98);

        // if (!empty($selected_template)) {
        //     $replace_tabs = get_option('wootale_replace_tabs', 'no');
            
        //     if ($replace_tabs === 'yes') {
        //         // Replace entire tabs section
        //         add_filter('woocommerce_product_tabs', array($this, 'wootale_replace_all_tabs'), 98);
        //     } else {
        //         // Modify description tab only (default behavior)
        //         add_filter('woocommerce_product_tabs', array($this, 'wootale_modify_description_tab'), 98);
        //     }
        // }

        if (!empty($selected_template)) {
            $replace_tabs = get_option('wootale_replace_tabs', 'no');
            
            if ($replace_tabs === 'yes') {
                // Replace entire tabs section
                add_filter('woocommerce_product_tabs', array($this, 'wootale_replace_all_tabs'), 98);
                // Add custom content after tabs area
                add_action('woocommerce_after_single_product_summary', array($this, 'wootale_display_custom_content'), 15);
            } else {
                // Modify description tab only (default behavior)
                add_filter('woocommerce_product_tabs', array($this, 'wootale_modify_description_tab'), 98);
            }
        }
    }

    
    
    /**
 * Modify description tab to use custom template
 */
    public function wootale_modify_description_tab($tabs) {
        if (isset($tabs['description'])) {
            $tabs['description']['callback'] = array($this, 'wootale_custom_description_tab_content');
        }
        return $tabs;
    }

    /**
     * Custom description tab content
     */
    public function wootale_custom_description_tab_content() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        echo '<div class="wootale-tab-content">';
        $this->wootale_load_template($product);
        echo '</div>';
    }
    /**
 * Replace all tabs with custom template
 */
    // public function wootale_replace_all_tabs($tabs) {
    //     // Remove description and reviews tabs
    //     unset($tabs['description']);
    //     unset($tabs['reviews']);
        
    //     // Add custom template tab
    //     $tabs['wootale_custom'] = array(
    //         'title'    => __('Product Details', 'wootale'),
    //         'priority' => 10,
    //         'callback' => array($this, 'wootale_custom_description_tab_content')
    //     );
        
    //     return $tabs;
    // }


    /**
     * Replace all tabs with custom template
     */
    public function wootale_replace_all_tabs($tabs) {
        // Remove all tabs completely
        return array();
    }

        /**
     * Display custom template content as plain div
     */
    public function wootale_display_custom_content() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        echo '<div class="wootale">';
        $this->wootale_load_template($product);
        echo '</div>';
    }
    /**
     * Add product template field to product edit page
     */
    public function wootale_add_product_template_field() {
        global $post;
        
        $templates = $this->wootale_get_available_templates();
        
        woocommerce_wp_select(array(
            'id' => '_wootale_product_template',
            'label' => __('Product Description Template', 'wootale'),
            'description' => __('Select a page or template for this product description', 'wootale'),
            'desc_tip' => true,
            'options' => $templates,
            'value' => get_post_meta($post->ID, '_wootale_product_template', true)
        ));
    }
    
    /**
     * Save product template field
     */
    public function wootale_save_product_template_field($post_id) {
        // Verify nonce for security
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }
        
        $template = isset($_POST['_wootale_product_template']) ? sanitize_text_field($_POST['_wootale_product_template']) : '';
        update_post_meta($post_id, '_wootale_product_template', $template);
    }
    
    /**
     * Get available templates (Pages + Elementor)
     */
    public function wootale_get_available_templates() {
        $templates = array(
            '' => __('Default WooCommerce Description', 'wootale'),
        );
        
        // Get WordPress Pages
        $pages_args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        // Option to filter only template pages
        $only_template_pages = get_option('wootale_only_template_pages', 'no');
        if ($only_template_pages === 'yes') {
            $pages_args['meta_key'] = '_wootale_is_template';
            $pages_args['meta_value'] = '1';
        }
        
        $pages = get_posts($pages_args);
        
        foreach ($pages as $page) {
            $templates['page_' . $page->ID] = __('Page:', 'wootale') . ' ' . $page->post_title;
        }
        
        // Get Elementor Templates (if Elementor is active)
        if (defined('ELEMENTOR_VERSION')) {
            $elementor_templates = get_posts(array(
                'post_type' => 'elementor_library',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_elementor_template_type',
                        'value' => array('page', 'section', 'widget'),
                        'compare' => 'IN'
                    )
                )
            ));
            
            foreach ($elementor_templates as $template) {
                $template_type = get_post_meta($template->ID, '_elementor_template_type', true);
                $templates['elementor_' . $template->ID] = sprintf(
                    __('Elementor %s:', 'wootale'),
                    ucfirst($template_type)
                ) . ' ' . $template->post_title;
            }
        }
        
        // Allow custom templates via filter
        $custom_templates = apply_filters('wootale_custom_templates', array());
        if (!empty($custom_templates)) {
            $templates = array_merge($templates, $custom_templates);
        }
        
        return $templates;
    }
    
    /**
     * Main function to load product description template
     */
    public function wootale_load_template($product) {
        if (!$product || !is_object($product)) {
            return;
        }
        
        $product_id = $product->get_id();
        
        // Get the selected template from product meta
        $selected_template = get_post_meta($product_id, '_wootale_product_template', true);
        
        if (!empty($selected_template)) {
            // Parse template type and ID
            if (strpos($selected_template, 'page_') === 0) {
                $template_id = str_replace('page_', '', $selected_template);
                $this->wootale_render_page_template($template_id, $product);
                return;
            } elseif (strpos($selected_template, 'elementor_') === 0) {
                $template_id = str_replace('elementor_', '', $selected_template);
                $this->wootale_render_elementor_template($template_id, $product);
                return;
            }
        }
        
        // Fallback to WooCommerce default description
        echo '<div class="wootale-default-description">';
        echo wpautop($product->get_description());
        echo '</div>';
    }
    
    /**
     * Render WordPress Page as template
     */
    private function wootale_render_page_template($page_id, $product) {
        $page = get_post($page_id);
        
        if (!$page || $page->post_status !== 'publish') {
            echo '<div class="wootale-template-error">' . __('Template page not found or not published.', 'wootale') . '</div>';
            return;
        }
        
        // Set global product data for use in the page content
        global $wootale_current_product;
        $wootale_current_product = $product;
        
        echo '<div class="wootale-page-template" data-page-id="' . esc_attr($page_id) . '">';
        
        // Get content and process it
        $content = $page->post_content;
        
        // Replace product placeholders BEFORE processing shortcodes
        $content = $this->wootale_replace_placeholders($content, $product);
        
        // Process shortcodes and apply content filters
        $content = do_shortcode($content);
        $content = apply_filters('the_content', $content);
        
        echo $content;
        echo '</div>';
        
        // Clean up global
        $wootale_current_product = null;
    }
    
    /**
     * Render Elementor Template
     */
    private function wootale_render_elementor_template($template_id, $product) {
        if (!defined('ELEMENTOR_VERSION')) {
            echo '<div class="wootale-template-error">' . __('Elementor is not active.', 'wootale') . '</div>';
            return;
        }
        
        $template = get_post($template_id);
        
        if (!$template || $template->post_status !== 'publish') {
            echo '<div class="wootale-template-error">' . __('Elementor template not found or not published.', 'wootale') . '</div>';
            return;
        }
        
        // Set global product data for use in Elementor widgets
        global $wootale_current_product;
        $wootale_current_product = $product;
        
        echo '<div class="wootale-elementor-template" data-template-id="' . esc_attr($template_id) . '">';
        
        // Render Elementor content
        if (class_exists('\Elementor\Plugin')) {
            $elementor_instance = \Elementor\Plugin::instance();
            $content = $elementor_instance->frontend->get_builder_content_for_display($template_id);
            
            // Replace placeholders in Elementor content
            $content = $this->wootale_replace_placeholders($content, $product);
            echo $content;
        } else {
            // Fallback if Elementor frontend is not available
            $content = get_post_field('post_content', $template_id);
            $content = $this->wootale_replace_placeholders($content, $product);
            echo do_shortcode($content);
        }
        
        echo '</div>';
        
        // Clean up global
        $wootale_current_product = null;
    }
    
    /**
     * Replace product placeholders in content
     */
    private function wootale_replace_placeholders($content, $product) {
        if (!$product || !is_object($product)) {
            return $content;
        }
        
        $placeholders = array(
            '[product_name]' => $product->get_name(),
            '[product_price]' => wc_price($product->get_price()),
            '[product_regular_price]' => $product->get_regular_price() ? wc_price($product->get_regular_price()) : '',
            '[product_sale_price]' => $product->is_on_sale() && $product->get_sale_price() ? wc_price($product->get_sale_price()) : '',
            '[product_sku]' => $product->get_sku() ? $product->get_sku() : '',
            '[product_description]' => $product->get_description(),
            '[product_short_description]' => $product->get_short_description(),
            '[product_weight]' => $product->get_weight() ? $product->get_weight() . ' ' . get_option('woocommerce_weight_unit') : '',
            '[product_dimensions]' => wc_format_dimensions($product->get_dimensions(false)),
            '[product_categories]' => wc_get_product_category_list($product->get_id()),
            '[product_tags]' => wc_get_product_tag_list($product->get_id()),
            '[product_rating]' => wc_get_rating_html($product->get_average_rating()),
            '[product_review_count]' => $product->get_review_count(),
            '[product_stock_status]' => $product->is_in_stock() ? __('In Stock', 'wootale') : __('Out of Stock', 'wootale'),
        );
        
        // Allow custom placeholders via filter
        $placeholders = apply_filters('wootale_placeholders', $placeholders, $product);
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }
    
    /**
     * Product data shortcode
     */
    public function wootale_product_data_shortcode($atts) {
        global $wootale_current_product, $product;
        
        $current_product = $wootale_current_product ? $wootale_current_product : $product;
        
        if (!$current_product) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'field' => 'name',
            'format' => 'text'
        ), $atts, 'wootale_product_data');
        
        switch ($atts['field']) {
            case 'name':
                return $current_product->get_name();
            case 'price':
                return $atts['format'] === 'price' ? wc_price($current_product->get_price()) : $current_product->get_price();
            case 'regular_price':
                return $atts['format'] === 'price' ? wc_price($current_product->get_regular_price()) : $current_product->get_regular_price();
            case 'sale_price':
                $sale_price = $current_product->get_sale_price();
                return $sale_price ? ($atts['format'] === 'price' ? wc_price($sale_price) : $sale_price) : '';
            case 'sku':
                return $current_product->get_sku();
            case 'description':
                return $current_product->get_description();
            case 'short_description':
                return $current_product->get_short_description();
            case 'weight':
                return $current_product->get_weight() ? $current_product->get_weight() . ' ' . get_option('woocommerce_weight_unit') : '';
            case 'dimensions':
                return wc_format_dimensions($current_product->get_dimensions(false));
            case 'categories':
                return wc_get_product_category_list($current_product->get_id());
            case 'tags':
                return wc_get_product_tag_list($current_product->get_id());
            case 'rating':
                return wc_get_rating_html($current_product->get_average_rating());
            case 'review_count':
                return $current_product->get_review_count();
            case 'stock_status':
                return $current_product->is_in_stock() ? __('In Stock', 'wootale') : __('Out of Stock', 'wootale');
            default:
                return apply_filters('wootale_shortcode_field', '', $atts['field'], $current_product, $atts);
        }
    }
    
    /**
     * Add meta box to pages
     */
    public function wootale_add_template_meta_box() {
        add_meta_box(
            'wootale_template_settings',
            __('WooTale Template Settings', 'wootale'),
            array($this, 'wootale_template_meta_box_callback'),
            'page',
            'side',
            'default'
        );
    }
    
    /**
     * Meta box callback
     */
    public function wootale_template_meta_box_callback($post) {
        wp_nonce_field('wootale_template_meta_box', 'wootale_template_meta_box_nonce');
        
        $is_template = get_post_meta($post->ID, '_wootale_is_template', true);
        
        echo '<label for="wootale_is_template">';
        echo '<input type="checkbox" id="wootale_is_template" name="wootale_is_template" value="1" ' . checked($is_template, '1', false) . '>';
        echo ' ' . __('Mark as Product Template', 'wootale') . '</label>';
        echo '<p><small>' . __('Check this box to identify this page as a product description template.', 'wootale') . '</small></p>';
        
        echo '<hr>';
        echo '<h4>' . __('Available Placeholders:', 'wootale') . '</h4>';
        echo '<div style="font-size: 12px; line-height: 1.4;">';
        echo '<code>[product_name]</code><br>';
        echo '<code>[product_price]</code><br>';
        echo '<code>[product_description]</code><br>';
        echo '<code>[product_sku]</code><br>';
        echo '<code>[product_categories]</code><br>';
        echo '<p><a href="' . admin_url('admin.php?page=wootale-settings') . '">' . __('View all placeholders', 'wootale') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Save meta box data
     */
    public function wootale_save_template_meta_box($post_id) {
        if (!isset($_POST['wootale_template_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['wootale_template_meta_box_nonce'], 'wootale_template_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        }
        
        $is_template = isset($_POST['wootale_is_template']) ? '1' : '0';
        update_post_meta($post_id, '_wootale_is_template', $is_template);
    }
    
    /**
     * Add template styles
     */
    public function wootale_template_styles() {
        if (is_product()) {
            ?>
            <style>
                .wootale-page-template,
                .wootale-elementor-template,
                .wootale-auto-description {
                    margin: 20px 0;
                    clear: both;
                    width: 100%;
                }
                
                .wootale-template-error {
                    background: #f8d7da;
                    color: #721c24;
                    padding: 15px;
                    border: 1px solid #f5c6cb;
                    border-radius: 4px;
                    margin: 10px 0;
                }
                
                .wootale-default-description {
                    margin: 20px 0;
                }
                
                .wootale-page-template .entry-title,
                .wootale-page-template .page-title {
                    display: none;
                }
                
                /* Ensure proper spacing and display */
                .wootale-auto-description p:first-child {
                    margin-top: 0;
                }
                
                .wootale-auto-description p:last-child {
                    margin-bottom: 0;
                }
            </style>
            <?php
        }
    }
    
    /**
     * Add admin menu
     */
    public function wootale_add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('WooTale Settings', 'wootale'),
            __('WooTale', 'wootale'),
            'manage_woocommerce',
            'wootale-settings',
            array($this, 'wootale_settings_page')
        );
    }
    
    /**
     * Settings page
     */
    public function wootale_settings_page() {
        if (isset($_POST['wootale_save_settings'])) {
            $this->wootale_save_settings();
        }
        
        $only_template_pages = get_option('wootale_only_template_pages', 'no');
        ?>
        <div class="wrap">
            <h1><?php _e('WooTale Settings', 'wootale'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('wootale_settings', 'wootale_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Template Pages Filter', 'wootale'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wootale_only_template_pages" value="yes" <?php checked($only_template_pages, 'yes'); ?>>
                                <?php _e('Only show pages marked as templates in the dropdown', 'wootale'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, only pages with "Mark as Product Template" checked will appear in the template selection dropdown.', 'wootale'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Template Display Mode', 'wootale'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wootale_replace_tabs" value="yes" <?php checked(get_option('wootale_replace_tabs', 'no'), 'yes'); ?>>
                                <?php _e('Replace entire Description and Reviews tabs with custom template', 'wootale'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, the custom template will replace both Description and Reviews tabs completely. When disabled, the template content will appear inside the Description tab.', 'wootale'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'wootale'), 'primary', 'wootale_save_settings'); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Available Placeholders', 'wootale'); ?></h2>
            <div class="wootale-placeholders">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h3><?php _e('Basic Information', 'wootale'); ?></h3>
                        <ul>
                            <li><code>[product_name]</code> - <?php _e('Product name', 'wootale'); ?></li>
                            <li><code>[product_price]</code> - <?php _e('Current price (formatted)', 'wootale'); ?></li>
                            <li><code>[product_regular_price]</code> - <?php _e('Regular price', 'wootale'); ?></li>
                            <li><code>[product_sale_price]</code> - <?php _e('Sale price', 'wootale'); ?></li>
                            <li><code>[product_sku]</code> - <?php _e('Product SKU', 'wootale'); ?></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3><?php _e('Descriptions', 'wootale'); ?></h3>
                        <ul>
                            <li><code>[product_description]</code> - <?php _e('Full description', 'wootale'); ?></li>
                            <li><code>[product_short_description]</code> - <?php _e('Short description', 'wootale'); ?></li>
                        </ul>
                        
                        <h3><?php _e('Physical Properties', 'wootale'); ?></h3>
                        <ul>
                            <li><code>[product_weight]</code> - <?php _e('Weight with unit', 'wootale'); ?></li>
                            <li><code>[product_dimensions]</code> - <?php _e('Formatted dimensions', 'wootale'); ?></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3><?php _e('Categories & Reviews', 'wootale'); ?></h3>
                        <ul>
                            <li><code>[product_categories]</code> - <?php _e('Product categories', 'wootale'); ?></li>
                            <li><code>[product_tags]</code> - <?php _e('Product tags', 'wootale'); ?></li>
                            <li><code>[product_rating]</code> - <?php _e('Star rating HTML', 'wootale'); ?></li>
                            <li><code>[product_review_count]</code> - <?php _e('Number of reviews', 'wootale'); ?></li>
                            <li><code>[product_stock_status]</code> - <?php _e('Stock status', 'wootale'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <h3><?php _e('Shortcode Alternative', 'wootale'); ?></h3>
                <p><?php _e('You can also use shortcodes instead of placeholders:', 'wootale'); ?></p>
                <ul>
                    <li><code>[wootale_product_data field="name"]</code></li>
                    <li><code>[wootale_product_data field="price" format="price"]</code></li>
                    <li><code>[wootale_product_data field="description"]</code></li>
                </ul>
            </div>
            
            <hr>
            
            <h2><?php _e('How to Use', 'wootale'); ?></h2>
            <ol>
                <li><?php _e('Create a WordPress Page or Elementor Template with your design', 'wootale'); ?></li>
                <li><?php _e('Add placeholders like [product_name] or [product_price] where you want dynamic content', 'wootale'); ?></li>
                <li><?php _e('Optionally, mark the page as a "Product Template" using the checkbox in the page editor', 'wootale'); ?></li>
                <li><?php _e('Go to any product edit page and select your template from the "Product Description Template" dropdown', 'wootale'); ?></li>
                <li><?php _e('Update the product and view it on the frontend', 'wootale'); ?></li>
            </ol>
        </div>
        <?php
    }
    
    
    /**
     * Save settings
     */
    private function wootale_save_settings() {
        if (!wp_verify_nonce($_POST['wootale_settings_nonce'], 'wootale_settings')) {
            return;
        }
        
        $only_template_pages = isset($_POST['wootale_only_template_pages']) ? 'yes' : 'no';
        update_option('wootale_only_template_pages', $only_template_pages);

        $replace_tabs = isset($_POST['wootale_replace_tabs']) ? 'yes' : 'no';
        update_option('wootale_replace_tabs', $replace_tabs);
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'wootale') . '</p></div>';
    }
    
    /**
     * Add settings link to plugins page
     */
    public function wootale_add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wootale-settings') . '">' . __('Settings', 'wootale') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Plugin activation
     */
    public function wootale_activate() {
        // Set default options
        add_option('wootale_only_template_pages', 'no');
        add_option('wootale_replace_tabs', 'no');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function wootale_deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
WooTale::get_instance();

/**
 * Helper function to load WooTale template (for use in themes)
 */
function wootale_load_product_template($product = null) {
    if (!$product) {
        global $product;
    }
    
    if ($product) {
        WooTale::get_instance()->wootale_load_template($product);
    }
}

/**
 * Check if WooCommerce is active
 */
function wootale_is_woocommerce_active() {
    return class_exists('WooCommerce');
}







?>