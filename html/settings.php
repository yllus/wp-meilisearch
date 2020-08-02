<h1>MeiliSearch for WordPress</h1>

<?php // settings_errors(); // display settings notices (saved/errors) ?>
<div class="wrap">
    <form method="post" action="options.php"> 
        <?php settings_fields('wp_meilisearch_options_group'); ?>
        <?php do_settings_sections('wp_meilisearch_options_group'); ?>

        <table class="form-table">
            <tr valign="top">
                <td colspan="2" style="padding: 0;">
                    <h2>MeiliSearch Settings</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-left: 0;">
                    <fieldset style="margin: 0; padding: 5px;">
                        <table style="width: 100%;">
                            <tr>
                                <td colspan="2">
                                    <h3 style="margin: 0;">Settings</h3>
                                    <p>
                                        Settings for interacting with your MeiliSearch instance, including the instance's URL and the master, read and write keys needed to interact with it.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Search Instance URL:</b></td>
                                <td><input type="text" name="wp_meilisearch_url" id="wp_meilisearch_url" value="<?php echo $str_wp_meilisearch_url; ?>" placeholder="http://" style="width: 350px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Search Index Name:</b></td>
                                <td><input type="text" name="wp_meilisearch_index" id="wp_meilisearch_index" value="<?php echo $str_wp_meilisearch_index; ?>" placeholder="<?php echo $str_default_index_name; ?>" style="width: 350px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Master Key:</b></td>
                                <td><input type="text" name="wp_meilisearch_master" id="wp_meilisearch_master" value="<?php echo $str_wp_meilisearch_master; ?>" style="width: 350px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Public Key:</b></td>
                                <td><input type="text"name="wp_meilisearch_public" id="wp_meilisearch_public" value="<?php echo $str_wp_meilisearch_public; ?>" style="width: 350px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h3 style="margin: 0;">Status</h3>
                                    <p>
                                        Provide a live view of the current status of your MeiliSearch search index.
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td style="width: 25%;"><b>Current Status:</b></td>
                                <td><span id="span_current_status">Unknown</span></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b># Of Indexed Documents:</b></td>
                                <td><span id="span_num_documents">?</span></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Actions:</b></td>
                                <td>
                                    <input type="button" name="btn_create_index" id="btn_create_index" class="button button-primary" value="Create Index" disabled="disabled">
                                    &nbsp;&nbsp;&nbsp;
                                    <input type="button" name="btn_delete_index" id="btn_delete_index" class="button button-secondary" value="Delete Index" disabled="disabled">
                                </td>
                            </tr>
                        </table>
                        <script>
                        jQuery( document ).on( "blur", "#wp_meilisearch_url", function( event ) {
                            update_index_status();
                        });
                        jQuery( document ).on( "blur", "#wp_meilisearch_index", function( event ) {
                            update_index_status();
                        });
                        jQuery( document ).on( "blur", "#wp_meilisearch_master", function( event ) {
                            update_index_status();
                        });
                        jQuery( document ).on( "blur", "#wp_meilisearch_public", function( event ) {
                            update_index_status();
                        });
                        jQuery( document ).on( "click", "#btn_create_index", function( event ) {
                            create_index();
                        });
                        jQuery( document ).on( "click", "#btn_delete_index", function( event ) {
                            delete_index();
                        });

                        function delete_index() {
                            var str_index = jQuery('#wp_meilisearch_index').val();
                            var str_url = jQuery('#wp_meilisearch_url').val() + 'indexes/' + str_index;

                            if ( confirm('Are you sure you want to delete the existing index?') !== true ) {
                                return;
                            }

                            jQuery.ajax({
                                type: "DELETE",
                                url: str_url,
                                data: '',
                                beforeSend: function( xhr ) { xhr.setRequestHeader('X-Meili-API-Key', jQuery('#wp_meilisearch_master').val()); },
                                success: function( data ) { 
                                    update_index_status();
                                }, 
                                error: function( data ) {
                                    jQuery('#span_current_status').html('<b style="color: red;">An error occurred trying to delete the index; please verify that your Search Instance URL and Master Key values are correct.</b>');
                                }
                            });
                        }

                        function create_index() {
                            var str_index = jQuery('#wp_meilisearch_index').val();
                            var str_url = jQuery('#wp_meilisearch_url').val() + 'indexes';

                            // Actually create the MeiliSearch index of the given name.
                            jQuery.ajax({
                                type: "POST",
                                url: str_url,
                                data: '{ "uid": "' + str_index + '", "primaryKey": "objectID" }',
                                contentType: "application/json",
                                dataType: 'json', 
                                beforeSend: function( xhr ) { xhr.setRequestHeader('X-Meili-API-Key', jQuery('#wp_meilisearch_master').val()); },
                                success: function( data ) { 
                                    var str_index = jQuery('#wp_meilisearch_index').val();
                                    var str_url = jQuery('#wp_meilisearch_url').val() + 'indexes/' + str_index + '/settings/ranking-rules';

                                    // Next, customize our ranking rules.
                                    jQuery.ajax({
                                        type: "POST",
                                        url: str_url,
                                        data: '["asc(content_type)", "desc(timestamp_gmt)", "exactness", "words", "wordsPosition", "proximity", "attribute", "typo" ]',
                                        contentType: "application/json",
                                        dataType: 'json', 
                                        beforeSend: function( xhr ) { xhr.setRequestHeader('X-Meili-API-Key', jQuery('#wp_meilisearch_master').val()); },
                                        success: function( data ) { 
                                            update_index_status();
                                        }, 
                                        error: function( data ) {
                                            jQuery('#span_current_status').html('<b style="color: red;">An error occurred trying to customize the index ruleset; please verify that your Search Instance URL and Master Key values are correct.</b>');
                                        }
                                    });
                                }, 
                                error: function( data ) {
                                    jQuery('#span_current_status').html('<b style="color: red;">An error occurred trying to create the index; please verify that your Search Instance URL and Master Key values are correct.</b>');
                                }
                            });
                        }

                        function update_index_status() {
                            if ( jQuery('#wp_meilisearch_url').val().length == 0 || jQuery('#wp_meilisearch_index').val().length == 0 || jQuery('#wp_meilisearch_master').val().length == 0 || jQuery('#wp_meilisearch_public').val().length == 0 ) {
                                return;
                            }

                            if ( jQuery('#wp_meilisearch_url').val().substr(-1) !== '/' ) {
                                jQuery('#wp_meilisearch_url').val( jQuery('#wp_meilisearch_url').val() + '/' );
                            }

                            var str_url = jQuery('#wp_meilisearch_url').val() + 'stats';
                            var str_index = jQuery('#wp_meilisearch_index').val();

                            jQuery.ajax({
                                type: "GET",
                                url: str_url,
                                data: {},
                                beforeSend: function( xhr ) { xhr.setRequestHeader('X-Meili-API-Key', jQuery('#wp_meilisearch_master').val()); },
                                success: function( data ) { 
                                    if ( typeof data.indexes[str_index] !== "undefined" ) {
                                        jQuery('#span_current_status').html('<b style="color: green;">Ready</b>');
                                        jQuery('#span_num_documents').html( data.indexes['cfl'].numberOfDocuments );
                                        jQuery('#btn_create_index').attr('disabled', 'disabled');
                                        jQuery('#btn_delete_index').removeAttr('disabled');
                                    }
                                    else {
                                        jQuery('#span_current_status').html('<b>Does Not Exist</b>');
                                        jQuery('#span_num_documents').html('?');
                                        jQuery('#btn_create_index').removeAttr('disabled');
                                        jQuery('#btn_delete_index').attr('disabled', 'disabled');
                                    }
                                }
                            });
                        }
                        update_index_status();
                        </script>
                    </fieldset>
                </td>                           
            </tr>

            <tr valign="top">
                <td colspan="2" style="padding: 0;">
                    <br>

                    <h2>WordPress Settings</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-left: 0;">
                    <fieldset style="margin: 0; padding: 5px;">
                        <table style="width: 100%;">
                            <tr>
                                <td colspan="2">
                                    <h3 style="margin: 0;">Post Type Settings</h3>
                                    <p>
                                        In this section you can configure what post types (types of content) will be indexed and made available from and on this WordPress website.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%; vertical-align: top;"><b>Post Types To Index & Display Results For:</b></td>
                                <td>
                                    <table style="width: 100%;">
                                    <?php foreach ( $arr_post_types as $post_type ): ?>
                                        <tr>
                                            <td style="display: table-cell; padding-bottom: 0;">
                                                <input type="checkbox" value="<?php echo $post_type->name; ?>" name="meilisearch_post_types[<?php echo $post_type->name; ?>]" <?php checked(!empty($arr_post_types_selected[$post_type->name])); ?> />
                                            </td>
                                            <td style="display: table-cell; padding-bottom: 0;">
                                                <b><?php echo __($post_type->labels->singular_name); ?></b> (<?php echo $post_type->name; ?>)
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Actions:</b></td>
                                <td>
                                    <input type="button" name="btn_reindex" id="btn_reindex" class="button button-primary" value="Re-index All Content" disabled="disabled">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h3 style="margin: 0;">Search Page Settings</h3>
                                    <p>
                                        In this area, you can define the settings for the page where search results from MeiliSearch will be displayed. If this Page exists, all searches on this WordPress site will be redirected to it.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Search Page Slug:</b></td>
                                <td>
                                    <input type="text" name="wp_meilisearch_page_slug" id="wp_meilisearch_page_slug" value="<?php echo $str_wp_meilisearch_page_slug; ?>" placeholder="search" style="width: 350px;">
                                    <br>
                                    <span class="description">Search results will be displayed at the URL: <a target="_blank" id="wp_meilisearch_results_url" href=""></a></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td style="width: 25%;"><b>Current Status:</b></td>
                                <td><span id="span_current_status_page">Unknown</span></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;"><b>Actions:</b></td>
                                <td>
                                    <input type="button" name="btn_create_page" id="btn_create_page" class="button button-primary" value="Create Page" disabled="disabled">
                                </td>
                            </tr>
                        </table>
                        <script>
                        jQuery( document ).on( "keyup", "#wp_meilisearch_page_slug", function( event ) {
                            check_allow_create_page();
                            update_search_results_url();
                        });

                        function update_search_results_url() {
                            var str_wp_home_url = '<?php echo get_bloginfo('url'); ?>';
                            var str_wp_meilisearch_page_slug = jQuery('#wp_meilisearch_page_slug').val();
                            if ( str_wp_meilisearch_page_slug.length == 0 ) {
                                return;
                            }

                            jQuery('#wp_meilisearch_results_url').attr('href', str_wp_home_url + '/' + str_wp_meilisearch_page_slug);
                            jQuery('#wp_meilisearch_results_url').html(str_wp_home_url + '/' + str_wp_meilisearch_page_slug);
                        }
                        update_search_results_url();

                        function check_allow_create_page() {
                            var str_wp_meilisearch_page_slug = jQuery('#wp_meilisearch_page_slug').val();

                            if ( str_wp_meilisearch_page_slug.length > 0 ) {
                                jQuery('#btn_create_page').removeAttr('disabled');
                            }
                            else {
                                jQuery('#btn_create_page').attr('disabled', 'disabled');
                            }
                        }
                        check_allow_create_page();

                        function check_search_result_page_status() {
                            var str_wp_meilisearch_page_slug = jQuery('#wp_meilisearch_page_slug').val();
                            if ( str_wp_meilisearch_page_slug.length == 0 ) {
                                return;
                            }

                            jQuery.ajax({
                                type: "GET",
                                url: '/wp-admin/admin-ajax.php?action=wp_meilisearch_get_page_status&page_slug=' + str_wp_meilisearch_page_slug,
                                data: {},
                                contentType: "application/json",
                                dataType: 'json', 
                                success: function( data ) { 
                                    if ( data.page_exists == 0 ) {
                                        jQuery('#span_current_status_page').html('<b style="color: red;">Does not exist; click the <i>Create Page</i> button found below to generate a search results page for MeiliSearch.</b>');

                                        return;
                                    }

                                    if ( data.page_is_published == 0 ) {
                                        jQuery('#span_current_status_page').html('<b style="color: #FF8333;">Page exists but is not in the Published status for it to be available to the public.</b>');
                                        jQuery('#btn_create_page').attr('disabled', 'disabled');

                                        return;
                                    }

                                    if ( data.page_shortcode_input_exists == 0 || data.page_shortcode_results_exists == 0 ) { 
                                        jQuery('#span_current_status_page').html('<b style="color: #FF8333;">Page exists but the <i>[wp_meilisearch_input]</i> and <i>[wp_meilisearch_results]</i> shortcodes may not be present on it. This may not be an issue if you\'ve manually coded this page to show MeiliSearch results.</b>');
                                        jQuery('#btn_create_page').attr('disabled', 'disabled');

                                        return;
                                    }

                                    jQuery('#span_current_status_page').html('<b style="color: green;">Published / Ready</b>');
                                    jQuery('#btn_create_page').attr('disabled', 'disabled');
                                }
                            });
                        }
                        check_search_result_page_status();
                        </script>
                    </fieldset>
                </td>                           
            </tr>
        </table>            
        <?php submit_button(); ?>
    </form>                
</div>