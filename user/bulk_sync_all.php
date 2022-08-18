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
// $userToken = $_COOKIE["webapitoken"];
// $url = $baseUrl . '/api/v2/users/'; 
// $result = callAPI("GET", $userToken, $url, false);
// $userId = $result['ID'];

$packageId = getPackageID();

// Query user authentication 

// get all the merchants in custom table

$allmerchants =  $arc->getCustomTable($packageId, "auth", $admin_token);

//echo json_encode($allmerchants);

foreach($allmerchants['Records'] as $merchant) {

  $userId = $merchant['merchant_guid'];


//$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId), array('Name' => 'access_token', "Operator" => "like",'Value' => 'shpua_'));
//$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
//$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $merchant['secret_key'];
$shop_api_key = $merchant['api_key'];
$shop = $merchant['shop'];
$auth_id = $merchant['Id'];
$access_token= $merchant['access_token'];


$total_created = 0;
$total_unchanged = 0;
$total_changed = 0;

// get the custom field id to tag that the items are from shopify

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
}



//step 1. Get all shopify products
$shopify_products = shopify_get_all_products($access_token, $shop);

//step 2.  Loop and check if the item has been sync e.g if item exists on synced products custom table

foreach($shopify_products as $product) {

    //get the shopify id
    $product_id = $product['node']['id'];
   // echo $product_id;

    //check if the item has been sync

$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);
    
//echo 'sync  result ' . json_encode($isItemSyncResult);

