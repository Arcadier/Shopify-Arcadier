<?php
include 'shopify_functions.php';
include_once 'api.php';
$arc = new ApiSdk();

$pack_id = getPackageID();
$marketplace_domain = getMarketplaceDomain();
date_default_timezone_set(TIME_ZONE_PLUGIN);
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

/*Test Request*/
if(isset($content['test'])){
	if (isset($content['usr'])) {
    $domain = $content['domain'];
    $username = $content['usr'];
    $password = $content['pwd'];
    // $m_username = $content['m_username'];
    // $m_password = $content['m_password'];
    // $m_domain = $content['m_domain'];
	 //$arc_token_gen1=$arc->getAdminTokenMerchant($m_username,$m_password,$m_domain);
     $arc_token_gen1=getAdminTokenAuth();
	if(empty($arc_token_gen1)){
	$message = 'Not Authenticated The Merchant domain is incorrect';
    echo json_encode(array('message' => $message)); die;
	}
	if(!empty($arc_token_gen1['error'])){
	$message = 'Not Authenticated The Merchant username or password is incorrect';
    echo json_encode(array('message' => $message)); die;
	}else{
	 $arc_token_gen=$arc_token_gen1;
	}
	
    $merchant_arc_guid=$arc_token_gen['UserId'];
    $arc_access_token=$arc_token_gen['access_token'];
    $arc_refresh_token=$arc_token_gen['refresh_token'];
    $response = $mag->magento_auth($domain, $username, $password);
    $mag_token1 = json_decode($response);
	if(empty($mag_token1)){
	$message = 'Not Authenticated The Magento domain is incorrect';
    echo json_encode(array('message' => $message)); die;
	}
	if(!empty($mag_token1->message)){
	$message = 'Not Authenticated The Magento username or password is incorrect';
    echo json_encode(array('message' => $message)); die;	
	}else{
	$mag_token=$mag_token1;
	}
    
    $data = [
        'merchant_guid' => $merchant_arc_guid,
        'username' => $username,
        'password' => $password,
        'domain' => $domain,
        'token' => $mag_token
    ];
	$dataa = [
        'merchant_guid' => $merchant_arc_guid,
        'enabled' => 1,
        'mode' => 0,
        'sync_threshold_1' => 0,
        'sync_threshold_2' => 0
    ];
    $data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $merchant_arc_guid
        ]
      ];
      $data2 = [
        'token' => $mag_token
    ];
	if (!empty($mag_token)) {
	$message = 'Successful';
    echo json_encode(array('message' => $message));
	}else{
	$message = 'Unsuccessful';
    echo json_encode(array('message' => $message));	
	}
    


}
}


/*Disconnect Auth*/
if (isset($content['deauth'])) {
    $domain = $content['domain1'];
    $username = $content['username'];
    $password = $content['password'];
	// $m_username = $content['m_username'];
    // $m_password = $content['m_password'];
    // $m_domain = $content['m_domain'];
    $auth = $content['auth'];
    if (isset($content['del'])) {
        $data = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
            ],
            [
                'Name'=> 'auth_status',
                'Operator'=> 'equal',
                'Value'=> "1"
            ]
            
            ];
        $authListByMerchantGuid=$arc->searchTable($pack_id, 'auth', $data);
        $data_config = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
            ],
            [
                'Name'=> 'domain',
                'Operator'=> 'equal',
                'Value'=> $authListByMerchantGuid['Records'][0]['domain']
            ]
            
            ];
        $deleteRowByMerchantGuid=$arc->deleteRowEntry($pack_id, 'auth',$authListByMerchantGuid['Records'][0]['Id']);
		$configListByMerchantGuid=$arc->searchTable($pack_id, 'config', $data_config);
        $deleteconfigRowByMerchantGuid=$arc->deleteRowEntry($pack_id, 'config',$configListByMerchantGuid['Records'][0]['Id']);
        /* unset($_COOKIE['mag_user']);
        unset($_COOKIE['mag_pass']);
        unset($_COOKIE['mag_domain']);
        unset($_COOKIE['mag_token']);
        unset($_COOKIE['arc_access_token']);
        unset($_COOKIE['merchant_arc_guid']);
        unset($_COOKIE['m_username']);
        unset($_COOKIE['m_password']);
        unset($_COOKIE['m_domain']);
        unset($_COOKIE['auth']);

        setcookie("mag_user", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("mag_pass", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("mag_domain", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("mag_token", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("arc_access_token", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("merchant_arc_guid", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("m_username", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("m_password", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("m_domain", '', time() + (10 * 365 * 24 * 60 * 60));
        setcookie("auth", '', time() + (10 * 365 * 24 * 60 * 60)); */
        $message = 'Disconnected';
        echo $message;
    } else {
        /* unset($_COOKIE['auth']);
        setcookie("auth", '', time() + (10 * 365 * 24 * 60 * 60)); */
        $data = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
            ],
            [
                'Name'=> 'auth_status',
                'Operator'=> 'equal',
                'Value'=> "1"
            ]
            
            ];
            $data1 = [
                'auth_status' => '0'
            ];
        $authListByMerchantGuid=$arc->searchTable($pack_id, 'auth', $data);
        //$deleteRowByMerchantGuid=$arc->deleteRowEntry($pack_id, 'auth',$authListByMerchantGuid['Records'][0]['Id']);
        $UpdateRowInauth=$arc->editRowEntry($pack_id, 'auth', $authListByMerchantGuid['Records'][0]['Id'], $data1);
        $message = 'Disconnected';
        echo $message;
    }

}

/*Category-Mapping Request*/
if(isset($content['cat_map'])){
    //error_log('Reached correct ajax request function: '.json_encode($content));
	$arc_cat_arr = $arc->getCategories();
    //error_log('Arcadier categories: '.json_encode($arc_cat_arr));
    if (!empty($content['arcadier_guid'])) {
        $arcadier_guid = explode(',', $content['arcadier_guid']);
        $shopify_category_id = $content['shopify_category_id'];

        $data_auth = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
            ],
            [
                'Name'=> 'auth_status',
                'Operator'=> 'equal',
                'Value'=> "1"
            ]
            
        ];
        $authListByMerchantGuid = $arc->searchTable($pack_id, 'auth', $data_auth);

        $data_map = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
            ],
            [
                'Name'=> 'shop',
                'Operator'=> 'equal',
                'Value'=> $authListByMerchantGuid['Records'][0]['shop']
            ]
            ];

        $response = $arc->searchTable($pack_id, 'map', $data_map);

        //if map already exists
        if ($response['Records'][0]['merchant_guid'] == $content['arc_user']) {
            
            $map_arr_unserialize = unserialize($response['Records'][0]['map']);
            $list = $map_arr_unserialize['list'];
            $found = false;
            foreach ($list as $key => $data) {
                if ($data['shopify_category'] == $shopify_category_id) {
                    unset($list[$key]);
                    $list1 = array_values($list);
                    $list_arr1 = [
                        "shopify_category" => $content['shopify_category_id'],
                        "arcadier_guid" => $arcadier_guid,
                    ];
                    array_push($list1, $list_arr1);
                    $map_arr = [
                        "merchant" => $content['arc_user'],
                        "list" => $list1,
                    ];
                    $map_arr_serialize = serialize($map_arr);
                    $data2 = [
                        'map' => $map_arr_serialize,
                        'shop' => $authListByMerchantGuid['Records'][0]['shop']
                    ];
                    $UpdateRowInmap = $arc->editRowEntry($pack_id, 'map', $response['Records'][0]['Id'], $data2);
                    $found = true;
                    $message = 'Mapped';
                    echo $message;
                    break;
                }
            }

            if ($found === false) {
                $list_arr1 = [
                    "shopify_category" => $content['shopify_category_id'],
                    "arcadier_guid" => $arcadier_guid,
                ];
                array_push($list, $list_arr1);
                $map_arr = [
                    "merchant" => $content['arc_user'],
                    "list" => $list,
                ];
                $map_arr_serialize = serialize($map_arr);
                $data2 = [
                    'map' => $map_arr_serialize,
                    'shop' => $authListByMerchantGuid['Records'][0]['shop']
                ];
                $UpdateRowInmap = $arc->editRowEntry($pack_id, 'map', $response['Records'][0]['Id'], $data2);
                $message = 'Mapped';
                echo $message;
            }
        
        //if map doesnt' already exist
        } else {
            error_log('Map doesnt exist');
            $list_arr = [
                [
                    "shopify_category" => $shopify_category_id,
                    "arcadier_guid" => $arcadier_guid,
                ],
            ];

            $map_arr = [
                "merchant" => $content['arc_user'],
                "list" => $list_arr,
            ];
            $map_arr_serialize = serialize($map_arr);
            $insertData = [
                'merchant_guid' => $content['arc_user'],
                'map' => $map_arr_serialize,
                'shop' => $authListByMerchantGuid['Records'][0]['shop']
            ];
            $insertRowInmap = $arc->createRowEntry($pack_id, 'map', $insertData);
            $message = 'Mapped';
            echo $message;
        }
    }
}


