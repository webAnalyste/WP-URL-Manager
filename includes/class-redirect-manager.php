<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Redirect_Manager {

    private $rules_manager;

    public function __construct($rules_manager) {
        $this->rules_manager = $rules_manager;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('template_redirect', array($this, 'handle_redirects'), 1);
    }

    public function handle_redirects() {
        if (is_admin() || is_404() || is_search() || is_archive() || is_home()) {
            return;
        }

        if (!is_singular()) {
            return;
        }

        global $post;

        if (!$post) {
            return;
        }

        $rules = $this->rules_manager->get_rules_by_post_type($post->post_type);

        foreach ($rules as $rule) {
            if (empty($rule['redirect_301']) || !$rule['active']) {
                continue;
            }

            $target_url = $this->build_target_url($post, $rule['target_pattern']);
            
            if (empty($target_url)) {
                continue;
            }

            $current_url = $this->get_current_url();
            
            $current_path = rtrim(parse_url($current_url, PHP_URL_PATH), '/');
            $target_path = rtrim(parse_url($target_url, PHP_URL_PATH), '/');

            if ($current_path !== $target_path) {
                wp_safe_redirect($target_url, 301);
                exit;
            }
        }
    }

    private function build_target_url($post, $pattern) {
        $url = WP_URL_Manager_Placeholder_Resolver::resolve_pattern($pattern, $post);
        return !empty($url) ? home_url($url) : '';
    }

    private function get_current_url() {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}
