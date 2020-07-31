<?php

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
        //pre_print_r('delete from meilisearch!');
    }

    return true;
}
add_action( 'transition_post_status', 'wp_meilisearch_transition_post_status', 10, 3 );

?>