/* Create Arcadier Item Request*/

elseif(isset($content['create_arc_item'])){
    $data11 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data11);
    //$configListById=$arc->searchTable($pack_id, 'config', $data11);
    if(!empty($authListById['Records'])){
        if($authListById['Records'][0]['auth_status'] == '1'){
            $authRowByMerchantGuid=$authListById['Records'][0];
            $data_config = [
                [
                    'Name'=> 'merchant_guid',
                    'Operator'=> 'equal',
                    'Value'=> $content['arc_user']
                ],
                [
                    'Name'=> 'domain',
                    'Operator'=> 'equal',
                    'Value'=> $authRowByMerchantGuid['domain']
                ]
                
                ];
            $configListById=$arc->searchTable($pack_id, 'config', $data_config);
            $configRowByMerchantGuid=$configListById['Records'][0];
        }
    }
    //echo "<pre>"; print_r($authRowByMerchantGuid);
	if(isset($content['override_default_category_select'])){

        
        $arc_cat=$arc->getCategories();
        $baseURL = $arc->baseUrl11($authRowByMerchantGuid['arc_domain']);
        $packageID = $pack_id;
        $admintoken = $authRowByMerchantGuid['arc_token'];
        $merchant = $authRowByMerchantGuid['merchant_guid'];
        $mag_product=$mag->magento_get_one_sku($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token'], $content['sku1']);
        $mag_product1 = $mag_product;


        $sync_date = date("Y-m-d H:i:s");
                $sync_type = 'Item';
                $sync_trigger = 'Manual';
                $sync_created = 0;
                $sync_changed = 0;
                $sync_unchanged = 0;

        $category_arrays = $content['override_default_category_select'];
        $item=array();
        array_push($item,$mag_product1);
        foreach($item as &$sku){
            $sku->category_id = $category_arrays;
        } 
        $items = $item;
        $domain = $authRowByMerchantGuid['domain'];
        $username = $authRowByMerchantGuid['username'];
        $password = $authRowByMerchantGuid['password'];
        $token = $authRowByMerchantGuid['token'];
        $unsuccessful_imports = [];
        $success = true;
        foreach($items as $sku){
            $product = $mag->magento_get_one_sku($domain, $token, $sku->sku);
            $url = $baseURL.'/api/v2/merchants/'.$merchant.'/items';
            $data = $arc->map($product, $domain, $merchant, $baseURL, $packageID, $sku->category_id);
            if($data != 0){
                $datt = [
                            'keywords' => $content['name1']
                        ];

                $checkItemExist=$arc->searchItems($datt);
                if(!empty($checkItemExist['Records'])){
                $result = $arc->editItem($data, $merchant, $checkItemExist['Records'][0]['ID']);
                $sync_changed++;
                }else{
                $result = $arc->createItem($data, $merchant);
                $sync_created++;
                }
                if($result['SKU'] = $sku->sku){
                    //$mag->update_magento($domain, $token, $sku->sku, 1);
                    //$mag->update_magento_arcadier_sync_timestamp($domain, $token, $sku->sku, $sync_date);

                    $arcadier_sync_marketplace = '';
                    foreach($sku->custom_attributes as $mag_products_attr_key1=>$mag_products_attr_value1){
                        if($mag_products_attr_value1->attribute_code == 'arcadier_sync_marketplace'){
                            $arcadier_sync_marketplace = $mag_products_attr_value1->value;
                        }
                    }
                    if(!empty($arcadier_sync_marketplace)){
                        $marketplace_domain_array = explode(',',$arcadier_sync_marketplace);
                        if(in_array($marketplace_domain,$marketplace_domain_array)){
                            $marketplace_domain1 = implode(',',$marketplace_domain_array);
                        }else{
                            array_push($marketplace_domain_array,$marketplace_domain);
                            $marketplace_domain1 = implode(',',$marketplace_domain_array);
                        }
                    }else{
                        $marketplace_domain1 = $marketplace_domain;
                    }
                    //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku->sku, $marketplace_domain1);
                    
                    
                    $arcadier_sync_merchant_guid = '';
                    foreach($sku->custom_attributes as $mag_products_attr_key11=>$mag_products_attr_value11){
                        if($mag_products_attr_value11->attribute_code == 'arcadier_sync_merchant_guid'){
                            $arcadier_sync_merchant_guid = $mag_products_attr_value11->value;
                        }
                    }
                    if(!empty($arcadier_sync_merchant_guid)){
                        $merchant_guid_array = explode(',',$arcadier_sync_merchant_guid);
                        if(in_array($merchant,$merchant_guid_array)){
                            $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                        }else{
                            array_push($merchant_guid_array,$merchant);
                            $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                        }
                    }else{
                        $merchant_guid_sync1 = $merchant;
                    }                       
                    //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku->sku, $merchant_guid_sync1);
                    
                    /* $arcadier_sync_package_id = '';
                    foreach($sku->custom_attributes as $mag_products_attr_key111=>$mag_products_attr_value111){
                        if($mag_products_attr_value111->attribute_code == 'arcadier_sync_package_id'){
                            $arcadier_sync_package_id = $mag_products_attr_value111->value;
                        }
                    }
                    if(!empty($arcadier_sync_package_id)){
                        $package_id_array = explode(',',$arcadier_sync_package_id);
                        if(in_array($pack_id,$package_id_array)){
                            $package_id_sync1 = implode(',',$package_id_array);
                        }else{
                            array_push($package_id_array,$pack_id);
                            $package_id_sync1 = implode(',',$package_id_array);
                        }
                    }else{
                        $package_id_sync1 = $pack_id;
                    }     */    
                    
                    $package_id_sync1 = $pack_id;

                    $mag->update_magento1($domain, $token, $sku->sku, 1, $marketplace_domain1, $sync_date, $merchant_guid_sync1, $package_id_sync1);
                } 
            }
            else{
                $success = false;
                array_push($unsuccessful_imports, $product->name);
            }
        }
        unset($sku);
        if($success){
            /* $getAllItemsAfter = $arc->getAllItems();
            foreach($getAllItemsAfter['Records'] as $getAllItemsAfters){
                if($getAllItemsAfters['CreatedDateTime'] == $getAllItemsAfters['ModifiedDateTime']){
                    $sync_unchanged++;
                }
            }
            //$mag_product2 = json_decode($mag_product1,true);
            $sync_status = 'Completed';
            $sync_table_array = array(
                "magento_id"=>$mag_product1->id,
                "arcadier_guid"=>$authRowByMerchantGuid['merchant_guid'],
                "sync_date" => $sync_date,
                "sync_type" => $sync_type,
                "sync_trigger" => $sync_trigger,
                "sync_created" => $sync_created,
                "sync_changed" => $sync_changed,
                "sync_unchanged" => $sync_unchanged,
                "sync_status" => $sync_status
            );
            $file = file_get_contents('item_control_data.json');
            $data = json_decode($file);
            unset($file);

        
            $data_index = array_search($mag_product1->id,array_column($data,"magento_id"));
            if ($data_index !== false) {
                $data[$data_index] = $sync_table_array;
            }else{
                $data[] = $sync_table_array;

            }    
            

            
            file_put_contents('item_control_data.json',json_encode($data));
            unset($data);  */
        
            //echo 1;
            echo json_encode(array("message"=>1,"data"=>array("sync_date"=>$sync_date)));
        }
        else{
            echo json_encode($unsuccessful_imports);
        }
	}
    
    
    else{
	$arc_cat=$arc->getCategories();
	$baseURL = $arc->baseUrl11($authRowByMerchantGuid['arc_domain']);
	$packageID = $pack_id;
	$admintoken = $authRowByMerchantGuid['arc_token'];
	$merchant = $authRowByMerchantGuid['merchant_guid'];
	$mag_product=$mag->magento_get_one_sku($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token'], $content['sku1']);
	$mag_product1 = $mag_product;
	$category_array = [];
		$found = false;
        $sync_date = date("Y-m-d H:i:s");
			$sync_type = 'Item';
			$sync_trigger = 'Manual';
			$sync_created = 0;
			$sync_changed = 0;
			$sync_unchanged = 0;
		foreach($mag_product1->extension_attributes->category_links as $category){
			$data1 = [
				[
				  'Name'=> 'merchant_guid',
				  'Operator'=> 'equal',
				  'Value'=> $merchant
                ],
                [
                    'Name'=> 'domain',
                    'Operator'=> 'equal',
                    'Value'=> $authRowByMerchantGuid['domain']
                  ]
			];
			$url = $baseURL.'/api/v2/plugins/'.$packageID.'/custom-tables/map';
			$response = $arc->callAPI('POST', null, $url, $data1);
			if($response['Records'][0]['merchant_guid'] == $merchant){
				$category_list = unserialize($response['Records'][0]['map']);
				foreach($category_list['list'] as $entry){
					if($entry['magento_cat'] == $category->category_id){
						if(count($entry['arcadier_guid']) != 0){
							$found = true;
							foreach($entry['arcadier_guid'] as $arc_cat){
								$category_array[] =$arc_cat;
							}
						}
					}
				}
			}
		}
		if($found == false){
			$category_arrays = array();
		}
		else{
			$category_arrays = $category_array;
		}
	$item=array();
	array_push($item,$mag_product1);
	 foreach($item as &$sku){
		$sku->category_id = $category_arrays;
	} 
	$items = $item;
	$domain = $authRowByMerchantGuid['domain'];
    $username = $authRowByMerchantGuid['username'];
    $password = $authRowByMerchantGuid['password'];
    $token = $authRowByMerchantGuid['token'];
    $unsuccessful_imports = [];
    $success = true;
    foreach($items as $sku){
        $product = $mag->magento_get_one_sku($domain, $token, $sku->sku);
        $url = $baseURL.'/api/v2/merchants/'.$merchant.'/items';
        $data = $arc->map($product, $domain, $merchant, $baseURL, $packageID, $sku->category_id);
        //echo "<pre>"; print_r($data);
		if($data != 0){
			$datt = [
						'keywords' => $content['name1']
					];
			$checkItemExist=$arc->searchItems($datt);
            
			if(!empty($checkItemExist['Records'])){
			$result = $arc->editItem($data, $merchant, $checkItemExist['Records'][0]['ID']);
            $sync_changed++;
            //echo "<pre>"; print_r($result); die;
			}else{
			$result = $arc->createItem($data, $merchant);
            $sync_created++;
            //echo "<pre>"; print_r($result); die;
			}
			if($result['SKU'] = $sku->sku){
				//$mag->update_magento($domain, $token, $sku->sku, 1);
                //$mag->update_magento_arcadier_sync_timestamp($domain, $token, $sku->sku, $sync_date);

                $arcadier_sync_marketplace = '';
                foreach($sku->custom_attributes as $mag_products_attr_key1=>$mag_products_attr_value1){
                    if($mag_products_attr_value1->attribute_code == 'arcadier_sync_marketplace'){
                        $arcadier_sync_marketplace = $mag_products_attr_value1->value;
                    }
                }
                if(!empty($arcadier_sync_marketplace)){
                    $marketplace_domain_array = explode(',',$arcadier_sync_marketplace);
                    if(in_array($marketplace_domain,$marketplace_domain_array)){
                        $marketplace_domain1 = implode(',',$marketplace_domain_array);
                    }else{
                        array_push($marketplace_domain_array,$marketplace_domain);
                        $marketplace_domain1 = implode(',',$marketplace_domain_array);
                    }
                }else{
                    $marketplace_domain1 = $marketplace_domain;
                }
				//$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku->sku, $marketplace_domain1);
				
                
                $arcadier_sync_merchant_guid = '';
                foreach($sku->custom_attributes as $mag_products_attr_key11=>$mag_products_attr_value11){
                    if($mag_products_attr_value11->attribute_code == 'arcadier_sync_merchant_guid'){
                        $arcadier_sync_merchant_guid = $mag_products_attr_value11->value;
                    }
                }
                if(!empty($arcadier_sync_merchant_guid)){
                    $merchant_guid_array = explode(',',$arcadier_sync_merchant_guid);
                    if(in_array($merchant,$merchant_guid_array)){
                        $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                    }else{
                        array_push($merchant_guid_array,$merchant);
                        $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                    }
                }else{
                    $merchant_guid_sync1 = $merchant;
                }                       
                //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku->sku, $merchant_guid_sync1);
                /* $arcadier_sync_package_id = '';
                foreach($sku->custom_attributes as $mag_products_attr_key111=>$mag_products_attr_value111){
                    if($mag_products_attr_value111->attribute_code == 'arcadier_sync_package_id'){
                        $arcadier_sync_package_id = $mag_products_attr_value111->value;
                    }
                }
                if(!empty($arcadier_sync_package_id)){
                    $package_id_array = explode(',',$arcadier_sync_package_id);
                    if(in_array($pack_id,$package_id_array)){
                        $package_id_sync1 = implode(',',$package_id_array);
                    }else{
                        array_push($package_id_array,$pack_id);
                        $package_id_sync1 = implode(',',$package_id_array);
                    }
                }else{
                    $package_id_sync1 = $pack_id;
                }        */
                
                $package_id_sync1 = $pack_id;

                $mag->update_magento1($domain, $token, $sku->sku, 1, $marketplace_domain1, $sync_date, $merchant_guid_sync1, $package_id_sync1);
			} 
        }
        else{
            $success = false;
            array_push($unsuccessful_imports, $product->name);
        }
    }
    unset($sku);
    if($success){
       /*  $getAllItemsAfter = $arc->getAllItems();
        foreach($getAllItemsAfter['Records'] as $getAllItemsAfters){
            if($getAllItemsAfters['CreatedDateTime'] == $getAllItemsAfters['ModifiedDateTime']){
                $sync_unchanged++;
            }
        }
        //$mag_product2 = json_decode($mag_product1,true);
        $sync_status = 'Completed';
        $sync_table_array = array(
            "magento_id"=>$mag_product1->id,
            "arcadier_guid"=>$authRowByMerchantGuid['merchant_guid'],
            "sync_date" => $sync_date,
            "sync_type" => $sync_type,
            "sync_trigger" => $sync_trigger,
            "sync_created" => $sync_created,
            "sync_changed" => $sync_changed,
            "sync_unchanged" => $sync_unchanged,
            "sync_status" => $sync_status
        );
        $file = file_get_contents('item_control_data.json');
        $data = json_decode($file);
        unset($file);

      
        $data_index = array_search($mag_product1->id,array_column($data,"magento_id"));
        if ($data_index !== false) {
            $data[$data_index] = $sync_table_array;
        }else{
            $data[] = $sync_table_array;

        }    
           

        //$data[] = $sync_table_array;
        file_put_contents('item_control_data.json',json_encode($data));
        unset($data);  */
       
        //echo 1;
        echo json_encode(array("message"=>1,"data"=>array("sync_date"=>$sync_date)));
    }
    else{
        echo json_encode($unsuccessful_imports);
    }
	}
}


/* Create Arcadier Item All Request*/

elseif(isset($content['create_arc_item_all'])){
	//if(isset($_COOKIE['m_domain']) && isset($_COOKIE['auth']) && isset($_COOKIE['mag_user']) && isset($_COOKIE['mag_pass']) && isset($_COOKIE['mag_domain'])  && isset($_COOKIE['arc_access_token'])){

            $data11 = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
                ],
                [
                    'Name'=> 'auth_status',
                    'Operator'=> 'equal',
                    'Value'=> "1"
                ]
            ];
            
            $authListById=$arc->searchTable($pack_id, 'auth', $data11);
            //$configListById=$arc->searchTable($pack_id, 'config', $data11);
            if(!empty($authListById['Records'])){
                if($authListById['Records'][0]['auth_status'] == '1'){
                    $authRowByMerchantGuid=$authListById['Records'][0];
                    $data_config = [
                        [
                            'Name'=> 'merchant_guid',
                            'Operator'=> 'equal',
                            'Value'=> $content['arc_user']
                        ],
                        [
                            'Name'=> 'domain',
                            'Operator'=> 'equal',
                            'Value'=> $authRowByMerchantGuid['domain']
                        ]
                        
                        ];
                    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
                    $configRowByMerchantGuid=$configListById['Records'][0];
                }
            }

            $mag_product=$mag->magento_products($authRowByMerchantGuid['domain'],$authRowByMerchantGuid['token']);
	        $mag_product1 = json_decode($mag_product, true);
	
			$arc_cat=$arc->getCategories();
			if(!empty($arc_cat['Records'])){
				$arc_first_cat = $arc_cat['Records'][0]['ID'];
			}else{
				$arc_first_cat = '';
			}
			$baseURL = $arc->baseUrl11($authRowByMerchantGuid['arc_domain']);
			$packageID = $pack_id;
			$admintoken = $authRowByMerchantGuid['arc_token'];
			$merchant = $authRowByMerchantGuid['merchant_guid'];
			$getAllItemsBefore = $arc->getAllItems();
			$getAllItemsBeforeCount = count($getAllItemsBefore['Records']);
			$sync_date = date("Y-m-d H:i:s");
			$sync_type = 'Item';
			$sync_trigger = 'Manual';
			$sync_created = 0;
			$sync_changed = 0;
			$sync_unchanged = 0;
			foreach($mag_product1['items'] as $mag_product11){
			$category_array = [];
				$found = false;
				foreach($mag_product11['extension_attributes']['category_links'] as $category){
					$data1 = [
						[
						  'Name'=> 'merchant_guid',
						  'Operator'=> 'equal',
						  'Value'=> $merchant
                        ],
                        [
                            'Name'=> 'domain',
                            'Operator'=> 'equal',
                            'Value'=> $authRowByMerchantGuid['domain']
                          ]
					];
					$url = $baseURL.'/api/v2/plugins/'.$packageID.'/custom-tables/map';
					$response = $arc->callAPI('POST', null, $url, $data1);
					if($response['Records'][0]['merchant_guid'] == $merchant){
						$category_list = unserialize($response['Records'][0]['map']);
						foreach($category_list['list'] as $entry){
							if($entry['magento_cat'] == $category['category_id']){
								if(count($entry['arcadier_guid']) != 0){
									$found = true;
									foreach($entry['arcadier_guid'] as $arc_cat){
										$category_array[] =$arc_cat;
									}
								}
							}
						}
					}
				}
				if($found == false){
					$category_arrays = array();
				}
				else{
					$category_arrays = $category_array;
				}
			$item=array();
			array_push($item,$mag_product11);
			 foreach($item as &$sku){
				$sku['category_id'] = $category_arrays;
			} 
			$items = $item;
			$domain = $authRowByMerchantGuid['domain'];
			$username = $authRowByMerchantGuid['username'];
			$password = $authRowByMerchantGuid['password'];
			$token = $authRowByMerchantGuid['token'];
			$unsuccessful_imports = [];
			$success = true;
			foreach($items as $sku){
				$product = $mag->magento_get_one_sku($domain, $token, $sku['sku']);
				$url = $baseURL.'/api/v2/merchants/'.$merchant.'/items';
				$data = $arc->map_all($product, $domain, $merchant, $baseURL, $packageID, $sku['category_id'], $arc_first_cat);
				 if($data != 0){
					$datt = [
								'keywords' => $sku['name']
							];
					$checkItemExist=$arc->searchItems($datt);
					if(!empty($checkItemExist['Records'])){
					$result = $arc->editItem($data, $merchant, $checkItemExist['Records'][0]['ID']);
					$sync_changed++;
					}else{
					$result = $arc->createItem($data, $merchant);
					$sync_created++;
					}
					if($result['SKU'] = $sku['sku']){
						//$mag->update_magento($domain, $token, $sku['sku'], 1);
                        //$mag->update_magento_arcadier_sync_timestamp($domain, $token, $sku['sku'], date("Y-m-d H:i:s"));

                        $arcadier_sync_marketplace = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key1=>$mag_products_attr_value1){
                            if($mag_products_attr_value1['attribute_code'] == 'arcadier_sync_marketplace'){
                                $arcadier_sync_marketplace = $mag_products_attr_value1['value'];
                            }
                        }
                        if(!empty($arcadier_sync_marketplace)){
                            $marketplace_domain_array = explode(',',$arcadier_sync_marketplace);
                            if(in_array($marketplace_domain,$marketplace_domain_array)){
                                $marketplace_domain1 = implode(',',$marketplace_domain_array);
                            }else{
                                array_push($marketplace_domain_array,$marketplace_domain);
                                $marketplace_domain1 = implode(',',$marketplace_domain_array);
                            }
                        }else{
                            $marketplace_domain1 = $marketplace_domain;
                        }
                        //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku['sku'], $marketplace_domain1);

                        $arcadier_sync_merchant_guid = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key11=>$mag_products_attr_value11){
                            if($mag_products_attr_value11['attribute_code'] == 'arcadier_sync_merchant_guid'){
                                $arcadier_sync_merchant_guid = $mag_products_attr_value11['value'];
                            }
                        }
                        if(!empty($arcadier_sync_merchant_guid)){
                            $merchant_guid_array = explode(',',$arcadier_sync_merchant_guid);
                            if(in_array($merchant,$merchant_guid_array)){
                                $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                            }else{
                                array_push($merchant_guid_array,$merchant);
                                $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                            }
                        }else{
                            $merchant_guid_sync1 = $merchant;
                        }
                        //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku['sku'], $merchant_guid_sync1);
                        
                        /* $arcadier_sync_package_id = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key111=>$mag_products_attr_value111){
                            if($mag_products_attr_value111['attribute_code'] == 'arcadier_sync_package_id'){
                                $arcadier_sync_package_id = $mag_products_attr_value111['value'];
                            }
                        }
                        if(!empty($arcadier_sync_package_id)){
                            $package_id_array = explode(',',$arcadier_sync_package_id);
                            if(in_array($pack_id,$package_id_array)){
                                $package_id_sync1 = implode(',',$package_id_array);
                            }else{
                                array_push($package_id_array,$pack_id);
                                $package_id_sync1 = implode(',',$package_id_array);
                            }
                        }else{
                            $package_id_sync1 = $pack_id;
                        }  */          
                        $package_id_sync1 = $pack_id;
                           
                        
                        $mag->update_magento1($domain, $token, $sku['sku'], 1, $marketplace_domain1, date("Y-m-d H:i:s"), $merchant_guid_sync1, $package_id_sync1);
					} 
				}
				else{
					$success = false;
					array_push($unsuccessful_imports, $product->name);
				} 
			}
			
			}
			
			
			//unset($sku);
			if($success){
				$getAllItemsAfter = $arc->getAllItems();
				foreach($getAllItemsAfter['Records'] as $getAllItemsAfters){
					if($getAllItemsAfters['CreatedDateTime'] == $getAllItemsAfters['ModifiedDateTime']){
						$sync_unchanged++;
					}
				}
				$sync_status = 'Completed';
				$sync_table_array = array(
					"arcadier_guid"=>$authRowByMerchantGuid['merchant_guid'],
					"sync_date" => $sync_date,
					"sync_type" => $sync_type,
					"sync_trigger" => $sync_trigger,
					"sync_created" => $sync_created,
					"sync_changed" => $sync_changed,
					"sync_unchanged" => $sync_unchanged,
					"sync_status" => $sync_status,
					"sync_for" => "synchronization_page",
                    "domain" => $authRowByMerchantGuid["domain"]
				);
				/* $file = file_get_contents('data.json');
				$data = json_decode($file);
				unset($file);
				$data[] = $sync_table_array;
				file_put_contents('data.json',json_encode($data));
				unset($data); */
                $insertRowInsync_data_log=$arc->createRowEntry($pack_id, 'sync_data_log', $sync_table_array);
				echo json_encode(array("message"=>1,"data"=>$sync_table_array));
			}
			else{
				if(!empty($mag_product1['items'])){
				    echo json_encode($unsuccessful_imports);
				}else{
					echo json_encode(array('No Items to Sync'));
				}
			}
	
	
	//}
}




