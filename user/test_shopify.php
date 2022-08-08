<?php
require_once("shopify_functions.php");

$shop = "codemafia";
$token = "shpua_6acbed863bbabf8b4f56c0c3f900b710";
$query = array(
"Content-type" => "application/json" // Tell Shopify that we're expecting a response in JSON format
);
//$shop = new shopifySdk();

//$products = shopify_products($token, $shop);

//$//cats = shopify_categories($token, $shop);

$products = shopify_get_all_products_paginated($token, $shop,null,true);

echo json_encode($products);
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

///$metafields = shopify_create_metafields($token, $shop, "gid://shopify/Product/7464399306911", "namespacesample", "key1", "value1");

//echo print_r($metafields);