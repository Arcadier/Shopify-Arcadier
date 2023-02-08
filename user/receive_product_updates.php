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
	$webhook_event = $content['event'];
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

		//check if webhook event is for "updates"
		if($webhook_event == "update"){
			//resync item details from shopify
			resync_item($arcadier_item_guid, $arcadier_merchant_guid, $baseUrl, $admin_token, $packageId);
		}

		if($webhook_event == "delete"){
			//call Arcadier API to delete item
		}
		
		
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
			$variants = $response['product']['variants'];  //from shopify product API (get one product)
			$child_items_array = [];
			foreach($variants as $element){
				//get Shopify variant ID
				$variant_ID = $element['id'];
				
				//get price of Shopify variant
				$variant_price = $element['price'];

				//get Shopify variant stock
				$variant_stock = $element['inventory_quantity'];

				//get all images of Shopify variant
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
				//loop through Arcadier's variants and find the variant which is equivalent to the above Shopify variant 
				foreach($item['ChildItems'] as $arcadier_item){
					$shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $arcadier_item['AdditionalDetails']);  //contains the Shopify variant ID
					if($shopify_variant_ID == $variant_ID){
						//get the details from the SHopify variant
						$details = [
							'SKU' => $element['sku'],                     //Shopify variant SKU
							'Price' => $variant_price - $minimum_price,   //Shopify variant surcharge calculation
							'StockQuantity' => $variant_stock,            //Shopify variant stock
							'Media' => [
								[
									'MediaUrl'=> $variant_picture         //the image of that Shopify variant
								]
							]
						];
						array_push($child_items_array, $details);         //ignore this
						//update the Arcadier Variant
						$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $arcadier_item['ID'];   //this item is the Arcadier Variant ID from the loop -> ChildItems[i].ID  (NOT ChildItems[i].Variants[j].ID)
						
						//API call
						$updateItem =  callAPI("PUT", $admin_token, $url, $details); 
						if($updateItem['Name'] == $response['product']['title']){
							echo "Existing variants item success";
						} else {
							echo "Arcadier Existing Variant Item Update failed";
						}
					}
					
					$child_items_array = [];   //ignore this
					$details = [];   //reset the details object to update the next variant
				}
			}

			/////////////////////////////////////////
			//find new variants

			//get Arcadier item details
			$url =  $baseUrl . '/api/v2/items/'. $id;
			$item =  callAPI("GET", $admin_token, $url, false);              //get the main item details stored in $id

			foreach($response['product']['variants'] as $shopify_variant){   //loop through all shopify variants
				$shopify_combination = [ $shopify_variant['option1'], $shopify_variant['option2'], $shopify_variant['option3'] ];  //for each shopify variant, create array of variant option names "Small", "Black", "Metal". Option2 and Option3 can be null
				sort($shopify_combination); //sort them for later

				$arcadier_combination = [];
				$found_match = false;
				foreach($item['ChildItems'] as $arcadier_variant){           //loop through each arcadier variant

					$found_new_variant_combination = false;   //changes value on line 218, in if condition
					foreach($arcadier_variant['Variants'] as $arcadier_variant_names){
						array_push($arcadier_combination, $arcadier_variant_names['Name']);   //create an array with variant option names "Small", "Black", "Metal"
					}
					
					
					if(count($arcadier_combination) == 1){         //in case there's only 1 variant option, add 2 nulls to make the array the same size as the shopify array (array size 3)
						array_push($arcadier_combination, null);
						array_push($arcadier_combination, null);
					}
					
					if(count($arcadier_combination) == 2){         //in case there's only 2 variant options, add 1 null to make the array the same size as the shopify array (array size 3)
						array_push($arcadier_combination, null);
						
					}
					sort($arcadier_combination);  //sort the array

					//after sorting, if the arrays are equal, it means that this iteration of the foreach loops found the matching variants. If not, the iteration is looking at definitely not the same variant
					//matching variants means that shopify variant exists on Arcadier, so it does not need to be created

					//no new variant found
					error_log('Arcadier options: '.json_encode($arcadier_combination));
					error_log('Shopify options: '.json_encode($shopify_combination));
					if($shopify_combination == $arcadier_combination){   //if the sorted arrays are equal, that shopify variant already exists. no need to create it. break out of loop
						$found_match = true;
						break;
					}
				}
				//new variant found
				if($found_match == false){     //this will only occur if the SHopify variant's match was never found. meaning its a new variant. Time to create it

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
					$shopify_combination = [ $shopify_variant['option1'], $shopify_variant['option2'], $shopify_variant['option3'] ];   //get the variant options in an array
					$variant_object = [];
					foreach($shopify_combination as $key => $combination){   //lopp through the above array, making sure to ignore nulls
						if($combination !== null){
							$data = [
								'ID' => '',
								'GroupID' => '',											 //kept empty for now, changes on line 251
								'Name' => $shopify_combination[$key],                        //variant options
								'GroupName' => $response['product']['options'][$key]['name'] //variant group name from the SHopify Get one product API
							];

							
							foreach($item['ChildItems'] as $child){                           //loop through Arcadier variants
								foreach($child['Variants'] as $variant_groups){               //loop the the variant options of 1 variant
									if($variant_groups['GroupName'] == $data['GroupName']){   //when the variant group is found matching the variant group of Shopify, get its ID
										$data['GroupID'] = $variant_groups['GroupID'];		  //put that ID back into line 242
										break 2;                                              //break the 2 loops because we already got the groupID we wanted.
									}
								}
							}
							array_push($variant_object, $data); //create this object for use on line 265, for the API call to create a new variant
						}
					}

					//request body for creating new variant
					$data = [
						'ChildItems' => [
							[
								'ID' => null,
								'CurrencyCode' => 'AUD',
								'Variants' => $variant_object,                          //from line 256
								'SKU' => $shopify_variant['sku'],                       
								'Price' => $shopify_variant['price'] - $item['Price'],  //surcharge
								'StockLimited' => true,
								'StockQuantity' => $shopify_variant['inventory_quantity'],
								'Media' => [
									[
										'MediaUrl' => $variant_picture
									]
								],
								'AdditionalDetails' => $shopify_variant['admin_graphql_api_id']  //include this mandatory
							]
						]
					];

					//update Arcadier Item
					$url =  $baseUrl . '/api/v2/merchants/'. $merchant .'/items/'. $id;           //the Arcadier parent item ID 
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