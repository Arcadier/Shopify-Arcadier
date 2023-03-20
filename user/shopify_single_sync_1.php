<?php
include 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();


$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i");

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
//$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

$result = callAPI("GET", $admin_token, $url, false);

$admin_id = $result['ID'];

$packageId = getPackageID();

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';


//get merchant's shipping method

//$url =  $baseUrl . '/api/v2/merchants/' . $userId . '/shipping-methods/';
$merchant_shippingMethods =  $arc->getShippingMethods($userId); //callAPI("GET", $admin_token, $url, false);
$admin_shippingMethods =  $arc->getShippingMethods($admin_id);

//error_log('shipping methods ' . json_encode($merchant_shippingMethods));


$all_shipping_methods = [];

if (!empty($shippingMethods)) {

    foreach($merchant_shippingMethods as $shipping) { 
        $all_shipping_methods[] = array("ID" => $shipping['ID']);
    
    }

}
else {

    foreach($admin_shippingMethods as $shipping) { 
         $all_shipping_methods[] = array("ID" => $shipping['ID']);
    
    }
    
}

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
}

//error_log('auth ' . json_encode($authDetails));

// $shop_secret_key = $authDetails['Records'][0]['secret_key'];
// $shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];


$product_id =  $content['id'];
$product_name = $content['name'];
$categories =  $content['category'];
$images = $content['images'];
//price = $content['price'];
$stock = $content['qty'];

$syncItems = array(array('Name' => 'product_id', "Operator" => "equal",'Value' => $product_id));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/synced_items';
$isItemSyncResult =  callAPI("POST", $admin_token, $url, $syncItems);

//1.  save the items on arc using merchant API,

$all_categories = [];

foreach($categories as $category) { 
    $all_categories[] = array("ID" => $category);
    
}

//get the variant
$images = shopify_get_images($access_token, $shop, $product_id);

//error_log(json_encode($images));


$product_details = shopify_product_details($access_token, $shop, ltrim($product_id,"gid://shopify/Product/"));   // shopify_get_variants($access_token, $shop, $product_id);


$product_name = $product_details['product']['title'];
$description = $product_details['product']['body_html'];
$description = strip_tags(html_entity_decode($description));

$variants = $product_details['product']['variants'];

$has_variants =  (count($variants) == 1 && $variants[0]['title'] == 'Default Title') ? 0 : 1;

$prices = [];

 foreach($variants as $variant){
    $prices[] = (float)$variant['price'];
 }

$price = min($prices);

//error_log('price ' . $price);

//$price = $variants[0]['price'];

$variant_id = $variants[0]['id'];
$inventory = $variants[0]['inventory_quantity'];
$sku = $variants[0]['sku'];

$allimages = [];

foreach($images  as $image) {
    $allimages[] = array('MediaUrl' => $image['node']['originalSrc']) ;
    
    //$allimages = [];

}

//$image =  $images[0]['node']['originalSrc'];


if ($has_variants) {

    $allvariants = [];


    $images = $product_details['product']['images'];
    

    //count the options array

    foreach($variants as $variant){

    error_log('variant ' .  json_encode($variant));
    $id = $variant['id'];
    $variant_image =  findItem($images, $id);
    $media = '';
    if ($variant_image['src'] != null) {

        $media = array(array( "MediaUrl" => $variant_image['src']));
       // $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
    }

    error_log('media  ' . json_encode($media));
   
      
    //   array_filter($images, function($image) use ($id) {
    //   $filtered =  in_array($id, $image['variant_ids']);
    //   return $filtered;
    
    // });

    
        count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  '',  'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price'] - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '','Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']   - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  '', 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  '', 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' =>  '', 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
    
    }

    
}

$allvariants = !empty($allvariants) ? $allvariants : null;

