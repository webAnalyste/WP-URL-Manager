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

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WP URL Manager: Starting add_rewrite_rules() - Found ' . count($rules) . ' active rules');
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
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            global $wp_rewrite;
            error_log('WP URL Manager: Finished add_rewrite_rules() - Total WP rules: ' . count($wp_rewrite->rules));
        }
    }

    private function add_rule_rewrite($rule) {
        $pattern = $rule['target_pattern'];
        $post_type = $rule['post_type'];

        $regex = $this->pattern_to_regex($pattern);
        $query = $this->pattern_to_query($pattern, $post_type);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WP URL Manager: Processing rule - Pattern: {$pattern}, Post Type: {$post_type}");
            error_log("WP URL Manager: Generated - Regex: {$regex}, Query: {$query}");
        }

        if ($regex && $query) {
            add_rewrite_rule($regex, $query, 'top');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WP URL Manager: ✅ add_rewrite_rule() called successfully");
                
                global $wp_rewrite;
                if (isset($wp_rewrite->rules[$regex])) {
                    error_log("WP URL Manager: ✅ Rule confirmed in \$wp_rewrite->rules");
                } else {
                    error_log("WP URL Manager: ❌ Rule NOT found in \$wp_rewrite->rules immediately after add");
                }
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WP URL Manager: ❌ Skipped - Invalid regex or query");
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
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WP URL Manager: schedule_rewrite_flush() called - Flushing rewrite rules');
        }
        flush_rewrite_rules();
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WP URL Manager: Flush complete');
        }
    }

    public function flush_rewrite_rules_delayed() {
        flush_rewrite_rules();
    }
}
