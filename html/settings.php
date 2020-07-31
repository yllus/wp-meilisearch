<h1>MeiliSearch for WordPress</h1>

<?php // settings_errors(); // display settings notices (saved/errors) ?>
<div class="wrap">
    <form method="post" action="options.php"> 
        <?php settings_fields('wp_meilisearch_options_group'); ?>
        <?php do_settings_sections('wp_meilisearch_options_group'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Automatically index and display results from these post types:</th>
                <td>
                    <fieldset>
                        <table style="width: 100%;">
                            <tr>
                                <th style="text-align: left;width: 50px;padding:0 10px;display:table-cell;"></th>                                
                                <th style="width: 90%;display:table-cell;padding-bottom:0;padding-top:0;"></th>
                            </tr>
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
                    </fieldset>
                </td>                           
            </tr>                    
        </table>            
        <?php submit_button(); ?>
    </form>                
</div>