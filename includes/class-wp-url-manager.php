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
        $required_files = array(
            'includes/class-rules-manager.php',
            'includes/class-pattern-validator.php',
            'includes/class-placeholder-resolver.php',
            'includes/class-permalink-manager.php',
            'includes/class-rewrite-manager.php',
            'includes/class-redirect-manager.php',
            'includes/class-debug-helper.php',
            'includes/class-updater.php',
        );
        
        foreach ($required_files as $file) {
            $filepath = WP_URL_MANAGER_PLUGIN_DIR . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            } else {
                error_log('WP URL Manager: Missing file - ' . $file);
            }
        }
        
        if (is_admin()) {
            $admin_file = WP_URL_MANAGER_PLUGIN_DIR . 'admin/class-admin-interface.php';
            if (file_exists($admin_file)) {
                require_once $admin_file;
            } else {
                error_log('WP URL Manager: Missing admin interface file');
            }
        }
    }

    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init_components'));
        add_action('init', array($this, 'load_textdomain'));
    }

    public function init_components() {
        try {
            $this->rules_manager = new WP_URL_Manager_Rules_Manager();
            $this->permalink_manager = new WP_URL_Manager_Permalink_Manager($this->rules_manager);
            $this->rewrite_manager = new WP_URL_Manager_Rewrite_Manager($this->rules_manager);
            $this->redirect_manager = new WP_URL_Manager_Redirect_Manager($this->rules_manager);
            
            if (is_admin()) {
                if (class_exists('WP_URL_Manager_Admin_Interface')) {
                    $this->admin_interface = new WP_URL_Manager_Admin_Interface($this->rules_manager);
                }
                if (class_exists('WP_URL_Manager_Updater')) {
                    $this->updater = new WP_URL_Manager_Updater(
                        WP_URL_MANAGER_PLUGIN_FILE,
                        'webAnalyste/WP-URL-Manager',
                        WP_URL_MANAGER_VERSION
                    );
                }
            }
        } catch (Exception $e) {
            error_log('WP URL Manager init error: ' . $e->getMessage());
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