/* Create Arcadier Item All Manual Request*/

elseif(isset($content['create_arc_item_all_manual'])){
	//if(isset($_COOKIE['m_domain']) && isset($_COOKIE['auth']) && isset($_COOKIE['mag_user']) && isset($_COOKIE['mag_pass']) && isset($_COOKIE['mag_domain'])  && isset($_COOKIE['arc_access_token'])){
			//echo "<pre>"; print_r($content); die;
            $data11 = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
                ],
                [
                    'Name'=> 'auth_status',
                    'Operator'=> 'equal',
                    'Value'=> "1"
                ]
            ];
            
            $authListById=$arc->searchTable($pack_id, 'auth', $data11);
            //$configListById=$arc->searchTable($pack_id, 'config', $data11);
            if(!empty($authListById['Records'])){
                if($authListById['Records'][0]['auth_status'] == '1'){
                    $authRowByMerchantGuid=$authListById['Records'][0];
                    $data_config = [
                        [
                            'Name'=> 'merchant_guid',
                            'Operator'=> 'equal',
                            'Value'=> $content['arc_user']
                        ],
                        [
                            'Name'=> 'domain',
                            'Operator'=> 'equal',
                            'Value'=> $authRowByMerchantGuid['domain']
                        ]
                        
                        ];
                    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
                    $configRowByMerchantGuid=$configListById['Records'][0];
                }
            }

            //$mag_product=$mag->magento_products($authRowByMerchantGuid['domain'],$authRowByMerchantGuid['token']);
	        //$mag_product1 = json_decode($mag_product, true);
			
	
			$arc_cat=$arc->getCategories();
			if(!empty($arc_cat['Records'])){
				$arc_first_cat = $arc_cat['Records'][0]['ID'];
			}else{
				$arc_first_cat = '';
			}
			$baseURL = $arc->baseUrl11($authRowByMerchantGuid['arc_domain']);
			$packageID = $pack_id;
			$admintoken = $authRowByMerchantGuid['arc_token'];
			$merchant = $authRowByMerchantGuid['merchant_guid'];
			$getAllItemsBefore = $arc->getAllItems();
			$getAllItemsBeforeCount = count($getAllItemsBefore['Records']);
			$sync_date = date("Y-m-d H:i:s");
			$sync_type = 'Item';
			$sync_trigger = 'Manual';
			$sync_created = 0;
			$sync_changed = 0;
			$sync_unchanged = 0;
			//foreach($mag_product1['items'] as $mag_product11){
			foreach($content['checked_data'] as $content_checked_data){
			$mag_product=$mag->magento_get_one_sku($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token'], $content_checked_data['sku1']);
            //$mag_product1 = $mag_product;
            $mag_product11 = json_decode(json_encode($mag_product), true);
            /* echo "<pre>"; print_r($content); 
            echo "<pre>"; print_r($content['checked_data']); 
            echo "<pre>"; print_r($content_checked_data); 
            echo "<pre>"; print_r($mag_product); 
            echo "<pre>"; print_r($mag_product11); 
            echo "<pre>"; print_r(json_decode(json_encode($mag_product), true)); 
            die; */
			$category_array = [];
				$found = false;
				foreach($mag_product11['extension_attributes']['category_links'] as $category){
					$data1 = [
						[
						  'Name'=> 'merchant_guid',
						  'Operator'=> 'equal',
						  'Value'=> $merchant
						],
                        [
                            'Name'=> 'domain',
                            'Operator'=> 'equal',
                            'Value'=> $authRowByMerchantGuid['domain']
                          ]
					];
					$url = $baseURL.'/api/v2/plugins/'.$packageID.'/custom-tables/map';
					$response = $arc->callAPI('POST', null, $url, $data1);
					if($response['Records'][0]['merchant_guid'] == $merchant){
						$category_list = unserialize($response['Records'][0]['map']);
						foreach($category_list['list'] as $entry){
							if($entry['magento_cat'] == $category['category_id']){
								if(count($entry['arcadier_guid']) != 0){
									$found = true;
									foreach($entry['arcadier_guid'] as $arc_cat){
										$category_array[] =$arc_cat;
									}
								}
							}
						}
					}
				}
				if($found == false){
					$category_arrays = array();
				}
				else{
					$category_arrays = $category_array;
				}
			$item=array();
			array_push($item,$mag_product11);
			 foreach($item as &$sku){
				$sku['category_id'] = $category_arrays;
			} 
			$items = $item;
			$domain = $authRowByMerchantGuid['domain'];
			$username = $authRowByMerchantGuid['username'];
			$password = $authRowByMerchantGuid['password'];
			$token = $authRowByMerchantGuid['token'];
			$unsuccessful_imports = [];
			$success = true;
			foreach($items as $sku){
				$product = $mag->magento_get_one_sku($domain, $token, $sku['sku']);
				$url = $baseURL.'/api/v2/merchants/'.$merchant.'/items';
				$data = $arc->map_all($product, $domain, $merchant, $baseURL, $packageID, $sku['category_id'], $arc_first_cat);
				 if($data != 0){
					$datt = [
								'keywords' => $sku['name']
							];
					$checkItemExist=$arc->searchItems($datt);
					if(!empty($checkItemExist['Records'])){

                    if($content['m_orders'] == 0){
                        unset($data["BuyerDescription"],$data["SellerDescription"]);
                    }
                    if($content['m_quantity'] == 0){
                        unset(
                            $data["StockLimited"],
                            $data["StockQuantity"]
                        );
                    }
                    if($content['m_details'] == 0){
                        unset(
                            $data["SKU"],
                            $data["Name"],
                            $data["PriceUnit"],
                            $data["IsVisibleToCustomer"],
                            $data["Active"],
                            $data["IsAvailable"],
                            $data["CurrencyCode"],
                            $data["Categories"],
                            $data["Media"],
                            $data["HasChildItems"],
                            $data["ChildItems"]
                        );
                    }
                    if($content['m_prices'] == 0){
                        unset(
                            $data["Price"]
                        );
                    }

					$result = $arc->editItem($data, $merchant, $checkItemExist['Records'][0]['ID']);
					$sync_changed++;
					}else{
					$result = $arc->createItem($data, $merchant);
					$sync_created++;
					}
					if($result['SKU'] = $sku['sku']){
						//$mag->update_magento($domain, $token, $sku['sku'], 1);
                        //$mag->update_magento_arcadier_sync_timestamp($domain, $token, $sku['sku'], date("Y-m-d H:i:s"));

                        $arcadier_sync_marketplace = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key1=>$mag_products_attr_value1){
                            if($mag_products_attr_value1['attribute_code'] == 'arcadier_sync_marketplace'){
                                $arcadier_sync_marketplace = $mag_products_attr_value1['value'];
                            }
                        }
                        if(!empty($arcadier_sync_marketplace)){
                            $marketplace_domain_array = explode(',',$arcadier_sync_marketplace);
                            if(in_array($marketplace_domain,$marketplace_domain_array)){
                                $marketplace_domain1 = implode(',',$marketplace_domain_array);
                            }else{
                                array_push($marketplace_domain_array,$marketplace_domain);
                                $marketplace_domain1 = implode(',',$marketplace_domain_array);
                            }
                        }else{
                            $marketplace_domain1 = $marketplace_domain;
                        }
                        //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku['sku'], $marketplace_domain1);

                        $arcadier_sync_merchant_guid = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key11=>$mag_products_attr_value11){
                            if($mag_products_attr_value11['attribute_code'] == 'arcadier_sync_merchant_guid'){
                                $arcadier_sync_merchant_guid = $mag_products_attr_value11['value'];
                            }
                        }
                        if(!empty($arcadier_sync_merchant_guid)){
                            $merchant_guid_array = explode(',',$arcadier_sync_merchant_guid);
                            if(in_array($merchant,$merchant_guid_array)){
                                $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                            }else{
                                array_push($merchant_guid_array,$merchant);
                                $merchant_guid_sync1 = implode(',',$merchant_guid_array);
                            }
                        }else{
                            $merchant_guid_sync1 = $merchant;
                        }
                        //$mag->update_magento_arcadier_sync_marketplace($domain, $token, $sku['sku'], $merchant_guid_sync1);
                        
                        /* $arcadier_sync_package_id = '';
                        foreach($sku['custom_attributes'] as $mag_products_attr_key111=>$mag_products_attr_value111){
                            if($mag_products_attr_value111['attribute_code'] == 'arcadier_sync_package_id'){
                                $arcadier_sync_package_id = $mag_products_attr_value111['value'];
                            }
                        }
                        if(!empty($arcadier_sync_package_id)){
                            $package_id_array = explode(',',$arcadier_sync_package_id);
                            if(in_array($pack_id,$package_id_array)){
                                $package_id_sync1 = implode(',',$package_id_array);
                            }else{
                                array_push($package_id_array,$pack_id);
                                $package_id_sync1 = implode(',',$package_id_array);
                            }
                        }else{
                            $package_id_sync1 = $pack_id;
                        }  */          
                        $package_id_sync1 = $pack_id;
                           
                        
                        $mag->update_magento1($domain, $token, $sku['sku'], 1, $marketplace_domain1, date("Y-m-d H:i:s"), $merchant_guid_sync1, $package_id_sync1);
					} 
				}
				else{
					$success = false;
					array_push($unsuccessful_imports, $product->name);
				} 
			}
			
			}
			
			
			//unset($sku);
			if($success){
				$getAllItemsAfter = $arc->getAllItems();
				foreach($getAllItemsAfter['Records'] as $getAllItemsAfters){
					if($getAllItemsAfters['CreatedDateTime'] == $getAllItemsAfters['ModifiedDateTime']){
						$sync_unchanged++;
					}
				}
				$sync_status = 'Completed';
				$sync_table_array = array(
					"arcadier_guid"=>$authRowByMerchantGuid['merchant_guid'],
					"sync_date" => $sync_date,
					"sync_type" => $sync_type,
					"sync_trigger" => $sync_trigger,
					"sync_created" => $sync_created,
					"sync_changed" => $sync_changed,
					"sync_unchanged" => $sync_unchanged,
					"sync_status" => $sync_status,
					"sync_for" => "synchronization_page",
					"domain" => $authRowByMerchantGuid["domain"]
				);
				/* $file = file_get_contents('data.json');
				$data = json_decode($file);
				unset($file);
				$data[] = $sync_table_array;
				file_put_contents('data.json',json_encode($data));
				unset($data); */
                
                $insertRowInsync_data_log=$arc->createRowEntry($pack_id, 'sync_data_log', $sync_table_array);
                //echo "<pre>"; print_r($insertRowInsync_data_log); die;
				echo json_encode(array("message"=>1,"data"=>$sync_table_array));
			}
			else{
				if(!empty($content['checked_data'])){
				    echo json_encode($unsuccessful_imports);
				}else{
					echo json_encode(array('No Items to Sync'));
				}
			}
	
	
	//}
}



