<?php
    include 'callAPI.php';
    include 'admin_token.php';

    $contentBodyJson = file_get_contents('php://input');
    $content = json_decode($contentBodyJson, true);

    $baseUrl = getMarketplaceBaseUrl();
    $admin_token = getAdminToken();
    $packageID = getPackageID();
    $plugin_cf_code = '';
    $i=0;

    $url = $baseUrl.'/api/v2/packages/'.$packageID.'/custom-field-definitions';
    
    $plugin_cf = callAPI('GET', $admin_token['access_token'], $url, false);
    $length = count($plugin_cf);
    
    for($i; $i < $length ; $i++){
        if($plugin_cf[$i]['Name'] == 'Magento Toggle'){
            $plugin_cf_code = $plugin_cf[$i]['Code'];
        }
    }
    // echo json_encode($plugin_cf_code);
    
    if($content['toggle'] == true){
        $url = $baseUrl.'/api/v2/marketplaces';
        $data = [
            'CustomFields' => [
                [
                    'Code' => $plugin_cf_code,
                    'Values' => [
                        "true"
                    ]
                ]
            ]
        ];
        $response = callAPI('POST', $admin_token['access_token'], $url, $data);   
        echo json_encode($response);    
    }
    else{
        $url = $baseUrl.'/api/v2/marketplaces';
        $data = [
            'CustomFields' => [
                [
                    'Code' => $plugin_cf_code,
                    'Values' => [
                        "false"
                    ]
                ]
            ]
        ];
        $response = callAPI('POST', $admin_token['access_token'], $url, $data);
        echo json_encode($response);  
    }
?>