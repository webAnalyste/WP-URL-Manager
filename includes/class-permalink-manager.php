<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Permalink_Manager {

    private $rules_manager;

    public function __construct($rules_manager) {
        $this->rules_manager = $rules_manager;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_filter('post_link', array($this, 'filter_post_link'), 10, 2);
        add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);
        add_filter('get_canonical_url', array($this, 'filter_canonical_url'), 10, 2);
        add_filter('wpseo_canonical', array($this, 'filter_yoast_canonical'), 20);
        add_action('wp_head', array($this, 'debug_yoast_hooks'), 1);
    }

    public function filter_post_link($permalink, $post) {
        if (!$post || is_wp_error($post)) {
            return $permalink;
        }

        return $this->generate_permalink($post, $permalink);
    }

    public function filter_post_type_link($permalink, $post) {
        if (!$post || is_wp_error($post)) {
            return $permalink;
        }

        return $this->generate_permalink($post, $permalink);
    }

    public function filter_canonical_url($canonical_url, $post) {
        if (!$post || is_wp_error($post)) {
            return $canonical_url;
        }

        return $this->generate_permalink($post, $canonical_url);
    }

    public function filter_yoast_canonical($canonical_url) {
        $post = get_queried_object();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $post_info = $post instanceof WP_Post ? "post #{$post->ID} ({$post->post_name})" : gettype($post);
            error_log("WP URL Manager [wpseo_canonical]: canonical_in=" . var_export($canonical_url, true) . " | queried_object=" . $post_info);
        }

        if (!$post instanceof WP_Post) {
            return $canonical_url;
        }

        $result = $this->generate_permalink($post, $canonical_url);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WP URL Manager [wpseo_canonical]: canonical_out=" . var_export($result, true));
        }

        return $result;
    }

    private function generate_permalink($post, $default_permalink) {
        if ($this->should_skip_permalink_generation($post)) {
            return $default_permalink;
        }

        $rules = $this->rules_manager->get_rules_by_post_type($post->post_type);

        if (empty($rules)) {
            return $default_permalink;
        }

        $rule = reset($rules);

        if (empty($rule['target_pattern'])) {
            return $default_permalink;
        }

        $new_url = WP_URL_Manager_Placeholder_Resolver::resolve_pattern(
            $rule['target_pattern'],
            $post
        );

        if (empty($new_url)) {
            return $default_permalink;
        }

        return home_url($new_url);
    }

    private function should_skip_permalink_generation($post) {
        if (is_admin() && !wp_doing_ajax()) {
            return false;
        }

        if (defined('DOING_CRON') && DOING_CRON) {
            return true;
        }

        if ($post->post_status !== 'publish') {
            return false;
        }

        return false;
    }

    public function debug_yoast_hooks() {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        global $wp_filter;
        $hooks_to_check = array('wpseo_canonical', 'wpseo_html_canonical', 'wpseo_head', 'Yoast\WP\SEO\Presenters\Canonical_Presenter');
        foreach ($hooks_to_check as $hook) {
            $exists = isset($wp_filter[$hook]) ? 'EXISTS' : 'NOT FOUND';
            error_log("WP URL Manager [debug_hooks]: hook '{$hook}' => {$exists}");
        }
    }

    public function get_original_permalink($post_id) {
        remove_filter('post_link', array($this, 'filter_post_link'), 10);
        remove_filter('post_type_link', array($this, 'filter_post_type_link'), 10);

        $permalink = get_permalink($post_id);

        add_filter('post_link', array($this, 'filter_post_link'), 10, 2);
        add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);

        return $permalink;
    }
}
