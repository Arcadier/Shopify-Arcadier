<?php
	include 'callAPI.php';
	include 'shopify_functions.php';
	
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);

	//get the product id from the webhook response
	$product_id = $content['id'];

	//search for the equivalent arcadier item guid
	$data = array(array('Name' => 'product_id', "Operator" => "eq",'Value' => 'gid://shopify/Product/'.$product_id));
	$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
	$arcadier_item =  callAPI("POST", $admin_token, $url, $data);

	//if results are found
	if($arcadier_item['TotalRecords'] == 1){
		$arcadier_item_guid = $arcadier_item['Records'][0]['arc_item_guid'];       //get item guid
		$arcadier_merchant_guid = $arcadier_item['Records'][0]['merchant_guid'];   //get item merchant guid

		//resync item details from shopify
		resync_item($arcadier_item_guid, $arcadier_merchant_guid);
	}
	//if results are not found
	else {
		echo ("Something fucked up. Check the \"synced_items\" custom table.");
	}
		
	echo json_encode($content);

	function resync_item($id, $merchant){
		//find shopify store details
		$data = array(array('Name' => 'merchant_guid', "Operator" => "eq",'Value' => $merchant));
		$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
		$shopify =  callAPI("POST", $admin_token, $url, $data);

		if($shopify['TotalRecords'] == 1){

			//get shopify store details
			$shopify_store = $shopify['Records'][0]['shop'];
			$shopify_access_token = $shopify['Records'][0]['access_token'];

			//get shopify item details

		}
		else{
			echo ("Something fucked up. Check the \"auth\" custom table.");
		}
	}
	
?>