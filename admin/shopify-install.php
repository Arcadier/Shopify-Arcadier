<?php

// Set variables for our request
require 'callAPI.php';
require 'admin_token.php';

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();

$shop = "codemafia-2";
$api_key = "c59630834c30e920dd5411a6b67c5da4";
$scopes = "read_orders,write_products";
$redirect_uri =  $baseUrl . '/admin/plugins/' . $packageId . '/shopify-token.php'; //"http://localhost/generate_token.php";

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

// Redirect
header("Location: " . $install_url);
die();