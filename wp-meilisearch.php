<?php
/*
 Plugin Name: MeiliSearch for WordPress
 Plugin URI: https://github.com/yllus/wp-meilisearch/
 Description: Automatically index posts from your WordPress site in your own MeiliSearch search engine, and display search results in a search bar and page.
 Author: Sully Syed
 Version: 1.0.0
 Author URI: http://yllus.com/
*/

require_once(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/admin.php');
require_once(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/post-actions.php');
require_once(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/shortcodes.php');

function wp_meilisearch_delete_document_now( $post_id ) {
    // Retrieve our current MeiliSearch settings.
    $str_wp_meilisearch_url = get_option('wp_meilisearch_url', '');
    $str_wp_meilisearch_index = get_option('wp_meilisearch_index', '');
    $str_wp_meilisearch_master = get_option('wp_meilisearch_master', '');

    if ( empty($str_wp_meilisearch_url) || empty($str_wp_meilisearch_index) || empty($str_wp_meilisearch_master) ) {
        error_log('wp_meilisearch_delete_document_now(): Wanted to delete post ID #' . $post_id . ' from index but MeiliSearch settings were not provided.');

        return false;
    }

    // Actually make the HTTP POST to MeiliSearch.
    $url = $str_wp_meilisearch_url . 'indexes/' . $str_wp_meilisearch_index . '/documents/' . $post_id;
    $args = array( 
        'method'  => 'DELETE',
        'timeout' => 10,
        'headers' => array( 
            'X-Meili-API-Key' => $str_wp_meilisearch_master,
        ), 
    );
    $response = wp_remote_post($url, $args);

    if ( is_wp_error( $response ) ) {
        error_log('wp_meilisearch_delete_document_now(): Tried to delete post ID #' . $post->ID . ' from the MeiliSearch index at ' . $url . ' , failed with response code ' . $response['response']['code'] . '.');
    }

    return true;
}
add_action( 'wp_meilisearch_delete_document', 'wp_meilisearch_delete_document_now', 10, 1 );

function wp_meilisearch_index_document_now( $post_id ) {
    // Retrieve our current MeiliSearch settings.
    $str_wp_meilisearch_url = get_option('wp_meilisearch_url', '');
    $str_wp_meilisearch_index = get_option('wp_meilisearch_index', '');
    $str_wp_meilisearch_master = get_option('wp_meilisearch_master', '');

    if ( empty($str_wp_meilisearch_url) || empty($str_wp_meilisearch_index) || empty($str_wp_meilisearch_master) ) {
        error_log('wp_meilisearch_index_document_now(): Wanted to index post ID #' . $post_id . ' but MeiliSearch settings were not provided.');

        return false;
    }

    // Retrieve the full Post object from the database.
    $post = get_post($post_id);
    $post_type = get_post_type_object($post->post_type);

    // Figure out if there's a featured image to display.
    $url_thumbnail = '';
    $attachment_id = get_post_thumbnail_id($post->ID); 
    if ( !empty($attachment_id) ) {
        $arr_url_thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
        $url_thumbnail = $arr_url_thumbnail[0];
    }

    // Retrieve the list of post types priorities numbers/levels that have been set.
    $arr_post_types_priority = get_option('meilisearch_post_type_priority', array());

    // Retrieve the list of group names that have been set.
    $arr_group_names = get_option('meilisearch_group_names', array());

    // Assemble the document to be sent to MeiliSearch.
    $obj_document = new stdClass;
    $obj_document->objectID = $post->ID;
    $obj_document->content_type = $arr_post_types_priority[$post->post_type];
    $obj_document->hierarchy_lvl0 = $arr_group_names[$post->post_type];
    $obj_document->hierarchy_lvl1 = __($post_type->labels->singular_name);
    $obj_document->hierarchy_lvl2 = html_entity_decode($post->post_title);
    $obj_document->hierarchy_lvl3 = 'null';
    $obj_document->hierarchy_lvl4 = 'null';
    $obj_document->hierarchy_lvl5 = 'null';
    $obj_document->hierarchy_lvl6 = 'null';
    $obj_document->anchor = $post->ID;
    $obj_document->date = $post->post_date;
    $obj_document->date_gmt = $post->post_date_gmt;
    $obj_document->timestamp_gmt = strtotime($obj_document->date_gmt);
    $obj_document->modified = $post->post_modified;
    $obj_document->modified_gmt = $post->post_modified_gmt;
    $obj_document->title = html_entity_decode($post->post_title);
    $obj_document->content = wp_meilisearch_get_the_excerpt_max_charlength($post->ID);
    $obj_document->url = get_permalink($post->ID);
    $obj_document->url_thumbnail = $url_thumbnail;

    $str_documents = json_encode(array($obj_document));

    // Actually make the HTTP POST to MeiliSearch.
    $url = $str_wp_meilisearch_url . 'indexes/' . $str_wp_meilisearch_index . '/documents';
    $args = array( 
        'timeout' => 10,
        'headers' => array( 
            'Content-Type' => "application/json",
            'X-Meili-API-Key' => $str_wp_meilisearch_master,
        ), 
        'body' => $str_documents,
    );
    $response = wp_remote_post($url, $args);

    if ( is_wp_error( $response ) ) {
        error_log('wp_meilisearch_index_document_now(): Tried to add post ID #' . $post->ID . ' to the MeiliSearch index at ' . $url . ' , failed with response code ' . $response['response']['code'] . '.');
    }

    return true;
}
add_action( 'wp_meilisearch_index_document', 'wp_meilisearch_index_document_now', 10, 1 );

function wp_meilisearch_get_the_excerpt_max_charlength( $post_id, $charlength = 200 ) {
    $str_output = '';

    $excerpt = apply_filters('the_excerpt', get_post_field('post_excerpt', $post_id));
    if ( strlen($excerpt) == 0 ) {
        $excerpt = apply_filters('the_excerpt', get_post_field('post_content', $post_id));
    }

    $excerpt = strip_shortcodes($excerpt);
    $excerpt = strip_tags($excerpt);

    $charlength++;

    if ( mb_strlen( $excerpt ) > $charlength ) {
        $subex = mb_substr( $excerpt, 0, $charlength - 5 );
        $exwords = explode( ' ', $subex );
        $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
        if ( $excut < 0 ) {
            $str_output = $str_output . mb_substr( $subex, 0, $excut );
        } else {
            $str_output = $str_output . $subex;
        }
        $str_output = $str_output . '...';
    } 
    else {
        $str_output = $str_output . $excerpt;
    }

    return $str_output;
}
?>