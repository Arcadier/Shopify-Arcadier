<?php

include 'callAPI.php';
include 'api.php';
include 'shopify_functions.php';
$arc = new ApiSdk();

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();

$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$cursor =  $content['cursor'];


$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
//$result = $arc->getUserInfo($_GET['user']);
$userId = $result['ID'];
$packageId = getPackageID();

$auth = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop = $authDetails['Records'][0]['shop'];
$access_token= $authDetails['Records'][0]['access_token'];


$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", $admin_token, $url, false);


$prods_code = '';


foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $prods_code = $cf['Code'];
    }
}

$products = shopify_products_paginated($access_token, $shop, $cursor, true);


if ($products) {

$data = [

    'CustomFields' => [
        [
            'Code' => $prods_code,
            'Values' => [json_encode($products)],
        ],
    ],
];

$url = $baseUrl . '/api/v2/users/' . $userId;
$result = callAPI("PUT", $admin_token, $url, $data);

echo json_encode($result);

}


?>