<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

$ignore_variants = false;
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i");

$location_id =  $content['location-id'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
//$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

//east-end-cellars shop name
// if ($userId == "967c7067-c589-417a-9f45-3b9902f7d1e3") {
//     $ignore_variants = true;
// }

$result = callAPI("GET", $admin_token, $url, false);

$admin_id = $result['ID'];

$packageId = getPackageID();

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';


//get merchant's shipping method

//$url =  $baseUrl . '/api/v2/merchants/' . $userId . '/shipping-methods/';
$merchant_shippingMethods =  $arc->getShippingMethods($userId); //callAPI("GET", $admin_token, $url, false);
$admin_shippingMethods =  $arc->getShippingMethods($admin_id);

//error_log('shipping methods ' . json_encode($merchant_shippingMethods));


$all_shipping_methods = [];

if (!empty($shippingMethods)) {

    foreach($merchant_shippingMethods as $shipping) { 
        
        $all_shipping_methods[] = array("ID" => $shipping['ID']);
    
    }

}
else {
   // error_log('empty merchant');

    foreach($admin_shippingMethods as $shipping) { 

        if ($shipping['Description'] != "Digital Download (FREE)") {
            //error_log('not equal ' . $shipping['Description']);

            $all_shipping_methods[] = array("ID" => $shipping['ID']);

        }
         
    
    }
    
}

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
    if ($cf['Name'] == 'shopify_variant_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $shopify_variant_id = $cf['Code'];
    }
    //location id
    if ($cf['Name'] == 'location_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $location_id_code = $cf['Code'];
    }
}

//error_log('auth ' . json_encode($authDetails));

// $shop_secret_key = $authDetails['Records'][0]['secret_key'];
// $shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];



//east-end-cellars shop name
if ($shop  == "east-end-cellars") {
    $ignore_variants = true;
}


$product_id =  $content['id'];
$product_name = $content['name'];
$categories =  $content['category'];
$images = $content['images'];
//price = $content['price'];
$stock = $content['qty'];

$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);

//1.  save the items on arc using merchant API,

$all_categories = [];

foreach($categories as $category) { 
    $all_categories[] = array("ID" => $category);
    
}

//get the variant
$images = shopify_get_images($access_token, $shop, $product_id);

$product_details = shopify_product_details($access_token, $shop, ltrim($product_id,"gid://shopify/Product/"));   // shopify_get_variants($access_token, $shop, $product_id);


$product_name = $product_details['product']['title'];
$description = $product_details['product']['body_html'];
$description = strip_tags(html_entity_decode($description));

$variants = $product_details['product']['variants'];

error_log('variant json ' . json_encode($variants));

$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;


$prices = [];

$locations = shopify_get_location($access_token, $shop);
               
//verify the store either post code 5000 or 5006
//$location_id = $location_id_set;


$total_inventory_no_variants = 0;

 foreach($variants as $variant){
    $prices[] = (float)$variant['price'];

    $id = $variant['id'];
    error_log('variant id '. $id);
    //get the inventory id from variant details
    $variants_details =  shopify_get_variant_details($access_token, $shop, $id);
    $inventory_item_id =  $variants_details['variant']['inventory_item_id'];
    error_log('inventory id ' .  $inventory_item_id);
    //get the total qty from the given location

   // if (count($locations['locations']) == 1) {


   // }else {
        $inventory_level = shopify_get_inventory_level_by_location($access_token, $shop, $inventory_item_id, $location_id);
        error_log('level ' .  json_encode($inventory_level));
        $inventory_count = $inventory_level['inventory_levels'][0]['available'];
        $total_inventory_no_variants  += $inventory_count;

   // }

 
    //error_log('count ' . $inventory_count);
}

$price = min($prices);

$variant_id = $variants[0]['id'];
$variant_title = $variants[0]['title'];
$variant_price = $variants[0]['price'];


error_log('variant id main ' . $variant_id);

$inventory = $variants[0]['inventory_quantity'];
$sku = $variants[0]['sku'];

$allimages = [];

foreach($images  as $image) {
    $allimages[] = array('MediaUrl' => $image['node']['originalSrc']) ;
    
    //$allimages = [];

}

