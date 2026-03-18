<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager {

    private static $instance = null;
    
    private $rules_manager;
    private $permalink_manager;
    private $rewrite_manager;
    private $redirect_manager;
    private $admin_interface;
    private $updater;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-rules-manager.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-pattern-validator.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-placeholder-resolver.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-permalink-manager.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-rewrite-manager.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-redirect-manager.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-debug-helper.php';
        require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-updater.php';
        
        if (is_admin()) {
            require_once WP_URL_MANAGER_PLUGIN_DIR . 'admin/class-admin-interface.php';
        }
    }

    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init_components'));
        add_action('init', array($this, 'load_textdomain'));
    }

    public function init_components() {
        $this->rules_manager = new WP_URL_Manager_Rules_Manager();
        $this->permalink_manager = new WP_URL_Manager_Permalink_Manager($this->rules_manager);
        $this->rewrite_manager = new WP_URL_Manager_Rewrite_Manager($this->rules_manager);
        $this->redirect_manager = new WP_URL_Manager_Redirect_Manager($this->rules_manager);
        
        if (is_admin()) {
            $this->admin_interface = new WP_URL_Manager_Admin_Interface($this->rules_manager);
            $this->updater = new WP_URL_Manager_Updater(
                WP_URL_MANAGER_PLUGIN_FILE,
                'webAnalyste/WP-URL-Manager',
                WP_URL_MANAGER_VERSION
            );
        }
    }

    public function load_textdomain() {
        load_plugin_textdomain('wp-url-manager', false, dirname(plugin_basename(WP_URL_MANAGER_PLUGIN_FILE)) . '/languages');
    }

    public static function activate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        $rules_manager = new WP_URL_Manager_Rules_Manager();
        $rules_manager->create_default_options();
        
        flush_rewrite_rules();
    }

    public static function deactivate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        flush_rewrite_rules();
    }

    public function get_rules_manager() {
        return $this->rules_manager;
    }

    public function get_permalink_manager() {
        return $this->permalink_manager;
    }

    public function get_rewrite_manager() {
        return $this->rewrite_manager;
    }

    public function get_redirect_manager() {
        return $this->redirect_manager;
    }
}
