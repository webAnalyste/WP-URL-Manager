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
        add_action('parse_request', array($this, 'check_legacy_url'), 1);
    }

    public function check_legacy_url($wp) {
        if (is_admin()) {
            return;
        }

        $request_path = trim($wp->request, '/');
        
        if (empty($request_path)) {
            return;
        }

        // Exclure les requêtes API REST, admin, uploads, etc.
        $excluded_paths = array('wp-json', 'wp-admin', 'wp-content', 'wp-includes', 'xmlrpc.php');
        foreach ($excluded_paths as $excluded) {
            if (strpos($request_path, $excluded) === 0) {
                return;
            }
        }

        $rules = $this->rules_manager->get_active_rules();

        foreach ($rules as $rule) {
            if (empty($rule['redirect_301'])) {
                continue;
            }

            $source_pattern = !empty($rule['source_pattern']) ? $rule['source_pattern'] : '';
            
            if (empty($source_pattern)) {
                continue;
            }

            $post = $this->find_post_by_legacy_url($request_path, $source_pattern, $rule['post_type']);
            
            if (!$post) {
                continue;
            }

            $target_url = $this->build_target_url($post, $rule['target_pattern']);
            
            if (empty($target_url)) {
                continue;
            }

            $current_url = home_url('/' . $request_path . '/');
            $current_path = '/' . trim($request_path, '/') . '/';
            $target_path = parse_url($target_url, PHP_URL_PATH);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WP URL Manager: Found post #{$post->ID} ({$post->post_name})");
                error_log("WP URL Manager: Current path: {$current_path}");
                error_log("WP URL Manager: Target path: {$target_path}");
            }

            if ($current_path !== $target_path) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("WP URL Manager: ✅ Redirecting 301 from {$current_path} to {$target_url}");
                }
                wp_safe_redirect($target_url, 301);
                exit;
            }
        }
    }

    public function handle_redirects() {
        if (is_admin() || is_search() || is_archive() || is_home()) {
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

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WP URL Manager: handle_redirects() - Current={$current_path}, Target={$target_path}");
            }

            if ($current_path !== $target_path) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("WP URL Manager: ✅ Redirecting 301 to {$target_url}");
                }
                wp_safe_redirect($target_url, 301);
                exit;
            }
        }
    }

    private function find_post_by_legacy_url($request_path, $source_pattern, $post_type) {
        $slug = $this->extract_slug_from_url($request_path, $source_pattern);
        
        if (empty($slug)) {
            return null;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WP URL Manager: Extracted slug: {$slug} from {$request_path}");
        }

        $args = array(
            'name' => $slug,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        );

        $posts = get_posts($args);

        return !empty($posts) ? $posts[0] : null;
    }

    private function extract_slug_from_url($url, $pattern) {
        $url = trim($url, '/');
        $pattern = trim($pattern, '/');
        
        $regex = preg_replace('/%postname%/', '([^/]+)', $pattern);
        $regex = preg_replace('/%post_id%/', '([0-9]+)', $regex);
        $regex = preg_replace('/%[a-z_]+%/', '[^/]+', $regex);
        $regex = preg_replace('/\{taxonomy:[a-z_]+\}/', '[^/]+', $regex);
        
        $regex = '/^' . str_replace('/', '\/', $regex) . '$/i';
        
        if (preg_match($regex, $url, $matches)) {
            return isset($matches[1]) ? $matches[1] : null;
        }
        
        return null;
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
