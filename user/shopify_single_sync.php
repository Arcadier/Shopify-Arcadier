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
$images = shopify_get_images($access_token, $shop, $product_id);

error_log(json_encode($images));


$product_details = shopify_product_details($access_token, $shop, ltrim($product_id,"gid://shopify/Product/"));   // shopify_get_variants($access_token, $shop, $product_id);

$product_name = $product_details['product']['title'];
$description = $product_details['product']['body_html'];
$description = strip_tags(html_entity_decode($description));

$variants = $product_details['product']['variants'];

$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

$price = $variants[0]['price'];
$variant_id = $variants[0]['id'];
$inventory = $variants[0]['inventory_quantity'];
$sku = $variants[0]['sku'];

$allimages = [];

foreach($images  as $image) {
    $allimages[] = array('MediaUrl' => $image['node']['originalSrc']) ;
}

//$image =  $images[0]['node']['originalSrc'];


if ($has_variants) {

    $all_variants = [];


    $images = $product_details['product']['images'];
    

    //count the options array

    foreach($variants as $variant){

    error_log('variant ' .  json_encode($variant));
    $id = $variant['id'];
    $variant_image =  findItem($images, $id);

    if ($variant_image['src'] == null) {
        $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
    }
      
      
    //   array_filter($images, function($image) use ($id) {
    //   $filtered =  in_array($id, $image['variant_ids']);
    //   return $filtered;
    
    // });


    error_log('variant image ' . json_encode($variant_image));
        
        count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => array(array( "MediaUrl" => $variant_image['src'])),'Tags' => array("gid://shopify/ProductVariant/" . $variant_id), 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => array(array("MediaUrl" => $variant_image['src'])),'Tags' => array("gid://shopify/ProductVariant/" . $id), 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' => '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => array(array( "MediaUrl" => $variant_image['src'])), 'Tags' => array("gid://shopify/ProductVariant/" . $id), 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
    
    }
}

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
      'ShippingMethods'  => null,
      'PickupAddresses' => null,
      'Media' => $allimages,
      'Tags' => null,
      'CustomFields' => null,
      'ChildItems' => $allvariants

);


$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);



if ($isItemSyncResult['TotalRecords'] == 0) {


    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
    $result =  callAPI("POST", $admin_token, $url, $item_details);
    $result1 = json_encode(['err' => $result]);
    error_log(json_encode($item_details));
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

                error_log(json_encode($response));

                
                //add counter to the total created 
                //$total_created++;

                            
            }

          echo json_encode('success');

}else {
    
    echo json_encode('This item has been updated');
    error_log(json_encode($item_details));
    

    $url =  $baseUrl . '/api/v2/merchants/'. $userId.'/items/' . $isItemSyncResult['Records'][0]['arc_item_guid'];

    error_log('url '. $url);
   // $updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 

   $updateItem =  $arc->editItem($item_details, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);

    error_log('updated ' . json_encode($updateItem));
    
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

?>