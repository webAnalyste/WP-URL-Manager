<?php
/**
 * Plugin Name: WP URL Manager
 * Plugin URI: https://github.com/webAnalyste/WP-URL-Manager
 * Description: Plugin WordPress de gestion des structures d'URL et redirections 301 par type de contenu
 * Version: 1.1.5
 * Author: webAnalyste
 * Author URI: https://github.com/webAnalyste
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-url-manager
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_URL_MANAGER_VERSION', '1.1.5');
define('WP_URL_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_URL_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_URL_MANAGER_PLUGIN_FILE', __FILE__);

require_once WP_URL_MANAGER_PLUGIN_DIR . 'includes/class-wp-url-manager.php';

function wp_url_manager() {
    return WP_URL_Manager::instance();
}

wp_url_manager();

register_activation_hook(__FILE__, array('WP_URL_Manager', 'activate'));
register_deactivation_hook(__FILE__, array('WP_URL_Manager', 'deactivate'));
