<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Pattern_Validator {

    private static $allowed_placeholders = array(
        '%postname%',
        '%year%',
        '%monthnum%',
        '%day%',
        '%post_id%',
        '%post_type%',
        '%author%',
        '%parent_postname%',
    );

    public static function validate_pattern($pattern, $post_type = '') {
        $errors = array();

        if (empty($pattern)) {
            $errors[] = __('Le pattern ne peut pas être vide.', 'wp-url-manager');
            return array('valid' => false, 'errors' => $errors);
        }

        if (!self::starts_with_slash($pattern)) {
            $errors[] = __('Le pattern doit commencer par un slash (/).', 'wp-url-manager');
        }

        if (!self::ends_with_slash($pattern)) {
            $errors[] = __('Le pattern doit se terminer par un slash (/).', 'wp-url-manager');
        }

        if (!self::has_content_identifier($pattern)) {
            $errors[] = __('Le pattern doit contenir au moins un identifiant de contenu (%postname%, %post_id%, etc.).', 'wp-url-manager');
        }

        $invalid_chars = self::check_invalid_characters($pattern);
        if (!empty($invalid_chars)) {
            $errors[] = sprintf(__('Caractères invalides détectés : %s', 'wp-url-manager'), implode(', ', $invalid_chars));
        }

        $invalid_placeholders = self::check_placeholders($pattern);
        if (!empty($invalid_placeholders)) {
            $errors[] = sprintf(__('Placeholders invalides : %s', 'wp-url-manager'), implode(', ', $invalid_placeholders));
        }

        $taxonomy_errors = self::validate_taxonomies($pattern, $post_type);
        if (!empty($taxonomy_errors)) {
            $errors = array_merge($errors, $taxonomy_errors);
        }

        return array(
            'valid' => empty($errors),
            'errors' => $errors,
        );
    }

    private static function starts_with_slash($pattern) {
        return strpos($pattern, '/') === 0;
    }

    private static function ends_with_slash($pattern) {
        return substr($pattern, -1) === '/';
    }

    private static function has_content_identifier($pattern) {
        $identifiers = array('%postname%', '%post_id%');
        foreach ($identifiers as $identifier) {
            if (strpos($pattern, $identifier) !== false) {
                return true;
            }
        }
        return false;
    }

    private static function check_invalid_characters($pattern) {
        $invalid_chars = array();
        
        if (preg_match('/[^a-zA-Z0-9\-_\/%{}:]/u', $pattern, $matches)) {
            $invalid_chars = array_unique($matches);
        }
        
        return $invalid_chars;
    }

    private static function check_placeholders($pattern) {
        $invalid = array();
        
        preg_match_all('/%([a-z_]+)%/', $pattern, $matches);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $placeholder) {
                if (!in_array($placeholder, self::$allowed_placeholders)) {
                    $invalid[] = $placeholder;
                }
            }
        }
        
        return $invalid;
    }

    private static function validate_taxonomies($pattern, $post_type) {
        $errors = array();
        
        preg_match_all('/\{taxonomy:([a-z_]+)\}/', $pattern, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $taxonomy) {
                if (!taxonomy_exists($taxonomy)) {
                    $errors[] = sprintf(__('La taxonomie "%s" n\'existe pas.', 'wp-url-manager'), $taxonomy);
                } elseif (!empty($post_type) && !is_object_in_taxonomy($post_type, $taxonomy)) {
                    $errors[] = sprintf(__('La taxonomie "%s" n\'est pas associée au type de contenu "%s".', 'wp-url-manager'), $taxonomy, $post_type);
                }
            }
        }
        
        return $errors;
    }

    public static function get_allowed_placeholders() {
        return self::$allowed_placeholders;
    }

    public static function check_pattern_conflict($pattern, $existing_patterns = array()) {
        $conflicts = array();
        
        foreach ($existing_patterns as $existing) {
            if (self::patterns_conflict($pattern, $existing)) {
                $conflicts[] = $existing;
            }
        }
        
        return $conflicts;
    }

    private static function patterns_conflict($pattern1, $pattern2) {
        $regex1 = self::pattern_to_regex($pattern1);
        $regex2 = self::pattern_to_regex($pattern2);
        
        return $regex1 === $regex2;
    }

    private static function pattern_to_regex($pattern) {
        $regex = preg_replace('/%[a-z_]+%/', '([^/]+)', $pattern);
        $regex = preg_replace('/\{taxonomy:[a-z_]+\}/', '([^/]+)', $regex);
        return $regex;
    }
}
