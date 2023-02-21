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

    foreach($admin_shippingMethods as $shipping) { 
         $all_shipping_methods[] = array("ID" => $shipping['ID']);
    
    }
    
}

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

$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

$prices = [];

 foreach($variants as $variant){
    $prices[] = (float)$variant['price'];
 }

$price = min($prices);

$variant_id = $variants[0]['id'];
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

    foreach($variants as $variant){

        $id = $variant['id'];
        $variant_image =  findItem($images, $id);
        $media = '';
        if ($variant_image['src'] != null) {

            $media = array(array( "MediaUrl" => $variant_image['src']));
        // $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
        }

            count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  '',  'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'] - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
            count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '','Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']   - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
            count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' =>  '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
    
    }
}

$allvariants = !empty($allvariants) ? $allvariants : null;

$item_details = array(
      'SKU' =>  'sku',
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
      'ShippingMethods'  => $all_shipping_methods,
      'PickupAddresses' => null,
      'Media' => $allimages,
      'Tags' => null,
      'CustomFields' => null,
      'ChildItems' =>  $allvariants

);

if ($isItemSyncResult['TotalRecords'] == 0 && $product_details['product']['status'] == 'active' )  {

    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
    $result =  callAPI("POST", $admin_token, $url, $item_details);
    $result1 = json_encode(['err' => $result]);
     
        if ($result['ID']){

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
                error_log('details ' . json_encode($item_details));
                echo json_encode('success');
            }else {
                echo json_encode('Cannot sync archived item');
            }
         
            ///error_log(json_encode($result1));

                if ($result['ID']){

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

        //set active : false if shopify item is archived
        if ($product_details['product']['status'] == 'archived' || $product_details['product']['status'] == 'draft' ) {

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
        

        }

        // if ($has_variants) {
        
        //     foreach ($childItems as $arc_variant) {

        //         $child_id = $arc_variant['ID'];

        //         $data =  [
        //             'ChildItems' => [
        //                     [
        //                         "ID" => $child_id,
        //                         "Active" => false
        //                     ]
        //                 ]
        //             ];

        //             $updateItem =  $arc->editItem($data, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);

        //     }
          

        // }
        echo json_encode('This item has been updated');
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