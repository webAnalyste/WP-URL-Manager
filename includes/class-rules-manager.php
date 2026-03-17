<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Rules_Manager {

    const OPTION_NAME = 'wp_url_manager_rules';
    const OPTION_VERSION = 'wp_url_manager_rules_version';

    private $rules = array();

    public function __construct() {
        $this->load_rules();
    }

    private function load_rules() {
        $rules = get_option(self::OPTION_NAME, array());
        $this->rules = is_array($rules) ? $rules : array();
    }

    public function get_all_rules() {
        return $this->rules;
    }

    public function get_active_rules() {
        return array_filter($this->rules, function($rule) {
            return !empty($rule['active']);
        });
    }

    public function get_rule($rule_id) {
        return isset($this->rules[$rule_id]) ? $this->rules[$rule_id] : null;
    }

    public function get_rules_by_post_type($post_type) {
        return array_filter($this->get_active_rules(), function($rule) use ($post_type) {
            return $rule['post_type'] === $post_type;
        });
    }

    public function add_rule($rule_data) {
        $rule_id = $this->generate_rule_id();
        
        $rule = $this->sanitize_rule($rule_data);
        $rule['id'] = $rule_id;
        $rule['created_at'] = current_time('mysql');
        $rule['updated_at'] = current_time('mysql');
        
        $this->rules[$rule_id] = $rule;
        $this->save_rules();
        
        return $rule_id;
    }

    public function update_rule($rule_id, $rule_data) {
        if (!isset($this->rules[$rule_id])) {
            return false;
        }
        
        $rule = $this->sanitize_rule($rule_data);
        $rule['id'] = $rule_id;
        $rule['created_at'] = $this->rules[$rule_id]['created_at'];
        $rule['updated_at'] = current_time('mysql');
        
        $this->rules[$rule_id] = $rule;
        $this->save_rules();
        
        return true;
    }

    public function delete_rule($rule_id) {
        if (!isset($this->rules[$rule_id])) {
            return false;
        }
        
        unset($this->rules[$rule_id]);
        $this->save_rules();
        
        return true;
    }

    private function sanitize_rule($rule_data) {
        return array(
            'active' => !empty($rule_data['active']),
            'label' => sanitize_text_field($rule_data['label'] ?? ''),
            'post_type' => sanitize_key($rule_data['post_type'] ?? 'post'),
            'source_pattern' => sanitize_text_field($rule_data['source_pattern'] ?? ''),
            'target_pattern' => sanitize_text_field($rule_data['target_pattern'] ?? ''),
            'redirect_301' => !empty($rule_data['redirect_301']),
        );
    }

    private function save_rules() {
        update_option(self::OPTION_NAME, $this->rules);
        update_option(self::OPTION_VERSION, WP_URL_MANAGER_VERSION);
        
        do_action('wp_url_manager_rules_updated', $this->rules);
    }

    private function generate_rule_id() {
        return 'rule_' . uniqid() . '_' . time();
    }

    public function create_default_options() {
        if (false === get_option(self::OPTION_NAME)) {
            add_option(self::OPTION_NAME, array());
        }
        if (false === get_option(self::OPTION_VERSION)) {
            add_option(self::OPTION_VERSION, WP_URL_MANAGER_VERSION);
        }
    }

    public function rule_exists($rule_id) {
        return isset($this->rules[$rule_id]);
    }
}
