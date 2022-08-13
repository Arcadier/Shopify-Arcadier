<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

//get shopify credentials
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$userId = $content['userId'];
$invoice_id = $content['invoice-id'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$packageId = getPackageID();
// Query to get marketplace id

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];




$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $admin_token, $url, false);
$admin_id = $result['ID'];

$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

//get the transaction record with invoice id

$url = $baseUrl . '/api/v2/admins/' . $admin_id . '/transactions/' . $invoice_id; 
$result = callAPI("GET", $admin_token, $url, false);
error_log('admin ' . json_encode($result));

//query for cart item custom field
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$order_sync_to_shopify_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'order_sync_to_shopify' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $order_sync_to_shopify_code = $cf['Code'];
    }
}


//loop through each orders, assuming there are multiple merchants / invoice , since this is bespoke


foreach($result['Orders'] as $order) {

    $orderId = $order['ID'];
   
    //loop through each cart item details, assuming there are multiple different items on the cart, or some items in the cart are not from shopify

    foreach($order['CartItemDetails'] as $cartItem) {
        
        $cartItemId =  $cartItem['ID'];
        $itemId =  $cartItem['ItemDetail']['ID'];

        //check the details of the item using the item id to check if the item is from shopify

        $url = $baseUrl . '/api/v2/items/' . $itemId;
        $item = callAPI("GET", $admin_token, $url, false);
        $is_shopify_item = '';
        
        if ($item['CustomFields'] != null)  {

        
            foreach ($item['CustomFields'] as $cf) {
                if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                    $is_shopify_item = $cf['Values'][0];
                    //if 1, it is a shopify item else not
                }
            }

            if ($is_shopify_item == "1") {
                //update the cart's custom field

                $data = [
                    'CustomFields' => [
                        [
                            'Code'=> $order_sync_to_shopify_code,
                            'Values' => [
                                1
                            ]
                
                        ]

                    ]   
        
                ];    
                error_log(json_encode($data));
                //echo json_encode(['data' => $data]);
                
                $url =  $baseUrl . '/api/v2/users/'. $userId .'/carts/' . $cartItemId;
                $updateOrders =  callAPI("PUT", $userToken, $url, $data); 
                //echo json_encode(['URL' =>  $updateOrders]);  



                //search the item details on the synced_items custom table

                $syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $itemId), array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
                $url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
                $isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);


                 $api_endpoint = "/admin/api/2022-04/orders.json";


                $arrOrder= [
                "order" =>[
                    "email"                    => "foo@example.com",
                    "fulfillment_status"       => "fulfilled",
                    "send_receipt"             => false,
                    "send_fulfillment_receipt" => false,
                   'line_items' => 
                    array (
                    0 => 
                    array (
                        'variant_id' => 43568896246012,
                        'quantity' => 1,
                    ),
                    ),
                    
                    "financial_status"=> "pending"
                ]
             ];

            // shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array());   
            $query = array('order' =>array('line_items' => array(array('variant_id' => 43568896246012,'quantity' => 1)),"financial_status"=> "pending"));
            $orders = shopify_call($access_token, $shop, "/admin/orders.json", json_encode($query), 'POST',array("Content-Type: application/json"));
            echo json_encode( $orders);

            
        
            //   $createorder=  array (
            //     'order' => 
            //     array (
            //         'line_items' => 
            //         array (
            //         0 => 
            //         array (
            //             'variant_id' => 43568896246012,
            //             'quantity' => 1,
            //         ),
            //         ),
            //         'customer' => 
            //         array (
            //         'first_name' => 'Paul',
            //         'last_name' => 'Norman',
            //         'email' => 'paul.norman@example.com',
            //         ),
            //         'billing_address' => 
            //         array (
            //         'first_name' => 'John',
            //         'last_name' => 'Smith',
            //         'address1' => '123 Fake Street',
            //         'phone' => '555-555-5555',
            //         'city' => 'Fakecity',
            //         'province' => 'Ontario',
            //         'country' => 'Canada',
            //         'zip' => 'K2P 1L4',
            //         ),
            //         'shipping_address' => 
            //         array (
            //         'first_name' => 'Jane',
            //         'last_name' => 'Smith',
            //         'address1' => '123 Fake Street',
            //         'phone' => '777-777-7777',
            //         'city' => 'Fakecity',
            //         'province' => 'Ontario',
            //         'country' => 'Canada',
            //         'zip' => 'K2P 1L4',
            //         ),
            //         'email' => 'jane@example.com',
            //         'transactions' => 
            //         array (
            //         0 => 
            //         array (
            //             'kind' => 'authorization',
            //             'status' => 'success',
            //             'amount' => 50,
            //         ),
            //         ),
            //         'financial_status' => 'partially_paid',
            //     ),
            //     );

                // $order_array = [
                //     'order' => [
                //         'email' => "foo@example.com",
                //         'line_items' => [
                //            [ 'variant_id' => 43568896246012,
                //             'quantity' => 1
                //            ]
                //         ]
                //     ]
                // ];
                
               


















                
            }   

        }
        
    }           

}







  
?>