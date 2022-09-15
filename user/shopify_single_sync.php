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

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
}

//error_log('auth ' . json_encode($authDetails));

// $shop_secret_key = $authDetails['Records'][0]['secret_key'];
// $shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];


$product_id =  $content['id'];
$product_name = $content['name'];
$categories =  $content['category'];
$images = $content['images'];
$price = $content['price'];
$stock = $content['qty'];

//1.  save the items on arc using merchant API,

$all_categories = [];

foreach($categories as $category) {
    $all_categories[] = array("ID" => $category);
    
}

//get the variant

$variant =  shopify_get_variants($access_token, $shop, $product_id);


$images = shopify_get_images($access_token, $shop, $product_id);

$price = $variant[0]['node']['price'];
$variant_id = $variant[0]['node']['id'];
$image =  $images[0]['node']['originalSrc'];


//error_log(json_encode($variant));

$item_details = array(
      'SKU' =>  'sku',
      'Name' =>  $product_name,
      'BuyerDescription' => 'description',
      'SellerDescription' => 'description',
      'Price' => (float)$price,
      'PriceUnit' => null,
      'StockLimited' => true,
      'StockQuantity' =>  $stock,
      'IsVisibleToCustomer' => true,
      'Active' => true,
      'IsAvailable' => '',
      'CurrencyCode' =>  'SGD',
      'Categories' =>   $all_categories,
      'ShippingMethods'  => null,
      'PickupAddresses' => null,
      'Media' => [
          array( "MediaUrl" => $image)
           
         ],
      'Tags' => null,
      'CustomFields' => null,
      'ChildItems' => null,

);


$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);



if ($isItemSyncResult['TotalRecords'] == 0) {


    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
    $result =  callAPI("POST", $admin_token, $url, $item_details);
    $result1 = json_encode(['err' => $result]);
   // echo $result1;

      if ($result['ID']){

                //error_log($result['ID']);
                //after syncing the product on arcadier, update the tags on shopify to 'synced'

                shopify_add_tag($access_token, $shop, $product_id, "synced");

                //if 0 - not exist yet, create a new row on synced_items table

                $sync_details = [

                "product_id" => $product_id,
                "synced_date" => time(),
                "merchant_guid" => $userId,
                'arc_item_guid' => $result['ID'],
                'variant_id' => $variant_id
                
                ];

            
                 //update the item's custom field

                $data = [
                    'CustomFields' => [
                        [
                            'Code' =>  $is_shopify_code,
                            'Values' => [ 1 ],
                        ],
                    ],
                ];

                $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $result['ID'];
                $result = callAPI("PUT", $admin_token, $url, $data);


                $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);
                //add counter to the total created 
                //$total_created++;

                            
            }

          echo json_encode('success');

}else {
    
    echo json_encode('This item has been updated');

    $url =  $baseUrl . '/api/v2/merchants/'. $userId.'/items/' . $isItemSyncResult['Records'][0]['arc_item_guid'];
    $updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 
}



?>