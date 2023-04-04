<?php
	include 'callAPI.php';
	include 'api.php';
	include 'shopify_functions.php';
	include_once 'admin_token.php';
	include_once 'api.php';

	$admin_token = getAdminToken()['access_token'];
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);
	$arc = new ApiSdk();

	$baseUrl = getMarketplaceBaseUrl();
	$packageId = getPackageID();
	$customFieldPrefix = getCustomFieldPrefix();

	//get the product id from the webhook response
	$product_id = $content['id'];
	$webhook_event = $content['event'];
	error_log(json_encode('Product ID from webhook: '.$product_id));

	//search for the equivalent arcadier item guid
	$data = array(array('Name' => 'product_id', "Operator" => "eq",'Value' => 'gid://shopify/Product/'.$product_id));
	$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
	$arcadier_item =  callAPI("POST", $admin_token, $url, $data);
	// error_log(json_encode($arcadier_item));


	$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
	$packageCustomFields = callAPI("GET", null, $url, false);

	

	//if item is found are found
	if($arcadier_item['TotalRecords'] == 1){
		$arcadier_item_guid = $arcadier_item['Records'][0]['arc_item_guid'];       //get item guid
		$arcadier_merchant_guid = $arcadier_item['Records'][0]['merchant_guid']; 


		$result = $arc->getUserInfo($arcadier_merchant_guid);


		if ($result['CustomFields'] != null)  {

			foreach ($result['CustomFields'] as $cf) {
				if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
							$sync_items_list = $cf['Values'][0];
							$sync_items_list = json_decode($sync_items_list,true);
						// echo (json_encode($sync_items_list));
							break;
				}
			
			}
		
		}

		  //get item merchant guid
		  //get the current location of the item
		foreach($sync_items_list as $item) {
			$shopify_id =  $item['itemguid']; //;$buffer['id'];

			if ('gid://shopify/Product/'.$product_id == $shopify_id ) {
				$location_id = $item['locationId'];
				break;
			
			}

		}


		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------


		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------

		//check if webhook event is for "updates"
		if($webhook_event == "update"){
			//resync item details from shopify
			resync_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId, $location_id,$packageCustomFields,$customFieldPrefix);
		}

		if($webhook_event == "delete"){
			//call Arcadier API to delete item
			delete_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);
            $synced_details = $arc->searchTable($packageId, 'synced_items', $data);

			foreach($synced_details['Records']  as $log) {
				$arc->deleteRowEntry($packageId, "synced_items", $log['Id']);
			}
		}
	}
	//if results are not found
	else {
		echo ("This item has not been synced to Arcadier.");
	}
		
	echo json_encode($content);

	function resync_item($id, $merchant, $baseUrl, $admin_token, $packageId, $location_id, $packageCustomFields,$customFieldPrefix){
		$arc = new ApiSdk();
		//find shopify store details
		$data = array(array('Name' => 'merchant_guid', "Operator" => "eq",'Value' => $merchant));
		$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
		$shopify =  callAPI("POST", $admin_token, $url, $data);
		$itemInfo =  $arc->getItemInfo($id);

		//$url = $baseUrl . '/api/v2/users/'. $merchant; 
		
		foreach ($packageCustomFields as $cf) {
			if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
				$is_shopify_code = $cf['Code'];
			}
			if ($cf['Name'] == 'shopify_variant_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
				$shopify_variant_id = $cf['Code'];
			}
			//location id
			if ($cf['Name'] == 'location_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
				$location_id_code = $cf['Code'];
			}
		}

		if($shopify['TotalRecords'] == 1){

			//get shopify store details
			$shopify_store = $shopify['Records'][0]['shop'];
			$shopify_access_token = $shopify['Records'][0]['access_token'];

			$item_details =  shopify_product_details($shopify_access_token, $shopify_store, $GLOBALS['product_id']);
			$locations = shopify_get_location($shopify_access_token, $shopify_store);
			
			//get shopify item details
			$url = 'https://'. $shopify_store . '.myshopify.com/admin/api/2022-07/products/' . $GLOBALS['product_id'] . '.json';
			$response = shopifyAPI("GET", $shopify_access_token, $url, false);
			//error_log('response ' . json_decode($response,true));

			//calculate total quantity of all variants
			$variants = $response['product']['variants'];
			$total_quantity = 0;
			$total_inventory_no_variants = 0;
			$minimum_price = (float)$response['product']['variants'][0]['price'];
			foreach($variants as $element){
				$variant_id = $element['id'];
				$total_quantity = $total_quantity + $element['inventory_quantity'];
				if((float)$element['price'] < $minimum_price){
					$minimum_price = (float)$element['price'];
				}
				//for multiple locations merchants
				$variants_details =  shopify_get_variant_details($shopify_access_token, $shopify_store, $variant_id);
				$inventory_item_id =  $variants_details['variant']['inventory_item_id'];

				$inventory_level = shopify_get_inventory_level_by_location($shopify_access_token, $shopify_store, $inventory_item_id, $location_id);
				error_log('level ' .  json_encode($inventory_level));
				$inventory_count = $inventory_level['inventory_levels'][0]['available'];
				$total_inventory_no_variants  += $inventory_count; 
			}

			//get all images
			$all_images = $response['product']['images'];
			$image_array = [];
			foreach($all_images as $variant_image){
				$data = [
					'MediaURL' => $variant_image['src']
				];
				array_push($image_array, $data);
				$data = [];
			}

			$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

			$prices = [];

			foreach($variants as $variant){
				$prices[] = (float)$variant['price'];
			}

			$price = min($prices);

			$variant_id = $variants[0]['id'];
			$inventory = $variants[0]['inventory_quantity'];
			$sku = $variants[0]['sku'];

			$allimages = [];

			foreach($images  as $image) {
				$allimages[] = array('MediaUrl' => $image['node']['originalSrc']) ;
				
				//$allimages = [];

			}

			if ($has_variants) {

				$allvariants = [];


				$images = $response['product']['images'];
				

				//count the options array

				foreach($variants as $variant){

					$variant_id = $variant['id'];
					$variant_image =  findItem($images, $variant_id);
					$media = '';
					if ($variant_image['src'] != null) {

						$media = array(array( "MediaUrl" => $variant_image['src']));
					// $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
					}

						count($response['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  '',  'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name'])], 'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price'] - $price, 'StockLimited' => true, 'StockQuantity' =>  count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count, 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $variant_id) : '';
						count($response['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name']), array('ID' =>  '','Name' => $variant['option2'], 'GroupName' => $response['product']['options'][1]['name'])],  'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price']   - $price, 'StockLimited' => true, 'StockQuantity' => count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count, 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $variant_id) : '';
						count($response['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name']), array('ID' =>  '', 'Name' => $variant['option2'], 'GroupName' => $response['product']['options'][1]['name']),array('ID' =>  '', 'Name' => $variant['option3'], 'GroupName' => $response['product']['options'][2]['name'])],  'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count,'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $variant_id) : '';
				
				}
			}

			$allvariants = !empty($allvariants) ? $allvariants : null;

			$active = true;
			if($response['product']['status'] == 'archived' || $response['product']['status'] == 'draft'){
				$active = false;
			}

			//edit parent item
			$item_details = array(
				'SKU' =>  $response['product']['variants'][0]['sku'],
				'Name' =>  $response['product']['title'],
				'BuyerDescription' => strip_tags($response['product']['body_html']),
				'SellerDescription' => strip_tags($response['product']['body_html']),
				'Price' => $minimum_price,
				'PriceUnit' => null,
				'StockLimited' => true,
				'StockQuantity' => count($locations['locations']) == 1 ? $total_quantity : $total_inventory_no_variants,
				'IsVisibleToCustomer' => $active,
				'Active' => true,
				'IsAvailable' => $active,
				'CurrencyCode' =>  'AUD',
				'Media' => $image_array,
				'ChildItems' => $allvariants
				
			);

			//make all the variants inactive
			$childItems =  $itemInfo['ChildItems']; 
			foreach ($childItems as $arc_variant) {
	
				$child_id = $arc_variant['ID'];
	
				$data =  [
					'ChildItems' => [
							[
								"ID" => $child_id,
								"Active" => false
							]
						]
					];
	
					$updateVariants =  $arc->editItem($data, $merchant, $id);
	
			}


			//update Arcadier Item
			//$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
			$updateItem =  $arc->editItem($item_details, $merchant, $id);	

			error_log( 'update ' .  json_encode($updateItem));

    		//$updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 
			// if($updateItem['Name'] == $response['product']['title']){
			// 	echo "Parent item success";
			// } else {
			// 	echo "Arcadier Parent Item Update failed";
			// }

			  //update the custom fields again on updating
			  $variant_details = [];
			  foreach($updateItem['ChildItems'] as $variant) {
	  
				  if ($variant['Active'] == true) {
					  $variant_details[] = [ 
						  "variant_id" => $variant['ID'],
						  'shopify_id' => $variant['SKU']
					  ];
	  
				  }
			  }
			  $data = [
				  'CustomFields' => [
					 
						[
						  'Code' =>  $shopify_variant_id,
						  'Values' => [ json_encode($variant_details) ]
						],
						  [
							  'Code' =>  $location_id_code,
							  'Values' => [ $location_id ],
						  ],
					  
	  
				  ],
			  ];
			  $updateItem =  $arc->editItem($item_details, $merchant, $updateItem['ID']);	
			 // $url = $baseUrl . '/api/v2/merchants/' . $merchant . '/items/' . $updateItem['ID'];
			 // $updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 
			//  $result = callAPI("PUT", $admin_token, $url, $data);


			////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//edit children

			//get Arcadier item details
			// $url =  $baseUrl . '/api/v2/items/'. $id;
			// $item =  callAPI("GET", $admin_token, $url, false); 

			// $variants = $response['product']['variants'];

			// $has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

			// //if ($has_variants){

			// foreach($item['ChildItems'] as $arc_variant){
			// 	$child_id = $arc_variant['ID'];

			// 	$data =  [
			// 		'ChildItems' => [
			// 				[
			// 					"ID" => $child_id,
			// 					"Active" => false
			// 				]
			// 			]
			// 		];

			// 		$updateItem =  $arc->editItem($data, $merchant, $id);	

			// }

			// $updateItem =  $arc->editItem($item_details, $merchant, $id);

			//if item is archived
			if($response['product']['status'] == 'archived' || $response['product']['status'] == 'draft') {

				delete_item($id, $merchant, $baseUrl, $admin_token, $packageId);

			}

		}
		//
		else{
			echo ("Something fucked up. Check the \"auth\" custom table.");
		}
	}

	function delete_item($id, $merchant, $baseUrl, $admin_token, $packageId){
		

		$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
    	$deleteItem =  callAPI("DELETE", $admin_token, $url, null); 

		$data = [
			[
			'Name'=> 'merchant_guid',
			'Operator'=> 'equal',
			'Value'=> $merchant
			],
			[
				'Name'=> 'product_id',
				'Operator'=> 'equal',
				'Value'=> 'gid://shopify/Product/'.$GLOBALS['product_id']
			]
		];

		$synced_details = $arc->searchTable($packageId, 'synced_items', $data);

		foreach($synced_details['Records']  as $log) {

			$deleteItem =  $arc->deleteRowEntry($packageId, "synced_items", $log['Id']);
		}
	}


	function findItem(array $variants, int $id)
	{
		foreach ($variants as $variant) {
	
			if  (in_array($id, $variant['variant_ids']))
			{
				return $variant;
			}

			//return null;
		}
	}

 	function findVariant(array $variants, string $name)
	{
		foreach ($variants as $variant) {


			$shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $variant['AdditionalDetails']);

			

		// foreach($variant['Variants'] as $childvariant) {

			//  error_log('name '  . $childvariant['Name']);
				if  ($name ==  $shopify_variant_ID)

				{
				//  error_log('found variant ' . json_encode($childvariant));
						
					return $variant;
				}

		//  }
				

			//return null;
		}
	}
	
?>