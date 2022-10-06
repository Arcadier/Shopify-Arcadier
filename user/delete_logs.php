<?php

include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();


$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$packageId = getPackageID();


$allLogs = $arc->getCustomTable($packageId, "synced_items", $admin_token);

echo json_encode($allLogs);


foreach($allLogs['Records']  as $log) {
    $arc->deleteRowEntry($packageId, "synced_items", $log['Id']);
}