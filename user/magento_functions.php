<?php
class MagSdk
{
function magento_auth($domain, $username, $password){
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://".$domain."/rest/V1/integration/admin/token?username=".$username."&password=".$password,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => array(
        "Cookie: PHPSESSID=7c2c76s5t3pb5g3osiclpftq3u"
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;
  
    // return "qwerty123456qwerty123456----";
  }



function get_categories($domain, $token){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/categories/list?searchCriteria%5BcurrentPage%5D=1&searchCriteria%5BpageSize%5D=0&fields=items%5Bid,name,children%5D",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = json_decode(curl_exec($curl));

    curl_close($curl);
    return $response;

    // $hardcoded_cats->items[0]->name = 'Category A';
	// $hardcoded_cats->items[0]->id = '1';
	// $hardcoded_cats->items[1]->name = 'Category B';
	// $hardcoded_cats->items[1]->id = '2';
	// $hardcoded_cats->items[2]->name = 'Category C';
	// $hardcoded_cats->items[2]->id = '3';
	// $hardcoded_cats->items[3]->name = 'Category D';
	// $hardcoded_cats->items[3]->id = '4';
	// $hardcoded_cats->items[4]->name = 'Category E';
    // $hardcoded_cats->items[4]->id = '5';
    
    // return $hardcoded_cats;
}

function magento_products($domain, $token){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //CURLOPT_URL => "https://".$domain."/rest/V1/products?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&fields=items%5Bsku,name%5D",
		CURLOPT_URL => "https://".$domain."/rest/V1/products?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&fields=items%5Bid,sku,name,attribute_set_id,price,status,visibility,type_id,created_at,updated_at,extension_attributes,product_links,options,media_gallery_entries,tier_prices,custom_attributes%5D",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array($auth)
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;

    
    // $hardcoded_items->items[0]->name = 'item A';
	// $hardcoded_items->items[0]->sku = '1';
	// $hardcoded_items->items[1]->name = 'item B';
	// $hardcoded_items->items[1]->sku = '2';
	// $hardcoded_items->items[2]->name = 'item C';
	// $hardcoded_items->items[2]->sku = '3';
	// $hardcoded_items->items[3]->name = 'item D';
	// $hardcoded_items->items[3]->sku = '4';
	// $hardcoded_items->items[4]->name = 'item E';
    // $hardcoded_items->items[4]->sku = '5';
    
    // return $hardcoded_items;
}


function magento_products_all($domain, $token){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //CURLOPT_URL => "https://".$domain."/rest/V1/products?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&fields=items%5Bsku,name%5D",
		//CURLOPT_URL => "https://".$domain."/rest/V1/products?searchCriteria%5BpageSize%5D=50&searchCriteria%5BcurrentPage%5D=1&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_synced&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=0&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bfield%5D=arcadier_forgotten&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5Bvalue%5D=&searchCriteria%5BfilterGroups%5D%5B1%5D%5Bfilters%5D%5B1%5D%5BconditionType%5D=null&fields=items%5Bid,sku,name,attribute_set_id,price,status,visibility,type_id,created_at,updated_at,extension_attributes,product_links,options,media_gallery_entries,tier_prices,custom_attributes%5D",
		CURLOPT_URL => "https://".$domain."/rest/V1/products?searchCriteria%5BpageSize%5D=50000",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array($auth)
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;

    
    // $hardcoded_items->items[0]->name = 'item A';
	// $hardcoded_items->items[0]->sku = '1';
	// $hardcoded_items->items[1]->name = 'item B';
	// $hardcoded_items->items[1]->sku = '2';
	// $hardcoded_items->items[2]->name = 'item C';
	// $hardcoded_items->items[2]->sku = '3';
	// $hardcoded_items->items[3]->name = 'item D';
	// $hardcoded_items->items[3]->sku = '4';
	// $hardcoded_items->items[4]->name = 'item E';
    // $hardcoded_items->items[4]->sku = '5';
    
    // return $hardcoded_items;
}


function initialise_magento_products($domain, $token){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 0,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_synced\",\r\n            \"frontend_input\": \"boolean\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"options\": [\r\n                {\r\n                    \"label\": \"Yes\",\r\n                    \"value\": \"1\"\r\n                },\r\n                {\r\n                    \"label\": \"No\",\r\n                    \"value\": \"0\"\r\n                }\r\n            ],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Boolean\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
      
    curl_close($curl);
    $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    $this->createArcadierSyncTimestampAttr($domain, $auth);
    $this->createArcadierSyncMarketplaceAttr($domain, $auth);
    $this->createArcadierSyncMerchantGuidAttr($domain, $auth);
    $this->createArcadierSyncPackageIdAttr($domain, $auth);
    return $this->createForgottenAttr($domain, $auth);
    // return $response;
}

function assign_to_all_sets($domain, $auth, $id){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attribute-sets/groups/list?searchCriteria%5BpageSize%5D=0&searchCriteria%5BcurrentPage%5D=1&fields=items%5Battribute_set_id,attribute_group_id%5D&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bfield%5D=attribute_group_name&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5Bvalue%5D=Product%20Details&searchCriteria%5BfilterGroups%5D%5B0%5D%5Bfilters%5D%5B0%5D%5BconditionType%5D=eq",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array($auth),
    ));

    $result = json_decode(curl_exec($curl));

    curl_close($curl);
    // return $id;
    // return $result->items[0]->attribute_group_id;
    foreach($result->items as $element){
        $t = str_replace('"', '', $element->attribute_group_id);
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".$domain."/rest/V1/products/attribute-sets/attributes",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"{\r\n  \"attributeSetId\": \"".$element->attribute_set_id."\",\r\n  \"attributeGroupId\": ".$t.",\r\n  \"attributeCode\": \"".$id."\",\r\n  \"sortOrder\": 0\r\n}",
        CURLOPT_HTTPHEADER => array(
            $auth,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // return "{\r\n  \"attributeSetId\": \"".$result->items[5]->attribute_set_id."\",\r\n  \"attributeGroupId\": ".$t.",\r\n  \"attributeCode\": \"".$id."\",\r\n  \"sortOrder\": 0\r\n}";
        sleep(1);
    }
    return 1;
}

function createForgottenAttr($domain, $auth){
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_forgotten\",\r\n            \"frontend_input\": \"boolean\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"options\": [\r\n                {\r\n                    \"label\": \"Yes\",\r\n                    \"value\": \"1\"\r\n                },\r\n                {\r\n                    \"label\": \"No\",\r\n                    \"value\": \"0\"\r\n                }\r\n            ],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Forgotten\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Boolean\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
      
    curl_close($curl);
    return $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    // return $response;
}
function createArcadierSyncTimestampAttr($domain, $auth){
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_timestamp\",\r\n            \"frontend_input\": \"text\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n             \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Timestamp\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"datetime\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Text\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    //CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_timestamp\",\r\n            \"frontend_input\": \"string\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"frontend_labels\": [\r\n\t\t\t  {\r\n\t\t\t\t\"store_id\": 0,\r\n\t\t\t\t\"label\": \"string\"\r\n\t\t\t  }\r\n\t\t\t],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Timestamp\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\Eav\\Model\\Entity\\Attribute\\Source\\String\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
	CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
    //echo "<pre>"; print_r($response); die;
    curl_close($curl);
    return $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    // return $response;
}

function createArcadierSyncMarketplaceAttr($domain, $auth){
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_marketplace\",\r\n            \"frontend_input\": \"textarea\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n             \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Marketplace\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"varchar\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Text\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    //CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_timestamp\",\r\n            \"frontend_input\": \"string\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"frontend_labels\": [\r\n\t\t\t  {\r\n\t\t\t\t\"store_id\": 0,\r\n\t\t\t\t\"label\": \"string\"\r\n\t\t\t  }\r\n\t\t\t],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Timestamp\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\Eav\\Model\\Entity\\Attribute\\Source\\String\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
	CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
    //echo "<pre>"; print_r($response); die;
    curl_close($curl);
    return $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    // return $response;
}
function createArcadierSyncMerchantGuidAttr($domain, $auth){
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_merchant_guid\",\r\n            \"frontend_input\": \"textarea\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n             \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Merchant Guid\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"varchar\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Text\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    //CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_timestamp\",\r\n            \"frontend_input\": \"string\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"frontend_labels\": [\r\n\t\t\t  {\r\n\t\t\t\t\"store_id\": 0,\r\n\t\t\t\t\"label\": \"string\"\r\n\t\t\t  }\r\n\t\t\t],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Timestamp\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\Eav\\Model\\Entity\\Attribute\\Source\\String\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
	CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
    //echo "<pre>"; print_r($response); die;
    curl_close($curl);
    return $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    // return $response;
}
function createArcadierSyncPackageIdAttr($domain, $auth){
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/attributes/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_package_id\",\r\n            \"frontend_input\": \"textarea\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n             \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Package Id\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"varchar\",\r\n            \"source_model\": \"Magento\\\\Eav\\\\Model\\\\Entity\\\\Attribute\\\\Source\\\\Text\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
    //CURLOPT_POSTFIELDS =>"{\r\n\t\"attribute\": {\r\n\t\t\t\"is_wysiwyg_enabled\": false,\r\n            \"is_html_allowed_on_front\": true,\r\n            \"used_for_sort_by\": false,\r\n            \"is_filterable\": true,\r\n            \"is_filterable_in_search\": false,\r\n            \"is_used_in_grid\": false,\r\n            \"is_visible_in_grid\": false,\r\n            \"is_filterable_in_grid\": false,\r\n            \"position\": 1,\r\n            \"apply_to\": [],\r\n            \"is_searchable\": \"0\",\r\n            \"is_visible_in_advanced_search\": \"0\",\r\n            \"is_comparable\": \"0\",\r\n            \"is_used_for_promo_rules\": \"1\",\r\n            \"is_visible_on_front\": \"0\",\r\n            \"used_in_product_listing\": \"0\",\r\n            \"is_visible\": true,\r\n            \"scope\": \"global\",\r\n            \r\n            \"attribute_code\": \"arcadier_sync_timestamp\",\r\n            \"frontend_input\": \"string\",\r\n            \"entity_type_id\": \"4\",\r\n            \"is_required\": false,\r\n            \"frontend_labels\": [\r\n\t\t\t  {\r\n\t\t\t\t\"store_id\": 0,\r\n\t\t\t\t\"label\": \"string\"\r\n\t\t\t  }\r\n\t\t\t],\r\n            \"is_user_defined\": true,\r\n            \"default_frontend_label\": \"Arcadier Synced Timestamp\",\r\n            \"frontend_labels\": [],\r\n            \"backend_type\": \"int\",\r\n            \"source_model\": \"Magento\\Eav\\Model\\Entity\\Attribute\\Source\\String\",\r\n            \"default_value\": \"\",\r\n            \"is_unique\": \"0\",\r\n            \"validation_rules\": []\r\n\t}\r\n}",
	CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    )
    ));
      
    $response = json_decode(curl_exec($curl));
    //echo "<pre>"; print_r($response); die;
    curl_close($curl);
    return $this->assign_to_all_sets($domain, $auth, $response->attribute_code);
    // return $response;
}

