<?php

/**
 * Global helper functions for WooTale
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to load WooTale template (for use in themes)
 */
function wootale_load_product_template($product = null) {
    if (!$product) {
        global $product;
    }
    
    if ($product) {
        $template_loader = new WooTale_Template_Loader();
        $template_loader->load_template($product);
    }
}

/**
 * Check if WooCommerce is active
 */
function wootale_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Get WooTale version
 */
function wootale_get_version() {
    return defined('WOOTALE_VERSION') ? WOOTALE_VERSION : '1.0.1';
}

/**
 * Check if current page is a product page with WooTale template
 */
function wootale_has_custom_template($product_id = null) {
    if (!$product_id) {
        global $product;
        if (!$product) {
            return false;
        }
        $product_id = $product->get_id();
    }
    
    $template = get_post_meta($product_id, '_wootale_product_template', true);
    return !empty($template);
}