if ($has_variants) {

    $allvariants = [];

    $images = $product_details['product']['images'];
    
    //count the options array
    $all_variant_ids = [];

    $total_inventory = 0;
    foreach($variants as $variant){
       // $all_variant_ids[] = [ "variant_id" => $variant['id'] ]
      
        $id = $variant['id'];
        error_log('variant id '. $id);
        //get the inventory id from variant details
        $variants_details =  shopify_get_variant_details($access_token, $shop, $id);
        $inventory_item_id =  $variants_details['variant']['inventory_item_id'];
        error_log('inventory id ' .  $inventory_item_id);
        //get the total qty from the given location
        $inventory_level = shopify_get_inventory_level_by_location($access_token, $shop, $inventory_item_id, $location_id);
        //error_log('level ' .  json_encode($inventory_level));
        $inventory_count = $inventory_level['inventory_levels'][0]['available'];
        $total_inventory += $inventory_count;
        error_log('count ' . $inventory_count);

        $variant_image =  findItem($images, $id);
        $media = '';
        if ($variant_image['src'] != null) {

            $media = array(array( "MediaUrl" => $variant_image['src']));
        // $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
        }
        
            count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  '',  'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price'] - $price, 'StockLimited' => true, 'StockQuantity' => count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count, 'Media' => $media, 'Name' => "gid://shopify/ProductVariant/" . $id, 'CustomFields' => $custom) : '';
            count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '','Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price']   - $price, 'StockLimited' => true, 'StockQuantity' => count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count, 'Media' => $media, 'Name' => "gid://shopify/ProductVariant/" . $id, 'CustomFields' => $custom) : '';
            count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' =>  '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => "gid://shopify/ProductVariant/" . $id, 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => count($locations['locations']) == 1 ? $variant['inventory_quantity'] : $inventory_count,'Media' => $media,  'Name' => "gid://shopify/ProductVariant/" . $id, 'CustomFields' => $custom) : '';
    
    }
}

$allvariants = !empty($allvariants) ? $allvariants : null;
$all_variant_ids = !empty($all_variant_idss) ? $all_variant_ids : null;


//error_log('all shipping ' . json_encode($all_shipping_methods));

if ($ignore_variants) {

    $item_details = array(
        'SKU' =>  'sku',
        'Name' =>  $product_name,
        'BuyerDescription' => $description . ' ' . $variant_title,
        'SellerDescription' => $description . ' ' . $variant_title,
        'Price' => (float)$variant_price,
        'PriceUnit' => null,
        'StockLimited' => true,
        'StockQuantity' => $inventory, //count($locations['locations']) == 1 ? $inventory : $total_inventory_no_variants,
        'IsVisibleToCustomer' => true,
        'Active' => true, 
        'IsAvailable' => '',
        'CurrencyCode' =>  'AUD',
        'Categories' =>   $all_categories,
        'ShippingMethods'  => $all_shipping_methods,
        'PickupAddresses' => null,
        'Media' => $allimages,
        'Tags' => null,
        'CustomFields' => null,
        'ChildItems' => null //$allvariants
  
  );

}else {
    $item_details = array(
        'SKU' =>  'sku',
        'Name' =>  $product_name,
        'BuyerDescription' => $description ,
        'SellerDescription' => $description,
        'Price' => (float)$variant_price,
        'PriceUnit' => null,
        'StockLimited' => true,
        'StockQuantity' =>  count($locations['locations']) == 1 ? $inventory : $total_inventory_no_variants,
        'IsVisibleToCustomer' => true,
        'Active' => true, 
        'IsAvailable' => '',
        'CurrencyCode' =>  'AUD',
        'Categories' =>   $all_categories,
        'ShippingMethods'  => $all_shipping_methods,
        'PickupAddresses' => null,
        'Media' => $allimages,
        'Tags' => null,
        'CustomFields' => null,
        'ChildItems' =>  $allvariants
  
  );

}



