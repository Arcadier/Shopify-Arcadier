<?php

ini_set('max_execution_time', 0); // 0 = Unlimited
include 'callAPI.php';
include 'api.php';
include 'shopify_functions.php';
$arc = new ApiSdk();

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();

//$userToken = $_COOKIE["webapitoken"];
//$url = $baseUrl . '/api/v2/users/'; 
//$result = callAPI("GET", $userToken, $url, false);
$result = $arc->getUserInfo($_GET['user']);
$userId = $result['ID'];
$packageId = getPackageID();

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", $admin_token, $url, false);

$prods_code = '';
foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $prods_code = $cf['Code'];
    }
}


$auth = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop = $authDetails['Records'][0]['shop'];
$access_token= $authDetails['Records'][0]['access_token'];

//import Shopify Product count
$product_count = shopify_product_count($access_token, $shop);

echo $product_count;
$product_import_speed = $product_count['count']/17;
//error_log("Import time: ".$product_import_speed." seconds.", 3, "tanoo_log.php");

//import Shopify Products
$products = shopify_products_paginated($access_token, $shop, null, false);

//$bulk = shopify_get_bulk_item($access_token, $shop);

// echo json_encode($bulk);


// if ($products){

//     $data = [

//     'CustomFields' => [
//         [
//             'Code' => $prods_code,
//             'Values' => [json_encode($products)],
//         ],
//     ],
// ];

// $url = $baseUrl . '/api/v2/users/' . $userId;
// $result = callAPI("PUT", $admin_token, $url, $data);
// }

$all_items = '';
if ($result['CustomFields'] != null)  {

    foreach ($result['CustomFields'] as $cf) {
        if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                    $all_items = $cf['Values'][0];
                    break;
                    //if 1, it is a shopify item else not
        }
    
    }

$all_items = json_decode($all_items, true);
//echo 'all items ' .  json_encode($all_items);

}

if ($all_items){
    
    $products  = $all_items;

}

$last_cursor ='';


if ($products){

    foreach($products as $product){
    if(!next($products)){
        $last_cursor = $product['cursor'];

    }
}

}

//echo json_encode($products);

$url = $baseUrl . '/api/v2/users/' . $userId;
$result = callAPI("GET", $admin_token, $url, null);
            
// echo 'res ' . json_encode($result);
// $all_items = '';
// if ($result['CustomFields'] != null)  {

//     foreach ($result['CustomFields'] as $cf) {
//         if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
//                     $all_items = $cf['Values'][0];
//                     break;
//                     //if 1, it is a shopify item else not
//         }
    
//     }

// $products = json_decode($all_items, true);
// //echo 'all items ' .  json_encode($all_items);

// }


//echo json_encode($products);


//$items =  shopify_products($access_token, $shop);


//  foreach($products as $shopify_productsss){ 

//     $product_details = shopify_product_details($access_token, $shop, ltrim($shopify_productsss['node']['id'],"gid://shopify/Product/"));

//  }


//error_log($products, 3, "tanoo_log.php");
//error_log('products ' . json_encode($products));


$pack_id = $packageId;
$UserInfo = $arc->getUserInfo($_GET['user']);
$isMerchant = false;
if(!empty($UserInfo)){
    foreach($UserInfo['Roles'] as $UserInfoRoles){
        if($UserInfoRoles == 'Merchant'){
            $isMerchant = true;
        }
    }
}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
}

if($isMerchant){
    if(isset($_COOKIE['marketplace']) && isset($_COOKIE['webapitoken']) && isset($_GET['user'])){
        $data_auth = [
            [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $_GET['user']
            ],
            [
                'Name'=> 'auth_status',
                'Operator'=> 'equal',
                'Value'=> "1"
            ]
        ];
        
        $authListById=$arc->searchTable($pack_id, 'auth', $data_auth);

        //if merchant has not connected Shopify
        if(!empty($authListById['Records'])){
            if($authListById['Records'][0]['auth_status'] == '1'){

                $authRowByMerchantGuid = $authListById['Records'][0];
                $data_create_arc_item_slow = [
                    [
                    'Name'=> 'merchant_guid',
                    'Operator'=> 'equal',
                    'Value'=> $_GET['user']
                    ],
                    [
                        'Name'=> 'shop',
                        'Operator'=> 'equal',
                        'Value'=> $authRowByMerchantGuid['shop']
                    ]
                ];

                //find if merchant has slow sync
                $create_arc_item_slowListById = $arc->searchTable($pack_id, 'create_arc_item_slow', $data_create_arc_item_slow);
               
            }else{
                header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
            }
            //Load arcadier categories
            $arcadier_categories = $arc->getCategories(1000, 1);
            $arcadier_categories = $arcadier_categories['Records'];

            //Load category map
            $data = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
            
            $category_map  =  $arc->searchTable($pack_id, "map", $data);
          //  echo 'cat map ' . json_encode($category_map);
            if($category_map['TotalRecords'] == 1){
                $category_map = $category_map['Records'][0]['map'];
            }
            else{
                $category_map = '<b>Not Mapped</b>';
            }
        }
    }else{
        header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
    }
}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
}

