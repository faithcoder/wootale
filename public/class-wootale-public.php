<?php

/**
 * The public-facing functionality of the plugin
 */
class WooTale_Public {

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
     * Setup product page hooks at the right time
     */
    public function setup_product_hooks() {
        if (!is_product()) {
            return;
        }
        
        global $product;
        if (!$product || !is_object($product)) {
            global $wp_query;
            if (isset($wp_query->post) && $wp_query->post) {
                $product = wc_get_product($wp_query->post->ID);
            }
        }
        
        if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }
        
        $selected_template = get_post_meta($product->get_id(), '_wootale_product_template', true);
        
        if (!empty($selected_template)) {
            $replace_tabs = get_option('wootale_replace_tabs', 'no');
            
            if ($replace_tabs === 'yes') {
                add_filter('woocommerce_product_tabs', array($this, 'replace_all_tabs'), 98);
                add_action('woocommerce_after_single_product_summary', array($this, 'display_custom_content'), 15);
            } else {
                add_filter('woocommerce_product_tabs', array($this, 'modify_description_tab'), 98);
            }
        }
    }

    /**
     * Modify description tab to use custom template
     */
    public function modify_description_tab($tabs) {
        if (isset($tabs['description'])) {
            $tabs['description']['callback'] = array($this, 'custom_description_tab_content');
        }
        return $tabs;
    }

    /**
     * Custom description tab content
     */
    public function custom_description_tab_content() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        echo '<div class="wootale-tab-content">';
        $template_loader = new WooTale_Template_Loader();
        $template_loader->load_template($product);
        echo '</div>';
    }

    /**
     * Replace all tabs with custom template
     */
    public function replace_all_tabs($tabs) {
        return array();
    }

    /**
     * Display custom template content as plain div
     */
    public function display_custom_content() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        echo '<div class="wootale">';
        $template_loader = new WooTale_Template_Loader();
        $template_loader->load_template($product);
        echo '</div>';
    }

    /**
     * Add template styles
     */
    public function add_template_styles() {
        if (is_product()) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wootale-public.css', array(), $this->version, 'all');
        }
    }
}