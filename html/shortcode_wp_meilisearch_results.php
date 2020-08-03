<div class="rsp_section rsp_group">
    <div class="rsp_col rsp_span_3_of_12">
        <div id="wp_m_content_types_title">Content Types</div>
        <ul id="wp_m_content_types_list">
        	<?php foreach ( $arr_post_types_selected as $arr_post_type ): ?>
    		<li>
    			<label for="content-type-option-<?php echo $arr_post_type; ?>" class="wp_m_content_type_option_label">
	        		<input id="content-type-option-<?php echo $arr_post_type; ?>" type="checkbox" class="wp_m_content_type_option_checkbox" value="<?php echo $arr_post_types[$arr_post_type]->labels->singular_name; ?>" checked="checked" onclick="triggerSearch();">
	        		<span class="wp_m_content_type_option_span"><?php echo $arr_post_types[$arr_post_type]->label; ?></span>
	        	</label>
    		</li>
        	<?php endforeach; ?>
        </ul>
    </div>

    <div class="rsp_col rsp_span_9_of_12">
        <div id="wp_m_stats">&nbsp;</div>

        <ol id="results" style="margin: 0;">
            <!-- documents matching requests -->
        </ol>
    </div>
</div>