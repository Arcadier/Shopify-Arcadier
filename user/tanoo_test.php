<?php
    include_once 'shopify_functions.php';

    $shop = "tanoo-joy2";
    $token = "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";

    $products = shopify_get_all_products($token, $shop, null, true);

    echo json_encode($products);

    ?>