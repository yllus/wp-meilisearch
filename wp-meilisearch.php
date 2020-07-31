<?php
/*
 Plugin Name: MeiliSearch for WordPress
 Plugin URI: https://github.com/yllus/wp-meilisearch/
 Description: Automatically index posts from your WordPress site in your own MeiliSearch search engine, and display search results in a search bar and page.
 Author: Sully Syed
 Version: 1.0.0
 Author URI: http://yllus.com/
*/

function wp_meilisearch_admin_init() {
    wp_meilisearch_register_cross_post_settings();
}

function wp_meilisearch_plugins_loaded() {
    add_action('admin_init', 'wp_meilisearch_admin_init');
    add_action('admin_menu', 'wp_meilisearch_admin_menu');
}
add_action('plugins_loaded', 'wp_meilisearch_plugins_loaded');

function wp_meilisearch_admin_menu() {
    add_submenu_page("options-general.php", "MeiliSearch Settings", "MeiliSearch", 'manage_options', 'meilisearch_settings', 'wp_meilisearch_admin_menu_display');
}

function wp_meilisearch_admin_menu_display() { 
    // Get our public, not-excluded from search post types.
    $arr_post_types = get_post_types(array('public' => true, 'exclude_from_search' => false), 'objects');
    
    // Sort by the singular Name before we display the post types.
    usort($arr_post_types, 'wp_meilisearch_sort_post_type_array');

    // Retrieve the list of post types we've saved a setting for.
    $arr_post_types_selected = get_option('meilisearch_post_types');

    require_once('html/settings.php'); 
}

function wp_meilisearch_register_cross_post_settings() {
    register_setting('wp_meilisearch_options_group', 'meilisearch_post_types');
}

function wp_meilisearch_sort_post_type_array( $post_type_a, $post_type_b ) {
    return strcasecmp($post_type_a->labels->singular_name, $post_type_b->labels->singular_name);
}
?>