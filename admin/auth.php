<?php
    // namespace Arcadier;
    //require "defines.php";
    require "admin_token.php";
    include 'defines.php';
    /* function getAdminToken(){
    
        $url = PROTOCOL.'://'.DOMAIN . '/token';
        $body = 'grant_type=client_credentials&client_id=' . CLIENT_ID . '&client_secret=' . CLIENT_SECRET . '&scope=admin';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
    
        return json_decode($result, true);
        
    }

    function getUserToken($username, $password){
    
        $url = PROTOCOL.'://'.DOMAIN . '/token';
        $body = 'grant_type=password&client_id=' . CLIENT_ID . '&client_secret=' . CLIENT_SECRET . '&scope=admin&username='.$username.'&password='.$password;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
    
        return json_decode($result, true);
        
    }

    function getMarketplaceDomain(){
        return DOMAIN;
    }

    function getProtocol(){
        return PROTOCOL;
    }
    function getMarketplaceBaseUrl() {
        $marketplace = DOMAIN;
        $protocol = PROTOCOL;
    
        $baseUrl = $protocol . '://' . $marketplace;
        return $baseUrl;
    }
	function getMarketplaceBaseUrl1($arcadier_domain) {
        $marketplace = $arcadier_domain;
        $protocol = PROTOCOL;
    
        $baseUrl = $protocol . '://' . $marketplace;
        return $baseUrl;
    } */
	function getAdminTokenAuth() {
    /* $marketplace = $_COOKIE["marketplace"];
    $protocol = $_COOKIE["protocol"];

    $baseUrl = $protocol . '://' . $marketplace;
    
	$client_id = 'atxQZihLXhPCuByESZyBg12xSm97f04EWNBoolIG';
    $client_secret = '31mbu7MHix_NvJL4nsb8uRamXQrU1U3VUFsi6S7hrD8zHhTcu9Q';

    $url = $baseUrl . '/token';
    $body = 'grant_type=client_credentials&client_id=' . $client_id . '&client_secret=' . $client_secret . '&scope=admin';

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true); */
	$admintoken = getAdminToken();
	return $admintoken;
}

function getMarketplaceDomain(){
    return $_COOKIE["marketplace"];
}

function getProtocol(){
    return $_COOKIE["protocol"];
}
	function getMarketplaceBaseUrl() {
    $marketplace = $_COOKIE["marketplace"];
    $protocol = $_COOKIE["protocol"]; 
	// $marketplace = 'fender.sandbox.arcadier.io';
    // $protocol = 'https';

    $baseUrl = $protocol . '://' . $marketplace;
    return $baseUrl;
}

function getPackageID() {
    $requestUri = "$_SERVER[REQUEST_URI]";
    preg_match('/([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/', $requestUri, $matches, 0);
    return $matches[0];
}

function getCustomFieldPrefix() {
    $requestUri = "$_SERVER[REQUEST_URI]";
    preg_match('/([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/', $requestUri, $matches, 0);
    $customFieldPrefix = str_replace('-', '', $matches[0]);
    return $customFieldPrefix;
}
?>