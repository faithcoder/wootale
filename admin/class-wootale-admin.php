<?php

/**
 * The admin-specific functionality of the plugin
 */
class WooTale_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Check if dependencies are met
     */
    public function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'dependency_notice'));
            return false;
        }
        return true;
    }

    /**
     * Show dependency notice
     */
    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WooTale requires WooCommerce to be installed and activated.', 'wootale'); ?></p>
        </div>
        <?php
    }

    /**
     * Add product template field to product edit page
     */
    public function add_product_template_field() {
        global $post;
        
        $template_loader = new WooTale_Template_Loader();
        $templates = $template_loader->get_available_templates();
        
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
    public function save_product_template_field($post_id) {
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }
        
        $template = isset($_POST['_wootale_product_template']) ? sanitize_text_field($_POST['_wootale_product_template']) : '';
        update_post_meta($post_id, '_wootale_product_template', $template);
    }

    /**
     * Add meta box to pages
     */
    public function add_template_meta_box() {
        add_meta_box(
            'wootale_template_settings',
            __('WooTale Template Settings', 'wootale'),
            array($this, 'template_meta_box_callback'),
            'page',
            'side',
            'default'
        );
    }

    /**
     * Meta box callback
     */
    public function template_meta_box_callback($post) {
        require_once plugin_dir_path(__FILE__) . 'partials/wootale-meta-box-display.php';
    }

    /**
     * Save meta box data
     */
    public function save_template_meta_box($post_id) {
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
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('WooTale Settings', 'wootale'),
            __('WooTale', 'wootale'),
            'manage_woocommerce',
            'wootale-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Settings page
     */
    public function settings_page() {
        require_once plugin_dir_path(__FILE__) . 'partials/wootale-admin-display.php';
    }

    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wootale-settings') . '">' . __('Settings', 'wootale') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}