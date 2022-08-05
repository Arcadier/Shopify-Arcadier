<?php
//require_once("shopify.php");

// class shopifySdk
// {

function get_shopify_categories($productTypes, $category_array, $token, $shop){
        //error_log(json_encode($productTypes));
        $hasnextpage = $productTypes['data']['shop']['products']['pageInfo']['hasNextPage'];
        $productTypes = $productTypes['data']['shop']['products']['edges'];
        $temp = [];

        //extract category names
        foreach ($productTypes as $category){
            if(in_array($category['node']['productType'], $temp) == false){
                array_push($temp, $category['node']['productType']);
                error_log(json_encode($temp));
            }
            if(!next($productTypes) && $hasnextpage == true) {
                $cursor = $category['cursor'];
                $category_array = array_merge($temp, $category_array);
                $category_array = array_merge(shopify_categories_api($token, $shop, $cursor), $category_array);
            }
        }

        return array_unique($category_array);
}



function shopify_categories_api($token, $shop, $page) {
	if(!isset($token)){
		$error_m = [
			"Error" => [
				"No access token"
			]
		];

		return json_encode($error_m);
	}

	if(!isset($shop)){
		$error_m = [
			"Error" => [
				"No specified Shopify store"
			]
		];

		return json_encode($error_m);
	}

	if(!isset($page)){
		$query = array("query" => '{
			shop {
				products(first:10, after: null) {
					edges {
						cursor
						node {
							productType
						}
					}
					pageInfo{
						hasNextPage
					}
				}
			}
		}');
	}
	else{
		$query = array("query" => '{
			shop {
				products(first:10, after:"'.$page.'") {
					edges {
						cursor
						node {
							productType
						}
					}
					pageInfo{
						hasNextPage
					}
				}
			}
		}');
	}

	$cats  = graphql($token, $shop, $query);   
	$productTypes = json_decode($cats['body'], true);
	$category_array = [];
	//error_log(json_encode($query));

	$category_array = get_shopify_categories($productTypes, $category_array, $token, $shop);

	return array_unique($category_array);
}





function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
    
	// Build URL
	$url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
	if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

	// Configure cURL
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
	// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
	curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

	// Setup headers
	$request_headers[] = "";
	if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
		if (is_array($query)) $query = http_build_query($query);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
	}
    
	// Send request to Shopify and capture any errors
	$response = curl_exec($curl);
	$error_number = curl_errno($curl);
	$error_message = curl_error($curl);

	// Close cURL to be nice
	curl_close($curl);

	// Return an error is cURL has a problem
	if ($error_number) {
		return $error_message;
	} else {

		// No error, return Shopify's response by parsing out the body and the headers
		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

		// Convert headers into an array
		$headers = array();
		$header_data = explode("\n",$response[0]);
		$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
		array_shift($header_data); // Remove status, we've already set it above
		foreach($header_data as $part) {
			$h = explode(":", $part);
			$headers[trim($h[0])] = trim($h[1]);
		}

		// Return headers and Shopify's response
		return array('headers' => $headers, 'response' => $response[1]);

	}
    
}

function graphql($token, $shop, $query = array()) {
	$url = "https://" . $shop .  '.myshopify.com/admin/api/2021-07/graphql.json';

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);


	$request_headers[] = "";
	$request_headers[] = "Content-Type: application/json";
	if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query));
	curl_setopt($curl, CURLOPT_POST, true);

	$response = curl_exec($curl);
	$error_number = curl_errno($curl);
	$error_message = curl_error($curl);
	curl_close($curl);

	if ($error_number) {
		return $error_message;
	} else {

		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

		$headers = array();
		$header_data = explode("\n",$response[0]);
		$headers['status'] = $header_data[0];
		array_shift($header_data);
		foreach($header_data as $part) {
			$h = explode(":", $part, 2);
			$headers[trim($h[0])] = trim($h[1]);
		}

		return array('headers' => $headers, 'body' => $response[1]);

	}

}

function shopify_products($token, $shop){
    $products = shopify_call($token, $shop, "/admin/products.json", array(), 'GET');
    // Run API call to get products

    // Convert product JSON information into an array
    $products = json_decode($products['response'], TRUE);
    
    return $products;

}

function shopify_categories($token, $shop) {
	$query = array("query" => '{ shop { productTypes(first:100){
      edges {
        node
      }
    }} }');

	$cats  = graphql($token, $shop, $query);   //shopify_call($token, $shop, "/admin/api/2022-07/graphql.json", $query, 'POST');
    //$cats = $cats;
	$cats = $cats['body'];

	$cats_list =  json_decode($cats,true);

	$categories = $cats_list['data']['shop']['productTypes']['edges'];

	return $categories;
}

function shopify_add_tag($token, $shop, $product_id, $tags) {

	$mutation = array("query" => 'mutation {
	tagsAdd(
		
			id:' .  $product_id . '
			tags:' . $tags . '
			
		) {
            node {
            id
            }
            userErrors {
            field
            message
            }
        }
    }
    
    ');

    $tagsCreate = graphql($token, $shop, $mutation);

	return $tagsCreate;
}

function shopify_remove_tag($token, $shop, $product_id, $tag){
	$mutation = array("query" => 'mutation {
	tagsAdd(
		
			id:' .  $product_id . '
			tags:'. $tag . '
			
		) {
            node {
            id
            }
            userErrors {
            field
            message
            }
        }
    }
    
    ');

	$tagsRemove = graphql($token, $shop, $mutation);

	return $tagsRemove;
}

function shopify_get_product_tags($token, $shop, $product_id) {
	$tags = array("query" => '{
	      product(id:' . $product_id .') {
			tags
    	}
	}
	');	
	$tags = graphql($token, $shop, $tags);
   return $tags;
}


function shopify_create_metafields($token, $shop, $product_id, $namespace, $key, $value){

	$mutation = array("query" => 'mutation {
	productUpdate(
		input: { 
			id:' .  $product_id . '
			"metafields": [
				{
					"namespace": ' . $namespace . ',
					"key":'. $key . ',
					"value":' . $value .',
					"type": "single_line_text_field"
				}
			]
		}
				
		) product {
			id
        }
    }
    
    ');
   
   echo print_r($mutation);
	$metafieldsCreate = graphql($token, $shop, $mutation);

	return $metafieldsCreate;

	
	
}