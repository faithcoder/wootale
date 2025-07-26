<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wootale_only_template_pages');
delete_option('wootale_replace_tabs');

// Delete post meta for all products
global $wpdb;

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_wootale_product_template'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_wootale_is_template'
    )
);

// Clear any cached data that has been removed
wp_cache_flush();