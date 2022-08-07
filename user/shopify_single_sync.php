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
//$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

$packageId = getPackageID();

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId), array(array('Name' => 'access_token', "Operator" => "like",'Value' => 'shpua_')));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);


error_log('auth ' . json_encode($authDetails));

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];



$product_id =  $content['id'];
$product_name = $content['name'];
//1.  save the items on arc using merchant API,

$item_details = array(
      'SKU' =>  'sku',
      'Name' =>  $product_name,
      'BuyerDescription' => 'description',
      'SellerDescription' => 'description',
      'Price' => 5,
      'PriceUnit' => null,
      'StockLimited' => true,
      'StockQuantity' =>  10,
      'IsVisibleToCustomer' => true,
      'Active' => true,
      'IsAvailable' => '',
      'CurrencyCode' =>  'SGD',
      'Categories' =>   [
          array( "ID" => "6afe4787-3867-4b27-b897-467534410aa2")
           
         ],
      'ShippingMethods'  => null,
      'PickupAddresses' => null,
      'Media' => [
          array( "MediaUrl" => "https://blog.hubspot.com/hubfs/customers%20enjoying%20the%20best%20product%20page%20designs%20while%20online%20shopping-1.jpg")
           
         ],
      'Tags' => null,
      'CustomFields' => null,
      'ChildItems' => null,

);

$url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
$result =  callAPI("POST", $admin_token, $url, $item_details);
$result1 = json_encode(['err' => $result]);
echo $result1;


if ($result['ID']){

    error_log($result['ID']);
     //after syncing the product on arcadier, update the tags on shopify to 'synced'

     shopify_add_tag($access_token, $shop, $product_id, "synced");
        
     
}

   


    

?>