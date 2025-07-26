<?php

/**
 * Fired during plugin activation
 */
class WooTale_Activator {

    /**
     * Plugin activation logic
     */
    public static function activate() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('WooTale requires WooCommerce to be installed and activated.', 'wootale'));
        }

        // Set default options
        add_option('wootale_only_template_pages', 'no');
        add_option('wootale_replace_tabs', 'no');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}