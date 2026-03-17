<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_URL_Manager_Placeholder_Resolver {

    public static function resolve_pattern($pattern, $post) {
        if (!$post || !is_object($post)) {
            return '';
        }

        $url = $pattern;

        $url = self::resolve_standard_placeholders($url, $post);
        $url = self::resolve_taxonomy_placeholders($url, $post);

        return $url;
    }

    private static function resolve_standard_placeholders($url, $post) {
        $replacements = array(
            '%postname%' => $post->post_name,
            '%post_id%' => $post->ID,
            '%post_type%' => $post->post_type,
            '%year%' => get_the_date('Y', $post),
            '%monthnum%' => get_the_date('m', $post),
            '%day%' => get_the_date('d', $post),
            '%author%' => self::get_author_slug($post),
            '%parent_postname%' => self::get_parent_slug($post),
        );

        foreach ($replacements as $placeholder => $value) {
            if (strpos($url, $placeholder) !== false) {
                $url = str_replace($placeholder, $value, $url);
            }
        }

        return $url;
    }

    private static function resolve_taxonomy_placeholders($url, $post) {
        preg_match_all('/\{taxonomy:([a-z_]+)\}/', $url, $matches);

        if (empty($matches[1])) {
            return $url;
        }

        foreach ($matches[1] as $index => $taxonomy) {
            $term_slug = self::get_taxonomy_term_slug($post->ID, $taxonomy);
            $url = str_replace($matches[0][$index], $term_slug, $url);
        }

        return $url;
    }

    private static function get_taxonomy_term_slug($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return apply_filters('wp_url_manager_taxonomy_fallback', 'uncategorized', $taxonomy, $post_id);
        }

        $term = is_array($terms) ? reset($terms) : $terms;
        
        return $term->slug;
    }

    private static function get_author_slug($post) {
        $author = get_userdata($post->post_author);
        
        if (!$author) {
            return 'author';
        }

        return sanitize_title($author->user_nicename);
    }

    private static function get_parent_slug($post) {
        if (empty($post->post_parent)) {
            return '';
        }

        $parent = get_post($post->post_parent);
        
        if (!$parent) {
            return '';
        }

        return $parent->post_name;
    }

    public static function extract_placeholders($pattern) {
        $placeholders = array();

        preg_match_all('/%([a-z_]+)%/', $pattern, $matches);
        if (!empty($matches[0])) {
            $placeholders = array_merge($placeholders, $matches[0]);
        }

        preg_match_all('/\{taxonomy:([a-z_]+)\}/', $pattern, $matches);
        if (!empty($matches[0])) {
            $placeholders = array_merge($placeholders, $matches[0]);
        }

        return array_unique($placeholders);
    }

    public static function preview_url($pattern, $post_type = 'post') {
        $sample_post = self::get_sample_post($post_type);
        
        if (!$sample_post) {
            return self::get_generic_preview($pattern);
        }

        return self::resolve_pattern($pattern, $sample_post);
    }

    private static function get_sample_post($post_type) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_status' => 'publish',
        ));

        return !empty($posts) ? $posts[0] : null;
    }

    private static function get_generic_preview($pattern) {
        $preview = $pattern;
        
        $generic_replacements = array(
            '%postname%' => 'mon-article',
            '%post_id%' => '123',
            '%post_type%' => 'post',
            '%year%' => date('Y'),
            '%monthnum%' => date('m'),
            '%day%' => date('d'),
            '%author%' => 'auteur',
            '%parent_postname%' => 'parent',
        );

        foreach ($generic_replacements as $placeholder => $value) {
            $preview = str_replace($placeholder, $value, $preview);
        }

        $preview = preg_replace('/\{taxonomy:([a-z_]+)\}/', 'categorie', $preview);

        return $preview;
    }
}
