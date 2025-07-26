<?php

/**
 * Fired during plugin deactivation
 */
class WooTale_Deactivator {

    /**
     * Plugin deactivation logic
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}