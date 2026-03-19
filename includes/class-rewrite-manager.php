<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Rewrite_Manager {

    private $rules_manager;

    public function __construct($rules_manager) {
        $this->rules_manager = $rules_manager;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('init', array($this, 'add_rewrite_rules'), 20);
        add_action('wp_url_manager_rules_updated', array($this, 'schedule_rewrite_flush'));
    }

    public function add_rewrite_rules() {
        $rules = $this->rules_manager->get_active_rules();

        // Log uniquement si des règles actives existent
        if (defined('WP_DEBUG') && WP_DEBUG && count($rules) > 0) {
            error_log('WP URL Manager: add_rewrite_rules() - Processing ' . count($rules) . ' active rule(s)');
        }

        foreach ($rules as $rule) {
            if (empty($rule['target_pattern'])) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('WP URL Manager: Skipping rule (empty target_pattern): ' . print_r($rule, true));
                }
                continue;
            }

            $this->add_rule_rewrite($rule);
        }
        
        // Logs réduits pour éviter le spam
    }

    private function add_rule_rewrite($rule) {
        $pattern = $rule['target_pattern'];
        $post_type = $rule['post_type'];

        $regex = $this->pattern_to_regex($pattern);
        $query = $this->pattern_to_query($pattern, $post_type);

        if ($regex && $query) {
            add_rewrite_rule($regex, $query, 'top');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WP URL Manager: ✅ Rewrite rule added - {$regex} => {$query}");
            }
        }
    }

    private function pattern_to_regex($pattern) {
        $pattern = trim($pattern, '/');
        
        $regex = preg_replace('/%postname%/', '([^/]+)', $pattern);
        $regex = preg_replace('/%post_id%/', '([0-9]+)', $pattern);
        $regex = preg_replace('/%year%/', '([0-9]{4})', $regex);
        $regex = preg_replace('/%monthnum%/', '([0-9]{1,2})', $regex);
        $regex = preg_replace('/%day%/', '([0-9]{1,2})', $regex);
        $regex = preg_replace('/%post_type%/', '([^/]+)', $regex);
        $regex = preg_replace('/%author%/', '([^/]+)', $regex);
        $regex = preg_replace('/%parent_postname%/', '([^/]+)', $regex);
        $regex = preg_replace('/\{taxonomy:[a-z_]+\}/', '([^/]+)', $regex);

        $regex = '^' . $regex . '/?$';

        return $regex;
    }

    private function pattern_to_query($pattern, $post_type) {
        $query_parts = array();
        $match_index = 1;
        
        $parts = explode('/', trim($pattern, '/'));
        
        foreach ($parts as $part) {
            if ($part === '%postname%') {
                $query_parts[] = 'name=$matches[' . $match_index . ']';
                $match_index++;
            } elseif ($part === '%post_id%') {
                $query_parts[] = 'p=$matches[' . $match_index . ']';
                $match_index++;
            } elseif (preg_match('/%[a-z_]+%|\{taxonomy:[a-z_]+\}/', $part)) {
                $match_index++;
            }
        }

        $query_parts[] = 'post_type=' . $post_type;

        return 'index.php?' . implode('&', $query_parts);
    }

    public function schedule_rewrite_flush() {
        flush_rewrite_rules();
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WP URL Manager: ✅ Rewrite rules flushed');
        }
    }

    public function flush_rewrite_rules_delayed() {
        flush_rewrite_rules();
    }
}