?>
<!DOCTYPE html>
<html lang="en" class="foot-plugin-footer">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <link rel="shortcut icon" href="images/favicon.ico">
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/metismenu.min.css" rel="stylesheet" type="text/css">
    <link href="css/icons.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">

    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/jquery-2.1.3.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
    <script type="text/javascript" src="scripts/jquery.dataTables.min.js"></script>
    <script src="scripts/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="css/chosen.css" />
    <script src="scripts/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <script src="scripts/bootstrap.bundle.min.js"></script>

    <style>
    .loader,
    .loader:after {
        border-radius: 50%;
        width: 10em;
        height: 10em;
    }

    .loader {
        margin: auto;
        font-size: 10px;
        position: absolute;
        right: 0;
        left: 0;
        top: 50%;
        text-indent: -1em;
        border-top: 1.1em solid rgba(255, 255, 255, 0.2);
        border-right: 1.1em solid rgba(255, 255, 255, 0.2);
        border-bottom: 1.1em solid rgba(255, 255, 255, 0.2);
        border-left: 1.1em solid #ffffff;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-animation: load8 <?php echo $product_import_speed ?>s infinite;
        animation: load8 <?php echo $product_import_speed ?>s infinite;
    }

    @-webkit-keyframes load8 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes load8 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    #loadingDiv {
        position: fixed;
        top: 0;
        z-index: 9999;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #0000006b;
    }

    button.ui-dialog-titlebar-close {
        display: none;
    }

    span.ui-icon.ui-icon-alert {
        display: none;
    }

    input[type=checkbox],
    input[type=radio] {
        visibility: unset;
    }

    /* .footer {
                    position:unset;
                    left:unset;
                } */
    #loadingDiv1 {
        position: fixed;
        top: 0;
        z-index: 9999;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #2a3142;
    }

    ul#side-menu li a img {
        max-width: 16px;
    }

    #sidebar-menu {
        padding-top: 50px;
    }

    .content-page .content {
        margin-top: 20px;
    }

    table.dataTable thead th,
    table.dataTable thead td {
        padding: 8px 10px;
    }

    /* .foot-plugin-footer .footer {
            padding-bottom: 10px;
            padding-top: 30px;
        }

        .foot-plugin-footer .footer .footer-navigation ul>li>a {
            padding-right: 79px;
        }

        .foot-plugin-footer ul.footer-social-media,
        .foot-plugin-footer .footer-bottom {
            display: none;
        } */

    .foot-plugin-footer .footer {
        padding: 0;
        /* position: absolute; */
        bottom: 0;
        width: inherit;
        /* margin: auto; */
        padding-left: 240px;
    }


    div#logTable_wrapper {
        min-height: 410px;
    }
    </style>
</head>