function update_magento_arcadier_sync_timestamp($domain, $token, $sku, $timestamp){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS =>"{\n\"product\": {\n\"custom_attributes\": [\n{\n\"attribute_code\": \"arcadier_sync_timestamp\",\n\"value\": \"".$timestamp."\"\n}\n]\n}\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}

function update_magento_arcadier_sync_marketplace($domain, $token, $sku, $marketplace){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS =>"{\n\"product\": {\n\"custom_attributes\": [\n{\n\"attribute_code\": \"arcadier_sync_marketplace\",\n\"value\": \"".$marketplace."\"\n}\n]\n}\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}

function update_magento($domain, $token, $sku, $status){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS =>"{\n\"product\": {\n\"custom_attributes\": [\n{\n\"attribute_code\": \"arcadier_synced\",\n\"value\": \"".$status."\"\n}\n]\n}\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}

function update_magento1($domain, $token, $sku, $status, $marketplace, $timestamp, $merchant_guid, $package_id){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS =>"{\n\"product\":{\n\"custom_attributes\":[\n{\n\"attribute_code\":\"arcadier_synced\",\n\"value\":\"".$status."\"\n},\n{\n\"attribute_code\":\"arcadier_sync_marketplace\",\n\"value\":\"".$marketplace."\"\n},\n{\n\"attribute_code\":\"arcadier_sync_merchant_guid\",\n\"value\":\"".$merchant_guid."\"\n},\n{\n\"attribute_code\":\"arcadier_sync_package_id\",\n\"value\":\"".$package_id."\"\n},\n{\n\"attribute_code\":\"arcadier_sync_timestamp\",\n\"value\":\"".$timestamp."\"\n}\n]\n}\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}

function magento_get_one_sku($domain, $token, $sku){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    // return 'from '.$domain.' has this token "'.$auth.'"and these SKUs: '.$sku;
    // return "https://".$domain."/rest/V1/products/".$sku;
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    //CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku['sku'],
	CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array($auth),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
	//return $response;

}
function forget_sku($domain, $token, $sku, $status){
    $token = str_replace('"', '', $token);
    $auth = 'Authorization: Bearer '.$token;

    if (strpos($sku, ' ') !== false) {
        $sku = str_replace(' ', '%20', $sku);
    }
    if (strpos($sku, "'") !== false) {
        $sku = str_replace(' ', '%27', $sku);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".$domain."/rest/V1/products/".$sku,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS =>"{\n\"product\": {\n\"custom_attributes\": [\n{\n\"attribute_code\": \"arcadier_forgotten\",\n\"value\": \"".$status."\"\n}\n]\n}\n}",
    CURLOPT_HTTPHEADER => array(
        $auth,
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}



}


?>