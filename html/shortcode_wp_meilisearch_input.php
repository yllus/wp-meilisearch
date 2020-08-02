<input id="search" class="input" type="text" autofocus placeholder="">

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

        if ( search_value.length == 0 ) {
        	results.innerHTML = '';
        }
        if ( search_value.length < 2 ) {
        	return;
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
                //time.innerHTML = `${processingTimeMs}ms`;
                //count.innerHTML = `${numberOfDocuments}`;

                for (result of httpResults.hits) {	
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