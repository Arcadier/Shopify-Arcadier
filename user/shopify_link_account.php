<?php
include 'callAPI.php';
require 'api.php';
//include 'admin_token.php';
$API = new ApiSdk();
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$shopify_key = $content['shopify-key'];
$shopify_store_name = $content['shopify-store'];
$shopify_secret_key = $content['secret-key'];


$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
//$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

// $url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
// $packageCustomFields = callAPI("GET", null, $url, false);

// //this will be for the later part once the connection is established

// foreach ($packageCustomFields as $cf) {
//     if ($cf['Name'] == 'shopify_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
//            $shopify_key_code = $cf['Code'];
//     }
//     if ($cf['Name'] == 'shopify_store_name' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
//         $shopify_storename_key = $cf['Code'];
//     }

//      if ($cf['Name'] == 'shopify_secret_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
//            $shopify_secretkey_code = $cf['Code'];
//     }
   
// }

// $data = [
//     'CustomFields' => [
//         [
//             'Code' =>  $shopify_key_code,
//             'Values' => [$shopify_key],
//         ],

//         [
//             'Code' =>  $shopify_storename_key,
//             'Values' => [$shopify_store_name],
//         ],

//         [
//             'Code' =>   $shopify_secretkey_code,
//             'Values' => [$shopify_secret_key],
//         ],

//     ],
// ];

// $url = $baseUrl . '/api/v2/users/' . $userId;
// $result = callAPI("PUT", $admin_token['access_token'], $url, $data);
//echo json_encode(['data' =>  $result ]);


//save to 'auth' custom table instead

$credentials = [

    "api_key" => $shopify_key,
    "secret_key" => $shopify_secret_key,
    //"access_token" => $shopify_key,
    "shop" => $shopify_store_name,
    "merchant_guid" => $userId,
    "auth_status"=> "1"
    
];

$packageId = getPackageID();
$response = $API->createRowEntry($packageId, 'auth', $credentials);


$shop = $shopify_store_name;
$api_key = $shopify_key;


$scopes = "read_orders,write_products,write_orders";
$redirect_uri =  $baseUrl . '/user/plugins/' . $packageId . '/shopify-token.php';   //?secret-key=' . $shopify_secret_key .'&api-key='. $api_key

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

echo json_encode([$install_url]);
// Redirect
//header("Location: " . $install_url);
//die();


?>