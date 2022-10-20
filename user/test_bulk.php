<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i");

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];


$userEmail = $result['Email'];
$userDisplayName = $result['DisplayName'];

$packageId = getPackageID();

// Query user authentication 

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];


$productsLink = shopify_get_bulk_item($access_token, $shop);

$fp = @fopen($productsLink, "r");
if ($fp) {
    while (($buffer = fgets($fp)) !== false) {
        //echo $buffer;

       $buffer =  json_decode($buffer, true);
        echo $buffer['id'];

        
    }
    if (!feof($fp)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($fp);
}