<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

//get shopify credentials
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$userId = $content['user-id'];
$sync_list = $content['product-list'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$packageId = getPackageID();
// Query to get marketplace id


error_log('sybc list');
$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];

$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $admin_token, $url, false);
$admin_id = $result['ID'];

// $userToken = $_COOKIE["webapitoken"];
// $url = $baseUrl . '/api/v2/users/'; 
// $result = callAPI("GET", $userToken, $url, false);
// $userId = $result['user-id'];

//query for cart item custom field
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$auto_sync_list_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $auto_sync_list_code = $cf['Code'];
    }

}

$data = [
    'CustomFields' => [
        [
            'Code' => $auto_sync_list_code,
            'Values' => [ $sync_list ]
        ],
    ],
];

$url = $baseUrl . '/api/v2/users/' . $userId;
$result = callAPI("PUT", $admin_token, $url, $data);

//error_log($result);
echo json_encode($result);

?>