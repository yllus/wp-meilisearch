<?php
function wp_meilisearch_reindex_all_content() {
    global $wpdb;

    // Execute any actions that have been coded into the theme/other plug-ins to run as a check before we 
    // potentially add or remove this post to MeiliSearch.
    do_action('wp_meilisearch_pre_index_test');

    // Create a string list of post_types we're going to prep indexing for.
    $arr_post_types_selected = get_option('meilisearch_post_types', array());
    $str_post_types = "";
    foreach ( $arr_post_types_selected as $post_type ) {
        $str_post_types .= "'" . $post_type . "',";
    }
    $str_post_types = rtrim($str_post_types, ',');

    // Create groups of post ID #s to schedule as cron scheduled tasks for indexing.
    $arr_post_ids = array();
    $int_index_offset = 0;
    $int_index_group_size = 1000;
    $bool_get_more_posts = true;
    $arr_post_groups = array();
    while ( $bool_get_more_posts === true ) {
        $query = "SELECT ID AS post_id FROM $wpdb->posts WHERE $wpdb->posts.post_type IN (" . $str_post_types . ") ORDER BY ID DESC LIMIT " . $int_index_group_size . " OFFSET " . $int_index_offset;
        $arr_result = $wpdb->get_results($query);

        // Add the first and last post ID # as a distinct group to run a scheduled task for.
        $arr_post_groups[] = array($arr_result[0]->post_id, $arr_result[(sizeof($arr_result) - 1)]->post_id);

        if ( sizeof($arr_result) < $int_index_group_size ) {
            $bool_get_more_posts = false;
        }

        $int_index_offset = $int_index_offset + $int_index_group_size;
    }

    // For each post group, schedule an event to undertake their indexing.
    $int_secs_offset = 0;
    $int_secs_period_per_group = 600;
    foreach ( $arr_post_groups as $arr_post_group ) {
        // Remove existing cron event for this post if one exists.
        wp_clear_scheduled_hook( 'wp_meilisearch_reindex_documents', array( 'first_post_id' => $arr_post_group[0], 'last_post_id' => $arr_post_group[1] ) );

        // Schedule the document indexing to occur.
        wp_schedule_single_event( (time() + 10 + $int_secs_offset), 'wp_meilisearch_reindex_documents', array( 'first_post_id' => $arr_post_group[0], 'last_post_id' => $arr_post_group[1] ) );

        $int_secs_offset = $int_secs_offset + $int_secs_period_per_group;
    }

    return true;
}

function wp_meilisearch_transition_post_status( $new_status, $old_status, $post ) {
    // Retrieve the list of post types we're indexing documents for.
    $arr_post_types_selected = get_option('meilisearch_post_types', array());

    // If the post type isn't listed, exit immediately and take no actions.
    if ( in_array($post->post_type, $arr_post_types_selected) === false ) {
        return false;
    }

    // Execute any actions that have been coded into the theme/other plug-ins to run as a check before we 
    // potentially add or remove this post to MeiliSearch.
    do_action('wp_meilisearch_pre_index_test');

    if ( $new_status == 'publish' ) {
        // Remove existing cron event for this post if one exists.
        wp_clear_scheduled_hook( 'wp_meilisearch_index_document', array( 'post_id' => $post->ID ) );

        // Schedule the document indexing to occur.
        wp_schedule_single_event( (time() + 10), 'wp_meilisearch_index_document', array( $post->ID ) );
    }

    if ( $old_status == 'publish' && $new_status != 'publish' ) {
        // Remove existing cron event for this post if one exists.
        wp_clear_scheduled_hook( 'wp_meilisearch_delete_document', array( 'post_id' => $post->ID ) );

        // Schedule the document removal to occur.
        wp_schedule_single_event( (time() + 10), 'wp_meilisearch_delete_document', array( $post->ID ) );
    }

    return true;
}
add_action( 'transition_post_status', 'wp_meilisearch_transition_post_status', 10, 3 );

?>