/*Enable Config Request*/
elseif(isset($content['enabled'])){
    $data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    //echo '<pre>'; print_r($authListById); echo '</pre>';
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    $data_config = [
        [
            'Name'=> 'merchant_guid',
            'Operator'=> 'equal',
            'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
        
        ];
    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
    $configList=$configListById['Records'][0];
    
    if(!empty($row)){
    if($row['auth_status'] == '1'){
	//if(isset($_COOKIE['auth'])){
	$data = [
        'enabled' => $content['enabled']
    ];
	//$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $content['Id'], $data);
	$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $configList['Id'], $data);
	if(!empty($UpdateRowInconfig)){
		$message='Enabled';
	}else{
		$message='Unable to enable';
	}
	echo $message;
	}
    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}

/*Disable Config Request*/
elseif(isset($content['disabled'])){
    $data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    //echo '<pre>'; print_r($content); 
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    $data_config = [
        [
            'Name'=> 'merchant_guid',
            'Operator'=> 'equal',
            'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
        
        ];
    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
    $configList=$configListById['Records'][0];
    
    
    if(!empty($row)){
    if($row['auth_status'] == '1'){
	//if(isset($_COOKIE['auth'])){
	$data = [
        'enabled' => $content['disabled']
    ];
	//$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $content['Id'], $data);
	$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $configList['Id'], $data);
	if(!empty($UpdateRowInconfig)){
		$message='Disabled';
	}else{
		$message='Unable to disable';
	}
	echo $message;
	}
    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}


/*MA Mode Config Request*/
elseif(isset($content['ma'])){
    $data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    //echo '<pre>'; print_r($authListById); echo '</pre>';
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    $data_config = [
        [
            'Name'=> 'merchant_guid',
            'Operator'=> 'equal',
            'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
        
        ];
    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
    $configList=$configListById['Records'][0];
    
    if(!empty($row)){
    if($row['auth_status'] == '1'){
	//if(isset($_COOKIE['auth'])){
	$data = [
        'mode' => $content['ma']
    ];
	//$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $content['Id'], $data);
	$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $configList['Id'], $data);
	if(!empty($UpdateRowInconfig)){
		$message='ma';
	}else{
		$message='Unable to change mode';
	}
	echo $message;
	}
    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}

/*AM mode Config Request*/
elseif(isset($content['am'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    //echo '<pre>'; print_r($authListById); echo '</pre>';
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    $data_config = [
        [
            'Name'=> 'merchant_guid',
            'Operator'=> 'equal',
            'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
        
        ];
    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
    $configList=$configListById['Records'][0];
    
    if(!empty($row)){
    if($row['auth_status'] == '1'){
	//if(isset($_COOKIE['auth'])){
	$data = [
        'mode' => $content['am']
    ];
	//$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $content['Id'], $data);
	$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $configList['Id'], $data);
	if(!empty($UpdateRowInconfig)){
		$message='am';
	}else{
		$message='Unable to change mode';
	}
	echo $message;
	}
    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}


/*min_sync_limit Config Request*/
elseif(isset($content['min_sync_limit'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    //echo '<pre>'; print_r($authListById); echo '</pre>';
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    $data_config = [
        [
            'Name'=> 'merchant_guid',
            'Operator'=> 'equal',
            'Value'=> $content['merchant_guid']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
        
        ];
    $configListById=$arc->searchTable($pack_id, 'config', $data_config);
    $configList=$configListById['Records'][0];
    
    if(!empty($row)){
    if($row['auth_status'] == '1'){
	//if(isset($_COOKIE['auth'])){
	$data = [
        'min_sync_limit' => $content['min_sync_limit'],
        'domain' => $row['domain']
    ];
	//$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $content['Id'], $data);
	$UpdateRowInconfig=$arc->editRowEntry($pack_id, 'config', $configList['Id'], $data);
	if(!empty($UpdateRowInconfig)){
        //echo "<pre>"; print_r($UpdateRowInconfig); die;
		$message='min_sync_limit';
	}else{
		$message='Unable to save Sync Limit';
	}
	echo $message;
	}
    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}


/*create_arc_item_slow Custom Table Request*/
elseif(isset($content['create_arc_item_all_slow'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];

      
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];

    $data_create_arc_item_slow = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
      ];
    
    
    if(!empty($row)){

    if($row['auth_status'] == '1'){
	$data_i = [
        'merchant_guid' => $content['arc_user'],
        'checked_data' => serialize($content['checked_data']),
        'domain' => $row['domain']
    ];

    $data_e = [
        'checked_data' => serialize($content['checked_data'])
    ];
    $create_arc_item_slowListById=$arc->searchTable($pack_id, 'create_arc_item_slow', $data_create_arc_item_slow);

    if(!empty($create_arc_item_slowListById['Records'])){
        $UpdateRowIncreate_arc_item_slow = $arc->editRowEntry($pack_id, 'create_arc_item_slow', $create_arc_item_slowListById['Records'][0]['Id'], $data_e);
        if(!empty($UpdateRowIncreate_arc_item_slow)){
            $message='Successfully updated checked_data';
        }else{
            $message='Unable to update checked_data';
        }
    }else{
        $insertRowIncreate_arc_item_slow = $arc->createRowEntry($pack_id, 'create_arc_item_slow', $data_i);
        if(!empty($insertRowIncreate_arc_item_slow)){
            $message='Successfully saved checked_data';
        }else{
            $message='Unable to save checked_data';
        }
    }
    echo $message;

	}else{
		$message='Please authenticate first';
		echo $message;
	}

    }
    }else{
		$message='Please authenticate first';
		echo $message;
	}
}





/*create_arc_item_slow Schedule Request*/
elseif(isset($content['create_arc_item_all_slow_schedule'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    
    
    if(!empty($row)){

    if($row['auth_status'] == '1'){

        if($content['s_schedule'] == 1){
            $cron_schedule = "0 0/15 * * * ?";
        }elseif($content['s_schedule'] == 2){
            $cron_schedule = "0 0 * * * ?";
        }elseif($content['s_schedule'] == 3){
            $cron_schedule = "0 0 12 * * ?";
        }

        $baseURL = $arc->baseUrl11($row['arc_domain']);
        $url = $baseURL."/user/plugins/".$pack_id."/create_arc_item_slow_cron.php?user=".$content['arc_user']."&m_orders=".$content['s_orders']."&m_quantity=".$content['s_quantity']."&m_details=".$content['s_details']."&m_prices=".$content['s_prices']."&domain=".$row['domain']."&schedule_type=".$content['schedule_type']."";



	$data_i = [
        'Url' => $url,
        'CronSchedule' => $cron_schedule
    ];

    $data_e = [
        'Url' => $url,
        'CronSchedule' => $cron_schedule
    ];

    
    $getAllSchedulers=$arc->getAllSchedulers();
    $getSchedulerById = array();
    foreach($getAllSchedulers as $getAllSchedulerKey=>$getAllSchedulerValue){
        if (strpos($getAllSchedulerValue['Url'], 'create_arc_item_slow_cron.php') !== false && strpos($getAllSchedulerValue['Url'], $row['domain']) !== false && strpos($getAllSchedulerValue['Url'], 'schedule_type') !== false && strpos($getAllSchedulerValue['Url'], $content['schedule_type']) !== false) {
            $getSchedulerById = $getAllSchedulers[$getAllSchedulerKey];
        }
    }
    if(!empty($getSchedulerById)){
        $UpdateScheduler = $arc->editScheduler($data_e, $getSchedulerById['Id']);
        if(!empty($UpdateScheduler)){
            $message='Successfully updated slow scheduler';
        }else{
            $message='Unable to update slow scheduler';
        }
    }else{
        $insertScheduler = $arc->createScheduler($data_i);
        if(!empty($insertScheduler)){
            $message='Successfully saved slow scheduler';
        }else{
            $message='Unable to save slow scheduler';
        }
    }
    echo $message;
	

	}else{
		$message='Please authenticate first';
		echo $message;
	}

    }
    }else{
		$message='Please authenticate first';
		echo $message;
	} 
}



/*create_arc_item_fast Schedule Request*/
elseif(isset($content['create_arc_item_all_fast_schedule'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    
    
    if(!empty($row)){

    if($row['auth_status'] == '1'){

        if($content['s_schedule'] == 1){
            $cron_schedule = "0 0/15 * * * ?";
        }elseif($content['s_schedule'] == 2){
            $cron_schedule = "0 0 * * * ?";
        }elseif($content['s_schedule'] == 3){
            $cron_schedule = "0 0 12 * * ?";
        }

        $baseURL = $arc->baseUrl11($row['arc_domain']);
        $url = $baseURL."/user/plugins/".$pack_id."/create_arc_item_fast_cron.php?user=".$content['arc_user']."&m_orders=".$content['s_orders']."&m_quantity=".$content['s_quantity']."&m_details=".$content['s_details']."&m_prices=".$content['s_prices']."&domain=".$row['domain']."&schedule_type=".$content['schedule_type']."";



	$data_i = [
        'Url' => $url,
        'CronSchedule' => $cron_schedule
    ];

    $data_e = [
        'Url' => $url,
        'CronSchedule' => $cron_schedule
    ];

    
    $getAllSchedulers=$arc->getAllSchedulers();
    $getSchedulerById = array();
    foreach($getAllSchedulers as $getAllSchedulerKey=>$getAllSchedulerValue){
        if (strpos($getAllSchedulerValue['Url'], 'create_arc_item_fast_cron.php') !== false && strpos($getAllSchedulerValue['Url'], $row['domain']) !== false && strpos($getAllSchedulerValue['Url'], 'schedule_type') !== false && strpos($getAllSchedulerValue['Url'], $content['schedule_type']) !== false) {
            $getSchedulerById = $getAllSchedulers[$getAllSchedulerKey];
        }
    }
    if(!empty($getSchedulerById)){
        $UpdateScheduler = $arc->editScheduler($data_e, $getSchedulerById['Id']);
        if(!empty($UpdateScheduler)){
            $message='Successfully updated fast scheduler';
        }else{
            $message='Unable to update fast scheduler';
        }
    }else{
        $insertScheduler = $arc->createScheduler($data_i);
        if(!empty($insertScheduler)){
            $message='Successfully saved fast scheduler';
        }else{
            $message='Unable to save fast scheduler';
        }
    }
    echo $message;
	

	}else{
		$message='Please authenticate first';
		echo $message;
	}

    }
    }else{
		$message='Please authenticate first';
		echo $message;
	} 
}



/*create_arc_item_event Checked Request*/
/* elseif(isset($content['create_arc_item_event'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];
    
    
    if(!empty($row)){

    if($row['auth_status'] == '1'){

      


	$data_i = [
        'merchant_guid' => $content['arc_user'],
        'orders' => $content['s_orders'],
        'quantity' => $content['s_quantity'],
        'details' => $content['s_details'],
        'prices' => $content['s_prices'],
        'items' => $content['s_items'],
        'categories' => $content['s_categories']
    ];

    $data_e = [
        'orders' => $content['s_orders'],
        'quantity' => $content['s_quantity'],
        'details' => $content['s_details'],
        'prices' => $content['s_prices'],
        'items' => $content['s_items'],
        'categories' => $content['s_categories']
    ];

    
    $getAllSchedulers=$arc->getAllSchedulers();
    $getSchedulerById = array();
    foreach($getAllSchedulers as $getAllSchedulerKey=>$getAllSchedulerValue){
        if (strpos($getAllSchedulerValue['Url'], 'create_arc_item_fast_cron.php') !== false && strpos($getAllSchedulerValue['Url'], $row['domain']) !== false && strpos($getAllSchedulerValue['Url'], 'schedule_type') !== false && strpos($getAllSchedulerValue['Url'], $content['schedule_type']) !== false) {
            $getSchedulerById = $getAllSchedulers[$getAllSchedulerKey];
        }
    }
    if(!empty($getSchedulerById)){
        $UpdateScheduler = $arc->editScheduler($data_e, $getSchedulerById['Id']);
        if(!empty($UpdateScheduler)){
            $message='Successfully updated fast scheduler';
        }else{
            $message='Unable to update fast scheduler';
        }
    }else{
        $insertScheduler = $arc->createScheduler($data_i);
        if(!empty($insertScheduler)){
            $message='Successfully saved fast scheduler';
        }else{
            $message='Unable to save fast scheduler';
        }
    }
    echo $message;
	

	}else{
		$message='Please authenticate first';
		echo $message;
	}

    }
    }else{
		$message='Please authenticate first';
		echo $message;
	} 
} */


/*create_arc_item_event Custom Table Request*/
elseif(isset($content['create_arc_item_event'])){
	$data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'auth_status',
            'Operator'=> 'equal',
            'Value'=> "1"
        ]
      ];

      
    
    $authListById=$arc->searchTable($pack_id, 'auth', $data1);
    if(!empty($authListById['Records'])){
    $row=$authListById['Records'][0];

    $data_create_arc_item_event = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $content['arc_user']
        ],
        [
            'Name'=> 'domain',
            'Operator'=> 'equal',
            'Value'=> $row['domain']
        ]
      ];
    
    
    if(!empty($row)){

    if($row['auth_status'] == '1'){
	$data_i = [
        'merchant_guid' => $content['arc_user'],
        'orders' => $content['s_orders'],
        'quantity' => $content['s_quantity'],
        'details' => $content['s_details'],
        'prices' => $content['s_prices'],
        //'items' => $content['s_items'],
        //'categories' => $content['s_categories'],
        'domain' => $row['domain']
    ];

    $data_e = [
        'orders' => $content['s_orders'],
        'quantity' => $content['s_quantity'],
        'details' => $content['s_details'],
        'prices' => $content['s_prices']
        //'items' => $content['s_items'],
        //'categories' => $content['s_categories']
    ];
    $create_arc_item_eventListById=$arc->searchTable($pack_id, 'create_arc_item_event', $data_create_arc_item_event);

    if(!empty($create_arc_item_eventListById['Records'])){
        $UpdateRowIncreate_arc_item_event = $arc->editRowEntry($pack_id, 'create_arc_item_event', $create_arc_item_eventListById['Records'][0]['Id'], $data_e);
        if(!empty($UpdateRowIncreate_arc_item_event)){
            $message = 1;
        }else{
            $message = 2;
        }
    }else{
        $insertRowIncreate_arc_item_event = $arc->createRowEntry($pack_id, 'create_arc_item_event', $data_i);
        if(!empty($insertRowIncreate_arc_item_event)){
            $message = 3;
        }else{
            $message = 4;
        }
    }
    $create_arc_item_eventListByIdAfter=$arc->searchTable($pack_id, 'create_arc_item_event', $data_create_arc_item_event);
    $create_arc_item_eventListByIdAfter1 = $create_arc_item_eventListByIdAfter['Records'][0];
    $baseURL = $arc->baseUrl11($row['arc_domain']);
    $url = $baseURL."/user/plugins/".$pack_id."/create_arc_item_event.php?user=".$content['arc_user']."&domain=".$create_arc_item_eventListByIdAfter1['domain']."";
    
    if($message == 1) $message_array = array('code'=>'success', 'message' => 'Successfully updated event', 'url' => $url);
    elseif($message == 2) $message_array = array('code'=>'error', 'message' => 'Unable to update event', 'url' => $url);
    elseif($message == 3) $message_array = array('code'=>'success', 'message' => 'Successfully saved event', 'url' => $url);
    elseif($message == 4) $message_array = array('code'=>'error', 'message' => 'Unable to save event', 'url' => $url);
    echo json_encode($message_array);

	}else{
        $message = 0;
		echo json_encode( array('code'=>'error1', 'message' => 'Please authenticate first'));
	}

    }
    }else{
        $message = 0;
		echo json_encode( array('code'=>'error1', 'message' => 'Please authenticate first'));
	}
}