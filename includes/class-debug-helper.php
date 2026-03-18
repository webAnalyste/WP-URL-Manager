<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Debug_Helper {
    
    public static function log_rewrite_rules() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wp_rewrite;
        error_log('=== WP URL Manager - Rewrite Rules ===');
        error_log(print_r($wp_rewrite->rules, true));
    }
    
    public static function log_request($wp) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        error_log('=== WP URL Manager - Request ===');
        error_log('REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
        error_log('$wp->request: ' . $wp->request);
        error_log('$wp->matched_rule: ' . $wp->matched_rule);
        error_log('$wp->matched_query: ' . $wp->matched_query);
    }
    
    public static function add_debug_page() {
        add_submenu_page(
            'wp-url-manager',
            'Debug',
            'Debug',
            'manage_options',
            'wp-url-manager-debug',
            array(__CLASS__, 'render_debug_page')
        );
    }
    
    public static function render_debug_page() {
        global $wp_rewrite;
        
        echo '<div class="wrap">';
        echo '<h1>WP URL Manager - Debug</h1>';
        
        echo '<h2>Rewrite Rules</h2>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto;">';
        print_r($wp_rewrite->rules);
        echo '</pre>';
        
        echo '<h2>Plugin Rules</h2>';
        $rules_manager = new WP_URL_Manager_Rules_Manager();
        $rules = $rules_manager->get_all_rules();
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto;">';
        print_r($rules);
        echo '</pre>';
        
        echo '<h2>Actions</h2>';
        echo '<form method="post">';
        wp_nonce_field('wp_url_manager_debug');
        echo '<button type="submit" name="flush_rules" class="button button-primary">Flush Rewrite Rules</button>';
        echo '</form>';
        
        if (isset($_POST['flush_rules']) && check_admin_referer('wp_url_manager_debug')) {
            flush_rewrite_rules();
            echo '<div class="notice notice-success"><p>Rewrite rules flushed!</p></div>';
        }
        
        echo '</div>';
    }
}
