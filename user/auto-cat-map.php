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

    //getmerchant GUID
    //find merchant's shopify store and token

    $merchant_guid = '02ec5b74-ecc2-4c9c-9048-dbfc9de419ba';
    $shop = 'tanoo-joy3';
    $access_token = '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';

    $shopify_categories = shopify_categories($access_token, $shop);

    foreach($shopify_categories as $product_type){
        $result = ask_GPT($product_type);
        
    }

    function ask_GPT($product_type){
        $url = 'https://arcadier-shopify.arcadier.io/category_map';
        $data = [
            'prompt' => $product_type
        ];
        callAPI('POST', null, $url, $data);
    }



?>