if ($isItemSyncResult['TotalRecords'] == 0) {

    //create a new item on arcadier 
    
    //check if the shopify category has been mapped

    //Load arcadier categories
            $arcadier_categories = $arc->getCategories(1000, 1);
          //  error_log('Arcadier Categories: '.json_encode($arcadier_categories));
            $arcadier_categories = $arcadier_categories['Records'];

            //Load Category Map
            //search custom table for category map
            // $data = [
            //     'Name' => 'merchant_guid',
            //     'Operator' => 'equal',
            //     'Value' => $userId
            // ];
            // $category_map = $arc->searchTable($packageId, 'map', $data);
            
            $data = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
            $url =  $baseUrl . '/api/v2/plugins/'. $packageId.'/custom-tables/map';
            $category_map  =  callAPI("POST", $admin_token, $url, $data);    

            //echo 'category map ' . json_encode($category_map);
            if($category_map['TotalRecords'] == 1){
                $category_map = $category_map['Records'][0]['map'];
                if($product['node']['customProductType'] == null){
                    $shopify_product_category = $product['node']['product_type'];
                }else{
                    $shopify_product_category = $product['node']['customProductType'];
                }
               // echo 'item has been mapped';
                //echo json_encode($category_map);
                $category_map_unserialized = unserialize($category_map);
                $shopify_category_list = $category_map_unserialized['list'];
        
                //find the corresponding Arcadier category according to map
                $destination_arcadier_categories = []; //these are the arcadier category id's needed
                foreach($shopify_category_list as $li){
                    if($li['shopify_category'] == $shopify_product_category.'_category'){
                        foreach($li['arcadier_guid'] as $arcadier_category){
                            array_push($destination_arcadier_categories, $arcadier_category);
                        }
                    }
                }

            //finally create the item with the mapped category
            $all_categories = [];
            foreach($destination_arcadier_categories as $category) {
                    $all_categories[] = array("ID" => $category);
                    
            }


            $item_details = array(
                'SKU' =>  'sku',
                'Name' =>  $product['node']['title'],
                'BuyerDescription' => $product['node']['description'],
                'SellerDescription' => $product['node']['description'],
                'Price' => (float)$product['node']['variants']['edges'][0]['node']['price'],
                'PriceUnit' => null,
                'StockLimited' => true,
                'StockQuantity' => $product['node']['totalInventory'] ,
                'IsVisibleToCustomer' => true,
                'Active' => true,
                'IsAvailable' => '',
                'CurrencyCode' =>  'SGD',
                'Categories' =>   $all_categories,
                'ShippingMethods'  => null,
                'PickupAddresses' => null,
                'Media' => [
                    array( "MediaUrl" => $product['node']['images']['edges'][0]['node']['originalSrc'])
                    
                    ],
                'Tags' => null,
                'CustomFields' => null,
                'ChildItems' => null,

            );

            $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
            $result =  callAPI("POST", $admin_token, $url, $item_details);
            $result1 = json_encode(['err' => $result]);
          //  error_log($result1);
            //echo $result1;
            //echo 'item added';


            //update the tag of the item in shopify after a successful item upload

            if ($result['ID']){

                error_log($result['ID']);
                //after syncing the product on arcadier, update the tags on shopify to 'synced'

                shopify_add_tag($access_token, $shop, $product_id, "synced");

                //if 0 - not exist yet, create a new row on synced_items table

                $sync_details = [

                "product_id" => $product_id,
                "synced_date" => time(),
                "merchant_guid" => $userId,
                'arc_item_guid' => $result['ID'],
                'variant_id' => $product['node']['variants']['edges'][0]['node']['id']
                
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
                $total_created++;



            }
            

        }
        else{
           // echo 'not mapped';
            $category_map = '<b>Not Mapped</b>';
        }
    

    
}else {
     // update the product details 


        $arcadier_categories = $arc->getCategories(1000, 1);
      //  error_log('Arcadier Categories: '.json_encode($arcadier_categories));
        $arcadier_categories = $arcadier_categories['Records'];

        //Load Category Map
        //search custom table for category map
        // $data = [
        //     'Name' => 'merchant_guid',
        //     'Operator' => 'equal',
        //     'Value' => $userId
        // ];
        // $category_map = $arc->searchTable($packageId, 'map', $data);
        
        $data = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
        $url =  $baseUrl . '/api/v2/plugins/'. $packageId.'/custom-tables/map';
        $category_map  =  callAPI("POST", $admin_token, $url, $data); 


        if($category_map['TotalRecords'] == 1){

            $category_map = $category_map['Records'][0]['map'];
                if($product['node']['customProductType'] == null){
                    $shopify_product_category = $product['node']['product_type'];
                }else{
                    $shopify_product_category = $product['node']['customProductType'];
                }
            // echo 'item has been mapped';
                //echo json_encode($category_map);
                $category_map_unserialized = unserialize($category_map);
                $shopify_category_list = $category_map_unserialized['list'];
        
                //find the corresponding Arcadier category according to map
                $destination_arcadier_categories = []; //these are the arcadier category id's needed
                foreach($shopify_category_list as $li){
                    if($li['shopify_category'] == $shopify_product_category.'_category'){
                        foreach($li['arcadier_guid'] as $arcadier_category){
                            array_push($destination_arcadier_categories, $arcadier_category);
                        }
                    }
                }

            //finally create the item with the mapped category
            $all_categories = [];
            foreach($destination_arcadier_categories as $category) {
                    $all_categories[] = array("ID" => $category);
                    
            }


            $data = 
                array(
                'SKU' =>  'sku',
                'Name' =>  $product['node']['title'],
                'BuyerDescription' => $product['node']['description'],
                'SellerDescription' => $product['node']['description'],
                'Price' => (float)$product['node']['variants']['edges'][0]['node']['price'],
                'PriceUnit' => null,
                'StockLimited' => true,
                'StockQuantity' => $product['node']['totalInventory'] ,
                'IsVisibleToCustomer' => true,
                'Active' => true,
                'IsAvailable' => '',
                'CurrencyCode' =>  'SGD',
                'Categories' =>   $all_categories,
                'ShippingMethods'  => null,
                'PickupAddresses' => null,
                'Media' => [
                    array( "MediaUrl" => $product['node']['images']['edges'][0]['node']['originalSrc'])
                    
                    ],
                'Tags' => null,
                'CustomFields' => null,
                'ChildItems' => null,

                );
                
            $url =  $baseUrl . '/api/v2/merchants/'. $userId.'/items/' . $isItemSyncResult['Records'][0]['arc_item_guid'];
            $updateItem =  callAPI("PUT", $admin_token, $url, $data); 
   

            //if exists, get the row id and edit the row entry of synced_date

            $synced_item_id =  $isItemSyncResult['Records'][0]['Id'];

            $sync_details = [

                "synced_date" => time(),

            ];	


             $item_details =  $arc->getItemInfo($isItemSyncResult['Records'][0]['arc_item_guid']);

            $changed = 0;
            $unchanged = 0;
            $field_changed = [];

            //check each properties 
            $product['node']['title'] != $item_details['Name'] ? ($changed++). ($field_changed[]='Title')  : $unchanged++;
            $product['node']['description'] != $item_details['SellerDescription'] ? ($changed++). ($field_changed[]='Description')  : $unchanged++;
            (float)$product['node']['variants']['edges'][0]['node']['price'] != $item_details['Price'] ? ($changed++). ($field_changed[]='Price')  : $unchanged++;
            $product['node']['totalInventory'] != $item_details['StockQuantity'] ? ($changed++). ($field_changed[]='Total Inventory')  : $unchanged++;
              
            
           // echo 'total changed ' . $changed;
           // echo 'total unchanged ' . $unchanged;
           // echo json_encode($field_changed);

            $changed !== 0 ?  $total_changed++ : $total_unchanged;


            $response = $arc->editRowEntry($packageId, 'synced_items', $synced_item_id, $sync_details);
            //echo 'the sync details has been updated';
         }
    }
    
    }
}

$count_details = [

    'sync_type' => 'Scheduled (every 15 minutes)',
    'sync_trigger' => 'Bulk sync',
    'total_changed' => $total_changed,
    'total_unchanged' => $total_unchanged,
    'total_created' => $total_created,
    'status' => 'Sync successful'
];

$create_event = $arc->createRowEntry($packageId, 'sync_events', $count_details);
echo json_encode('done syncing');
error_log('bulk sync all has been run ');