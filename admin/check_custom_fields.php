<?php
include 'callAPI.php';
include 'admin_token.php';

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$i=0;
$plugin_cf_code = '';
$success = [];

$packageID = getPackageID();
$url = $baseUrl.'/api/v2/packages/'.$packageID.'/custom-field-definitions';
$plugin_cf = callAPI('GET', $admin_token['access_token'], $url, null);

for($i; $i < count($plugin_cf); $i++){
    if($plugin_cf[$i]['Name'] == 'Magento Toggle'){
        $plugin_cf_code = $plugin_cf[$i]['Code'];
    }
}

$url = $baseUrl.'/api/v2/marketplaces';
$data = [
    'CustomFields'=> [
        [
            'Code'=> $plugin_cf_code,
            'Values'=> [
                'false'
            ]
        ]
    ]
];
$response = callAPI('POST', $admin_token['access_token'], $url, $data);   

echo json_encode($response);
?>