<?php
//require_once("shopify.php");

// class shopifySdk
// {

function get_shopify_categories($productTypes, $category_array, $token, $shop){
		//error_log(json_encode($productTypes));
        $hasnextpage = $productTypes['data']['shop']['products']['pageInfo']['hasNextPage'];
        $productTypes = $productTypes['data']['shop']['products']['edges'];
        $temp = [];
		$error_items = [];

        //extract category names
        foreach ($productTypes as $category){
			//if product has no producttype, skip
			if($category['node']['productType'] == "" || $category['node']['productType'] == null){
				continue;
			}

			//if producttype hasnt been added to the list yet, add it
            if(in_array($category['node']['productType'], $temp) == false){
                array_push($temp, $category['node']['productType']);
            }

			//if reached the last item in the iteration page and there's more, call for the next 10 items
            if(!next($productTypes) && $hasnextpage == true) {
                $cursor = $category['cursor'];
                $category_array = array_merge($temp, $category_array);
                $category_array = array_merge(shopify_categories_api($token, $shop, $cursor), $category_array);
            }

			//if reached the last item in the iteration and there's no more products to call, send back the current list
			elseif(!next($productTypes) && $hasnextpage == false){
				$category_array = array_merge($temp, $category_array);
			}
        }

		$category_array = array_unique($category_array);
        return $category_array;
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
	//error_log(json_encode($productTypes));
	$category_array = [];

	$category_array = get_shopify_categories($productTypes, $category_array, $token, $shop);
	//error_log("Category Array: ".json_encode($category_array));
	return array_unique($category_array);
}

function shopify_get_all_products($token, $shop){
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

	$query = array("query" => '{
		products(first:50) {
			edges {
				cursor
				node {
					id
					title
					description
					vendor
					customProductType
					productType
					hasOnlyDefaultVariant
					totalInventory
					totalVariants
					status
					tags
					createdAt
					updatedAt
					images(first: 5) {
						edges{
							node {
								originalSrc
								altText	
							}
						}
					}
					variants(first: 1) {
						edges{
							node {
								price
								id
							}
						}
					}
					
					
				}
			}
			pageInfo{
				hasNextPage
			}
		}
	}');

	$api_call  = graphql($token, $shop, $query);   
	$products = json_decode($api_call['body'], true);
	$productlist = $products['data']['products']['edges'];
	
	return $productlist;
}

