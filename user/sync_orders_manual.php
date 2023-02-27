<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

//get shopify credentials
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
///$userId = $content['userId'];
$invoice_id = $content['invoice-id'];
$order_id = $content['order-id'];
$user_id = $content['user-id']; //buyer id now

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$packageId = getPackageID();
// Query to get marketplace id

$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = $arc->getUserInfo($user_id);
$userId = $result['ID'];



$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $admin_token, $url, false);
$admin_id = $result['ID'];



//get the transaction record with invoice id

$url = $baseUrl . '/api/v2/admins/' . $admin_id . '/transactions/' . $invoice_id; 
$result = callAPI("GET", $admin_token, $url, false);
//error_log('admin ' . json_encode($result));

//query for cart item custom field
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$order_sync_to_shopify_code = '';
$user_shopify_customerId_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'order_sync_to_shopify' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $order_sync_to_shopify_code = $cf['Code'];
    }

     if ($cf['Name'] == 'shopify_customer_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $user_shopify_customerId_code = $cf['Code'];
    }

}

//loop through each orders, assuming there are multiple merchants / invoice , since this is bespoke


foreach($result['Orders'] as $order) { 

    $orderId = $order['ID'];

   // if ($order_id == $orderId) {

        $merchant_id = $order['MerchantDetail']['ID'];

        $auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $merchant_id));
        $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
        $authDetails =  callAPI("POST", $admin_token, $url, $auth);

        $shop_secret_key = $authDetails['Records'][0]['secret_key'];
        $shop_api_key = $authDetails['Records'][0]['api_key'];
        $shop = $authDetails['Records'][0]['shop'];
        $auth_id = $authDetails['Records'][0]['Id'];
        $access_token= $authDetails['Records'][0]['access_token'];


        //check if the order has already been synced
        $syncOrders = array(array('Name' => 'order_id', "Operator" => "equal",'Value' => $orderId), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $merchant_id));
        $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_orders';
        $isOrderSyncResult =  callAPI("POST", $admin_token, $url, $syncOrders);

        error_log('Check if order was in synced_orders table: '.json_encode($isOrderSyncResult));
        
        if ($isOrderSyncResult['TotalRecords'] == 0) {    

            //check if the customer already exist,if exist, get the shopify customer id
            //if not, create a new customer, shopify validates duplicate customer via email
            
            $consumer_id = $order['ConsumerDetail']['ID'];

            $shopify_id = '';

            //revising to user custom tables instead to support buyers -> multi store registration


            $customers = array(array('Name' => 'arc_user_guid', "Operator" => "equal",'Value' => $consumer_id), array('Name' =>
            'store_name', "Operator" => "equal",'Value' => $shop) );
            
            $url = $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/customers';
            $customerDetails = callAPI("POST", $admin_token, $url,  $customers);

            error_log("customer exist " . json_encode($customerDetails));



             if ($customerDetails['TotalRecords']  ==  1) {

                $shopify_id = ltrim($customerDetails['Records'][0]['shopify_user_id'],"gid://shopify/Customer/");
                
            }
        
          

            //if no customer id exists, create a new one
            if (!$shopify_id) {

                //get the customer details ;

                $consumer_fname =  $order['ConsumerDetail']['FirstName'];
                $consumer_lname =  $order['ConsumerDetail']['LastName'];
                $consumer_email = $order['ConsumerDetail']['Email'];

                //create the customer
                $customer =  createCustomer($access_token, $shop, $consumer_fname, $consumer_lname, $consumer_email);
                error_log('New Customer created: '. json_encode($customer));

                //if customer email exists on shopify store

                if ($customer['userErrors'][0]['message'] == 'Email has already been taken') {

                    $search_email = shopify_get_customer_by_email($access_token, $shop, $order['ConsumerDetail']['Email']);

                    $shopify_id = $search_email['customers'][0]['id'];
                    error_log('existing ' . $shopify_id);
                  
                }
                  
        
                if ($customer['customer']['id']) {

                   $shopify_id = ltrim($customer,"gid://shopify/Customer/");
                    
                    //save the obtained customer id on customer's custom table

                        $customer_details = [
                            
                            "arc_user_guid" => $consumer_id,
                            "shopify_user_id"=> $customer,
                            "store_name" =>  $shop

                            
                        ];

                 //   error_log(json_encode($customer_details));
                    

                    $response = $arc->createRowEntry($packageId, 'customers',  $customer_details);
                  //  error_log('customer create ' . json_encode($response));
                    

                    // $data = [
                    //     'CustomFields' => [
                    //         [
                    //             'Code' => $user_shopify_customerId_code,
                    //             'Values' => [ $customer_id ]
                    //         ],
                    //     ],
                    // ];
            
                    // $url = $baseUrl . '/api/v2/users/' . $consumer_id ;
                    // $result = callAPI("PUT", $admin_token, $url, $data);
                    // error_log('Customer with new custom field:'.json_encode($result));
                }
            }

            //loop through each cart item details, assuming there are multiple different items on the cart, or some items in the cart are not from shopify
            $all_items = [];
            foreach($order['CartItemDetails'] as $cartItem) {

                 $quantity = $cartItem['Quantity'];
                
                $cartItemId =  $cartItem['ID'];
                $itemId =  $cartItem['ItemDetail']['ID'];

              

                //checking if the item is a variant or not
                if ($cartItem['ItemDetail']['ParentID'] == null) {
                    
                    $itemId =  $cartItem['ItemDetail']['ID'];
                
                    $syncItems = array(array('Name' => 'arc_item_guid', "Operator" => "equal",'Value' => $itemId));
                    $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
                    $isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);


                    $product_details = shopify_product_details($access_token, $shop, ltrim($isItemSyncResult['Records'][0]['product_id'],"gid://shopify/Product/"));  

                    //echo json_encode($product_details);
                    
                    // echo json_encode($isItemSyncResult);
                    
                    $variant_id = ltrim($isItemSyncResult['Records'][0]['variant_id'],"gid://shopify/ProductVariant/"); 
                    $global_variant_id = $isItemSyncResult['Records'][0]['variant_id'];

                    //echo 'variant id no variant ' . $variant_id;
                   
                }else {

                    $product_details = shopify_product_details($access_token, $shop, ltrim($isItemSyncResult['Records'][0]['product_id'],"gid://shopify/Product/"));  

                        //echo json_encode($product_details);
                    $variantId = $cartItem['ItemDetail']['ID'];
                    $parentId = $cartItem['ItemDetail']['ParentID'];
                    $url = $baseUrl . '/api/v2/items/' . $parentId;
                    $item = callAPI("GET", $admin_token, $url, false);
                    $childItems = $item['ChildItems'];
                   // error_log('variant id ' . $variantId);
                   // error_log('ChildItems: '.json_encode($childItems));

                    // $filtered = array_filter($childItems, function($value) use ($variantId) {
                    //     return $value['ID'] == $variantId;
                    // });
                    
                    //search the variant id on item's customfields, 
                    $variant_ids = '';
                    foreach($item['CustomFields'] as $customfield){

                        if ($customfield['Name'] == 'shopify_variant_id' && substr($customfield['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                            $variant_ids = json_decode($customfield['Values'][0],true);
                        }
                    }

                   // error_log('variant ids ' . json_encode($variant_ids));

                    foreach ($variant_ids as $child) { 

                        //$isthere =  strpos($child['AdditionalDetails'], "gid://shopify/ProductVariant/");
                        //error_log($isthere);

                        if ($child['variant_id'] ==  $variantId ) {
                            
                            error_log('yes');

                         //   if (strpos($child['AdditionalDetails'], "gid://shopify/ProductVariant/") == true) {
                               // echo 'true';
                               $variant_id = ltrim($child['shopify_id'], "gid://shopify/ProductVariant/");
                               $global_variant_id = $child['shopify_id'];
                               break;
                          //  }


                        }
                    }

                 
                }

                $all_items[] = array('variant_id' => $variant_id,'quantity' => $quantity);
                    
                    

                //check the details of the item using the item id to check if the item is from shopify

                $url = $baseUrl . '/api/v2/items/' . $itemId;
                $item = callAPI("GET", $admin_token, $url, false);
                $is_shopify_item = '';
                
                if ($item['CustomFields'] != null)  {

                    foreach ($item['CustomFields'] as $cf) {
                        if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                            $is_shopify_item = $cf['Values'][0];
                            error_log('Is shopify item?'.json_encode($is_shopify_item));
                            //if 1, it is a shopify item else not
                        }
                    }

                    if ($is_shopify_item == "1") {
                    
                        //search the item details on the synced_items custom table

                        $syncItems = array(array('Name' => 'arc_item_guid', "Operator" => "equal",'Value' => $itemId));
                        $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
                        $isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);

                        // echo json_encode($syncItems);
                        
                        // echo json_encode($isItemSyncResult);
                        
                       // $variant_id = ltrim($isItemSyncResult['Records'][0]['variant_id'],"gid://shopify/ProductVariant/");     

                        
                                  
                    }   
                }
            }   

            //get latest payment status
            $latest = 0;
            foreach($order['Statuses'] as $key => $status){
                $timestamp = (int)date("U",strtotime($status['CreatedDateTime']));
                if($timestamp > $latest && $status['Type'] == 'Payment'){
                    $latest = $timestamp;
                    $latest_key = $key;
                }
            }

            $latest_payment_status = $order['Statuses'][$latest_key]['Name'];

            //get latest fulfilment status
            $latest = 0;
            foreach($order['Statuses'] as $key => $status){
                $timestamp = (int)date("U",strtotime($status['CreatedDateTime']));
                if($timestamp > $latest && $status['Type'] == 'Fulfilment'){
                    $latest = $timestamp;
                    $latest_key = $key;
                }
            }

            $latest_fulfilment_status = $order['Statuses'][$latest_key]['Name'];
            error_log('Lastest FulfilmentStatus:'. json_encode($latest_fulfilment_status));

            $order_statuses = status_mapping($latest_fulfilment_status, $latest_payment_status);
            $payment_status = $order_statuses['payment_status'];
            $fulfilment_status = $order_statuses['fulfilment_status'];

          //  error_log('Payment Status:'.json_encode($payment_status));
         //   error_log('Payment Status:'.json_encode($fulfilment_status));


            $api_endpoint = "/admin/api/2022-04/orders.json";

            //part where you will send the orders, but this is for 1 item only
            $query = array(
                'order' => array('line_items' => $all_items,
                                "financial_status"=> $payment_status,
                                "fulfillment_status"=> $fulfilment_status,
                                "customer" => array(
                                    "id" => (int)$shopify_id
                                ),
                                "tags" => "Arcadier",
                                "note" => $baseUrl,
                                "shipping_address" => [
                                    "first_name" => explode(" ", $order['DeliveryToAddress']['Name'])[0],
                                    "last_name" => end(explode(" ", $order['DeliveryToAddress']['Name'])),
                                    "address1" => $order['DeliveryToAddress']['Line1'],
                                    "address2" => $order['DeliveryToAddress']['Line2'],
                                    "city"=> $order['DeliveryToAddress']['City'],
                                    "zip"=> $order['DeliveryToAddress']['PostCode'],
                                    "province" => $order['DeliveryToAddress']['State'],
                                    "country"=> $order['DeliveryToAddress']['Country'],
                                    "name"=> $order['DeliveryToAddress']['Name'],
                                    "country_code"=> $order['DeliveryToAddress']['CountryCode']
                                ],
                                "billing_address" => [
                                    "first_name" => explode(" ", $order['DeliveryToAddress']['Name'])[0],
                                    "last_name" => end(explode(" ", $order['DeliveryToAddress']['Name'])),
                                    "address1" => $order['DeliveryToAddress']['Line1'],
                                    "address2" => $order['DeliveryToAddress']['Line2'],
                                    "city"=> $order['DeliveryToAddress']['City'],
                                    "zip"=> $order['DeliveryToAddress']['PostCode'],
                                    "province" => $order['DeliveryToAddress']['State'],
                                    "country"=> $order['DeliveryToAddress']['Country'],
                                    "name"=> $order['DeliveryToAddress']['Name'],
                                    "country_code"=> $order['DeliveryToAddress']['CountryCode']
                                ]
                            ),
            );
           // error_log('SHopify Order query: '.  json_encode($query));

           //$location_id =  shopify_get_variant_location($access_token, $shop, $global_variant_id);

          // error_log('location id ' . $location_id);

            $orders = shopify_call($access_token, $shop, "/admin/orders.json", json_encode($query), 'POST',array("Content-Type: application/json"));

            //error_log('Shopify orders API response ' .  json_encode($orders['response']));
            $order_response = json_encode($orders['response']);
            error_log($order_response);

            if ($order_response['order']){


                $locations = shopify_get_location($access_token, $shop);
               
                 //verify the store either post code 5000 or 5006
                 $location_id;
      
                  if (count($locations['locations']) == 1) {
      
                      $location_id = $locations['locations'][0]['id'];
                      
                  }else {
      
                      foreach($locations['locations'] as $location) {
      
                          //  error_log('loc ' . json_encode($location));
                
                            //get the zip
                          
                            // if ( $location['zip'] == "0005") {  //test location
                
                            if ($location['zip'] == "5000"  || $location['zip'] == "5006" ) {  //adelaide location
                
                                    $location_id = $location['id'];
                                
                            }
                            break;
                
                      }
      
                  }
      
               error_log($location_id);
      
               //get the inventory item id from variant id
      
              //test 42815844286623
               $variants =  shopify_get_variant_details($access_token, $shop, $variant_id);
      
               $inventory_item_id =  $variants['variant']['inventory_item_id'];
      
      
               $inventory_details = [
                  "location_id" =>  $location_id,
                  "inventory_item_id" =>  $inventory_item_id,
                  "available_adjustment" => -$quantity
               ];
      
               $adjust_inventory =  shopify_update_inventory($access_token, $shop,  $inventory_details);

            }

  
            $count_details = [

                'sync_type' => 'Manual (Orders)',
                'sync_trigger' => 'Order Creation',
                'total_changed' => '-',
                'total_unchanged' => '-',
                'total_created' => 1,
                'status' => 'Sync successful',
                'merchant_guid' => $userId
            ];


            $create_event = $arc->createRowEntry($packageId, 'sync_events', $count_details);

            //register the event on synced_orders custom table

            $sync_details = [

                "order_id" => $orderId,
                "merchant_guid" => $merchant_id,
                'status' => $order_response['order'] ? 'Success' :  'Failed',
                'status_description' => json_encode($order_response)
            
            ];
                
            $response = $arc->createRowEntry($packageId, 'synced_orders', $sync_details);
            error_log('Arcadier Custom Table response: '.json_encode($response));

            //echo json_encode('success');

        // } else {
        //     echo json_encode('This order has been sync');
        // }
   }        
}

function status_mapping($fulfilment, $payment){

    $payment_status_map = [
        "Waiting For Payment" => "pending",
        "Payment Requested" => "pending",
        "Pending" => "pending",
        "Acknowledged" => "pending",
        "Fully Paid" => "paid",
        "Paid" => "paid",
        "Failed" => "voided",
        "Refunded" => "refunded",
        "Acknowledged" => "pending"
    ];

    $fulfilment_status_map = [
        "Delivered" => "fulfilled",
        "Acknowledged" => null,
        "Collected" => "fulfilled",
        "Ready For Consumer Collection" => "partial"
    ];

    $response = [
        "payment_status" => $payment_status_map[$payment],
        "fulfilment_status" => $fulfilment_status_map[$fulfilment]
    ];

    return $response;
}
?>