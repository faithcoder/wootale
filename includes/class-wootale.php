<?php

/**
 * The file that defines the core plugin class
 */

class WooTale {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        if (defined('WOOTALE_VERSION')) {
            $this->version = WOOTALE_VERSION;
        } else {
            $this->version = '1.0.1';
        }
        $this->plugin_name = 'wootale';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_template_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wootale-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wootale-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wootale-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wootale-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/class-wootale-template-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/class-wootale-template-renderer.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/class-wootale-placeholders.php';

        $this->loader = new WooTale_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new WooTale_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WooTale_Admin($this->get_plugin_name(), $this->get_version());

        // Check dependencies
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'check_dependencies');

        // Admin menu and settings
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu', 80);
        
        $this->loader->add_filter('plugin_action_links_' . WOOTALE_PLUGIN_BASENAME, $plugin_admin, 'add_settings_link');

        // Product meta fields
        $this->loader->add_action('woocommerce_product_options_general_product_data', $plugin_admin, 'add_product_template_field');
        $this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'save_product_template_field');

        // Page meta box
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_template_meta_box');
        $this->loader->add_action('save_post', $plugin_admin, 'save_template_meta_box');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new WooTale_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_head', $plugin_public, 'add_template_styles');
        $this->loader->add_action('template_redirect', $plugin_public, 'setup_product_hooks');
    }

    /**
     * Register template-related hooks.
     */
    private function define_template_hooks() {
        $template_loader = new WooTale_Template_Loader();
        
        // Shortcode registration
        $this->loader->add_shortcode('wootale_product_data', $template_loader, 'product_data_shortcode');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}