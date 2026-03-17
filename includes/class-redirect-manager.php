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
        add_action('parse_request', array($this, 'check_legacy_urls'), 1);
        add_action('template_redirect', array($this, 'handle_redirects'), 1);
    }

    public function check_legacy_urls($wp) {
        if (is_admin()) {
            return;
        }

        $request_path = '/' . ltrim($wp->request, '/');
        if (empty($request_path) || $request_path === '/') {
            return;
        }

        $rules = $this->rules_manager->get_active_rules();

        foreach ($rules as $rule) {
            if (empty($rule['redirect_301']) || empty($rule['source_pattern'])) {
                continue;
            }

            $matched_post = $this->match_legacy_url($request_path, $rule);
            
            if ($matched_post) {
                $target_url = $this->build_target_url($matched_post, $rule['target_pattern']);
                
                if ($target_url && !$this->would_create_loop($target_url)) {
                    wp_safe_redirect($target_url, 301);
                    exit;
                }
            }
        }
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

    private function match_legacy_url($request_path, $rule) {
        $source_pattern = $rule['source_pattern'];
        $post_type = $rule['post_type'];

        $regex = $this->pattern_to_regex($source_pattern);
        $request_path_clean = '/' . trim($request_path, '/') . '/';

        if (preg_match($regex, $request_path_clean, $matches)) {
            $post_slug = null;
            $post_id = null;

            if (strpos($source_pattern, '%postname%') !== false) {
                $placeholder_index = $this->get_placeholder_index($source_pattern, '%postname%');
                $post_slug = isset($matches[$placeholder_index]) ? $matches[$placeholder_index] : null;
            } elseif (strpos($source_pattern, '%post_id%') !== false) {
                $placeholder_index = $this->get_placeholder_index($source_pattern, '%post_id%');
                $post_id = isset($matches[$placeholder_index]) ? intval($matches[$placeholder_index]) : null;
            }

            if ($post_slug) {
                $posts = get_posts(array(
                    'name' => $post_slug,
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                ));
                return !empty($posts) ? $posts[0] : null;
            } elseif ($post_id) {
                $post = get_post($post_id);
                return ($post && $post->post_type === $post_type && $post->post_status === 'publish') ? $post : null;
            }
        }

        return null;
    }

    private function pattern_to_regex($pattern) {
        $pattern = trim($pattern, '/');
        
        $regex = preg_replace('/%postname%/', '([^/]+)', $pattern);
        $regex = preg_replace('/%post_id%/', '([0-9]+)', $regex);
        $regex = preg_replace('/%year%/', '([0-9]{4})', $regex);
        $regex = preg_replace('/%monthnum%/', '([0-9]{1,2})', $regex);
        $regex = preg_replace('/%day%/', '([0-9]{1,2})', $regex);
        $regex = preg_replace('/%post_type%/', '([^/]+)', $regex);
        $regex = preg_replace('/%author%/', '([^/]+)', $regex);
        $regex = preg_replace('/%parent_postname%/', '([^/]+)', $regex);
        $regex = preg_replace('/\{taxonomy:[a-z_]+\}/', '([^/]+)', $regex);

        $regex = '#^/' . $regex . '/?$#';

        return $regex;
    }

    private function get_placeholder_index($pattern, $placeholder) {
        $parts = explode('/', trim($pattern, '/'));
        $index = 1;

        foreach ($parts as $part) {
            if (preg_match('/%[a-z_]+%|\{taxonomy:[a-z_]+\}/', $part)) {
                if ($part === $placeholder) {
                    return $index;
                }
                $index++;
            }
        }

        return 1;
    }
}