$item_details = array(
      'SKU' =>  'sku',
      'Name' =>  $product_name,
      'BuyerDescription' => $description,
      'SellerDescription' => $description,
      'Price' => (float)$price,
      'PriceUnit' => null,
      'StockLimited' => true,
      'StockQuantity' =>  $inventory,
      'IsVisibleToCustomer' => true,
      'Active' => true, 
      'IsAvailable' => '',
      'CurrencyCode' =>  'AUD',
      'Categories' =>   $all_categories,
      'ShippingMethods'  => $all_shipping_methods,
      'PickupAddresses' => null,
      'Media' => $allimages,
      'Tags' => null,
      'CustomFields' => null,
      'ChildItems' =>  $allvariants

);


error_log('total records ' . $isItemSyncResult['TotalRecords']);

if ($isItemSyncResult['TotalRecords'] == 0) {

    error_log('in zero condition');

    error_log(json_encode($allimages));
    $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
    $result =  callAPI("POST", $admin_token, $url, $item_details);
    $result1 = json_encode(['err' => $result]);
     
    ///error_log(json_encode($result1));

        if ($result['ID']){

            //error_log($result['ID']);
            //after syncing the product on arcadier, update the tags on shopify to 'synced'

            //shopify_add_tag($access_token, $shop, $product_id, "synced");

            //if 0 - not exist yet, create a new row on synced_items table

            $sync_details = [

            "product_id" => $product_id,
            "synced_date" => time(),
            "merchant_guid" => $userId,
            'arc_item_guid' => $result['ID'],
            'variant_id' => "gid://shopify/ProductVariant/" . $variant_id
            
            ];

        
                //update the item's custom field

            $data = [
                'CustomFields' => [
                    [
                        'Code' =>  $is_shopify_code,
                        'Values' => [ 1 ],
                    ],
                ],
            ];

            $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $result['ID'];
            $result = callAPI("PUT", $admin_token, $url, $data);


            $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);

            // error_log(json_encode($response));

            
            //add counter to the total created 
            //$total_created++;

                        
        }

        echo json_encode('success');

}       
else {

    error_log('exists!');

    $itemInfo =  $arc->getItemInfo($isItemSyncResult['Records'][0]['arc_item_guid']);


    if (array_key_exists('Code', $itemInfo)) {

        error_log('in code cond');

        if ($itemInfo['Code'] == 400){

            error_log('in 400');

            //if the item is on synced details and does not exist on arcadier, add it as new one

            $url =  $baseUrl . '/api/v2/merchants/' . $userId . '/items';
            $result =  callAPI("POST", $admin_token, $url, $item_details);
            $result1 = json_encode(['err' => $result]);
            error_log('details ' . json_encode($item_details));
            ///error_log(json_encode($result1));

                if ($result['ID']){

                    //error_log($result['ID']);
                    //after syncing the product on arcadier, update the tags on shopify to 'synced'

                    //shopify_add_tag($access_token, $shop, $product_id, "synced");

                    //if 0 - not exist yet, create a new row on synced_items table

                    $sync_details = [

                    "product_id" => $product_id,
                    "synced_date" => time(),
                    "merchant_guid" => $userId,
                    'arc_item_guid' => $result['ID'],
                    'variant_id' => "gid://shopify/ProductVariant/" . $variant_id
                    
                    ];

                
                    //update the item's custom field

                    $data = [
                        'CustomFields' => [
                            [
                                'Code' =>  $is_shopify_code,
                                'Values' => [ 1 ],
                            ],
                        ],
                    ];

                    $url = $baseUrl . '/api/v2/merchants/' . $userId . '/items/' . $result['ID'];
                    $result = callAPI("PUT", $admin_token, $url, $data);


                    $response = $arc->createRowEntry($packageId, 'synced_items', $sync_details);

                // error_log(json_encode($response));

                    
                    //add counter to the total created 
                    //$total_created++;

                                
                }

            echo json_encode('success');
        }



    }
    else {


        error_log('not code cond');

    //start
    $childItems =  $itemInfo['ChildItems']; 
    if ($has_variants) {

//     $allvariants = [];

    $images = $product_details['product']['images'];
    

//     //count the options array
    
    foreach($variants as $variant){
    $found = false;

    error_log('shopify variant ' .  json_encode($variant));

    
    $id = $variant['id'];
    $variant_image =  findItem($images, $id);
    $media = '';
    if ($variant_image['src'] != null) {

        $media = array(array( "MediaUrl" => $variant_image['src']));
       // $variant_image['src'] = "https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg";
    }
   
      
   // if ( $variant['option1'] != null) {

        $found = false;

            foreach ($childItems as $arc_variant) {


                    $shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $arc_variant['AdditionalDetails']);

                    
                    if ($id ==  $shopify_variant_ID) {
                            $ex_variant_details_1 =  $arc_variant;
                            $found = true;    
                            error_log('found ' . $id);
                            
                         $child_items_array = [];
            	            $details = [
							'SKU' => $variant['sku'],                     //Shopify variant SKU
							'Price' => $variant['price']  - $price,   //Shopify variant surcharge calculation
							'StockQuantity' =>  $variant['inventory_quantity'],            //Shopify variant stock
							'Media' => [
								[
									'MediaUrl'=> $media         //the image of that Shopify variant
								]
							]
						];
						array_push($child_items_array, $details);         //ignore this
						//update the Arcadier Variant
						$url =  $baseUrl . '/api/v2/merchants/'. $userId .'/items/'. $ex_variant_details_1['ID'];   //this item is the Arcadier Variant ID from the loop -> ChildItems[i].ID  (NOT ChildItems[i].Variants[j].ID)
						
						//API call
						$updateItem =  callAPI("PUT", $admin_token, $url, $details); 
                             
                    }
           
            }  

            if ($found == false) {

                $shopify_combination = [ $variant['option1'], $variant['option2'], $variant['option3'] ];   //get the variant options in an array
					$variant_object = [];
					foreach($shopify_combination as $key => $combination){   //lopp through the above array, making sure to ignore nulls
						if($combination !== null){
							$data = [
								'ID' => '',
								'GroupID' => '',											 //kept empty for now, changes on line 251
								'Name' => $shopify_combination[$key],                        //variant options
								'GroupName' => $product_details['product']['options'][$key]['name'] //variant group name from the SHopify Get one product API
							];

							
							foreach( $childItems as $child){                           //loop through Arcadier variants
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

                    //error_log('variant pic ' . json_encode($variant_picture));
					$data = [
						'ChildItems' => [
							[
								'ID' => null,
								'CurrencyCode' => 'AUD',
								'Variants' => $variant_object,                          //from line 256
								'SKU' => $variant['sku'],                       
								'Price' => $variant['price']  - $price,  //surcharge
								'StockLimited' => true,
								'StockQuantity' => $variant['inventory_quantity'],
								'Media' => [
									[
										'MediaUrl' => $media
									]
								],
								'AdditionalDetails' => $variant['admin_graphql_api_id']  //include this mandatory
							]
						]
					];

					//update Arcadier Item
					$url =  $baseUrl . '/api/v2/merchants/'. $userId .'/items/'. $isItemSyncResult['Records'][0]['arc_item_guid'];           //the Arcadier parent item ID 
					$updateItem =  callAPI("PUT", $admin_token, $url, $data); 
					if($updateItem['Name'] == $product_details['product']['title']){
						//echo "New variant added success";
					} else {
					//	echo "New variant addition failed";
					}


            }

          
          
          
        ///  else {

           




            //no variant id found

            
         // }

          
   // }

    //if ( $variant['option2'] != null) {
       //   $ex_variant_details_2 = findVariant($childItems, $variant['option2']);
   // }
    
   //  if ( $variant['option3'] != null) {
   
   //      $ex_variant_details_3 = findVariant($childItems, $variant['option3']);
   //  }
    
       // count($product_details['product']['options']) == 1 ?  $allvariants[] = array('Variants' => [array('ID' =>  $ex_variant_details_1['ID'], 'GroupID' => $ex_variant_details_1['GroupID'],  'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name'])], 'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        //count($product_details['product']['options']) == 2 ?  $allvariants[] = array('Variants' => [array('ID' =>  $ex_variant_details_1['ID'], 'GroupID' => $ex_variant_details_1['GroupID'], 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  $ex_variant_details_2['ID'], 'GroupID' => $ex_variant_details_2['GroupID'],'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'], 'Media' => $media, 'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
        //count($product_details['product']['options']) == 3 ?  $allvariants[] = array('Variants' => [array('ID' =>  $ex_variant_details_1['ID'], 'GroupID' => $ex_variant_details_1['GroupID'], 'Name' => $variant['option1'], 'GroupName' => $product_details['product']['options'][0]['name']), array('ID' =>  $ex_variant_details_2['ID'], 'GroupID' => $ex_variant_details_2['GroupID'], 'Name' => $variant['option2'], 'GroupName' => $product_details['product']['options'][1]['name']),array('ID' =>  $ex_variant_details_3['ID'], 'GroupID' => $ex_variant_details_3['GroupID'], 'Name' => $variant['option3'], 'GroupName' => $product_details['product']['options'][2]['name'])],  'SKU' => $variant['sku'] , 'Price' => $variant['price']  - $price, 'StockLimited' => true, 'StockQuantity' => $variant['inventory_quantity'],'Media' => $media,  'AdditionalDetails' => "gid://shopify/ProductVariant/" . $id) : '';
    
    }
}

$allvariants = !empty($allvariants) ? $allvariants : null;

error_log('all variants ' . json_encode($allvariants));


    $item_details = array(
        'SKU' =>  'sku',
        'Name' =>  $product_name,
        'BuyerDescription' => $description,
        'SellerDescription' => $description,
        'Price' => (float)$price,
        'PriceUnit' => null,
        'StockLimited' => true,
        'StockQuantity' =>  $inventory,
        'IsVisibleToCustomer' => true,
        'Active' => true,
        'IsAvailable' => '',
        'CurrencyCode' =>  'AUD',
        'Categories' =>   $all_categories,
        'ShippingMethods'  => $all_shipping_methods,
        'PickupAddresses' => null,
        'Media' => $allimages,
        //  'Tags' => null,
    //   'CustomFields' => null,
        // 'ChildItems' =>  $allvariants

    );

    $updateItem =  $arc->editItem($item_details, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);


//stop
if ($has_variants) {
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
				$variants_images = $product_details['product']['images'];
				foreach($variants_images as $images){
					if(count($images['variant_ids']) !== 0){
						if($images['variant_ids'][0] == $variant_ID){
							$variant_picture = $images['src'];
						}
					}
				}

				//get Arcadier item details
				$url =  $baseUrl . '/api/v2/items/'. $isItemSyncResult['Records'][0]['arc_item_guid'];
				$item =  callAPI("GET", $admin_token, $url, false); 

				//apply changes to existing variants
				//loop through Arcadier's variants and find the variant which is equivalent to the above Shopify variant 
				foreach($item['ChildItems'] as $arcadier_item){
					$shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $arcadier_item['AdditionalDetails']);  //contains the Shopify variant ID
					if($shopify_variant_ID == $variant_ID){
						//get the details from the SHopify variant
						$details = [
							'SKU' => $element['sku'],                     //Shopify variant SKU
							'Price' => $variant_price - $price,   //Shopify variant surcharge calculation
							'StockQuantity' => $variant_stock,            //Shopify variant stock
							'Media' => [
								[
									'MediaUrl'=> $variant_picture         //the image of that Shopify variant
								]
							]
						];
						array_push($child_items_array, $details);         //ignore this
						//update the Arcadier Variant
						$url =  $baseUrl . '/api/v2/merchants/'. $userId .'/items/'. $arcadier_item['ID'];   //this item is the Arcadier Variant ID from the loop -> ChildItems[i].ID  (NOT ChildItems[i].Variants[j].ID)
						
						//API call
						//$updateItem =  callAPI("PUT", $admin_token, $url, $details); 
						if($updateItem['Name'] == $product_details['product']['title']){
							//echo "Existing variants item success";
						} else {
							//echo "Arcadier Existing Variant Item Update failed";
						}
					}
					
					$child_items_array = [];   //ignore this
					$details = [];   //reset the details object to update the next variant
				}
			}


            	/////////////////////////////////////////
			//find new variants

			//get Arcadier item details
			$url =  $baseUrl . '/api/v2/items/'. $isItemSyncResult['Records'][0]['arc_item_guid'];
			$item =  callAPI("GET", $admin_token, $url, false);              //get the main item details stored in $id

			foreach($variants as $shopify_variant){   //loop through all shopify variants
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
				//	error_log('Arcadier options: '.json_encode($arcadier_combination));
				///	error_log('Shopify options: '.json_encode($shopify_combination));
					if($shopify_combination == $arcadier_combination){   //if the sorted arrays are equal, that shopify variant already exists. no need to create it. break out of loop
						$found_match = true;
						break;
					}
				}
				//new variant found
				if($found_match == false){     //this will only occur if the SHopify variant's match was never found. meaning its a new variant. Time to create it

					//get variant image
					$variant_picture = "";
					$variants_images = $product_details['product']['images'];
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
								'GroupName' => $product_details['product']['options'][$key]['name'] //variant group name from the SHopify Get one product API
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

                    error_log('variant pic ' . json_encode($variant_picture));
					$data = [
						'ChildItems' => [
							[
								'ID' => null,
								'CurrencyCode' => 'AUD',
								'Variants' => $variant_object,                          //from line 256
								'SKU' => $shopify_variant['sku'],                       
								'Price' => $variant_price - $price,  //surcharge
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
					$url =  $baseUrl . '/api/v2/merchants/'. $userId .'/items/'. $isItemSyncResult['Records'][0]['arc_item_guid'];           //the Arcadier parent item ID 
					//$updateItem =  callAPI("PUT", $admin_token, $url, $data); 
					if($updateItem['Name'] == $product_details['product']['title']){
						//echo "New variant added success";
					} else {
					//	echo "New variant addition failed";
					}
				}
			}
    }


                echo json_encode('This item has been updated');

    //get the current variants


  // error_log('details ' . json_encode($item_details));


   // $url =  $baseUrl . '/api/v2/merchants/'. $userId.'/items/' . $isItemSyncResult['Records'][0]['arc_item_guid'];

   // error_log('url '. $url);
   // $updateItem =  callAPI("PUT", $admin_token, $url, $item_details); 

  // $updateItem =  $arc->editItem($item_details, $userId, $isItemSyncResult['Records'][0]['arc_item_guid']);

    // error_log('updated ' . json_encode($updateItem));

}

  // error_log('updated ' . json_encode($updateItem));
    
}

 function findItem(array $variants, int $id)
{
    foreach ($variants as $variant) {
 
        if  (in_array($id, $variant['variant_ids']))
        {
            return $variant;
        }

        //return null;
    }
}

 function findVariant(array $variants, string $name)
{
    foreach ($variants as $variant) {


         $shopify_variant_ID = str_replace("gid://shopify/ProductVariant/", "", $variant['AdditionalDetails']);

         

       // foreach($variant['Variants'] as $childvariant) {

          //  error_log('name '  . $childvariant['Name']);
            if  ($name ==  $shopify_variant_ID)

            {
              //  error_log('found variant ' . json_encode($childvariant));
                    
                return $variant;
            }

      //  }
             

        //return null;
    }
}


?>