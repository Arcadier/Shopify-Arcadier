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
	error_log(json_encode('Product ID from webhook: '.$product_id));

	//search for the equivalent arcadier item guid
	$data = array(array('Name' => 'product_id', "Operator" => "eq",'Value' => 'gid://shopify/Product/'.$product_id));
	$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
	$arcadier_item =  callAPI("POST", $admin_token, $url, $data);
	error_log(json_encode($arcadier_item));


	//if item is found are found
	if($arcadier_item['TotalRecords'] == 1){
		$arcadier_item_guid = $arcadier_item['Records'][0]['arc_item_guid'];       //get item guid
		$arcadier_merchant_guid = $arcadier_item['Records'][0]['merchant_guid'];   //get item merchant guid

		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------




		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------

		//resync item details from shopify
		resync_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);
	}
	//if results are not found
	else {
		echo ("This item has not been synced to Arcadier.");
	}
		
	echo json_encode($content);

	function resync_item($id, $merchant, $baseUrl, $admin_token, $packageId){
		//find shopify store details
		$data = array(array('Name' => 'merchant_guid', "Operator" => "eq",'Value' => $merchant));
		$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
		$shopify =  callAPI("POST", $admin_token, $url, $data);

		if($shopify['TotalRecords'] == 1){

			//get shopify store details
			$shopify_store = $shopify['Records'][0]['shop'];
			$shopify_access_token = $shopify['Records'][0]['access_token'];

			//get shopify item details
			$url = 'https://'. $shopify_store . '.myshopify.com/admin/api/2022-07/products/' . $GLOBALS['product_id'] . '.json';
			$response = shopifyAPI("GET", $shopify_access_token, $url, false);
			error_log(json_encode($response));

			$item_details = array(
				'SKU' =>  $response['product']['variants'][0]['sku'],
				'Name' =>  $response['product']['title'],
				'BuyerDescription' => $response['product']['body_html'],
				'SellerDescription' => $response['product']['body_html'],
				'Price' => (float)$response['product']['variants'][0]['price'],
				'PriceUnit' => null,
				'StockLimited' => true,
				'StockQuantity' =>  $response['product']['variants'][0]['inventory_quantity'],
				'IsVisibleToCustomer' => $response['product']['status'],
				'Active' => true,
				'IsAvailable' => '',
				'CurrencyCode' =>  'SGD',
				'Media' => [
					array( "MediaUrl" => $response['product']['image']['src'])	
				]
			);

			//update Arcadier Item
			$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
    		$updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 

			if($updateItem['Name'] == $response['product']['title']){
				echo "success";
			} else {
				echo "Arcadier Item Update failed";
			}
		}
		//
		else{
			echo ("Something fucked up. Check the \"auth\" custom table.");
		}
	}
	
?>