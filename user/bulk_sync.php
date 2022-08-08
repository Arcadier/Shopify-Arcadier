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

$packageId = getPackageID();

// Query user authentication 

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId), array('Name' => 'access_token', "Operator" => "like",'Value' => 'shpua_'));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];


//step 1. Get all shopify products

$shopify_products = shopify_get_all_products_paginated($access_token, $shop, null,true);

//step 2.  Loop and check if the item has been sync e.g if item exists on synced products custom table

//foreach($shopify_products as $product) {

    //get the shopify id
    $product_id = $shopify_products[2]['node']['id'];
    echo $product_id;

    //check if the item has been sync

$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);
    
echo 'sync  result ' . json_encode($isItemSyncResult);

if ($isItemSyncResult['TotalRecords'] == 0) {

    //if 0 - not exist yet, create a new row on synced_items table

    $sync_details = [

    "product_id" => $product_id,
    "synced_date" => time(),
    "merchant_guid" => $userId
    
    ];

    $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);
   
    //create a new item on arcadier 
    

    
}else {
    //if exists, get the row id and edit the row entry of synced_date

    $synced_item_id =  $isItemSyncResult['Records'][0]['Id'];

    $sync_details = [

        "synced_date" => time(),

    ];	

    $response = $arc->editRowEntry($packageId, 'synced_items', $synced_item_id, $sync_details);
    echo 'the sync details has been updated';

}
    
//}