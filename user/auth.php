<?php
require_once "admin_token.php";
include 'defines.php';
    
function get_admin_token(){
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