<?php
/**
 * Plugin Name: WooTale
 * Plugin URI: https://yourwebsite.com/wootale
 * Description: Dynamic product description templates using WordPress Pages and Elementor Templates for WooCommerce products.
 * Version: 1.0.1
 * Author: M Arif
 * Author URI: https://faithcoder.com
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

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('WOOTALE_VERSION', '1.0.1');
define('WOOTALE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOTALE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WOOTALE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wootale() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wootale-activator.php';
    WooTale_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wootale() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wootale-deactivator.php';
    WooTale_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wootale');
register_deactivation_hook(__FILE__, 'deactivate_wootale');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wootale.php';

/**
 * Begins execution of the plugin.
 */
function run_wootale() {
    $plugin = new WooTale();
    $plugin->run();
}
run_wootale();