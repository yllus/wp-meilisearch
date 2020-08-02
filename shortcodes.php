<?php

function wp_meilisearch_input_shortcode( $atts ) {
    // Retrieve our current MeiliSearch settings.
    $str_wp_meilisearch_url = get_option('wp_meilisearch_url', '');
    $str_wp_meilisearch_index = get_option('wp_meilisearch_index', '');
    $str_wp_meilisearch_public = get_option('wp_meilisearch_public', '');

    require_once('html/shortcode_wp_meilisearch_input.php'); 
}
add_shortcode( 'wp_meilisearch_input', 'wp_meilisearch_input_shortcode' );

function wp_meilisearch_results_shortcode( $atts ) {
    require_once('html/shortcode_wp_meilisearch_results.php'); 
}
add_shortcode( 'wp_meilisearch_results', 'wp_meilisearch_results_shortcode' );

?>