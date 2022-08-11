<?php
include 'callAPI.php';
require 'api.php';
//include 'admin_token.php';
$API = new ApiSdk();
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$shopify_key = $content['shopify-key'];
$shopify_store_name = $content['shopify-store'];
$shopify_secret_key = $content['secret-key'];


$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
//$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

$credentials = [

    "api_key" => $shopify_key,
    "secret_key" => $shopify_secret_key,
    //"access_token" => $shopify_key,
    "shop" => $shopify_store_name,
    "merchant_guid" => $userId,
    "auth_status"=> "1"
    
];

$packageId = getPackageID();
$response = $API->createRowEntry($packageId, 'auth', $credentials);


$shop = $shopify_store_name;
$api_key = $shopify_key;


$scopes = "read_orders,write_products";
$redirect_uri =  $baseUrl . '/user/plugins/' . $packageId . '/shopify-token.php';   //?secret-key=' . $shopify_secret_key .'&api-key='. $api_key

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

echo json_encode([$install_url]);
// Redirect
//header("Location: " . $install_url);
//die();


?>