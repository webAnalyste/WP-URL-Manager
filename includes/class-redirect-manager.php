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

        $this->check_and_redirect($post);
    }

    private function check_and_redirect($post) {
        $rules = $this->rules_manager->get_rules_by_post_type($post->post_type);

        foreach ($rules as $rule) {
            if (empty($rule['redirect_301']) || empty($rule['source_pattern'])) {
                continue;
            }

            if ($this->should_redirect($post, $rule)) {
                $this->perform_redirect($post, $rule);
                break;
            }
        }
    }

    private function should_redirect($post, $rule) {
        $current_url = $this->get_current_url();
        $source_url = $this->build_source_url($post, $rule['source_pattern']);
        $target_url = $this->build_target_url($post, $rule['target_pattern']);

        if (empty($source_url) || empty($target_url)) {
            return false;
        }

        $current_path = parse_url($current_url, PHP_URL_PATH);
        $source_path = parse_url($source_url, PHP_URL_PATH);
        $target_path = parse_url($target_url, PHP_URL_PATH);

        $current_path = rtrim($current_path, '/');
        $source_path = rtrim($source_path, '/');
        $target_path = rtrim($target_path, '/');

        if ($current_path === $target_path) {
            return false;
        }

        return $current_path === $source_path;
    }

    private function perform_redirect($post, $rule) {
        $target_url = $this->build_target_url($post, $rule['target_pattern']);

        if (empty($target_url)) {
            return;
        }

        if ($this->would_create_loop($target_url)) {
            return;
        }

        wp_safe_redirect($target_url, 301);
        exit;
    }

    private function build_source_url($post, $pattern) {
        $url = WP_URL_Manager_Placeholder_Resolver::resolve_pattern($pattern, $post);
        return !empty($url) ? home_url($url) : '';
    }

    private function build_target_url($post, $pattern) {
        $url = WP_URL_Manager_Placeholder_Resolver::resolve_pattern($pattern, $post);
        return !empty($url) ? home_url($url) : '';
    }

    private function get_current_url() {
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function would_create_loop($target_url) {
        $current_url = $this->get_current_url();
        
        $current_path = rtrim(parse_url($current_url, PHP_URL_PATH), '/');
        $target_path = rtrim(parse_url($target_url, PHP_URL_PATH), '/');

        return $current_path === $target_path;
    }
}
