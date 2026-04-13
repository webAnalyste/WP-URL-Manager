<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Admin_Interface {

    private $rules_manager;

    public function __construct($rules_manager) {
        $this->rules_manager = $rules_manager;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_wp_url_manager_save_rule', array($this, 'ajax_save_rule'));
        add_action('wp_ajax_wp_url_manager_delete_rule', array($this, 'ajax_delete_rule'));
        add_action('wp_ajax_wp_url_manager_validate_pattern', array($this, 'ajax_validate_pattern'));
        add_action('wp_ajax_wp_url_manager_preview_url', array($this, 'ajax_preview_url'));
        add_action('wp_ajax_wp_url_manager_toggle_rule', array($this, 'ajax_toggle_rule'));
        add_action('wp_ajax_wp_url_manager_import_rules', array($this, 'ajax_import_rules'));
        add_action('wp_ajax_wp_url_manager_purge_data', array($this, 'ajax_purge_data'));
        add_action('admin_post_wp_url_manager_export_rules', array($this, 'handle_export_rules'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('WP URL Manager', 'wp-url-manager'),
            __('URL Manager', 'wp-url-manager'),
            'manage_options',
            'wp-url-manager',
            array($this, 'render_main_page'),
            'dashicons-admin-links',
            30
        );

        add_submenu_page(
            'wp-url-manager',
            __('Vérifier les mises à jour', 'wp-url-manager'),
            __('Mises à jour', 'wp-url-manager'),
            'manage_options',
            'wp-url-manager-updates',
            array($this, 'render_updates_page')
        );
        
        WP_URL_Manager_Debug_Helper::add_debug_page();
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-url-manager') === false) {
            return;
        }

        wp_enqueue_style(
            'wp-url-manager-admin',
            WP_URL_MANAGER_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            WP_URL_MANAGER_VERSION
        );
        
        wp_enqueue_style(
            'wp-url-manager-admin-v2',
            WP_URL_MANAGER_PLUGIN_URL . 'admin/css/admin-style-v2.css',
            array('wp-url-manager-admin'),
            WP_URL_MANAGER_VERSION
        );

        wp_enqueue_script(
            'wp-url-manager-admin',
            WP_URL_MANAGER_PLUGIN_URL . 'admin/js/admin-script-v2.js',
            array('jquery'),
            WP_URL_MANAGER_VERSION,
            true
        );

        wp_localize_script('wp-url-manager-admin', 'wpUrlManager', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_url_manager_nonce'),
            'i18n' => array(
                'confirmDelete' => __('Êtes-vous sûr de vouloir supprimer cette règle ?', 'wp-url-manager'),
                'saving' => __('Enregistrement...', 'wp-url-manager'),
                'saved' => __('Règle enregistrée avec succès', 'wp-url-manager'),
                'error' => __('Une erreur est survenue', 'wp-url-manager'),
                'validating' => __('Validation...', 'wp-url-manager'),
                'valid' => __('Pattern valide', 'wp-url-manager'),
                'invalid' => __('Pattern invalide', 'wp-url-manager'),
            ),
        ));
    }

    public function render_main_page() {
        $rules = $this->rules_manager->get_all_rules();
        $post_types = $this->get_available_post_types();
        
        include WP_URL_MANAGER_PLUGIN_DIR . 'admin/views/main-page.php';
    }

    public function render_updates_page() {
        $current_version = WP_URL_MANAGER_VERSION;
        $cache_key = 'wp_url_manager_update_cache';
        $cache = get_transient($cache_key);
        $last_check = $cache ? __('Moins d\'1 heure', 'wp-url-manager') : __('Jamais', 'wp-url-manager');
        
        if (isset($_GET['check-now']) && $_GET['check-now'] === '1') {
            delete_transient($cache_key);
            delete_site_transient('update_plugins');
            $cache = false;
            $last_check = __('À l\'instant', 'wp-url-manager');
        }

        $response = wp_remote_get(
            'https://api.github.com/repos/webAnalyste/WP-URL-Manager/releases/latest',
            array('timeout' => 10, 'headers' => array('Accept' => 'application/vnd.github.v3+json'))
        );

        $remote_version = null;
        $update_available = false;
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($body['tag_name'])) {
                $remote_version = ltrim($body['tag_name'], 'v');
                $update_available = version_compare($current_version, $remote_version, '<');
            }
        }

        include WP_URL_MANAGER_PLUGIN_DIR . 'admin/views/updates-page.php';
    }

    private function get_available_post_types() {
        $post_types = get_post_types(array('public' => true), 'objects');
        
        unset($post_types['attachment']);
        
        return $post_types;
    }

    public function ajax_save_rule() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'wp-url-manager')));
        }

        $rule_id = isset($_POST['rule_id']) ? sanitize_text_field($_POST['rule_id']) : '';
        $rule_data = array(
            'active' => (isset($_POST['active']) && $_POST['active'] === 'true') ? 1 : 0,
            'label' => sanitize_text_field($_POST['label'] ?? ''),
            'post_type' => sanitize_key($_POST['post_type'] ?? 'post'),
            'source_pattern' => sanitize_text_field($_POST['source_pattern'] ?? ''),
            'target_pattern' => sanitize_text_field($_POST['target_pattern'] ?? ''),
            'redirect_301' => (isset($_POST['redirect_301']) && $_POST['redirect_301'] === 'true') ? 1 : 0,
        );

        $validation = WP_URL_Manager_Pattern_Validator::validate_pattern(
            $rule_data['target_pattern'],
            $rule_data['post_type']
        );

        if (!$validation['valid']) {
            wp_send_json_error(array(
                'message' => __('Pattern invalide', 'wp-url-manager'),
                'errors' => $validation['errors'],
            ));
        }

        if (!empty($rule_data['source_pattern'])) {
            $source_validation = WP_URL_Manager_Pattern_Validator::validate_pattern(
                $rule_data['source_pattern'],
                $rule_data['post_type']
            );

            if (!$source_validation['valid']) {
                wp_send_json_error(array(
                    'message' => __('Pattern source invalide', 'wp-url-manager'),
                    'errors' => $source_validation['errors'],
                ));
            }
        }

        if (empty($rule_id)) {
            $rule_id = $this->rules_manager->add_rule($rule_data);
            $message = __('Règle créée avec succès', 'wp-url-manager');
        } else {
            $this->rules_manager->update_rule($rule_id, $rule_data);
            $message = __('Règle mise à jour avec succès', 'wp-url-manager');
        }

        wp_send_json_success(array(
            'message' => $message,
            'rule_id' => $rule_id,
        ));
    }

    public function ajax_delete_rule() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'wp-url-manager')));
        }

        $rule_id = isset($_POST['rule_id']) ? sanitize_text_field($_POST['rule_id']) : '';

        if (empty($rule_id)) {
            wp_send_json_error(array('message' => __('ID de règle manquant', 'wp-url-manager')));
        }

        $result = $this->rules_manager->delete_rule($rule_id);

        if ($result) {
            wp_send_json_success(array('message' => __('Règle supprimée avec succès', 'wp-url-manager')));
        } else {
            wp_send_json_error(array('message' => __('Impossible de supprimer la règle', 'wp-url-manager')));
        }
    }

    public function ajax_validate_pattern() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        $pattern = isset($_POST['pattern']) ? sanitize_text_field($_POST['pattern']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : 'post';

        $validation = WP_URL_Manager_Pattern_Validator::validate_pattern($pattern, $post_type);

        wp_send_json_success($validation);
    }

    public function ajax_preview_url() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        $pattern = isset($_POST['pattern']) ? sanitize_text_field($_POST['pattern']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : 'post';

        $preview = WP_URL_Manager_Placeholder_Resolver::preview_url($pattern, $post_type);

        wp_send_json_success(array('preview' => $preview));
    }

    public function ajax_toggle_rule() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'wp-url-manager')));
        }

        $rule_id = isset($_POST['rule_id']) ? sanitize_text_field($_POST['rule_id']) : '';
        $active = isset($_POST['active']) && $_POST['active'] === 'true';

        if (empty($rule_id)) {
            wp_send_json_error(array('message' => __('ID de règle manquant', 'wp-url-manager')));
        }

        $rule = $this->rules_manager->get_rule($rule_id);

        if (!$rule) {
            wp_send_json_error(array('message' => __('Règle introuvable', 'wp-url-manager')));
        }

        $rule['active'] = $active;
        $this->rules_manager->update_rule($rule_id, $rule);

        wp_send_json_success(array('message' => __('Statut mis à jour', 'wp-url-manager')));
    }

    public function handle_export_rules() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission refusée', 'wp-url-manager'));
        }

        check_admin_referer('wp_url_manager_export');

        $rules = $this->rules_manager->get_all_rules();
        $export = array(
            'version' => WP_URL_MANAGER_VERSION,
            'exported_at' => current_time('c'),
            'rules' => $rules,
        );

        $filename = 'wp-url-manager-rules-' . date('Y-m-d') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        echo wp_json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function ajax_import_rules() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'wp-url-manager')));
        }

        if (empty($_FILES['import_file']['tmp_name'])) {
            wp_send_json_error(array('message' => __('Aucun fichier reçu', 'wp-url-manager')));
        }

        $raw = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data['rules']) || !is_array($data['rules'])) {
            wp_send_json_error(array('message' => __('Fichier JSON invalide', 'wp-url-manager')));
        }

        $imported = 0;
        foreach ($data['rules'] as $rule) {
            if (empty($rule['target_pattern'])) {
                continue;
            }
            $this->rules_manager->add_rule($rule);
            $imported++;
        }

        flush_rewrite_rules();

        wp_send_json_success(array(
            'message' => sprintf(__('%d règle(s) importée(s) avec succès', 'wp-url-manager'), $imported),
        ));
    }

    public function ajax_purge_data() {
        check_ajax_referer('wp_url_manager_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'wp-url-manager')));
        }

        delete_option('wp_url_manager_rules');
        delete_option('wp_url_manager_rules_version');
        delete_transient('wp_url_manager_update_cache');
        flush_rewrite_rules();

        wp_send_json_success(array('message' => __('Toutes les données ont été supprimées', 'wp-url-manager')));
    }
}
