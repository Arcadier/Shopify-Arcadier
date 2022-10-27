<?php
//ini_set('max_execution_time', 0); // 0 = Unlimited
ini_set('memory_limit','1024000000');
ini_set('max_input_time', 0);
ini_set('max_execution_time', 0); // 0 = Unlimited


ignore_user_abort(True);
set_time_limit(0);
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

$total_created = 0;
$total_unchanged = 0;
$total_changed = 0;

//$userToken = $_COOKIE["webapitoken"];
//$url = $baseUrl . '/api/v2/users/'; 
//$result = callAPI("GET", $userToken, $url, false);

if ($result['CustomFields'] != null)  {

    foreach ($result['CustomFields'] as $cf) {
        if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                    $sync_items_list = $cf['Values'][0];
                    $sync_items_list = json_decode($sync_items_list,true);
                   // echo (json_encode($sync_items_list));
                    break;
                   
        }
    
    }

}

error_log(json_encode($sync_items_list));


// get the custom field id to tag that the items are from shopify

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
}

$time_start = microtime(true);
$arcadier_categories = $arc->getCategories(1000, 1);
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
//execution time of the script
error_log('<b>Total Execution Time of getting arc categories:</b> '.$execution_time.' seconds');
$arcadier_categories = $arcadier_categories['Records'];

//category mapping query

$time_start = microtime(true);
$data = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
//error_log(json_encode($data));

$url =  $baseUrl . '/api/v2/plugins/'. $packageId.'/custom-tables/map';
$category_map  =  callAPI("POST", $admin_token, $url, $data);    
//error_log(json_encode($category_map));



$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
//execution time of the script
//error_log('<b>Total Execution Time of getting category mapping:</b> '.$execution_time.' seconds');

$product_count = shopify_product_count($access_token, $shop);
$total = $product_count['count'];
//step 1. Get all shopify products

$count_chosen =  count($sync_items_list);

//$shopify_products = shopify_get_all_products($access_token, $shop);

//send initial edm to let users know the import has started
$html = "<html><body> <h2>Import Started</h2> <p style=\"background-color: white\"> Hi $userDisplayName !</p> <br> 
Your Shopify products import has started at " . date("h:i:sa") . "<br>
<b> </b> $count_chosen products were found. <br>
<b> You will receive another notification once the import is completed. <br>
</body> </html>";

$subject= 'Product import started for ' . $shop;

$arc->sendEmail($userEmail, $html, $subject);


//$shopify_products = shopify_get_all_products_unstable_test($access_token, $shop, null, true);


//error_log(json_encode($shopify_products));



//dividing with 60 will give the execution time in minutes other wise seconds


//if ($shopify_products) {
    
    $time_start = microtime(true);
    bulk_sync_items($sync_items_list, $access_token, $shop,$baseUrl,$userId,$admin_token, $packageId,$arc, $is_shopify_code,$arcadier_categories, $category_map,$userEmail);
    $time_end = microtime(true);
    //dividing with 60 will give the execution time in minutes other wise seconds
    $execution_time = ($time_end - $time_start);

    //execution time of the script
    error_log('<b>Total Execution Time of getting bulk sync products:</b> '.$execution_time.' seconds');
//}

function bulk_sync_items($products, $access_token, $shop, $baseUrl, $userId, $admin_token, $packageId, $arc, $is_shopify_code,$arcadier_categories,$category_map,$userEmail) {

    //step 2.  Loop and check if the item has been sync e.g if item exists on synced products custom table
    $time_start = microtime(true);
    $productsLink = shopify_get_bulk_item($access_token, $shop);

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);

