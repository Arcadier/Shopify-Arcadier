<?php
ini_set('max_execution_time', 0); // 0 = Unlimited
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

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];

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

//$shopify_products = shopify_get_all_products($access_token, $shop);

$shopify_products = shopify_products_paginated_id($access_token, $shop, null, false);


if ($shopify_products) {

    bulk_sync_items($shopify_products, $access_token, $shop,$baseUrl,$userId,$admin_token, $packageId,$arc, $is_shopify_code);
}


function bulk_sync_items($products, $access_token, $shop, $baseUrl, $userId, $admin_token, $packageId, $arc, $is_shopify_code) {

    //step 2.  Loop and check if the item has been sync e.g if item exists on synced products custom table

    foreach($products['data']['products']['edges'] as $product) {

        //get the shopify id
        $allvariants = [];
        $product_id = $product['node']['id'];
        
        $product_details = shopify_product_details($access_token, $shop, ltrim($product_id,"gid://shopify/Product/"));   // shopify_get_variants($access_token, $shop, $product_id);
        error_log('prod ' . json_encode($product_details));
        

        $product_name = $product_details['product']['title'];
        $description = $product_details['product']['body_html'];
        $product_type = $product_details['product']['product_type'];

        $variants = $product_details['product']['variants'];


        $has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

        $price = $variants[0]['price'];
        $variant_id = $variants[0]['id'];
        $inventory = $variants[0]['inventory_quantity'];
        $sku = $variants[0]['sku'];

        $image =  $product_details['product']['images'][0]['src'];


        if ($has_variants) {

            $images = $product_details['product']['images'];

        
            //count the options array
        // ;
            foreach($variants as $variant){


        // echo 'variant ' .  json_encode($variant);
            $id = $variant['id'];
            $variant_image =  findItem($images, $id);
            
            
            //   array_filter($images, function($image) use ($id) {
            //   $filtered =  in_array($id, $image['variant_ids']);
            //   return $filtered;
            
            // });

        //echo ('variant image ' . json_encode($variant_image));

      //  echo 'count ' . count($product_details['product']['options']);
        
                
                count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => array( "MediaUrl" => $variant_image['src'])) : '';
                count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => array("MediaUrl" => $variant_image['src'])) : '';
                count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' => '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => array( "MediaUrl" => $variant_image['src'])) : '';
            
        

            }

         //   echo 'all variants ' .json_encode($allvariants);
            
        }


    // echo $product_id;

        //check if the item has been sync
        $syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
        $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
        $isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);
       

        if ($isItemSyncResult['TotalRecords'] == 0) {

            //create a new item on arcadier 
            
            //check if the shopify category has been mapped

            //Load arcadier categories
                    $arcadier_categories = $arc->getCategories(1000, 1);

                    //error_log(json_encode($arcadier_categories));
                // error_log('Arcadier Categories: '.json_encode($arcadier_categories));
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
                        if($product_type == null){
                            $shopify_product_category =  $product_type;
                        }else{
                            $shopify_product_category =  $product_type;
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
                        'SKU' =>  $sku,
                        'Name' =>  $product_name,
                        'BuyerDescription' => $description,
                        'SellerDescription' => $description,
                        'Price' => (float)$price,
                        'PriceUnit' => null,
                        'StockLimited' => true,
                        'StockQuantity' =>  $inventory,
                        'IsVisibleToCustomer' => true,
                        'Active' => true,
                        'IsAvailable' => '',
                        'CurrencyCode' =>  'AUD',
                        'Categories' =>   $all_categories,
                        'ShippingMethods'  => null,
                        'PickupAddresses' => null,
                        'Media' => [
                            array( "MediaUrl" =>  $image)
                            
                            ],
                        'Tags' => null,
                        'CustomFields' => null,
                        'ChildItems' => $allvariants,

                    );

                  //  echo json_encode($item_details);
                    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
                    $result =  callAPI("POST", $admin_token, $url, $item_details);
                    $result1 = json_encode(['err' => $result]);
                    // error_log($result1);
                    //echo 'item added';

                    //update the tag of the item in shopify after a successful item upload

                    if ($result['ID']){

                       // error_log($result['ID']);
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
                        $total_created++;

                                    
                    }
                    

                }
                else{
                // echo 'not mapped';
                    $category_map = '<b>Not Mapped</b>';
                }
            

            
        }else {
            
                $arcadier_categories = $arc->getCategories(1000, 1);
            // error_log('Arcadier Categories: '.json_encode($arcadier_categories));
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
                        if($product_type == null){
                            $shopify_product_category = $product_type;
                        }else{
                            $shopify_product_category = $product_type;
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
                        'SKU' =>  $sku,
                        'Name' =>  $product_name,
                        'BuyerDescription' => $description,
                        'SellerDescription' => $description,
                        'Price' => (float)$price,
                        'PriceUnit' => null,
                        'StockLimited' => true,
                        'StockQuantity' =>  $inventory,
                        'IsVisibleToCustomer' => true,
                        'Active' => true,
                        'IsAvailable' => '',
                        'CurrencyCode' =>  'AUD',
                        'Categories' =>   $all_categories,
                        'ShippingMethods'  => null,
                        'PickupAddresses' => null,
                        'Media' => [
                            array( "MediaUrl" => $image)
                            
                            ],
                        'Tags' => null,
                        'CustomFields' => null,
                        'ChildItems' => $allvariants,

                        );
                        
                    $url =  $baseUrl . '/api/v2/merchants/'. $userId.'/items/' . $isItemSyncResult['Records'][0]['arc_item_guid'];
                    $updateItem =  callAPI("PUT", $admin_token, $url, $data); 
        

                    //if exists, get the row id and edit the row entry of synced_date

                    $synced_item_id =  $isItemSyncResult['Records'][0]['Id'];

                    $sync_details = [

                        "synced_date" => time(),

                    ];	
                    //register any changes on the update
                    //1. retrieve the item details

                    $item_details =  $arc->getItemInfo($isItemSyncResult['Records'][0]['arc_item_guid']);

                    $changed = 0;
                    $unchanged = 0;
                    $field_changed = [];

                    //check each properties 
                    $product['title'] != $item_details['Name'] ? ($changed++). ($field_changed[]='Title')  : $unchanged++;
                    $product['body_html'] != $item_details['SellerDescription'] ? ($changed++). ($field_changed[]='Description')  : $unchanged++;
                    (float)$product['variants'][0]['price'] != $item_details['Price'] ? ($changed++). ($field_changed[]='Price')  : $unchanged++;
                    $product['variants'][0]['inventory_quantity'] != $item_details['StockQuantity'] ? ($changed++). ($field_changed[]='Total Inventory')  : $unchanged++;
                    
                    
                // echo 'total changed ' . $changed;
                // echo 'total unchanged ' . $unchanged;
                // echo json_encode($field_changed);

                    $changed !== 0 ?  $total_changed++ : $total_unchanged++;

                    $response = $arc->editRowEntry($packageId, 'synced_items', $synced_item_id, $sync_details);

                }
            }
        }


            $count_details = [

                'sync_type' => 'Manual',
                'sync_trigger' => 'Bulk sync',
                'total_changed' => $total_changed,
                'total_unchanged' => $total_unchanged,
                'total_created' => $total_created,
                'status' => 'Sync successful'
            ];

            $create_event = $arc->createRowEntry($packageId, 'sync_events', $count_details);

            //next iteration

           $hasnextpage = $products['data']['products']['pageInfo']['hasNextPage'];

           error_log('has next page ' . $hasnextpage);
            if ($hasnextpage){

            if($hasnextpage == false){
                    
                    echo json_encode('done syncing');
                    
                }else {
                    //error_log('Found more items');
                    $productList = $products['data']['products']['edges'];
                    foreach($productList as $product){
                        if(!next($productList)){
                            $last_cursor = $product['cursor'];

                            $moreProducts =  shopify_products_paginated_id($access_token, $shop, $last_cursor, true);
                            if ($moreProducts) {
                                bulk_sync_items($moreProducts, $access_token, $shop, $baseUrl, $userId, $admin_token, $packageId, $arc, $is_shopify_code);     
                            }
                            
                            
                        }
                        
                    }

                } 

            }else {
                echo json_encode('done syncing');    
            }
			

    
    }


 function findItem(array $variants, int $id)
{
    foreach ($variants as $variant) {
        if  (in_array($id, $variant['variant_ids']))
        {
            return $variant;
        }

        //return null;
    }
}