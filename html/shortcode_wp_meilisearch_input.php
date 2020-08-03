<link rel="stylesheet" id="wp-meilisearch-css"  href="<?php echo plugin_dir_url(__FILE__) . 'css/wp-meilisearch.css'; ?>" type="text/css" media="all" />

<div class="rsp_section rsp_group">
	<div class="rsp_col rsp_span_12_of_12">
		<div id="wp_m_search_container">
			<svg id="wp_m_magnify" width="36" height="36" viewBox="0 0 36 36"><path fill="#A6A6A6" d="M6.06464039,6.06458401 C2.64511987,9.48438934 2.64511987,15.0284312 6.06456334,18.4481594 C9.48432347,21.8677075 15.0282788,21.8677075 18.4480004,18.448198 C21.8677541,15.0284079 21.8677605,9.48443987 18.4480197,6.0646418 C15.028258,2.64511298 9.48431704,2.6451194 6.06464039,6.06458401 Z M21.5630375,19.4417171 L28.0606602,25.9393398 L25.9393398,28.0606602 L19.4417189,21.5630392 C14.830452,25.1324621 8.17531757,24.801292 3.94323171,20.5694685 C-0.647743904,15.9781105 -0.647743904,8.53470999 3.94330876,3.94327495 C8.53462672,-0.647758318 15.9779756,-0.647758318 20.5692935,3.94327495 C24.8012726,8.17529907 25.1325206,14.8303758 21.5630375,19.4417171 Z"></path></svg>
			<input id="search" class="input" type="text" autofocus placeholder="">
		</div>
	</div>
</div>

<script>
    function sanitizeHTMLEntities(str) {
        if (str && typeof str === 'string') {
            str = str.replace(/</g,"&lt;");
            str = str.replace(/>/g,"&gt;");
            str = str.replace(/&lt;em&gt;/g,"<em>");
            str = str.replace(/&lt;\/em&gt;/g,"<\/em>");
        }
        return str;
    }

    function httpGet(theUrl, apiKey) {
        var xmlHttp = new XMLHttpRequest();

        xmlHttp.open("GET", theUrl, false); // false for synchronous request
        xmlHttp.setRequestHeader("x-Meili-API-Key", apiKey);
        xmlHttp.send(null);

        return xmlHttp.responseText;
    }

    let lastRequest = undefined;

    function triggerSearch() {
        var index = '<?php echo $str_wp_meilisearch_index; ?>';
        var search_value = search.value;

        if ( search_value.length == 0 && typeof results !== 'undefined' ) {
        	results.innerHTML = '';
        }

        let theUrl = `${baseUrl}indexes/${index}/search?q=${search_value}&attributesToHighlight=*`;

        if (lastRequest) { lastRequest.abort() }
        lastRequest = new XMLHttpRequest();

        lastRequest.open("GET", theUrl, true);
        lastRequest.setRequestHeader("X-Meili-API-Key", apiKey);

        lastRequest.onload = function (e) {
            if (lastRequest.readyState === 4 && lastRequest.status === 200) {
                let sanitizedResponseText = sanitizeHTMLEntities(lastRequest.responseText);
                let httpResults = JSON.parse(sanitizedResponseText);
                results.innerHTML = '';

                let processingTimeMs = httpResults.processingTimeMs;
                let numberOfDocuments = httpResults.hits.length;

                var num_start_results = httpResults.offset;
                var num_end_results = numberOfDocuments;

                if ( num_end_results > 0 ) {
                	if ( num_start_results == 0 ) {
                		num_start_results = num_start_results + 1;
                	}
                }

                document.getElementById('wp_m_stats').innerHTML = 'Showing results ' + num_start_results + ' - ' + num_end_results + ' for: “' + httpResults.query + '“';

                //time.innerHTML = `${processingTimeMs}ms`;
                //count.innerHTML = `${numberOfDocuments}`;

                for ( result of httpResults.hits ) {
                	const element = {...result, ...result._formatted };
                    delete element._formatted;

                	var str_result = '';
                	var str_image = '';
                	var str_col_width_main = 'rsp_span_12_of_12';

                	if ( typeof element.url_thumbnail === 'string' ) {
                        if ( element.url_thumbnail.length > 0 ) {
                        	str_col_width_main = 'rsp_span_9_of_12';
                        	str_image = '<div class="rsp_col rsp_span_3_of_12 wp_m_image"><img src="' + element.url_thumbnail + '"></div>';
                        }
                    }

                	str_result = str_result + '<li class="rsp_section wp_m_result">';
                	str_result = str_result + '	<div class="rsp_group">';
                	str_result = str_result + '		<div class="rsp_col ' + str_col_width_main + '"><div class="wp_m_metadata">' + element.hierarchy_lvl1 + ' | ' + element.date + '</div><div class="wp_m_title"><a href="' + element.url + '">' + element.title + '</a></div><div class="wp_m_content">' + element.content + '</div></div>';
                	str_result = str_result + str_image;
                	str_result = str_result + '	</div>';
                	str_result = str_result + '</li>';

                	results.innerHTML = results.innerHTML + str_result;

                	/*
                    const element = {...result, ...result._formatted };
                    delete element._formatted;

                    const elem = document.createElement('li');
                    elem.classList.add("document");

                    const ol = document.createElement('ol');
                    let image = undefined;

                    for (const prop in element) {
                        // Check if property is an image url link.
                        if (typeof result[prop] === 'string') {
                            if (image == undefined && result[prop].match(/^(https|http):\/\/.*(jpe?g|png|gif)(\?.*)?$/g)) {
                                image = result[prop];
                            }
                        }

                        const field = document.createElement('li');
                        field.classList.add("field");

                        const attribute = document.createElement('div');
                        attribute.classList.add("attribute");
                        attribute.innerHTML = prop;

                        const content = document.createElement('div');
                        content.classList.add("content");
                        if (typeof (element[prop]) === "object") {
                          content.innerHTML = JSON.stringify(element[prop]);
                        } else {
                          content.innerHTML = element[prop];
                        }

                        field.appendChild(attribute);
                        field.appendChild(content);

                        ol.appendChild(field);
                    }

                    elem.appendChild(ol);

                    if (image != undefined) {
                        const div = document.createElement('div');
                        div.classList.add("image");

                        const img = document.createElement('img');
                        img.src = image;

                        div.appendChild(img);
                        elem.appendChild(div);
                    }

                    results.appendChild(elem)
                    */
                }
            } else {
                console.error(lastRequest.statusText);
            }
        };
        lastRequest.send(null);
    }

    let baseUrl = '<?php echo $str_wp_meilisearch_url; ?>';
    let apiKey = '<?php echo $str_wp_meilisearch_public; ?>';

    search.oninput = triggerSearch;

    triggerSearch();
  </script>