if ($isItemSyncResult['TotalRecords'] == 0 && $product_details['product']['status'] == 'active' )  {

    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
    $result =  callAPI("POST", $admin_token, $url, $item_details);
    $result1 = json_encode($result);

    //error_log('items added ' . $result1);
     
        if ($result['ID']){

            //get the variant id and shopify id from the child items loop
            $variant_details = [];
            foreach($result['ChildItems'] as $variant) {

                $variant_details[] = [ 
                    "variant_id" => $variant['ID'],
                    'shopify_id' => $variant['SKU']
                ];
            }

            //if 0 - not exist yet, create a new row on synced_items table

            $sync_details = [

            "product_id" => $product_id,
            "synced_date" => time(),
            "merchant_guid" => $userId,
            'arc_item_guid' => $result['ID'],
            'variant_id' => "gid://shopify/ProductVariant/" . $variant_id
            
            ];
                //update the item's custom field

            $data = [
                'CustomFields' => [
                    [
                        'Code' =>  $is_shopify_code,
                        'Values' => [ 1 ],
                    ],
                    [
                        'Code' =>  $shopify_variant_id,
                        'Values' => [ json_encode($variant_details) ],
                    ],
                    [
                        'Code' =>  $location_id_code,
                        'Values' => [ $location_id ],
                    ],


                ],
            ];

            $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $result['ID'];
            $result = callAPI("PUT", $admin_token, $url, $data);


            $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);
            //add counter to the total created 
            //$total_created++;
            echo json_encode('success');
                        
        }

       

}       
else {


    error_log('exists!');
    $itemInfo =  $arc->getItemInfo($isItemSyncResult['Records'][0]['arc_item_guid']);

    if (array_key_exists('Code', $itemInfo)) {

        error_log('in code cond');

        if ($itemInfo['Code'] == 400){

            error_log('in 400');

            //if the item is on synced details and does not exist on arcadier, add it as new one
            if ($product_details['product']['status'] == 'active'){

                $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
                $result =  callAPI("POST", $admin_token, $url, $item_details);
                $result1 = json_encode(['err' => $result]);
               // error_log('details ' . json_encode($item_details));
                echo json_encode('success');
            }else {
                echo json_encode('Cannot sync draft or archived item');
            }
         
            ///error_log(json_encode($result1));

                if ($result['ID']){

                     //get the variant id and shopify id from the child items loop
                    $variant_details = [];
                    foreach($result['ChildItems'] as $variant) {

                        $variant_details[] = [ 
                            "variant_id" => $variant['ID'],
                            'shopify_id' => $variant['SKU']
                        ];
                    }

                    //error_log($result['ID']);
                    //after syncing the product on arcadier, update the tags on shopify to 'synced'

                    //shopify_add_tag($access_token, $shop, $product_id, "synced");

                    //if 0 - not exist yet, create a new row on synced_items table

                    $sync_details = [

                    "product_id" => $product_id,
                    "synced_date" => time(),
                    "merchant_guid" => $userId,
                    'arc_item_guid' => $result['ID'],
                    'variant_id' => "gid://shopify/ProductVariant/" . $variant_id
                    
                    ];

                
                    //update the item's custom field

                    $data = [
                        'CustomFields' => [
                            [
                                'Code' =>  $is_shopify_code,
                                'Values' => [ 1 ],
                            ],
                            [
                                'Code' =>  $shopify_variant_id,
                                'Values' => [ json_encode($variant_details) ],
                            ],
                            [
                                'Code' =>  $location_id_code,
                                'Values' => [ $location_id ],
                            ],
                        ],
                    ];

                    $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $result['ID'];
                    $result = callAPI("PUT", $admin_token, $url, $data);


                    $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);

                // error_log(json_encode($response));

                    
                    //add counter to the total created 
                    //$total_created++;

                                
                }
        }

    }
    else {
       
        //error_log('details ' . json_encode($item_details));
        //error_log('update ' .  json_encode($updateItem));

        //set active : false if shopify item is archived
        if ($product_details['product']['status'] == 'archived' || $product_details['product']['status'] == 'draft' ) {


            echo json_encode('Cannot sync draft or archived item');

            $url =  $baseUrl . '/api/v2/merchants/'. $userId .'/items/'. $isItemSyncResult['Records'][0]['arc_item_guid'];
            $deleteItem =  callAPI("DELETE", $admin_token, $url, null); 


            $data = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $userId
                ],
                [
                    'Name'=> 'product_id',
                    'Operator'=> 'equal',
                    'Value'=> $product_id
                ]
            ];
    
            $synced_details = $arc->searchTable($packageId, 'synced_items', $data);
    
                foreach($synced_details['Records']  as $log) {
        
                    $deleteItem =  $arc->deleteRowEntry($packageId, "synced_items", $log['Id']);
                }
        

        }else {


             //make all the variants inactive
        $childItems =  $itemInfo['ChildItems']; 
        foreach ($childItems as $arc_variant) {

            $child_id = $arc_variant['ID'];

            $data =  [
                'ChildItems' => [
                        [
                            "ID" => $child_id,
                            "Active" => false
                        ]
                    ]
                ];

                $updateItem =  $arc->editItem($data, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);

        }

        error_log('not code cond');
        $updateItem =  $arc->editItem($item_details, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);
        //error_log('item detailssss ' . json_encode($item_details));
        //error_log('updated item ' . json_encode($updateItem));
        //update the custom fields again on updating
        $variant_details = [];
        foreach($updateItem['ChildItems'] as $variant) {

            if ($variant['Active'] == true) {
                $variant_details[] = [ 
                    "variant_id" => $variant['ID'],
                    'shopify_id' => $variant['SKU']
                ];

            }
        }
        $data = [
            'CustomFields' => [
               
                  [
                    'Code' =>  $shopify_variant_id,
                    'Values' => [ json_encode($variant_details) ]
                  ],
                    [
                        'Code' =>  $location_id_code,
                        'Values' => [ $location_id ],
                    ],
                

            ],
        ];

        $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $updateItem['ID'];
        $result = callAPI("PUT", $admin_token, $url, $data);

        echo json_encode('This item has been updated');


        }

       
    
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

 function findVariant(array $variants, string $name)
{
    foreach ($variants as $variant) {


         $shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $variant['AdditionalDetails']);

         

       // foreach($variant['Variants'] as $childvariant) {

          //  error_log('name '  . $childvariant['Name']);
            if  ($name ==  $shopify_variant_ID)

            {
              //  error_log('found variant ' . json_encode($childvariant));
                    
                return $variant;
            }

      //  }
             

        //return null;
    }
}


?>