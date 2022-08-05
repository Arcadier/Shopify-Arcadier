<?php
include 'magento_functions.php';
include 'api.php';
$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$marketplace_domain = getMarketplaceDomain();
date_default_timezone_set(TIME_ZONE_PLUGIN);

/* Create Arcadier Item All Manual Request*/

if(isset($_GET['user']) && isset($_GET['schedule_type']) && $_GET['schedule_type'] == 's_schedule_fast'){ 
//if(isset($_GET['user']) && isset($_GET['m_orders']) && isset($_GET['m_quantity']) && isset($_GET['m_details']) && isset($_GET['m_prices'])){ 
	

            $content['arc_user']  = $_GET['user'];
            $content['m_orders']  = $_GET['m_orders'];
            $content['m_quantity']  = $_GET['m_quantity'];
            $content['m_details']  = $_GET['m_details'];
            $content['m_prices']  = $_GET['m_prices'];
            $content['domain']  = $_GET['domain'];
            $content['checked_data']  = array();

            $data11 = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $content['arc_user']
                ]
            ];
            $data_auth = [
				[
				  'Name'=> 'merchant_guid',
				  'Operator'=> 'equal',
				  'Value'=> $content['arc_user']
				],
				[
					'Name'=> 'domain',
					'Operator'=> 'equal',
					'Value'=> $content['domain']
				]
			  ];  
            $authListById=$arc->searchTable($pack_id, 'auth', $data_auth);
            $configListById=$arc->searchTable($pack_id, 'config', $data_auth);
            $create_arc_item_slowListById=$arc->searchTable($pack_id, 'create_arc_item_slow', $data_auth);
            if(!empty($authListById['Records'])){
                //if($authListById['Records'][0]['auth_status'] == '1'){
                    $authRowByMerchantGuid=$authListById['Records'][0];
                    $configRowByMerchantGuid=$configListById['Records'][0];
                    if(!empty($create_arc_item_slowListById['Records'])){
                        $create_arc_item_slowRowByMerchantGuid=$create_arc_item_slowListById['Records'][0];
                        $content['checked_data']  = unserialize($create_arc_item_slowRowByMerchantGuid["checked_data"]);
                    }else{
                        $create_arc_item_slowRowByMerchantGuid=array();
                        $content['checked_data']  = array();
                    }
               /*  }else{
                    //header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
                    echo 'Not Authenticated'; die;
                } */
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
			$sync_trigger = 'Fast';
			$sync_created = 0;
			$sync_changed = 0;
			$sync_unchanged = 0;
			//foreach($mag_product1['items'] as $mag_product11){
			foreach($content['checked_data'] as $content_checked_data){
			$mag_product=$mag->magento_get_one_sku($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token'], $content_checked_data['sku1']);
            //$mag_product1 = $mag_product;
            $mag_product11 = json_decode(json_encode($mag_product), true);
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
                            'Value'=> $content['domain']
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