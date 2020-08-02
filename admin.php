<?php
function wp_meilisearch_admin_init() {
    wp_meilisearch_register_cross_post_settings();
}

function wp_meilisearch_plugins_loaded() {
    add_action('admin_init', 'wp_meilisearch_admin_init');
    add_action('admin_menu', 'wp_meilisearch_admin_menu');

    add_action('admin_enqueue_scripts', 'wp_meilisearch_admin_enqueue_scripts');
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
    $arr_post_types_selected = get_option('meilisearch_post_types', array());

    // Get a good default name for an index for the placeholder of that field.
    $arr_url = parse_url(home_url());
    $str_default_index_name = wp_meilisearch_get_name_as_slug($arr_url['host']);

    // Retrieve our current values.
    $str_wp_meilisearch_url = get_option('wp_meilisearch_url', '');
    $str_wp_meilisearch_index = get_option('wp_meilisearch_index', '');
    $str_wp_meilisearch_master = get_option('wp_meilisearch_master', '');
    $str_wp_meilisearch_public = get_option('wp_meilisearch_public', '');
    $str_wp_meilisearch_page_slug = get_option('wp_meilisearch_page_slug', '');

    // Actually display the admin page.
    require_once('html/settings.php'); 
}

// Instruct WordPress to keep our needed settings as 'options'.
function wp_meilisearch_register_cross_post_settings() {
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_url');
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_index');
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_master');
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_public');
    register_setting('wp_meilisearch_options_group', 'meilisearch_post_types');
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_page_slug');
}

function wp_meilisearch_sort_post_type_array( $post_type_a, $post_type_b ) {
    return strcasecmp($post_type_a->labels->singular_name, $post_type_b->labels->singular_name);
}

function wp_meilisearch_admin_enqueue_scripts() {
    wp_enqueue_script('jquery');
}

function wp_meilisearch_get_name_as_slug( $name ) {
    setlocale(LC_ALL, 'en_US.UTF8');

    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
    $name = preg_replace("/[^a-z0-9]+/i", "_", preg_replace("/[ \.']+/i", "_", strtolower($name)));

    return $name;
}

?>