//execution time of the script
    error_log('<b>Total Execution Time of getting paginated products:</b> '.$execution_time.' seconds');
        $fp = @fopen($productsLink, "r");
        // if ($fp) {
        //     while (($buffer = fgets($fp)) !== false) {
        //         //echo $buffer;

        //     $buffer =  json_decode($buffer, true);
        //         echo $buffer['id'];

                
        //     }
        //     if (!feof($fp)) {
        //         echo "Error: unexpected fgets() fail\n";
        //     }
        //     fclose($fp);
        // }


    //error_log('prods list ' . json_encode($products));
    $total_variants = 0;
    foreach($products as $product) {
        // while (($buffer = fgets($fp)) !== false) {
        //         //echo $buffer;

        // $buffer =  json_decode($buffer, true);
                //echo $buffer['id'];

        //get the shopify id
        $allvariants = [];
        $product_id =  $product; //;$buffer['id'];
        
        $time_start = microtime(true);
        $product_details = shopify_product_details($access_token, $shop, ltrim($product_id,"gid://shopify/Product/"));   // shopify_get_variants($access_token, $shop, $product_id);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        //execution time of the script
        error_log('<b>Total Execution Time of getting product details:</b> '.$execution_time.' seconds');

        $product_name = $product_details['product']['title'];
        $description = $product_details['product']['body_html'];
        $product_type = $product_details['product']['product_type'];

        $variants = $product_details['product']['variants'];
       // error_log('variants ' . json_encode($variants));

        $has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

        $price = $variants[0]['price'];
        $variant_id = $variants[0]['id'];
       
        $inventory = $variants[0]['inventory_quantity'];
        $sku = $variants[0]['sku'];

        $image =  $product_details['product']['images'][0]['src'];


        $images1 = $product_details['product']['images'];
               // error_log(json_encode($images1));
                
                $allimages = [];

                    foreach($images1  as $image) {
                        $allimages[] = array('MediaUrl' => $image['src']);
                    }

        if ($has_variants) {

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
            
                count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
                count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '' ;
                
                count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' => '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' => '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' => '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price'], 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
                $total_variants++;
            }

         //   echo 'all variants ' .json_encode($allvariants);
            
        }


    // echo $product_id;

        //check if the item has been sync
        $time_start = microtime(true);
        $syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
        $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
        $isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        //execution time of the script
       // error_log('<b>Total Execution Time of checking if the product exists:</b> '.$execution_time.' seconds');
        
        error_log('exist check ' . $isItemSyncResult['TotalRecords']);
        
        if ($isItemSyncResult['TotalRecords'] == 0) {

            //create a new item on arcadier 
            
            //check if the shopify category has been mapped

            //Load arcadier categories
                 

                    //Load Category Map
                    //search custom table for category map
                    // $data = [
                    //     'Name' => 'merchant_guid',
                    //     'Operator' => 'equal',
                    //     'Value' => $userId
                    // ];
                    // $category_map = $arc->searchTable($packageId, 'map', $data);

                   
                   
                    //echo 'category map ' . json_encode($category_map);
                    if($category_map['TotalRecords'] == 1){
                        $category_maps = $category_map['Records'][0]['map'];
                        if($product_type == null){
                            $shopify_product_category =  $product_type;
                        }else{
                            $shopify_product_category =  $product_type;
                        }
                    // echo 'item has been mapped';
                        //echo json_encode($category_map);
                        $category_map_unserialized = json_decode($category_maps, 1);
                        //error_log($category_maps);
                       
                        //  $filtered = array_filter($category_map_unserialized, function($value) use ($shopify_product_category) {

                        //     error_log('val '. json_encode($value));
                        //  return $value['shopify_category'] == $shopify_product_category;


                        //  });
                                            
                        $destination_arcadier_categories = [];
                         foreach($category_map_unserialized as $category){
                            if ($category['shopify_category'] == $shopify_product_category) {
                                $destination_arcadier_categories = $category['mapped_arc_categories'];
                            }
                         }


                
                    //finally create the item with the mapped category
                    $all_categories = [];
                    foreach($destination_arcadier_categories as $category) {
                            $all_categories[] = array("ID" => $category);
                            
                    }
                
                    $time_start = microtime(true);
                    $item_details = array(
                        'SKU' =>  $sku,
                        'Name' =>  $product_name,
                        'BuyerDescription' => strip_tags($description),
                        'SellerDescription' => strip_tags($description),
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
                        'ChildItems' => $allvariants,

                    );

                  //  echo json_encode($item_details);
                    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
                    $result =  callAPI("POST", $admin_token, $url, $item_details);
                    $time_end = microtime(true);
                    $execution_time = ($time_end - $time_start);
                    //execution time of the script
                   // error_log('<b>Total Execution Time of saving the item:</b> '.$execution_time.' seconds');
                    $result1 = json_encode(['err' => $result]);
                    // error_log($result1);
                    //echo 'item added';

                    //update the tag of the item in shopify after a successful item upload
                    //error_log('item id ' .$result['ID']);
                    if ($result['ID']){

                       // error_log($result['ID']);
                        //after syncing the product on arcadier, update the tags on shopify to 'synced'
                        // $time_start = microtime(true);
                        // shopify_add_tag($access_token, $shop, $product_id, "synced");
                        // $time_end = microtime(true);
                        // $execution_time = ($time_end - $time_start);
                        //execution time of the script
                       // error_log('<b>Total Execution Time of updating tags to shopify:</b> '.$execution_time.' seconds');    
                        //if 0 - not exist yet, create a new row on synced_items table

                        
                        $sync_details = [

                            "product_id" => $product_id,
                            "synced_date" => time(),
                            "merchant_guid" => $userId,
                            'arc_item_guid' => $result['ID'],
                            'variant_id' => "gid://shopify/ProductVariant/" . $variant_id
                            
                        ];

                    
                        //update the item's custom field
                        $time_start = microtime(true);
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
                        $time_end = microtime(true);
                        $execution_time = ($time_end - $time_start);
                        //execution time of the script
                      //  error_log('<b>Total Execution Time of updating the item custom field:</b> '.$execution_time.' seconds');    


                        $time_start = microtime(true);
                        $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);
                       
                        $time_end = microtime(true);
                        $execution_time = ($time_end - $time_start);
                        //execution time of the script
                       // error_log('<b>Total Execution Time of updating the item synced custom table:</b> '.$execution_time.' seconds');    

                        

                       // error_log('sync customt able ' . json_encode($response));
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


                if($category_map['TotalRecords'] == 1) {

                    $category_maps = $category_map['Records'][0]['map'];
                        if($product_type == null){
                            $shopify_product_category = $product_type;
                        }else{
                            $shopify_product_category = $product_type;
                        }
                   
                         $category_map_unserialized = json_decode($category_maps, 1);
                       

                        $destination_arcadier_categories = [];
                         foreach($category_map_unserialized as $category){
                            if ($category['shopify_category'] == $shopify_product_category) {
                                $destination_arcadier_categories = $category['mapped_arc_categories'];
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
                        'BuyerDescription' => strip_tags($description),
                        'SellerDescription' => strip_tags($description),
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
                    $product_name != $item_details['Name'] ? ($changed++). ($field_changed[]='Title')  : $unchanged++;
                    strip_tags($description) != $item_details['SellerDescription'] ? ($changed++). ($field_changed[]='Description')  : $unchanged++;
                    (float)$product['variants'][0]['price'] != $item_details['Price'] ? ($changed++). ($field_changed[]='Price')  : $unchanged++;
                    $product['variants'][0]['inventory_quantity'] != $item_details['StockQuantity'] ? ($changed++). ($field_changed[]='Total Inventory')  : $unchanged++;
                    
                    
                // echo 'total changed ' . $changed;
                // echo 'total unchanged ' . $unchanged;
                // echo json_encode($field_changed);

                    $changed !== 0 ?  $total_changed++ : $total_unchanged++;

                    $response = $arc->editRowEntry($packageId, 'synced_items', $synced_item_id, $sync_details);

                }
            }
        } //end product loop

        //  if (!feof($fp)) {
        //         echo "Error: unexpected fgets() fail\n";
        //     }
        //     fclose($fp);

            $count_details = [

                'sync_type' => 'Manual',
                'sync_trigger' => 'Bulk sync',
                'total_changed' => $total_changed,
                'total_unchanged' => $total_unchanged,
                'total_created' => $total_created,
                'status' => 'Sync successful'
            ];
            $time_start = microtime(true);
            $create_event = $arc->createRowEntry($packageId, 'sync_events', $count_details);
            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start);
            //execution time of the script
           // error_log('<b>Total Execution Time of saving the event log to custom table:</b> '.$execution_time.' seconds');
            //next iteration
            echo json_encode('done syncing');

            $total_created = $total_created ? $total_created : 0;
            $total_unchanged =  $total_unchanged ?  $total_unchanged : 0;
            $total_changed =  $total_changed ?  $total_changed  : 0;
            error_log('total variants ' .  $total_variants);

            //send edm here instead
    
            $html = "<html><body> <h2>Import Completed</h2> <p style=\"background-color: white\"> Hi $userEmail !</p> <br> 
            Your Shopify products that you started importing is now complete. <br>
            <b> $total_created </b> products were created. <br>
            <b> $total_changed </b> products were updated. <br>
            </body> </html>";
            // ];
            // $admin_id = $arc->getAdminId();
            // $url =  $baseUrl . '/api/v2/admins/' . $admin_id .'/emails';
            // $sendEDM = callAPI("POST", $admin_token, $url, $data);
            // error_log(json_encode($sendEDM));
            $subject= 'Product import complete for ' . $shop;
            
            $arc->sendEmail($userEmail, $html, $subject);
            
    
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