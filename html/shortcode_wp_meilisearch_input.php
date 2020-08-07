<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.css@7/themes/algolia-min.css" />
<link rel="stylesheet" id="wp-meilisearch-css"  href="<?php echo plugin_dir_url(__FILE__) . 'css/wp-meilisearch.css?_t=202008031929'; ?>" type="text/css" media="all" />

<div class="rsp_section rsp_group">
  <div class="rsp_col rsp_span_12_of_12">
    <div id="wp_m_search_container">
      <div id="searchbox" class="input"></div>
    </div>
  </div>
</div>

<div class="rsp_section rsp_group">
    <div class="rsp_col rsp_span_2_of_12">
      <div id="clear-refinements"></div>

        <h2>Content Types</h2>
        <div id="content-types-list"></div>

        <?php /*
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
        */ ?>
    </div>

    <div class="rsp_col rsp_span_10_of_12">
        <div id="wp_m_stats">&nbsp;</div>

        <div id="hits" style="margin: 0;">
            <!-- documents matching requests -->
        </div>

        <div id="pagination"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@meilisearch/instant-meilisearch@v0.1.3/dist/instant-meilisearch.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/instantsearch.js@4"></script>
<script>
function getMeiliSearchQueryParameter() {
    var sParam = 'q',
        sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

const search = instantsearch({
    indexName: "cfl",
    searchClient: instantMeiliSearch(
        "https://search.cfl.ca",
        "<?php echo $str_wp_meilisearch_public; ?>",
        {
            hitsPerPage: 8,
            limitPerRequest: 800
        }
    ),
    searchFunction( helper ) {
        var str_query = getMeiliSearchQueryParameter();
        if ( typeof str_query !== 'undefined' ) {
            if ( str_query.length > 0 ) {
                /*
                var obj_searchbox = document.querySelector("[type='search']");
                obj_searchbox.value = str_query;
                */

                helper.setQuery(str_query).search();

                return;
            }
        }
        
        helper.search();
    },
});

search.addWidgets([
    instantsearch.widgets.searchBox({
        container: "#searchbox", 
        autofocus: true,
        queryHook( query, search ) {
            // Update our URL with the current query string.
            if ( history.pushState ) {
                var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?q=' + query;
                window.history.pushState({path:newurl},'',  newurl);
            }

            search(query);
        },
    }),

    instantsearch.widgets.clearRefinements({
        container: "#clear-refinements"
    }),

    instantsearch.widgets.refinementList({
        container: "#content-types-list",
        attribute: "hierarchy_lvl1"
    }),

  instantsearch.widgets.hits({
    container: "#hits",
    templates: {
      item: `
      <div class="wp_m_result">
        <div class="">
          <div class="wp_m_image">
            {{#url_thumbnail}}
              <a href="{{ url }}"><img src="{{ url_thumbnail }}"></a>
            {{/url_thumbnail}}
            {{^url_thumbnail}}
              <a href="{{ url }}"><img src="/wp-content/themes/cfl.ca/images/og-image-default.jpg?_t=201902271522"></a>
            {{/url_thumbnail}}
          </div>
          <div class="wp_m_metadata">{{ hierarchy_lvl1 }}&nbsp;&nbsp;|&nbsp;&nbsp;{{ date }}</div>
          <div class="Xwp_m_title"><a href="{{ url }}">{{#helpers.highlight}}{ "attribute": "title" }{{/helpers.highlight}}</a></div>
          <div class="Xwp_m_content">{{#helpers.snippet}}{ "attribute": "content", "highlightedTagName": "mark" }{{/helpers.snippet}}</div>
        </div>
      </div>
      `
    }
  }),
  instantsearch.widgets.pagination({
    container: "#pagination"
  })
]);

search.start();

</script>