<?php
include 'magento_functions.php';
include 'api.php';
$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$marketplace_domain = getMarketplaceDomain();
date_default_timezone_set(TIME_ZONE_PLUGIN);
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$getAllMerchants = $arc->getAllMerchants();
echo "<pre>"; print_r($getAllMerchants); die;