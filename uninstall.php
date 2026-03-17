<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('wp_url_manager_rules');
delete_option('wp_url_manager_rules_version');

flush_rewrite_rules();
