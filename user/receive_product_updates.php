<?php
	include 'callAPI.php';
	include 'api.php';
	include 'shopify_functions.php';
	include_once 'admin_token.php';
	include_once 'api.php';

	$admin_token = getAdminToken()['access_token'];
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);

	$baseUrl = getMarketplaceBaseUrl();
	$packageId = getPackageID();

	//get the product id from the webhook response
	$product_id = $content['id'];
	$webhook_event = $content['event'];
	error_log(json_encode('Product ID from webhook: '.$product_id));

	//search for the equivalent arcadier item guid
	$data = array(array('Name' => 'product_id', "Operator" => "eq",'Value' => 'gid://shopify/Product/'.$product_id));
	$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
	$arcadier_item =  callAPI("POST", $admin_token, $url, $data);
	// error_log(json_encode($arcadier_item));


	//if item is found are found
	if($arcadier_item['TotalRecords'] == 1){
		$arcadier_item_guid = $arcadier_item['Records'][0]['arc_item_guid'];       //get item guid
		$arcadier_merchant_guid = $arcadier_item['Records'][0]['merchant_guid'];   //get item merchant guid

		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------


		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------

		//check if webhook event is for "updates"
		if($webhook_event == "update"){
			//resync item details from shopify

			resync_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);
		}

		if($webhook_event == "delete"){
			//call Arcadier API to delete item
			error_log(json_encode($webhook_event));
			delete_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);

			
            
            $synced_details = $arc->searchTable($packageId, 'synced_items', $data);

//echo json_encode($synced_details);

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

	function resync_item($id, $merchant, $baseUrl, $admin_token, $packageId){
		$arc = new ApiSdk();
		//find shopify store details
		$data = array(array('Name' => 'merchant_guid', "Operator" => "eq",'Value' => $merchant));
		$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
		$shopify =  callAPI("POST", $admin_token, $url, $data);

		if($shopify['TotalRecords'] == 1){

			//get shopify store details
			$shopify_store = $shopify['Records'][0]['shop'];
			$shopify_access_token = $shopify['Records'][0]['access_token'];

			$item_details =  shopify_product_details($shopify_access_token, $shopify_store, $GLOBALS['product_id']);
			
			//get shopify item details
			$url = 'https://'. $shopify_store . '.myshopify.com/admin/api/2022-07/products/' . $GLOBALS['product_id'] . '.json';
			$response = shopifyAPI("GET", $shopify_access_token, $url, false);
			//error_log('response ' . json_decode($response,true));

			//calculate total quantity of all variants
			$variants = $response['product']['variants'];
			$total_quantity = 0;
			$minimum_price = (float)$response['product']['variants'][0]['price'];
			foreach($variants as $element){
				$total_quantity = $total_quantity + $element['inventory_quantity'];
				if((float)$element['price'] < $minimum_price){
					$minimum_price = (float)$element['price'];
				}
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

					$id = $variant['id'];
					$variant_image =  findItem($images, $id);
					$media = '';
					if ($variant_image['src'] != null) {

						$media = array(array( "MediaUrl" => $variant_image['src']));
					// $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
					}

						count($response['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  '',  'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'] - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
						count($response['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name']), array('ID' =>  '','Name' => $variant['option2'], 'GroupName' => $response['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']   - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
						count($response['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $response['product']['options'][0]['name']), array('ID' =>  '', 'Name' => $variant['option2'], 'GroupName' => $response['product']['options'][1]['name']),array('ID' =>  '', 'Name' => $variant['option3'], 'GroupName' => $response['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
				
				}
			}

			$allvariants = !empty($allvariants) ? $allvariants : null;

			//edit parent item
			$item_details = array(
				'SKU' =>  $response['product']['variants'][0]['sku'],
				'Name' =>  $response['product']['title'],
				'BuyerDescription' => strip_tags($response['product']['body_html']),
				'SellerDescription' => strip_tags($response['product']['body_html']),
				'Price' => $minimum_price,
				'PriceUnit' => null,
				'StockLimited' => true,
				'StockQuantity' =>  $total_quantity,
				'IsVisibleToCustomer' => $response['product']['status'],
				'Active' => true,
				'IsAvailable' => '',
				'CurrencyCode' =>  'AUD',
				'Media' => $image_array,
				'ChildItems' => $allvariants
				
			);

			//update Arcadier Item
			$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
    		$updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 
			if($updateItem['Name'] == $response['product']['title']){
				echo "Parent item success";
			} else {
				echo "Arcadier Parent Item Update failed";
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//edit children

			//get Arcadier item details
			$url =  $baseUrl . '/api/v2/items/'. $id;
			$item =  callAPI("GET", $admin_token, $url, false); 

			$variants = $response['product']['variants'];

			$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

			//if ($has_variants){

			foreach($item['ChildItems'] as $arc_variant){
				$child_id = $arc_variant['ID'];

				$data =  [
					'ChildItems' => [
							[
								"ID" => $child_id,
								"Active" => false
							]
						]
					];

					$updateItem =  $arc->editItem($data, $merchant, $id);	

			}

			$updateItem =  $arc->editItem($item_details, $merchant, $id);

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