function shopify_get_all_products_unstable($token, $shop, $page, $all){
	/*
		shopify_get_all_products($token, $shop, null, false)
			gets only first 10 items
		
		shopify_get_all_products($token, $shop, null, true)
			gets all items
	*/

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

	//get 10 items only
	if(!isset($page) && $all == false){
		$query = array("query" => '{
			products(first:250) {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	//get ALL items
	if(!isset($page) && $all == true){
		//error_log('Querying all items');
		$query = array("query" => '{
			products(first:250, after: null) {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	if($page != null && $all == true){
		//error_log('Querying next 10 items');
		$query = array("query" => '{
			products(first:250, after: "'.$page.'") {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}
//do {
	//sleep(1);
	$api_call  = graphql($token, $shop, $query);   
	$products = json_decode($api_call['body'], true);
//}
	echo json_encode($api_call);

	//while ($api_call['errors'][0]['message'] == 'Throttled');
		
			$productlist = $products['data']['products']['edges'];
			$hasnextpage = $products['data']['products']['pageInfo']['hasNextPage'];

			if($hasnextpage == false){
				return $productlist;
			} 
			//error_log('Found more items');
			foreach($productlist as $product){
				if(!next($productlist)){
					$last_cursor = $product['cursor'];

					
					$productlist = array_merge($productlist, shopify_get_all_products_unstable($token, $shop, $last_cursor, true));
					
				}
				sleep(1);
			}

	

	//echo ('prod ' . json_encode($products));
	
	return $productlist;

	//echo $productlist;
}

function shopify_product_count($access_token, $shop){
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://'.$shop.'.myshopify.com/admin/api/2022-07/products/count.json',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'X-Shopify-Access-Token: '. $access_token,
			'Content-Type: application/json'
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	return json_decode($response, true);
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

function shopify_products($token, $shop){
	//$page = $i + 1;
    $products = shopify_call($token, $shop, "/admin/api/2022-10/products.json", array('limit' => 5), 'GET');
    $products =  json_decode($products['response'], TRUE);
   
    //return $products['headers'];

}

function shopify_product_details($token, $shop, $prod_id){
	$products = shopify_call($token, $shop, "/admin/api/2022-04/products/" . $prod_id .".json", array(), 'GET');
	//echo json_encode('prods '. $products);
	
    $products = json_decode($products['response'], TRUE);
    //echo json_encode($products);
    return $products;

}

function shopify_categories($token, $shop) {
	$query = array("query" => '{ shop { productTypes(first:250){
      edges {
        node
      }
    }} }');

	$cats  = graphql($token, $shop, $query);   //shopify_call($token, $shop, "/admin/api/2022-07/graphql.json", $query, 'POST');
    //$cats = $cats;
	$cats = $cats['body'];

	$cats_list =  json_decode($cats,true);

	$categories = $cats_list['data']['shop']['productTypes']['edges'];
	$category_list = [];
	foreach($categories as $category){
		array_push($category_list, $category['node']);
	}

	return array_unique($category_list);
}

function shopify_get_variants($token, $shop, $product_id){
	$query = array('query' => "{ product ( id: \"$product_id\"){
      	variants(first: 100) {
			edges{
				node {
					price
					id
				}
			}
		}
   	}}");

	$variants = graphql($token, $shop, $query);	

	$variants = $variants['body'];

	$variants_list =  json_decode($variants,true);

	$variants_list = $variants_list['data']['product']['variants']['edges'];

	return $variants_list;

}

function shopify_get_images($token, $shop, $product_id){
	$query = array('query' => "{ product ( id: \"$product_id\"){
      	images(first: 5) {
			edges{
				node {
					originalSrc
					altText	
				}
			}
		}
   	}}");

	$variants = graphql($token, $shop, $query);	

	$variants = $variants['body'];

	$variants_list =  json_decode($variants,true);

	$variants_list = $variants_list['data']['product']['images']['edges'];

	return $variants_list;

}

function shopify_add_tag($token, $shop, $product_id, $tags) {

	// error_log('shop ' . $shop);
	// error_log('prod-id' . $product_id);
	// error_log('tags '. $tags);


	$mutation = array('query' => "mutation {
	tagsAdd(
		
			id: \"$product_id\",
			tags:\"$tags\"
			
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
    
    ");

	//error_log('mutation '. json_encode($mutation));

    $tagsCreate = graphql($token, $shop, $mutation);
	//error_log('tags create ' .json_encode($tagsCreate));
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

function createCustomer($token, $shop, $first_name, $last_name, $email){
	
	$mutation = array('query' => "mutation  {
      customerCreate(
        input: {
          firstName: \"$first_name\",
          lastName: \"$last_name\",
          email: \"$email\"
        
            }
      ) {
        customer {
          id
          firstName
          lastName
          email
        }
		

        userErrors {
          field
          message
        }
        customer {
          id
          
        }
      }
    }
    
    ");


	//output
	// "body":"{\"data\":{\"customerCreate\":
	// {\"customer\":{\"id\":\"gid:\\\/\\\/shopify\\\/Customer\\\/6405413601532\",
	// \"firstName\":\"dave\",\"lastName\":\"smith\",\"email\":\"someone@gmail.com\"}

	//error_log('mutation '. json_encode($mutation));

    $customerCreate = graphql($token, $shop, $mutation);

	$customerCreate  = $customerCreate['body'];

	$customer =  json_decode($customerCreate,true);

	$customer_details = $customer['data']['customerCreate']['customer']['id'];

	//error_log('customer ' . json_encode($customerCreate));
	//error_log('customer ' . $customer_details);



	return $customer_details; //return the ID

	

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

function graphql($token, $shop, $query = array()) {
	$url = "https://" . $shop .  '.myshopify.com/admin/api/2022-07/graphql.json';

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
	//curl_setopt($curl, CURLOPT_HTTPHEADER,array("Expect:"));
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



function shopify_get_all_products_unstable_test($token, $shop, $page, $all){
	/*
		shopify_get_all_products($token, $shop, null, false)
			gets only first 10 items
		
		shopify_get_all_products($token, $shop, null, true)
			gets all items
	*/

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

	//get 10 items only
	if(!isset($page) && $all == false){
		$query = array("query" => '{
			products(first:250) {
				edges {
					cursor
					node {
						id
						
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	//get ALL items
	if(!isset($page) && $all == true){
		//error_log('Querying all items');
		$query = array("query" => '{
			products(first:250, after: null) {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
						
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	if($page != null && $all == true){
		//error_log('Querying next 10 items');
		$query = array("query" => '{
			products(first:250, after: "'.$page.'") {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
						
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	$api_call  = graphql($token, $shop, $query);   

	//echo json_encode($api_call);
	$products = json_decode($api_call['body'], true);


		
			$productlist = $products['data']['products']['edges'];
			$hasnextpage = $products['data']['products']['pageInfo']['hasNextPage'];


			 $queryCost= $products['extensions']['cost'];
				if($queryCost['throttleStatus']['currentlyAvailable'] < $queryCost['requestedQueryCost'])
				
				{//wait because of graphql request limit rate
					error_log("sleep for a while");
					$diff = $queryCost['requestedQueryCost']-$queryCost['throttleStatus']['currentlyAvailable'];
					$waitTime = $diff*1000/$queryCost['throttleStatus']['restoreRate'];
					error_log("Wait for: " .$waitTime ." miliseconds");
					$waitTime = $waitTime;
					usleep($waitTime * 1000);
				}

			if($hasnextpage == false){
				return $productlist;
			} 


			//syncing 
			
			//error_log('Found more items');
			foreach($productlist as $product){
				if(!next($productlist)){
					$last_cursor = $product['cursor'];

					$productlist = array_merge($productlist, shopify_get_all_products_unstable_test($token, $shop, $last_cursor, true));

				}
				
			}
	
	return $productlist;

	//echo $productlist;
}


//paginate the api


function shopify_products_paginated($token, $shop, $page, $all){

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

	//get 10 items only
	if(!isset($page) && $all == false){
		$query = array("query" => '{
			products(first:250) {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	//get ALL items
	// if(!isset($page) && $all == true){
	// 	//error_log('Querying all items');
	// 	$query = array("query" => '{
	// 		products(first:250, after: null) {
	// 			edges {
	// 				cursor
	// 				node {
	// 					id
	// 					title
	// 					description
	// 					vendor
	// 					customProductType
	// 					productType
	// 					hasOnlyDefaultVariant
	// 					totalInventory
	// 					totalVariants
	// 					status
	// 					tags
	// 					createdAt
	// 					updatedAt
						
	// 				}
	// 			}
	// 			pageInfo{
	// 				hasNextPage
	// 			}
	// 		}
	// 	}');
	// }

	if($page != null && $all == true){
		//error_log('Querying next 10 items');
		$query = array("query" => '{
			products(first:250, after: "'.$page.'") {
				edges {
					cursor
					node {
						id
						title
						description
						vendor
						customProductType
						productType
						hasOnlyDefaultVariant
						totalInventory
						totalVariants
						status
						tags
						createdAt
						updatedAt
						
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	$api_call  = graphql($token, $shop, $query);   

	//echo json_encode($api_call);
	$products = json_decode($api_call['body'], true);

			$productlist = $products['data']['products']['edges'];
			$hasnextpage = $products['data']['products']['pageInfo']['hasNextPage'];


			 $queryCost= $products['extensions']['cost'];
				if($queryCost['throttleStatus']['currentlyAvailable'] < $queryCost['requestedQueryCost'])
				
				{//wait because of graphql request limit rate
					error_log("sleep for a while");
					$diff = $queryCost['requestedQueryCost']-$queryCost['throttleStatus']['currentlyAvailable'];
					$waitTime = $diff*1000/$queryCost['throttleStatus']['restoreRate'];
					error_log("Wait for: " .$waitTime ." miliseconds");
					$waitTime = $waitTime;
					usleep($waitTime * 1000);
				}

			// if($hasnextpage == false){
			// 	return $productlist;
			// } 
			// //error_log('Found more items');
			// foreach($productlist as $product){
			// 	if(!next($productlist)){
			// 		$last_cursor = $product['cursor'];

			// 		$productlist = array_merge($productlist, shopify_get_all_products_unstable_test($token, $shop, $last_cursor, true));

			// 	}
				
			// }
	
			return $productlist;



}


function shopify_products_paginated_id($token, $shop, $page, $all){

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

	//get first 250 items only
	if(!isset($page) && $all == false){
		$query = array("query" => '{
			products(first:250) {
				edges {
					cursor
					node {
						id
						
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}


	if($page != null && $all == true){
		//error_log('Querying next 10 items');
		$query = array("query" => '{
			products(first:250, after: "'.$page.'") {
				edges {
					cursor
					node {
						id
					}
				}
				pageInfo{
					hasNextPage
				}
			}
		}');
	}

	$api_call  = graphql($token, $shop, $query);   

	//echo json_encode($api_call);
	$products = json_decode($api_call['body'], true);

			$productlist = $products['data']['products']['edges'];
			$hasnextpage = $products['data']['products']['pageInfo']['hasNextPage'];


			 $queryCost= $products['extensions']['cost'];
				if($queryCost['throttleStatus']['currentlyAvailable'] < $queryCost['requestedQueryCost'])
				
				{//wait because of graphql request limit rate
					error_log("sleep for a while");
					$diff = $queryCost['requestedQueryCost']-$queryCost['throttleStatus']['currentlyAvailable'];
					$waitTime = $diff*1000/$queryCost['throttleStatus']['restoreRate'];
					error_log("Wait for: " .$waitTime ." miliseconds");
					$waitTime = $waitTime;
					usleep($waitTime * 1000);
				}

			// if($hasnextpage == false){
			// 	return $productlist;
			// } 
			// //error_log('Found more items');
			// foreach($productlist as $product){
			// 	if(!next($productlist)){
			// 		$last_cursor = $product['cursor'];

			// 		$productlist = array_merge($productlist, shopify_get_all_products_unstable_test($token, $shop, $last_cursor, true));

			// 	}
				
			// }
	
			return $products;
}