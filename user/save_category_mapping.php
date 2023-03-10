<?php
include 'shopify_functions.php';
include_once 'api.php';
$arc = new ApiSdk();

$pack_id = getPackageID();
$marketplace_domain = getMarketplaceDomain();
date_default_timezone_set(TIME_ZONE_PLUGIN);
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$userId = $content['user-id'];
$all_mapped = $content['mapping-data'];

//search for merchant auth of the user if exists

$data_auth = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $userId
            ],
            [
                'Name'=> 'auth_status',
                'Operator'=> 'equal',
                'Value'=> "1"
            ]
            
        ];
$authListByMerchantGuid = $arc->searchTable($pack_id, 'auth', $data_auth);

//search map custom field

  $data_map = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $userId
            ],
            [
                'Name'=> 'shop',
                'Operator'=> 'equal',
                'Value'=> $authListByMerchantGuid['Records'][0]['shop']
            ]
            ];

        $response = $arc->searchTable($pack_id, 'map', $data_map);

//if exists, save the data first
$insertData = [
    'merchant_guid' => $userId,
    'map' => $all_mapped,
    'shop' => $authListByMerchantGuid['Records'][0]['shop']
];

  if ($response['TotalRecords'] == 1) { 

    $arc->editRowEntry($pack_id, 'map', $response['Records'][0]['Id'], $insertData);
    echo 'edited';

  }else {
        $insertRowInmap = $arc->createRowEntry($pack_id, 'map', $insertData);
        echo 'added';

  }