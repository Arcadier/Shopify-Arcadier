<?php
	include 'callAPI.php';
	
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);

	$shop = str_replace('.myshopify.com', '', $content['shop']);
	error_log(json_encode($shop));

	$data = [
		'marketplace'=> $content['marketplace'],
		'shop' => $shop,
		'merchant_guid' => $content['merchant_guid'],
		'pluginID' => $content['pluginID']
	];
	error_log("Data object: ".json_encode($data));
	$url = 'https://arcadier-shopify.herokuapp.com/shopify_link_account?shop='.$shop.'&merchant_guid='.$content['merchant_guid'].'&marketplace='.$content['marketplace'].'&pluginID='.$content['pluginID'];

	$response = callAPI("GET", null, $url, false);

	echo $response;
	
?>