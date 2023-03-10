<?php

include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
///$userId = $content['userId'];
$item_id = $content['itemId'];
$merchant_id = $content['merchantId'];


$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$packageId = getPackageID();

  $data = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $merchant_id
                ],
                [
                    'Name'=> 'arc_item_guid',
                    'Operator'=> 'equal',
                    'Value'=> $item_id
                ]
            ];
            
            $synced_details = $arc->searchTable($packageId, 'synced_items', $data);

//echo json_encode($synced_details);

foreach($synced_details['Records']  as $log) {
   $deleteItem =  $arc->deleteRowEntry($packageId, "synced_items", $log['Id']);

    $product_id = $deleteItem['product_id'];

    $data = [
        [
        'Name'=> 'merchant_guid',
        'Operator'=> 'equal',
        'Value'=> $merchant_id
        ],
        [
            'Name'=> 'product_id',
            'Operator'=> 'equal',
            'Value'=> $product_id
        ]
    ];
    
    $product_details = $arc->searchTable($packageId, 'synced_items', $data);

        foreach($product_details['Records']  as $log) {

            $deleteItem =  $arc->deleteRowEntry($packageId, "synced_items", $log['Id']);

        }
}

echo json_encode($synced_details['TotalRecords']);