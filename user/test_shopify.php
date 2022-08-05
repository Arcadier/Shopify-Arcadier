<?php
require_once("shopify_functions.php");

$shop = "codemafia-2";
$token = "shpua_ceb145079023685a85fe46fee76c9208";
$query = array(
"Content-type" => "application/json" // Tell Shopify that we're expecting a response in JSON format
);
//$shop = new shopifySdk();

$products = shopify_products($token, $shop);

$cats = shopify_categories($token, $shop);


// $mutation = array("query" => 'mutation {
// tagsAdd(
    
//         id: "gid://shopify/Product/7464399306911"
//         tags: "synced"
        
//     ) {
//             node {
//             id
//             }
//             userErrors {
//             field
//             message
//             }
//         }
//     }
    
//     ');

//     $tagsCreate = graphql($token, $shop, $mutation);

// echo print_r($tagsCreate);

$metafields = shopify_create_metafields($token, $shop, "gid://shopify/Product/7464399306911", "namespacesample", "key1", "value1");

echo print_r($metafields);