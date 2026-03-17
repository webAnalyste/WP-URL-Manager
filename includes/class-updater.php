<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $github_repo;
    private $version;
    private $cache_key;
    private $cache_allowed;

    public function __construct($plugin_file, $github_repo, $version) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_repo = $github_repo;
        $this->version = $version;
        $this->cache_key = 'wp_url_manager_update_cache';
        $this->cache_allowed = true;

        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('site_transient_update_plugins', array($this, 'check_update'));
        add_action('upgrader_process_complete', array($this, 'purge_cache'), 10, 2);
        add_action('admin_init', array($this, 'force_check_on_plugins_page'));
        add_action('load-plugins.php', array($this, 'maybe_force_check'));
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->get_remote_version();

        if ($remote_version && version_compare($this->version, $remote_version, '<')) {
            $plugin_data = array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version,
                'url' => "https://github.com/{$this->github_repo}",
                'package' => $this->get_download_url($remote_version),
                'tested' => $this->get_tested_version(),
                'requires_php' => '7.4',
                'compatibility' => new stdClass(),
            );

            $transient->response[$this->plugin_slug] = (object) $plugin_data;
        }

        return $transient;
    }

    public function plugin_info($false, $action, $response) {
        if ($action !== 'plugin_information') {
            return $false;
        }

        if (empty($response->slug) || $response->slug !== dirname($this->plugin_slug)) {
            return $false;
        }

        $remote_version = $this->get_remote_version();
        $remote_info = $this->get_remote_info();

        if (!$remote_version || !$remote_info) {
            return $false;
        }

        $plugin_info = array(
            'name' => 'WP URL Manager',
            'slug' => dirname($this->plugin_slug),
            'version' => $remote_version,
            'author' => '<a href="https://github.com/webAnalyste">webAnalyste</a>',
            'author_profile' => 'https://github.com/webAnalyste',
            'homepage' => "https://github.com/{$this->github_repo}",
            'requires' => '5.8',
            'tested' => $this->get_tested_version(),
            'requires_php' => '7.4',
            'download_link' => $this->get_download_url($remote_version),
            'sections' => array(
                'description' => $this->get_description(),
                'installation' => $this->get_installation(),
                'changelog' => $this->get_changelog($remote_info),
            ),
            'banners' => array(),
            'external' => true,
        );

        return (object) $plugin_info;
    }

    private function get_remote_version() {
        $cache = get_transient($this->cache_key);

        if ($this->cache_allowed && $cache !== false && isset($cache['version'])) {
            return $cache['version'];
        }

        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->github_repo}/releases/latest",
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json',
                ),
            )
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['tag_name'])) {
            return false;
        }

        $version = ltrim($body['tag_name'], 'v');

        $cache_data = array(
            'version' => $version,
            'info' => $body,
        );

        set_transient($this->cache_key, $cache_data, 1 * HOUR_IN_SECONDS);

        return $version;
    }

    private function get_remote_info() {
        $cache = get_transient($this->cache_key);

        if ($this->cache_allowed && $cache !== false && isset($cache['info'])) {
            return $cache['info'];
        }

        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->github_repo}/releases/latest",
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json',
                ),
            )
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body;
    }

    private function get_download_url($version) {
        return "https://github.com/{$this->github_repo}/archive/refs/tags/v{$version}.zip";
    }

    private function get_tested_version() {
        global $wp_version;
        return $wp_version;
    }

    private function get_description() {
        return '<p><strong>WP URL Manager</strong> est un plugin WordPress de gestion des structures d\'URL et redirections 301 par type de contenu.</p>
        <h3>Fonctionnalités principales</h3>
        <ul>
            <li>✅ Définir des structures d\'URL personnalisées par type de contenu</li>
            <li>✅ Support des placeholders dynamiques (%postname%, %year%, {taxonomy:xxx}, etc.)</li>
            <li>✅ Génération automatique des permaliens</li>
            <li>✅ Redirections 301 intelligentes</li>
            <li>✅ Interface d\'administration moderne et intuitive</li>
            <li>✅ Validation en temps réel des patterns</li>
            <li>✅ Aucune dépendance tierce</li>
        </ul>';
    }

    private function get_installation() {
        return '<ol>
            <li>Téléchargez et installez le plugin</li>
            <li>Activez le plugin depuis l\'administration WordPress</li>
            <li>Accédez à <strong>URL Manager</strong> dans le menu admin</li>
            <li>Créez votre première règle d\'URL</li>
        </ol>';
    }

    private function get_changelog($remote_info) {
        if (empty($remote_info['body'])) {
            return '<p>Aucun changelog disponible.</p>';
        }

        return '<pre>' . esc_html($remote_info['body']) . '</pre>';
    }

    public function purge_cache($upgrader, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            delete_transient($this->cache_key);
        }
    }

    public function force_check_on_plugins_page() {
        if (isset($_GET['force-check']) && $_GET['force-check'] === 'wp-url-manager') {
            delete_transient($this->cache_key);
            delete_site_transient('update_plugins');
            wp_redirect(admin_url('plugins.php?force-check-done=1'));
            exit;
        }
    }

    public function maybe_force_check() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'plugins') {
            $cache = get_transient($this->cache_key);
            if ($cache === false) {
                delete_site_transient('update_plugins');
            }
        }
    }
}
