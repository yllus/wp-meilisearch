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

    // Retrieve the list of post types priorities numbers/levels that have been set.
    $arr_post_types_priority = get_option('meilisearch_post_type_priority', array());

    // Retrieve the list of result group names that have been set.
    $arr_group_names = get_option('meilisearch_group_names', array());

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
    register_setting('wp_meilisearch_options_group', 'meilisearch_post_type_priority');
    register_setting('wp_meilisearch_options_group', 'meilisearch_group_names');
    register_setting('wp_meilisearch_options_group', 'wp_meilisearch_page_slug');
}

function wp_meilisearch_get_page_status_ajax() {
    $obj_response = new stdClass();
    $obj_response->page_exists = 0;
    $obj_response->page_is_published = 0;
    $obj_response->page_shortcode_input_exists = 0;
    $obj_response->page_shortcode_results_exists = 0;

    $str_page_slug = $_GET['page_slug'];

    // Check to see if a Page at the given slug exists.
    $args = array(
        'name'        => $str_page_slug,
        'post_type'   => 'page',
        'numberposts' => 1,
    );
    $arr_posts = get_posts($args);

    // If no Page exists, return that response.
    if ( empty($arr_posts) ) {
        $obj_response->page_exists = 0;

        echo json_encode($obj_response);
        exit;
    }
    else {
        $obj_response->page_exists = 1;
    }

    // If the Page isn't published, send that info back.
    if ( $arr_posts[0]->post_status != 'publish' ) {
        $obj_response->page_exists = 1;
        $obj_response->page_is_published = 0;

        echo json_encode($obj_response);
        exit;
    }
    else {
        $obj_response->page_is_published = 1;
    }

    // Check to see if the [wp_meilisearch_input] shortcode is in the post body.
    if ( strpos($arr_posts[0]->post_content, '[wp_meilisearch_input]') !== false ) {
        $obj_response->page_shortcode_input_exists = 1;
    }

    // Check to see if the [wp_meilisearch_results] shortcode is in the post body.
    if ( strpos($arr_posts[0]->post_content, '[wp_meilisearch_results]') !== false ) {
        $obj_response->page_shortcode_results_exists = 1;
    }

    echo json_encode($obj_response);
    exit;
}
add_action( 'wp_ajax_wp_meilisearch_get_page_status', 'wp_meilisearch_get_page_status_ajax' );

function wp_meilisearch_sort_post_type_array( $post_type_a, $post_type_b ) {
    return strcasecmp($post_type_a->labels->singular_name, $post_type_b->labels->singular_name);
}

function wp_meilisearch_admin_enqueue_scripts() {
    wp_enqueue_script('jquery');
}

// Flush rewrite rules on the site when this plug-in is activated so our AJAX handlers get registered.
function wp_meilisearch_flush_rewrite_rules( $value ) {
    global $wp_rewrite;

    $wp_rewrite->flush_rules( false );

    return $value;
}
register_activation_hook( __FILE__, 'wp_meilisearch_flush_rewrite_rules' );

function wp_meilisearch_get_name_as_slug( $name ) {
    setlocale(LC_ALL, 'en_US.UTF8');

    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
    $name = preg_replace("/[^a-z0-9]+/i", "_", preg_replace("/[ \.']+/i", "_", strtolower($name)));

    return $name;
}

?>