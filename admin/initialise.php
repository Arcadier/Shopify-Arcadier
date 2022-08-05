<?php
    include 'callAPI.php';
    include 'admin_token.php';

    $contentBodyJson = file_get_contents('php://input');
    $content = json_decode($contentBodyJson, true);

    $baseUrl = getMarketplaceBaseUrl();
    $admin_token = getAdminToken();
    $packageID = getPackageID();
    $plugin_cf_code = '';
    $toggle_value = '';
    $i=0;

    $url = $baseUrl.'/api/v2/packages/'.$packageID.'/custom-field-definitions';
    $plugin_cf = callAPI('GET', $admin_token['access_token'], $url, null);
    

    for($i; $i < count($plugin_cf); $i++){
        if($plugin_cf[$i]['Name'] == 'Magento Toggle'){
            $plugin_cf_code = $plugin_cf[$i]['Code'];
        }
    }

    // echo json_encode($plugin_cf_code);

    $url = $baseUrl.'/api/v2/marketplaces';
    $response = callAPI('GET', $admin_token['access_token'], $url, false);   
    
    $marketplace_cf_array = $response['CustomFields'];
    $length = count($marketplace_cf_array);

    for($i = 0; $i < $length; $i++){
        if($marketplace_cf_array[$i]['Code'] ==  $plugin_cf_code){
            $toggle_value = $marketplace_cf_array[$i]['Values'][0];
        }
    }

    echo json_encode($toggle_value);
    
?>