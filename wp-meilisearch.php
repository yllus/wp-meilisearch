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

    $post_content = $post->post_excerpt;
    if ( empty($post_content) ) {
        $post_content = $post->post_title;
    }

    // Assemble the document to be sent to MeiliSearch.
    $obj_document = new stdClass;
    $obj_document->objectID = $post->ID;
    $obj_document->content = html_entity_decode(strip_tags($post_content));
    $obj_document->url = get_permalink($post->ID);
    $obj_document->anchor = $post->ID;
    $obj_document->hierarchy_lvl0 = 'Content';
    $obj_document->hierarchy_lvl1 = $post_type->labels->singular_name;
    $obj_document->hierarchy_lvl2 = html_entity_decode($post->post_title);
    $obj_document->hierarchy_lvl3 = 'null';
    $obj_document->hierarchy_lvl4 = 'null';
    $obj_document->hierarchy_lvl5 = 'null';
    $obj_document->hierarchy_lvl6 = 'null';
    $obj_document->content_type = 1;
    $obj_document->date = $post->post_date;
    $obj_document->date_gmt = $post->post_date_gmt;
    $obj_document->timestamp_gmt = strtotime($obj_document->date_gmt);
    $obj_document->modified = $post->post_modified;
    $obj_document->modified_gmt = $post->post_modified_gmt;
    $obj_document->title = html_entity_decode($post->post_title);

    $arr_documents = array($obj_document);

    // Actually make the HTTP POST to MeiliSearch.
    $url = $str_wp_meilisearch_url . $str_wp_meilisearch_index . '/documents';
    $args = array( 
        'timeout' => 10,
        'headers' => array( 
            'X-Meili-API-Key' => $str_wp_meilisearch_master,
        ), 
        'body' => json_encode($arr_documents),
    );
    $request = wp_remote_post($url, $args);

    debug_log(print_r($request, true));

    return true;
}
add_action( 'wp_meilisearch_index_document', 'wp_meilisearch_index_document_now', 10, 1 );
?>