<body>
    <script>
    function removeClass(div_id, time) {
        $("#" + div_id).fadeOut(time, function() {
            $("#" + div_id).remove();
        });
    }

    function addLoader1() {
        $('body').append(
            '<div id="loadingDiv1"><div style="position: absolute; top: 45%;left: 45%;">Loading <?php echo $product_count['count'] ?> items in about <?php echo round($product_import_speed, 0); ?>s... </div><div class="loader"></div></div>'
        );
    }
    addLoader1();
    </script>
    <div id="wrapper">
        <div class="topbar">
            <!-- LOGO -->
            <div class="topbar-left">
                <a href="index" class="logo">
                </a>
            </div>
            <nav class="navbar-custom">
                <ul class="navbar-right d-flex list-inline float-right mb-0">
                    <li class="dropdown notification-list d-none d-md-block">
                    </li>
                    <!-- full screen -->
                    <li class="dropdown notification-list d-none d-md-block">
                        <a class="nav-link waves-effect" href="#" id="btn-fullscreen">
                            <i class="mdi mdi-fullscreen noti-icon"></i>
                        </a>
                    </li>
                </ul>
                <ul class="list-inline menu-left mb-0">
                    <li class="float-left">
                        <button class="button-menu-mobile open-left waves-effect">
                            <i class="mdi mdi-menu"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- <% include ./Partials/Sidebar  %> -->
        <!-- ========== Left Sidebar Start ========== -->
        <div class="left side-menu">
            <div class="slimscroll-menu" id="remove-scroll">
                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu" id="side-menu">
                        <!--<li class="menu-title">Main</li>-->
                        <li>
                            <a href="index.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"><img
                                    src="images/home-icon.png"> <span> Configuration </span> </a>
                        </li>
                        <li>
                            <a href="category-mapping.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/list-icon.png"> <span> Category Mapping </span></a>
                        </li>
                        <li>
                            <a href="item-control.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/setting-icon.png"> <span> Item Control </span></a>
                        </li>
                        <li>
                            <a href="synchronization.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/synchronize-icon.png"> <span> Synchronization </span></a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Sidebar -left -->
        </div>
        <!-- Left Sidebar End -->
        <div class="content-page">
            <!-- Start content -->
            <div class="content">
                <div class="container-fluid">


                    <div class="page-title-box">
                        <div class="row align-items-center">
                            <div class="col-sm-6" id="flash_message">
                                <h4 class="page-title">Item Control</h4>

                                <a href="#" onclick="loadnext250items('<?php echo $last_cursor ?>');" id="show-next"
                                    cursor-id="<?php echo $last_cursor ?>"> Show Next 250 Items
                                </a>

                            </div>
                            <div id="dialog" title="Alert message" style="display: none">
                                <div class="ui-dialog-content ui-widget-content">
                                    <p>
                                        <span class="ui-icon ui-icon-alert"
                                            style="float: left; margin: 0 7px 20px 0"></span>
                                        <label id="lblMessage"></label>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->
                    <div class="row">
                        <div class="col-12 p-3 bg-white shadow rounded">
                            <div class="table-responsive">
                                <!--<table class="table table-bordered table-striped" id="logTable" >-->
                                <table class="table hover" id="logTable">
                                    <thead>
                                        <tr>
                                            <th>Shopify Item</th>
                                            <th>Shopify Created</th>
                                            <th>Shopify Updated</th>
                                            <th>Arcadier Synced</th>
                                            <th>Shopify Category</th>
                                            <th>Syncronise</th>
                                            <th>Default Category</th>
                                            <th>Override Default Category</th>
                                            <th>Override Category</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($products as $shopify_products){ 
                                            ?>
                                        <tr id="<?php echo $shopify_products['node']['id']; ?>">
                                            <td><?php echo $shopify_products['node']['title']; ?></td>
                                            <td><?php echo date_format(date_create($shopify_products['node']['createdAt']),"d/m/Y H:i"); ?>
                                            </td>
                                            <td><?php echo date_format(date_create($shopify_products['node']['updatedAt']),"d/m/Y H:i"); ?>
                                            </td>
                                            <td><?php  //   synced date
                                                if ( in_array("synced",$shopify_products['node']['tags'] )){
                                                     echo '<b>Yes</b>';
                                                }else {
                                                     echo '<b>No</b>';
                                                }
                                            ?></td>
                                            <td><?php  //Shopify Category
                                                if($shopify_products['node']['customProductType'] == null){
                                                    echo $shopify_products['node']['productType'];
                                                }else{
                                                    echo $shopify_products['node']['customProductType'];
                                                }
                                                    
                                            ?></td>
                                            <td>
                                                <!-- Synchronise checkbox -->
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="sync_product" class="sync_product"
                                                        id="sync_product-<?php echo ltrim($shopify_products['node']['id'],"gid://shopify/Product/"); ?>"
                                                        data-name="<?php echo $shopify_products['node']['title']; ?>"
                                                        data-id="<?php echo $shopify_products['node']['id']; ?>">
                                                    <label class="" for=""><span name="customSpan"></span></label>
                                                </div>
                                            </td>
                                            <td><?php // Arcadier Category Map
                                                //check if category map has been loaded
                                                if($category_map != '<b>Not Mapped</b>'){
                                                    //error_log('Got in the if condition');
                                                    //get shopify product category
                                                    if($shopify_products['node']['customProductType'] == null){
                                                        $shopify_product_category = $shopify_products['node']['productType'];
                                                    }else{
                                                        $shopify_product_category = $shopify_products['node']['customProductType'];
                                                    }
                                                    //('Product Type: '.$shopify_product_category);

                                                    //unserialize the map from table
                                                    //error_log(json_encode($category_map));
                                                    $category_map_unserialized = unserialize($category_map);
                                                    //error_log(json_encode($category_map));
                                                    $shopify_category_list = $category_map_unserialized['list'];
                                                    //echo 'shopify cat list ' . json_encode($shopify_category_list);
                                                    //find the corresponding Arcadier category according to map
                                                    $destination_arcadier_categories = [];
                                                    foreach($shopify_category_list as $li){
                                                        if($li['shopify_category'] == $shopify_product_category.'_category'){
                                                            foreach($li['arcadier_guid'] as $arcadier_category){
                                                                array_push($destination_arcadier_categories, $arcadier_category);
                                                            }
                                                        }
                                                    }

                                                    $category_names = '';
                                                    $category_div_ids = implode(',',$destination_arcadier_categories);
                                                    
                                                    foreach($arcadier_categories as $cat){

                                                        if(in_array($cat['ID'], $destination_arcadier_categories)){
                                                            $category_names = $category_names.$cat['Name'].' ';
                                                        }
                                                    }
                                                    
                                                    echo '<div cat-id='.$category_div_ids.'  id=cat-' . ltrim($shopify_products['node']['id'],"gid://shopify/Product/") .' image-src='. $shopify_products['node']['images']['edges'][0]['node']['originalSrc'] .' price=' .  $shopify_products['node']['variants']['edges'][0]['node']['price'] .' qty='. $shopify_products['node']['totalInventory'] .'>'.$category_names.'</div>';
                                                }
                                                else{
                                                    echo $category_map;
                                                }
                                                ?></td>
                                            <td>
                                                <!-- Override Category checkbox -->
                                                <div>
                                                    <div class="custom-control custom-checkbox">
                                                        <?php 
                                                            // foreach($arcadier_mapped_category_lists as $arcadier_mapped_category_list1){
                                                            //     $arc_cat_index = array_search($arcadier_mapped_category_list1['ID'],array_column($arc_cat_arr['Records'],"ID"));
                                                            //     $default_cat = $arc_cat_arr['Records'][$arc_cat_index]["Name"];
                                                            // }
                                                        ?>
                                                        <input type="checkbox" class="sync_product1"
                                                            name="override_default_category"
                                                            id="override_default_category-<?php echo ltrim($shopify_products['node']['id'],"gid://shopify/Product/") ?>" />
                                                        <label class="" for=""><span name="customSpan"></span></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- Override Category List -->
                                                <div>
                                                    <?php 
                                                        foreach($arcadier_mapped_category_lists as $arcadier_mapped_category_list2){
                                                            $arc_cat_index = array_search($arcadier_mapped_category_list2['ID'],array_column($arc_cat_arr['Records'],"ID"));
                                                            $default_cat = $arc_cat_arr['Records'][$arc_cat_index]["Name"];
                                                        }
                                                    ?>
                                                    <select
                                                        id="override_default_category_select-<?php echo ltrim($shopify_products['node']['id'],"gid://shopify/Product/"); ?>"
                                                        name="override_default_category_select" class="chosen-select"
                                                        data-placeholder="Select Arcadier Category" multiple>
                                                        <?php 
                                                            foreach($arcadier_categories as $arcadier_cat){
                                                                ?>
                                                        <option value="<?php echo $arcadier_cat['ID']; ?>">
                                                            <?php echo $arcadier_cat['Name']; ?></option>
                                                        <?php 
                                                            } 
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- Sync Button -->
                                                <?php

                                                    $short_id = ltrim($shopify_products['node']['id'],"gid://shopify/Product/");
                                                ?>
                                                <a id="save_map"
                                                    onclick="sync_shopify_product('<?php echo $shopify_products['node']['id']; ?>','<?php echo $shopify_products['node']['title']; ?>','<?php echo $short_id; ?>');"
                                                    style="margin-left: 25px;border: #0e77d4;box-sizing: border-box;background-color: #333547;border-radius: 6px;color: white;padding: 5px 10px;font-size: 14px; cursor: pointer;">Sync</a>
                                            </td>
                                        </tr>
                                        <?php 
                                    }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <footer class="footer text-center"> © 2021. </footer> -->
    <script src="scripts/metisMenu.min.js"></script>
    <script src="scripts/jquery.slimscroll.js"></script>
    <script src="scripts/waves.min.js"></script>
    <script src="scripts/app.js"></script>
    <script>
    $(document).ready(function() {
        $('input[type=checkbox]').click(function() {
            if (!$(this).is(':checked')) {
                $('#' + this.id).prop('checked', false);
            }
        });
        $.noConflict();
        $(".chosen-select").chosen({
            width: "125px"
        });

        $.extend($.fn.dataTable.defaults, {
            //searching: false,
            ordering: false,
            //lengthMenu:false,
            //paging:false,
            //info:false
        });
        //$('table.table').DataTable();

        $('#logTable').DataTable({
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ]
        });
        myDialog = $("#dialog").dialog({
            // dialog settings:
            //autoOpen : false,
            // ... 
        });
        myDialog.dialog("close");
    });

    function ShowCustomDialog(dialogtype, dialogmessage) {
        ShowDialogBox(dialogtype, dialogmessage, 'Ok', '', 'GoToAssetList', null);
    }

    function ShowDialogBox(title, content, btn1text, btn2text, functionText, parameterList) {
        var btn1css;
        var btn2css;

        if (btn1text == '') {
            btn1css = "hidecss";
        } else {
            btn1css = "showcss";
        }

        if (btn2text == '') {
            btn2css = "hidecss";
        } else {
            btn2css = "showcss";
        }
        $("#lblMessage").html(content);

        $("#dialog").dialog({
            resizable: false,
            title: title,
            modal: true,
            width: '400px',
            height: 'auto',
            bgiframe: false,
            hide: {
                effect: 'scale',
                duration: 400
            },

            buttons: [{
                text: btn1text,
                "class": btn1css,
                click: function() {
                    myDialog.dialog("close");

                }
            }]
        });
    }

    function clear_form_elements(class_name) {
        $("." + class_name).find(':input').each(function() {
            switch (this.type) {
                case 'password':
                case 'text':
                case 'textarea':
                case 'file':
                case 'select-one':
                case 'select-multiple':
                case 'date':
                case 'number':
                case 'tel':
                case 'email':
                    $(this).val('');
                    break;
                case 'checkbox':
                case 'radio':
                    this.checked = false;
                    break;
            }
        });
    }

    function removeClass(div_id, time) {
        $("#" + div_id).fadeOut(time, function() {
            $("#" + div_id).remove();
        });
    }

    function sync_shopify_product(id, name, shortId) {

        console.log('syncing');

        if ($('#sync_product-' + shortId).is(":checked")) {
            var category_names = [];
            var category;
            var override_category_array = [];
            if ($(`#override_default_category-${shortId}`).is(":checked")) {

                //loop through override choices, which are the Arcadier category names
                $("#override_default_category_select_" + shortId + "_chosen > div.chosen-drop > ul.chosen-results > li")
                    .each(function(index, element) {
                        if ($(element).attr("class") == "result-selected") {
                            category_names.push($(element).context.innerHTML);
                        }
                    });
                console.log("Category Names: ", category_names);

                $(category_names).each(function(index1, element1) { //for each category name
                    //get Arcadier all category ids
                    var category_ids = <?php echo json_encode($arcadier_categories); ?>;


                    $(category_ids).each(function(index, element) { //for all category IDs
                        if (element.Name ==
                            element1) { //if chosen category names are found, pull their ID
                            override_category_array.push(element.ID);
                            console.log(override_category_array);
                        }
                    });
                });

                category = override_category_array;
            } else {
                category = $(`#cat-${shortId}`).attr('cat-id').split(',');
            }
            console.log("Override Category: ", category);
            console.log($(`#cat-${shortId}`).attr('image-src'));
            data = {
                id,
                name,
                'method': 'sync_one',
                'category': category,
                'images': $(`#cat-${shortId}`).attr('image-src'),
                'price': $(`#cat-${shortId}`).attr('price'),
                'qty': $(`#cat-${shortId}`).attr('qty')
            };
            // console.table(data);
            $('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');

            $.ajax({
                type: "POST",
                url: "shopify_single_sync.php",
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    removeClass('loadingDiv', 500);
                    // console.log(JSON.parse(response));
                    // response = JSON.parse(response);
                    var result = JSON.parse(response);
                    console.log(`result  ${result}`);
                    if (result == 'success') {
                        var message = 'Sync successfully';
                        ShowCustomDialog('Alert', message);
                        // toastr.success(`Synced order Number: ${orderId}`);
                    } else {
                        var message = result;
                        ShowCustomDialog('Alert', message);

                    }

                    //if (response.message == 1) {
                    // $("tr#mag-" + id1 + " td:nth-child(4)").html("<b>Yes</b> at " +
                    //     response.data.sync_date);

                    // } else {
                    // response = JSON.parse(JSON.stringify(response));
                    // var message1 = response.toString();
                    // var message = "The following items did not have their categories mapped: " +
                    //    message1 + ", and were not created.";
                    // ShowCustomDialog('Alert', message);
                    // }
                }
            });

        } else {
            alert('Please check Synchronize');
        }
    }


    function sync_product(sku, name, id) {
        var configRowByMerchantGuid_min_sync_limit = '<?php echo $configRowByMerchantGuid["min_sync_limit"]; ?>';
        var configRowByMerchantGuid_min_sync_limit1 = parseInt(configRowByMerchantGuid_min_sync_limit);
        var mag_product1_count = '<?php echo $mag_product1_count; ?>';
        var mag_product1_count1 = parseInt(mag_product1_count);
        console.log(configRowByMerchantGuid_min_sync_limit);
        console.log(configRowByMerchantGuid_min_sync_limit1);
        console.log(mag_product1_count);
        console.log(mag_product1_count);

        sku1 = sku;
        name1 = name;
        id1 = id;

        if ($('#sync_product-' + id).is(":checked")) {
            // if (mag_product1_count1 >= configRowByMerchantGuid_min_sync_limit1) {
            var override_default_category_select = $('#override_default_category_select-' + id).val();
            var arc_user = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
            if ($('#override_default_category-' + id).is(":checked")) {
                if (override_default_category_select == null || override_default_category_select.length == '0') {
                    alert("Please Select Override Category");
                    return false;
                }
                data = {
                    sku1: sku1,
                    name1: name1,
                    id1: id1,
                    override_default_category_select: override_default_category_select,
                    create_arc_item: 'create_arc_item',
                    arc_user: arc_user
                };
            } else {
                data = {
                    sku1: sku1,
                    name1: name1,
                    id1: id1,
                    create_arc_item: 'create_arc_item',
                    arc_user: arc_user
                };
            }
            $('body').append(
                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');

            console.log(data);

            $.ajax({
                type: "POST",
                url: "ajaxrequest.php",
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    removeClass('loadingDiv', 500);
                    console.log(JSON.parse(response));
                    response = JSON.parse(response);
                    if (response.message == 1) {
                        $("tr#mag-" + id1 + " td:nth-child(4)").html("<b>Yes</b> at " +
                            response.data.sync_date);
                        var message = 'Sync successfully';
                        ShowCustomDialog('Alert', message);
                    } else {
                        response = JSON.parse(JSON.stringify(response));
                        var message1 = response.toString();
                        var message = "The following items did not have their categories mapped: " +
                            message1 + ", and were not created.";
                        ShowCustomDialog('Alert', message);
                    }
                }
            });

            // } else {
            //    var message = 'Cannot sync, Please increase sync limit from configuration';
            //    ShowCustomDialog('Alert', message);
            // }
        } else {
            alert('Please check Synchronize');
        }
    }

    const removeById = (arr, id1) => {

        const requiredIndex = arr.findIndex(el => {
            return el.id1 === String(id1);
        });
        if (requiredIndex === -1) {
            return false;
        };
        return !!arr.splice(requiredIndex, 1);
    };

    const checkIdExists = (arr, id) => {
        const requiredIndex1 = arr.findIndex(el => {
            //return el.id === String(id);
            return el.id === id;
        });
        if (requiredIndex1 === -1) {
            return false;
        } else {
            return true;
        }
    };

    $(document).ready(function() {
        var baseUrl = window.location.hostname;
        var token = getCookie('webapitoken');
        var user = $("#userGuid").val();
        var arc_user1 = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
        if (($('#merchantId') && $('#merchantId').length) && (user == arc_user1)) {
            removeClass('loadingDiv1', 500);
            return false;
        } else {
            window.location.replace('https://' + baseUrl);
        }
    });

    jQuery($ => {
        //var checked_data = [];
        var magento_products1 = '<?php echo json_encode($mag_product1['items']); ?>';
        var magento_products = JSON.parse(magento_products1);
        console.log(magento_products);

        var checked_data = JSON.parse(localStorage.getItem('checked_data')) || [];
        console.log("checked_data:");
        console.log(checked_data);

        $.each(checked_data, function(index, checked_dataByIndex) {
            if (checkIdExists(magento_products, parseInt(checked_dataByIndex.id1))) {
                console.log('yes');
            } else {
                console.log('no');
                var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                var idToDelete = parseInt(checked_dataByIndex.id1);
                removeById(cart, idToDelete);
                localStorage.removeItem("checked_data");
                localStorage.setItem("checked_data", JSON.stringify(cart));
            }
        });


        var arr = JSON.parse(localStorage.getItem('checked')) || [];
        console.log("arr1:" + arr);
        arr.forEach((c, i) => $('.sync_product').eq(i).prop('checked', c));

        //$(".sync_product").click((elem) => { 
        $('.sync_product').change(function() {
            console.log($(this).attr('data-id'));
            var data_id = $(this).attr('data-id').split(",");

            if ($('#' + $(this).attr('id')).is(":checked")) {
                var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                cart.push({
                    sku1: data_id[0],
                    name1: data_id[1],
                    id1: data_id[2],
                    create_arc_item_manual: 'create_arc_item_manual',
                    arc_user: data_id[3]
                });
                localStorage.setItem("checked_data", JSON.stringify(cart));




            } else {
                var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                var idToDelete = data_id[2];
                removeById(cart, idToDelete);
                localStorage.removeItem("checked_data");
                localStorage.setItem("checked_data", JSON.stringify(cart));



            }

            var checked_data1 = JSON.parse(localStorage.getItem('checked_data')) || [];
            console.log("checked_data1:");
            console.log(checked_data1);

            var arc_user2 =
                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
            var data = {
                create_arc_item_all_slow: 'create_arc_item_all_slow',
                arc_user: arc_user2,
                checked_data: checked_data1
            };
            $.ajax({
                type: "POST",
                url: "ajaxrequest.php",
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    console.log(response);

                }
            });

            var arr = $('.sync_product').map((i, el) => el.checked).get();
            console.log(arr);
            localStorage.setItem("checked", JSON.stringify(arr));
        });
        var arr1 = JSON.parse(localStorage.getItem('sync_product1')) || [];
        console.log("arr11:" + arr1);
        arr1.forEach((c, i) => $('.sync_product1').eq(i).prop('checked', c));

        $(".sync_product1").click((elem) => {
            var arr1 = $('.sync_product1').map((i, el) => el.checked).get();
            console.log("arr22:" + arr1);
            localStorage.setItem("sync_product1", JSON.stringify(arr1));
        });

        var create_arc_item_slowRowByMerchantGuid =
            '<?php  if(!empty($create_arc_item_slowRowByMerchantGuid)){ echo json_encode(unserialize($create_arc_item_slowRowByMerchantGuid['checked_data'])); }  ?>';
        var create_arc_item_slowRowByMerchantGuid1 = JSON.parse(
            create_arc_item_slowRowByMerchantGuid);
        console.log('create_arc_item_slowRowByMerchantGuid:');
        console.log(create_arc_item_slowRowByMerchantGuid);
        console.log(create_arc_item_slowRowByMerchantGuid1);

    });

    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }



    function loadnext250items(cursor) {
        var apiUrl = 'load_next_250.php';
        var data = {
            cursor
        }

        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(result) {

                location.reload()

                // result =  JSON.parse(result);
                //     console.log(`result  ${result}`);

                //     if (result == 'success') {
                //          toastr.success(`Synced order Number: ${orderId}`);
                //     } else {
                //         toastr.error(`This order has already been synced`);

                //     }

            },
            error: function(jqXHR, status, err) {
                //	toastr.error('Error!');
            }
        })

    }
    </script>
</body>

</html>