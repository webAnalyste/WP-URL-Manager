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
        
        if (isset($_POST['flush_rules']) && check_admin_referer('wp_url_manager_debug')) {
            flush_rewrite_rules();
            echo '<div class="notice notice-success"><p><strong>✅ Rewrite rules flushed!</strong></p></div>';
        }
        
        if (isset($_POST['test_url']) && check_admin_referer('wp_url_manager_debug')) {
            $test_url = sanitize_text_field($_POST['test_url_input']);
            echo '<div class="notice notice-info"><p><strong>Test URL:</strong> ' . esc_html($test_url) . '</p></div>';
        }
        
        echo '<h2>Actions</h2>';
        echo '<form method="post" style="margin-bottom: 20px;">';
        wp_nonce_field('wp_url_manager_debug');
        echo '<button type="submit" name="flush_rules" class="button button-primary">Flush Rewrite Rules</button>';
        echo '</form>';
        
        echo '<form method="post" style="margin-bottom: 20px;">';
        wp_nonce_field('wp_url_manager_debug');
        echo '<input type="text" name="test_url_input" placeholder="/articles/test/" style="width: 300px;" />';
        echo '<button type="submit" name="test_url" class="button">Test URL</button>';
        echo '</form>';
        
        echo '<h2>WordPress Rewrite Rules</h2>';
        echo '<p><em>Recherchez votre pattern (ex: "articles") dans la liste ci-dessous</em></p>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; max-height: 400px;">';
        if (!empty($wp_rewrite->rules)) {
            foreach ($wp_rewrite->rules as $regex => $query) {
                echo esc_html($regex) . ' => ' . esc_html($query) . "\n";
            }
        } else {
            echo 'Aucune rewrite rule trouvée';
        }
        echo '</pre>';
        
        echo '<h2>Plugin Rules</h2>';
        $rules_manager = new WP_URL_Manager_Rules_Manager();
        $rules = $rules_manager->get_all_rules();
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto;">';
        print_r($rules);
        echo '</pre>';
        
        echo '<h2>PHP Info</h2>';
        echo '<p><strong>WP_DEBUG:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? '✅ ON' : '❌ OFF') . '</p>';
        echo '<p><strong>WP_DEBUG_LOG:</strong> ' . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '✅ ON' : '❌ OFF') . '</p>';
        echo '<p><strong>Plugin Version:</strong> ' . WP_URL_MANAGER_VERSION . '</p>';
        
        if (!(defined('WP_DEBUG') && WP_DEBUG)) {
            echo '<div class="notice notice-warning" style="margin-top: 20px;"><p><strong>⚠️ WP_DEBUG est désactivé</strong><br>';
            echo 'Pour voir les logs de debug, activez WP_DEBUG dans wp-config.php :<br>';
            echo '<code>define(\'WP_DEBUG\', true);<br>define(\'WP_DEBUG_LOG\', true);</code></p></div>';
        }
        
        echo '</div>';
    }
}
