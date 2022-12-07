<?php
	include 'callAPI.php';
	include 'api.php';
	include 'shopify_functions.php';
	include_once 'admin_token.php';
	include_once 'api.php';

	$admin_token = getAdminToken()['access_token'];
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);

	$baseUrl = getMarketplaceBaseUrl();
	$packageId = getPackageID();

	//get the product id from the webhook response
	$product_id = $content['id'];
	error_log(json_encode('Product ID from webhook: '.$product_id));

	//search for the equivalent arcadier item guid
	$data = array(array('Name' => 'product_id', "Operator" => "eq",'Value' => 'gid://shopify/Product/'.$product_id));
	$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
	$arcadier_item =  callAPI("POST", $admin_token, $url, $data);
	// error_log(json_encode($arcadier_item));


	//if item is found are found
	if($arcadier_item['TotalRecords'] == 1){
		$arcadier_item_guid = $arcadier_item['Records'][0]['arc_item_guid'];       //get item guid
		$arcadier_merchant_guid = $arcadier_item['Records'][0]['merchant_guid'];   //get item merchant guid

		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------




		//----------------------------------------------------------------------->//check if item is flagged for syncing <-------------------------------------------------------------------------------

		//resync item details from shopify
		resync_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);
	}
	//if results are not found
	else {
		echo ("This item has not been synced to Arcadier.");
	}
		
	echo json_encode($content);

	function resync_item($id, $merchant, $baseUrl, $admin_token, $packageId){
		//find shopify store details
		$data = array(array('Name' => 'merchant_guid', "Operator" => "eq",'Value' => $merchant));
		$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
		$shopify =  callAPI("POST", $admin_token, $url, $data);

		if($shopify['TotalRecords'] == 1){

			//get shopify store details
			$shopify_store = $shopify['Records'][0]['shop'];
			$shopify_access_token = $shopify['Records'][0]['access_token'];

			//get shopify item details
			$url = 'https://'. $shopify_store . '.myshopify.com/admin/api/2022-07/products/' . $GLOBALS['product_id'] . '.json';
			$response = shopifyAPI("GET", $shopify_access_token, $url, false);
			// error_log(json_encode($response));

			//calculate total quantity of all variants
			$variants = $response['product']['variants'];
			$total_quantity = 0;
			$minimum_price = (float)$response['product']['variants'][0]['price'];
			foreach($variants as $element){
				$total_quantity = $total_quantity + $element['inventory_quantity'];
				if((float)$element['price'] < $minimum_price){
					$minimum_price = (float)$element['price'];
				}
			}

			//get all images
			$all_images = $response['product']['images'];
			$image_array = [];
			foreach($all_images as $variant_image){
				$data = [
					'MediaURL' => $variant_image['src']
				];
				array_push($image_array, $data);
				$data = [];
			}

			//edit parent item
			$item_details = array(
				'SKU' =>  $response['product']['variants'][0]['sku'],
				'Name' =>  $response['product']['title'],
				'BuyerDescription' => strip_tags($response['product']['body_html']),
				'SellerDescription' => strip_tags($response['product']['body_html']),
				'Price' => $minimum_price,
				'PriceUnit' => null,
				'StockLimited' => true,
				'StockQuantity' =>  $total_quantity,
				'IsVisibleToCustomer' => $response['product']['status'],
				'Active' => true,
				'IsAvailable' => '',
				'CurrencyCode' =>  'AUD',
				'Media' => $image_array
			);

			//update Arcadier Item
			$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
    		$updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 
			if($updateItem['Name'] == $response['product']['title']){
				echo "Parent item success";
			} else {
				echo "Arcadier Parent Item Update failed";
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//edit children

			//find changes in variants
			$variants = $response['product']['variants'];
			$child_items_array = [];
			foreach($variants as $element){
				//get variant ID
				$variant_ID = $element['id'];
				
				//get price of variant
				$variant_price = $element['price'];

				//get variant stock
				$variant_stock = $element['inventory_quantity'];

				//get images
				$variant_picture = "";
				$variants_images = $response['product']['images'];
				foreach($variants_images as $images){
					if(count($images['variant_ids']) !== 0){
						if($images['variant_ids'][0] == $variant_ID){
							$variant_picture = $images['src'];
						}
					}
				}

				//get Arcadier item details
				$url =  $baseUrl . '/api/v2/items/'. $id;
				$item =  callAPI("GET", $admin_token, $url, false); 

				//apply changes to existing variants
				foreach($item['ChildItems'] as $arcadier_item){
					$shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $arcadier_item['AdditionalDetails']);
					if($shopify_variant_ID == $variant_ID){
						$details = [
							'SKU' => $element['sku'],
							'Price' => $variant_price - $minimum_price,
							'StockQuantity' => $variant_stock,
							'Media' => [
								[
									'MediaUrl'=> $variant_picture
								]
							]
						];
						array_push($child_items_array, $details);
						//update Arcadier Item
						$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $arcadier_item['ID'];
						$updateItem =  callAPI("PUT", $admin_token, $url, $details); 
						if($updateItem['Name'] == $response['product']['title']){
							echo "Existing variants item success";
						} else {
							echo "Arcadier Existing Variant Item Update failed";
						}
					}
					
					$child_items_array = [];
					$details = [];
				}
			}

			/////////////////////////////////////////
			//find new variants

			//get Arcadier item details
			$url =  $baseUrl . '/api/v2/items/'. $id;
			$item =  callAPI("GET", $admin_token, $url, false); 

			foreach($response['product']['variants'] as $shopify_variant){
				$shopify_combination = [ $shopify_variant['option1'], $shopify_variant['option2'], $shopify_variant['option3'] ];
				sort($shopify_combination);

				$arcadier_combination = [];
				$found_match = false;
				foreach($item['ChildItems'] as $arcadier_variant){

					$found_new_variant_combination = false;
					foreach($arcadier_variant['Variants'] as $arcadier_variant_names){
						array_push($arcadier_combination, $arcadier_variant_names['Name']);
					}
					
					
					if(count($arcadier_combination) == 1){
						array_push($arcadier_combination, null);
						array_push($arcadier_combination, null);
					}
					
					if(count($arcadier_combination) == 2){
						array_push($arcadier_combination, null);
						
					}
					sort($arcadier_combination);

					//no new variant found
					error_log('Arcadier options: '.json_encode($arcadier_combination));
					error_log('Shopify options: '.json_encode($shopify_combination));
					if($shopify_combination == $arcadier_combination){
						$found_match = true;
						break;
					}
				}
				//new variant found
				if($found_match == false){

					//get variant image
					$variant_picture = "";
					$variants_images = $response['product']['images'];
					foreach($variants_images as $images){
						if(count($images['variant_ids']) !== 0){
							if($images['variant_ids'][0] == $shopify_variant['id']){
								$variant_picture = $images['src'];
							}
						}
					}
					//build 'Variants' object for Arcadier API
					$shopify_combination = [ $shopify_variant['option1'], $shopify_variant['option2'], $shopify_variant['option3'] ];
					$variant_object = [];
					foreach($shopify_combination as $key => $combination){
						if($combination !== null){
							$data = [
								'ID' => '',
								'GroupID' => '',
								'Name' => $shopify_combination[$key],
								'GroupName' => $response['product']['options'][$key]['name']
							];

							foreach($item['ChildItems'] as $child){
								foreach($child['Variants'] as $variant_groups){
									if($variant_groups['GroupName'] == $data['GroupName']){
										$data['GroupID'] = $variant_groups['GroupID'];
										break 2;
									}
								}
							}
							array_push($variant_object, $data);
						}
					}

					$data = [
						'ChildItems' => [
							[
								'ID' => null,
								'CurrencyCode' => 'AUD',
								'Variants' => $variant_object,
								'SKU' => $shopify_variant['sku'],
								'Price' => $shopify_variant['price'] - $item['Price'],
								'StockLimited' => true,
								'StockQuantity' => $shopify_variant['inventory_quantity'],
								'Media' => [
									[
										'MediaUrl' => $variant_picture
									]
								],
								'AdditionalDetails' => $shopify_variant['admin_graphql_api_id']
							]
						]
					];

					//update Arcadier Item
					$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;
					$updateItem =  callAPI("PUT", $admin_token, $url, $data); 
					if($updateItem['Name'] == $response['product']['title']){
						echo "New variant added success";
					} else {
						echo "New variant addition failed";
					}
				}
			}

			
		}
		//
		else{
			echo ("Something fucked up. Check the \"auth\" custom table.");
		}
	}
	
?>