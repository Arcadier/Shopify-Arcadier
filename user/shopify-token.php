<?php

// Get our helper functions
require_once("shopify.php");

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
//$userId = $content['userguid'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();

$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];





$url = $baseUrl . '/api/v2/users/' . $userId; 
$merchant = callAPI("GET", $admin_token['access_token'], $url, false);  

foreach($merchant['CustomFields'] as $cf) 
{ 
    
if ($cf['Name'] == 'shopify_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $shop_api_key = $cf['Values'][0];
     
 }
  if ($cf['Name'] == 'shopify_secret_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $shop_secret_key = $cf['Values'][0];
    
}

}

if ($shop_secret_key) {
error_log($shop_secret_key);
// Set variables for our request
//peroroncino - app name 
$api_key = $shop_api_key; //"c59630834c30e920dd5411a6b67c5da4";
$shared_secret = $shop_secret_key; //aee1f1eba05050081ec5e35368d7935f";
$params = $_GET; // Retrieve all request parameters
$hmac = $_GET['hmac']; // Retrieve HMAC request parameter

$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
ksort($params); // Sort params lexographically

$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

// Use hmac data to check that the response is from Shopify or not
if (hash_equals($hmac, $computed_hmac)) {

	// Set variables for our request
	$query = array(
		"client_id" => $api_key, // Your API key
		"client_secret" => $shared_secret, // Your app credentials (secret key)
		"code" => $params['code'] // Grab the access key from the URL
	);

	// Generate access token URL
	$access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

	// Configure curl client and execute request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $access_token_url);
	curl_setopt($ch, CURLOPT_POST, count($query));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
	$result = curl_exec($ch);
	curl_close($ch);

	// Store the access token
	$result = json_decode($result, true);
	$access_token = $result['access_token'];


	$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
	$packageCustomFields = callAPI("GET", null, $url, false);

	foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'shopify_access_token' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
           $shopify_access_token = $cf['Code'];
    }
	}
	$data = [
    'CustomFields' => [
        [
            'Code' =>  $shopify_access_token,
            'Values' => [$access_token],
        ],


    ],
];

$url = $baseUrl . '/api/v2/users/' . $userId;
$result = callAPI("PUT", $admin_token['access_token'], $url, $data);

	
	// Show the access token (don't do this in production!)
	echo 'This application has already been installed.';
	echo $access_token;

} else {
	// Someone is trying to be shady!
	die('This request is NOT from Shopify!');
}

}