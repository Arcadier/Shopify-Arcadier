<?php

// Get our helper functions
include 'callAPI.php';
require_once("shopify.php");
require 'api.php';
//
//include 'admin_token.php';
$API = new ApiSdk;

//include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
//$userId = $content['userguid'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $API->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();

$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];
$packageId = getPackageID();


//search on 'auth' custom table instead, referencing the merchant guid, 
//store per merchant should only be 1 at a time, 
//else if adding another store, delete the former one

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId), array(array('Name' => 'access_token', "Operator" => "like",'Value' => 'shpua_')));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

//echo json_encode($authDetails);
//update the access token from the obtained credentials


$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];

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

	
$auth_id = $authDetails['Records'][0]['Id'];

		$auth_details = [
            
            'access_token' => $access_token
    
        ];	

$response = $API->editRowEntry($packageId, 'auth', $auth_id, $auth_details);

	// Show the access token (don't do this in production!)
	echo 'This application has already been installed.';
	echo $access_token;

} else {
	// Someone is trying to be shady!
	die('This request is NOT from Shopify!');
}

}