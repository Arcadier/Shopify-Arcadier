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
$sync_items_list_code = '';
$sync_items_list = '';
foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $prods_code = $cf['Code'];
    }
    if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
       $sync_items_list_code = $cf['Code'];
    }
}

if ($result['CustomFields'] != null)  {

    foreach ($result['CustomFields'] as $cf) {
        if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                    $sync_items_list = $cf['Values'][0];
                    $sync_items_list = json_decode($sync_items_list,true);
                   // echo (json_encode($sync_items_list));
                    break;
                   
        }
    
    }

}


$auth = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop = $authDetails['Records'][0]['shop'];
$access_token= $authDetails['Records'][0]['access_token'];

//import Shopify Product count
$product_count = shopify_product_count($access_token, $shop);

//echo $product_count;
$product_import_speed = $product_count['count']/17;
//error_log("Import time: ".$product_import_speed." seconds.", 3, "tanoo_log.php");

//import Shopify Products
//$products = shopify_products_paginated($access_token, $shop, null, false);
//$products = shopify_get_bulk_item($access_token, $shop);

//var_dump(json_decode($products, true));


// if ($products) {
//     error_log(json_encode($products));
// }
// //echo json_encode($products);


//echo $bulk;
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

// $all_items = '';
// if ($result['CustomFields'] != null)  {

//     foreach ($result['CustomFields'] as $cf) {
//         if ($cf['Name'] == 'all_items' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
//                     $all_items = $cf['Values'][0];
//                     break;
//                     //if 1, it is a shopify item else not
//         }
    
//     }

// $all_items = json_decode($all_items, true);
// //echo 'all items ' .  json_encode($all_items);

// }

// if ($all_items){
    
//     $products  = $all_items;

// }

// $last_cursor ='';
// $first_cursor = $first_cursor = $products[0]['cursor'];


// if ($products){

//     foreach($products as $product){
//     if(!next($products)){
//         $last_cursor = $product['cursor'];

//     }
//    // if(next($products)){
        

//     //}
// }

// }

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
                $category_map_unserialized = unserialize($category_map);
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

 <!-- <script src="scripts/jquery.min.js"></script> -->
    <script src="scripts/jquery-2.1.3.min.js"></script>


    
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js" defer></script> -->


   
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.0/css/select.dataTables.min.css" />
 
    <script type="text/javascript" src="https://cdn.datatables.net/searchpanes/2.1.1/js/dataTables.searchPanes.min.js"> </script>
    <script src="https://cdn.datatables.net/searchpanes/2.1.1/js/dataTables.searchPanes.min.js" defer></script>
    <script src="https://cdn.datatables.net/select/1.6.0/js/dataTables.select.min.js" defer></script> -->

    <script src="scripts/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="css/chosen.css" />
    <script src="scripts/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <script src="scripts/bootstrap.bundle.min.js"></script>

    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.3.1/css/select.bootstrap.css" /> -->
 
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.js"></script> -->
    <script type="text/javascript" src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.js"></script>
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/searchpanes/2.1.1/js/dataTables.searchPanes.min.js" defer></script> -->

    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.2/css/jquery.dataTables.min.css" /> -->
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.0/css/select.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.0/css/select.dataTables.min.css" /> 
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" /> 
   

    <!-- <script type="text/javascript" src="scripts/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/searchpanes/2.1.1/js/dataTables.searchPanes.min.js" defer></script>

    <script type="text/javascript" src="https://cdn.datatables.net/select/1.6.0/js/dataTables.select.min.js" defer></script>
 
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">

   
    <link href="https://nightly.datatables.net/select/css/select.dataTables.css?_=766c9ac11eda67c01f759bab53b4774d.css"
        rel="stylesheet" type="text/css" />
    <script src="https://nightly.datatables.net/select/js/dataTables.select.js?_=766c9ac11eda67c01f759bab53b4774d"> -->
 

<style>




.fade.in {
    opacity: 1;
}



/* modal */

#chkSync .modal-title {
    font-weight: 700;
    margin-bottom: 16px;
    color: #4D4D4D;
}

#chkSync .modal-body {
    font-weight: 400;
    padding: 20px;
    padding-bottom: 0;
}

#chkSync .modal-footer {
    padding-top: 0;
}

/* #chkSync .modal-dialog {
    width: 296px;
} */

#chkSync .modal-footer>div {
    width: 100%;
}

#chkSync .modal-footer button {
    background: #333547;
    border: 1px solid #333547;
    border-radius: 5px;
    max-width: 130px;
}
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
        -webkit-animation: load8 3s infinite;
        animation: load8 3s infinite;
    }


    /*loader*/
    .data-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, .5);
        overflow: hidden;
        z-index: 11;
    }

    .data-loader.position-fixed {
        position: fixed;
    }

    .data-loader.active {
        display: flex;
    }

    .round-load {
        height: 150px;
        width: 150px;
        border-radius: 100%;
        border-width: 7px;
        border-color: #51c8ff transparent;
        border-style: solid;
        -webkit-animation: rotation 2s infinite linear;
    }

    @-webkit-keyframes rotation {
        from {
            -webkit-transform: rotate(0deg);
        }

        to {
            -webkit-transform: rotate(359deg);
        }
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    #brnd_preloader {
        border: 5px solid #ddd;
        border-radius: 50%;
        border-top: 5px solid #50C8FE;
        border-bottom: 5px solid #50c8ff;
        width: 35px;
        height: 35px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 1.5s linear infinite;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
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

    #loadingDiv2 {
        position: fixed;
        top: 0;
        z-index: 9999;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #2a3142;
    }

    #loadingDiv3 {
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



    /* table.dataTable th {
            border-bottom: 1px solid #333;
            border-right: 1px solid #333;
        }

        table.dataTable td {
            border-bottom: 1px solid #333;
            border-right: 1px solid #333;
        } */

        .filterIcon {
            height: 10px;
            width: 10px;
            margin-left: 3px;
        }

        .modalFilter {
            display: none;
            height: auto;
            width: 200px;
            background: #FFF;
            border: solid 1px #ccc;
            padding: 8px;
            position: absolute;
            z-index: 1001;
        }

            .modalFilter .modal-contents {
                max-height: 250px;
                width: 200px;
                overflow-y: auto;
                width: 250px;
            }

            .modalFilter .modal-footer {
                background: #FFF;
                height: 35px;
                padding-top: 6px;
            }

            .modalFilter .btn {
                padding: 0 1em;
                height: 28px;
                line-height: 28px;
                text-transform: none;
            }

        #mask {
            display: none;
            background: transparent;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1;
            width: 100%;
            height: 100%;

        }

        .arrow-down {
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 8px solid #201d1e;
        display: inline-block;
}
    label {
        display: inline-block;
        margin-bottom: .5rem;
        margin-left: 4px;
    }

    td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat left bottom;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat left bottom;
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
    // addLoader1();
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

                                <div class="loading-message active">

                                    <h5>We're loading your data...</h5>
                                </div>


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
                    <div class="row" id="app">

                        <input type="hidden" id="user-id" value="<?php echo $_GET['user'] ?>" />
                        <input type="hidden" id="package-id" value="<?php echo $packageId ?>" />

                        <input type="hidden" id="sync-list" value='<?php echo json_encode($sync_items_list) ?>' />


                        <div class="col-12 p-3 bg-white shadow rounded">
                            <div class="table-responsive">
                                <!--<table class="table table-bordered table-striped" id="logTable" >-->
                                <div class="data-loader active">
                                    <div class="round-load"></div>
                                </div>

                                <table class="table hover" id="logTable">
                                    <thead>
                                        <tr>
                                          
                                            <th>Shopify Item</th>
                                            <th>Location</th>
                                            <th>Shopify Created</th>
                                            <th>Shopify Updated</th>
                                            <th>Arcadier Synced</th>
                                            <th>Archived</th>
                                            <th>Draft</th>
                                            <th>Shopify Category</th>
                                            <th> <input type="checkbox" @click="onSelect" class="selectAll"
                                                    name="selectAll" value="all"> Select All -

                                                Syncronise </th>
                                            <th>Default Category</th>
                                            <th>Override Default Category</th>
                                            <th>Override Category</th>
                                            <th>Action</th>
                                            <th>ID</th>
                                            <th>Location ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- <tr>
                                            <td>{{product.id}}</td>
                                            <td>{{product.title}}</td>

                                        </tr> -->


                                    </tbody>
                                </table>

                                <div id="chkSync" class="modal x-boot-modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body un-inputs text-center">
                    <h4 class="modal-title">Unable to select item</h4>
                    <p>
                        This item has already been selected from a different location. To proceed, please unselect it
                        from the previous location before selecting it again."
                    </p>
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="black-btn" data-dismiss="modal">Okay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
    </div>

    <!-- <footer class="footer text-center"> © 2021. </footer> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17-beta.0/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js"></script>
    <script src="scripts/metisMenu.min.js"></script>
    <script src="scripts/jquery.slimscroll.js"></script>
    <script src="scripts/waves.min.js"></script>
    <script src="scripts/app.js"></script>
    <script>
    const packageId = $('#packageId').val()
    const userId = $('#userGuid').val()
    var shopify = new Vue({
        el: "#app",
        data() {
            return {

                sortOrders: {},
                results: "",
                getbulkApiUrl: "all_products_ajax.php",
                getAutoSyncList: "auto_sync_list.php",
                getLocationsUrl: "get_locations.php",
                bulkUrl: "",
                locations: "",
                productsData: "",
                data: "",
                categories: [],
                protocol: window.location.protocol,
                baseURL: window.location.hostname,
                userId: "",
                packageId: "",
                category_map: "",
                //unserialized: "",
                total: "",
                autoSyncList: "",
                currentCount: 0,
                existingMaps: "",
                isExisting: ""
                



            };
        },
        filters: {
            capitalize: function(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }
        },

        methods: {
            sortBy: function(key) {
                var vm = this;
                vm.sortKey = key;
                vm.sortOrders[key] = vm.sortOrders[key] * -1;
            },
            // JavaScript object

            async loadCSV() {
                var vm = this;
                vm.addLoader1();

                vm.bulkUrl = await axios({
                        method: "post",
                        url: vm.getbulkApiUrl,

                    })
                    .then((response) => {

                        vm.bulkUrl = response.data;
                        console.log(response);

                        vm.readTextFile(vm.bulkUrl, function(text) {
                            vm.data = text;
                            if (vm.data) {
                                // console.log(`data ${vm.data}`);
                                vm.data = vm.data.replaceAll(/\n\n+/ig, `\n`, vm.data)

                                var lines = vm.data.split('\n');

                                var total = lines.length - 1;
                                vm.total = total;
                                $.each(lines, function(key, model) {

                                    // $('body').append(
                                    //     `<div id="loadingDiv2"><div style="position: absolute; top: 45%;left: 45%;">Loading ${key} ${total} items </div><div class="loader"></div></div>`
                                    // );
                                    if (vm.isJsonString(model)) {
                                        console.log('json')
                                        // vm.currentCount = key;
                                        // vm.addLoader(key, total);
                                        vm.parseJSON(model, total, key);
                                    } else {

                                        console.log('not json')
                                        var promises = []

                                        var table = $("#logTable").DataTable()
                                        table.rows().every(function(rowIdx, tableLoop,
                                            rowLoop) {



                                            var data = this.data();
                                            id = data[11];

                                            // var cell = table.cell(this);
                                            // table.cell(node).data('40');

                                            var data = [{
                                                'Name': 'product_id',
                                                'Operator': "equal",
                                                "Value": id
                                            }]


                                            vm.isExisting = $.ajax({


                                                method: "POST",
                                                url: `${vm.protocol}//${vm.baseURL}/api/v2/plugins/${vm.packageId}/custom-tables/synced_items/`,
                                                headers: {
                                                    "Content-Type": "application/json"
                                                },

                                                data: JSON.stringify(
                                                    data),
                                                success: function(res) {



                                                    const items =
                                                        res
                                                    const
                                                        itemsDetails =
                                                        items
                                                        .Records[0]
                                                    //if existing user, verify the status
                                                    if (items
                                                        .TotalRecords ==
                                                        1
                                                    ) {
                                                        console.log(
                                                            'mapped'
                                                        );

                                                        table.cell(
                                                                rowIdx,
                                                                4)
                                                            .data(
                                                                new Date(
                                                                    itemsDetails[
                                                                        'synced_date'
                                                                    ] *
                                                                    1000
                                                                )
                                                                .format(
                                                                    "dd/mm/yyyy"
                                                                )
                                                            )
                                                            .draw();

                                                    } else {
                                                        console.log(
                                                            'not mapped'
                                                        );
                                                        table.cell(
                                                                rowIdx,
                                                                4)
                                                            .data(
                                                                'No'
                                                            )
                                                            .draw();
                                                    }





                                                },








                                            })

                                            promises.push(vm.isExisting);

                                        })

                                        // $.when.apply($, promises)
                                        //     .done(function() {
                                        //         console.log(
                                        //             "All done!"
                                        //         )

                                        //     }).fail(function() {
                                        //         // something went wrong here, handle it
                                        //         $(".data-loader")
                                        //             .removeClass(
                                        //                 "active"

                                        //             ) // do other stuff
                                        //     });



                                        $('#loadingDiv3').remove();
                                        $("#logTable").dataTable().fnDestroy();         
                                        var dt =  $("#logTable").DataTable({

                                           // processing: true,
                                           // serverSide: true,
                                          //  ajax: 'scripts/ids-objects.php',
                                            // columns: [
                                            //     {
                                            //         class: 'details-control',
                                            //         orderable: false,
                                                  
                                            //     },
                                               
                                            // ],
                                                                    

                                            "lengthMenu": [
                                                    [10, 25, 50, -1],
                                                    [10, 25, 50, "All"]
                                                ],
                                               dom: 'Plfrtip',
                                                searchPanes: {
                                                    clear: false,
                                                    initCollapsed: true,
                                                    layout: 'columns-3',
                                                    threshold: 1
                                                },
                                            
                                                columnDefs: [{
                                                    targets: [13,14],
                                                    visible: false
                                                
                                                },
                                                {
                                                    searchPanes: {
                                                        show: true
                                                    },
                                                    targets: [6,8],
                                                },
                                                // {
                                                //     searchPanes: {
                                                //         show: false
                                                //     },
                                                //     targets: ['_all'],
                                                // }
                                                
                                            ],
                                                                                
                                            "initComplete": function() {

                                                configFilter(this, [5,6]);
                                            }
                                        });

                                                                                
                                                // Array to track the ids of the details displayed rows
                                        //     var detailRows = [];
                                        
                                        // $('#logTable tbody').on('click', 'tr td.details-control', function () {
                                        //     var tr = $(this).parents('tr');
                                        //         var row = table.row( tr );
                                            
                                        //         if ( row.child.isShown() ) {
                                        //             // This row is already open - close it
                                        //             row.child.hide();
                                        //             tr.removeClass('shown');
                                        //         }
                                        //         else {
                                        //             // Open this row (the format() function would return the data to be shown)
                                        //             if(row.child() && row.child().length)
                                        //             {
                                        //                 row.child.show();
                                        //             }
                                        //             else {
                                        //                 row.child( vm.format(vm.locations, row.data()) ).show();
                                        //             }
                                        //             tr.addClass('shown');
                                        //         }
                                        // });


                                        // $('#logTable tbody').on('change','.location-select', function() {

                                        // console.log('radio event')
                                        // $(this).parents('tr').prev('tr').attr('location-id', this.value);

                                        // });

                                        // On each draw, loop over the `detailRows` array and show any child rows
                                        // var table = $('#logTable').DataTable();
                                        // table.rows().every( function () {
                                        //     this
                                        //         .child(
                                        //             $(
                                        //                 '<tr>'+
                                        //                     '<td>'+''+'.1</td>'+
                                        //                     '<td>'+''+'.2</td>'+
                                        //                     '<td>'+''+'.3</td>'+
                                        //                     '<td>'+''+'.4</td>'+
                                        //                 '</tr>'
                                        //             )
                                        //         )
                                                
                                        // } );
                                                                            

                                        if (vm.total > 5000) {
                                            setTimeout(function() {
                                                $(".data-loader")
                                                    .removeClass(
                                                        "active"

                                                    )
                                                $(".loading-message").remove();

                                            }, 30000);

                                        } else if (vm.total < 100) {
                                            setTimeout(function() {
                                                $(".data-loader")
                                                    .removeClass(
                                                        "active"

                                                    )
                                                $(".loading-message").remove();

                                            }, 5000);
                                        } else {

                                            setTimeout(function() {
                                                $(".data-loader")
                                                    .removeClass(
                                                        "active"

                                                    )
                                                $(".loading-message").remove();

                                            }, 15000);

                                        }



                                        //$('#loadingDiv2').remove();

                                    }

                                })




                                // removeClass('loadingDiv2', 500);
                            }


                        });


                        //$(".data-loader").removeClass("active");

                        // this.$nextTick(() => {
                        //     $(".table").find("tbody tr:last").hide();
                        //     // Scroll Down
                        // });
                    })
                    .catch(function(response) {
                        //handle error
                        console.log(response);
                    });
            },
            parseJSON(line, total, count) {
                //$('#loadingDiv2').remove();


                vm = this;


                let auto_sync_list = vm.autoSyncList.length ? JSON.parse(vm.autoSyncList) : '';
                let itemDetails = JSON.parse(line);
                let createdAt = new Date(itemDetails.createdAt).toLocaleString()
                console.log(createdAt);
                let updatedAt = new Date(itemDetails.updatedAt).toLocaleString()
                console.log(updatedAt);


                let category_options;
                $.each(vm.categories, function(index, option) {
                    category_options +=
                        `<option name='${option.Name}' value="${option.ID}">${option.Name}</option>`
                });

                let shopify_product_category = itemDetails.productType;
                let category_maps = '';
                if (vm.existingMaps) {
                    let rendered_category = vm.existingMaps.filter(name =>
                        name
                        .shopify_category ==
                        shopify_product_category);

                    console.log({
                        rendered_category
                    });

                    if (rendered_category.length != 0) {
                        //if (selected_arc_categories.length != 0) {
                        let selected_arc_categories = rendered_category[0]
                            .mapped_arc_categories;
                        // }


                        if (selected_arc_categories.length != 0) {


                            category_names = '';
                            category_div_ids = selected_arc_categories


                            $.each(vm.categories, function(index, cat) {

                                if (selected_arc_categories.includes(cat['ID'])) {
                                    category_names = `${category_names}${cat['Name']} `;

                                }
                            })

                            category_maps =
                                `<div cat-id=${category_div_ids}  id=cat-${itemDetails.id.replace("gid://shopify/Product/", "")}>${category_names}</div>`;

                        } else {
                            category_maps = 'Not Mapped';
                        }

                    }


                }

                //start looping for the locations
                $.each(vm.locations, function(index, location) {

                let checkStatus =  checkAutoSyncList(auto_sync_list, itemDetails.id, location.id) ? checked = 'checked' : '';   //auto_sync_list['itemguid'].includes(itemDetails.id) ? checked = 'checked' : '';
                let selectedStatus = checkAutoSyncList(auto_sync_list, itemDetails.id, location.id) ? checked = 'selected' : '';

                //checking if already sync
                    
                let isDraft = itemDetails.status == "DRAFT" ? 'Yes' : 'No'
                let isArchived = itemDetails.status == "ARCHIVED" ? 'Yes' : 'No'
                let isExist = '-'; //vm.checkIfExist(itemDetails.id);

                const tr = $(
                    `<tr id=${itemDetails.id}_${index} location-id=${location.id} index=${index}>
                    
                    <td>${itemDetails.title} </td>

                    <td>${location.name} ${location.city}</td>

                    <td>${createdAt}</td>

                    <td>${updatedAt}</td>

                    <td>${isExist}</td>
                    
                    <td>${isArchived}</td>

                    <td>${isDraft}</td>
                
                    <td>${itemDetails.productType}</td>

                    <td> <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="sync_product" class="sync_product" data-id="${itemDetails.id.replace("gid://shopify/Product/", "")}" ${checkStatus}
                        id="sync_product-${itemDetails.id.replace("gid://shopify/Product/", "")}_${index}"
                        data-name="${itemDetails.title}"
                        data-id="${itemDetails.id}">
                        <label class="" for=""><span name="customSpan"></span></label>
                    </div></td>

                    <td>

                    ${category_maps}
                    
                    </td>

                    <td> <div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="sync_product1"
                            name="override_default_category"
                            id="override_default_category-${itemDetails.id.replace("gid://shopify/Product/", "")}" />
                        <label class="" for=""><span name="customSpan"></span></label>
                    </div>
                    </div></td>
                    <td>

                     <div>                      
                        <select
                            id="override_default_category_select-${itemDetails.id.replace("gid://shopify/Product/", "")}"
                            name="override_default_category_select" class="chosen-select"
                            data-placeholder="Select Arcadier Category" multiple>
                            ${category_options}
                        </select>
                    </div>
                    </td>
                   
                    <td> 
                    <a id="save_map"
                        onclick="sync_shopify_product('${itemDetails.id}','${itemDetails.id.replace("gid://shopify/Product/", "")}','${index}');"
                        style="margin-left: 25px;border: #0e77d4;box-sizing: border-box;background-color: #333547;border-radius: 6px;color: white;padding: 5px 10px;font-size: 14px; cursor: pointer;">Sync</a></td>
                        
                    <td> 
                    
                        ${itemDetails.id}

                    </td>

                    <td> 
                    
                    ${location.id}

                     </td>
                        
                    </tr>`
                );
                var table = $("#logTable").DataTable()
                var trDOM = table.row.add(tr[0]).draw().node();
                $(trDOM).addClass(
                    selectedStatus
                );

            })
                // $(`"#${itemDetails.id}"`).addClass(selectedStatus);
                //document.getElementById(`${itemDetails.id}`).classList.add('.selected');


                //table.column([10]).visible(false);


                console.log(`count ${count} total ${total}`);
                vm.currentCount = count;
                var newtotal = total - 1;
                // if (count === newtotal) {

                // }
            },
            async readTextFile(file, callback) {
                var rawFile = new XMLHttpRequest();
                rawFile.overrideMimeType("application/json");
                rawFile.open("GET", file, true);
                rawFile.onreadystatechange = function() {
                    if (rawFile.readyState === 4 && rawFile.status == "200") {
                        callback(rawFile.responseText);
                    }
                }
                rawFile.send(null);
            },

            async checkIfExist(prodId) {




                // isExisting = "";
                var data = [{
                    'Name': 'product_id',
                    'Operator': "equal",
                    "Value": prodId
                }]

                $.ajax({
                        method: "POST",
                        url: `${vm.protocol}//${vm.baseURL}/api/v2/plugins/${vm.packageId}/custom-tables/synced_items/`,
                        headers: {
                            "Content-Type": "application/json"
                        },

                        data: JSON.stringify(data),
                        async: true,
                    })

                    .then(res => {

                        const items = res.data
                        const itemsDetails = items.Records[0]
                        //if existing user, verify the status
                        if (items.TotalRecords >= 1) {
                            console.log('mapped');

                            return itemsDetails['synced_date'];

                        } else {
                            console.log('not mapped');
                            return 'No'
                        }

                        var table = $("#logTable").DataTable()
                        table.rows().every(function(rowIdx, tableLoop, rowLoop) {


                            var data = this.data();
                            data[4] += ' >> updated in loop' //append a string to every col #2
                            this.data(data)
                        })



                        table.rows().every(function() {

                            var Row = this.data(); //store every row data in a variable

                            console.log(Row[4]); //show Row + Cell index
                            $(".data-loader").removeClass("active");

                        });

                    })
                // success: function(response) {
                //     console.log({
                //         response
                //     })



            },

            async getExistingMaps(userId) {

                var data = [{
                    'Name': 'merchant_guid',
                    'Operator': "equal",
                    "Value": userId
                }]

                const isExisting = await axios({
                        method: "POST",
                        url: `${vm.protocol}//${vm.baseURL}/api/v2/plugins/${vm.packageId}/custom-tables/map/`,
                        headers: {
                            "Content-Type": "application/json"
                        },

                        data: JSON.stringify(data)
                    })

                    .then(res => {

                        const mapping = res.data
                        const mappingDetails = mapping.Records[0]

                        const categoryMapping = mappingDetails.map;
                        if (categoryMapping) {
                            vm.existingMaps = JSON.parse(categoryMapping);
                            console.log(vm.existingMaps);
                        }




                    })



            },
            
            format(d, data) {
                  vm = this;

                  console.log(data);

                  let location_div = "";
                  let isChecked;
                  d.length == 1 ? 'checked' : ''; // auto check if there is only 1 location
                  $.each(d, function(index, location) {
                    location  != undefined ?
                    location_div += `<div class="fancy-radio">
                        <input type="radio" value="${location.id}_${data[12]}" name="location_${data[12]}" id="${location.id}_${data[12]}" class="location-select" checked=${isChecked}>
                        <label for="${location.id}_${data[12]}"><span>${location.address1} ${location.city} </span></label>
                    </div>` : location_div += "";    

                })

                //console.log(`(vm.locations)}`);

                return location_div;
                
                
            },

            isJsonString(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            },
            addLoader(count, total) {
                $('body').append(
                    ` < div id = "loadingDiv2" > < div style = "position: absolute; top: 45%;left: 45%;" > Loading $ {
                        count
                    } of $ {
                        total
                    }
                    items < /div><div class="loader"></div > < /div>`
                );
            },

            addLoader1() {
                vm = this;
                $('body').append(
                    `<div id="loadingDiv3"><div style="position: absolute; top: 45%;left: 45%;">Checking on all of your Shopify items... <br> This could take some time (Up to 5 minutes.)</div><div class="loader"></div></div>`
                );
            },
            async onSelect() {

                vm = this;
                var DT1 = $("#logTable").DataTable();


                $(".selectAll").on("click", function(e) {
                    if ( $('[type=search]').val() == "" ) {

                        alert("Please filter items by location first");
                        $(".selectAll").prop("checked", false);

                    
                    }
                       
                    else {

                        if ($(this).is(":checked")) {

                        $('.sync_product').prop("checked", true);

                            DT1.rows({
                                search: 'applied'
                            }).select();


                            var rows = DT1.rows({
                                'search': 'applied'
                            }).nodes();
                            // Check/uncheck checkboxes for all rows in the table
                            $('.sync_product', rows).prop('checked', true);

                            var ids = $.map(DT1.rows('.selected').data(), function(item) {
                                return { 'itemguid' : item[13], 'locationId' : item[14] }
                            });
                            console.log(ids)

                            vm.saveAutoSyncProducts(ids);


                        }

                        else {
                        // DT1.rows().deselect();
                        $('.sync_product').prop("checked", false);
                        // $('.sync_product').parents('tr').removeClass('selected');

                        DT1.rows({
                            search: 'applied'
                        }).deselect();


                        var rows = DT1.rows({
                            'search': 'applied'
                        }).nodes();
                        // Check/uncheck checkboxes for all rows in the table
                        $('.sync_product', rows).prop('checked', false);

                        var ids = $.map(DT1.rows('.selected').data(), function(item) {
                            return { 'itemguid' : item[13], 'locationId' : item[14] }
                        });
                        
                        //});
                        console.log(ids)

                        vm.saveAutoSyncProducts(ids);
                    }
                    }
                });
            },


            async onImport() {

                vm = this;
                var DT1 = $("#logTable").DataTable();

                var ids = $.map(DT1.rows('.selected').data(), function(item) {
                    return item[0]
                });

                $.each(ids, function(index, id) {
                    console.log(id);
                })


            },


            async saveAutoSyncProducts(productIds) {
              
                vm = this;
                var data = {
                    'product-list': JSON.stringify(productIds),
                    'user-id': vm.userId
                }
                vm.autoSyncList = await axios({
                        method: "post",
                        url: vm.getAutoSyncList,
                        data: JSON.stringify(data)
                    })
                    .then((response) => {
                        console.log(response.data)
                        return response.data;

                    })
                    .catch(function(response) {
                        //handle error
                        console.log(response);
                    });



            },

            async getCategoryMapping() {

                vm = this;

                var data = [{
                    'Name': 'merchant_guid',
                    'Operator': "equal",
                    "Value": vm.userId
                }]

                $.ajax({
                    method: "POST",
                    url: `${vm.protocol}//${vm.baseURL}/api/v2/plugins/${vm.packageId}/custom-tables/map/`,
                    headers: {
                        "Content-Type": "application/json"
                    },

                    data: JSON.stringify(data),

                    success: function(response) {
                        console.log({
                            response
                        })

                        const maps = response
                        const mapDetails = maps.Records[0]
                        //if existing user, verify the status
                        if (maps.TotalRecords == 1) {
                            console.log('mapped');
                            return mapDetails['map'];

                        } else {
                            console.log('not mapped');
                            return 'Not Mapped';
                        }



                    }
                })



            }, 
            async getLocations()
            {
                var vm = this;
              
                let locations = await axios({
                        method: "post",
                        url: vm.getLocationsUrl,

                    })
                    .then((response) => {
                        var data =  JSON.parse(response.data);
                         vm.locations = data.result.locations;

                        console.log(vm.locations);
                        //vm.locations = Object.entries(response.data[0].locations);
                       // console.log(`${vm.locations.locations}`)
                       // vm.locations =  Object.entries(vm.locations.locations);
                       // console.log(vm.locations)
                       // vm.format(vm.locations); 
                      
                    })
                    .catch(function(response) {
                        //handle error
                        console.log(response);

                    });
            }


        },

        mounted: function() {
            //  

            this.userId = document.getElementById("user-id").value,
                this.packageId = document.getElementById("package-id").value,
                this.loadCSV();
            this.getCategoryMapping();
            this.getExistingMaps(vm.userId),
            this.getLocations(),

                //categories
                axios({
                    method: 'GET',
                    url: `${vm.protocol}//${vm.baseURL}/api/v2/categories?pageSize=1000`,

                }).then(response => {
                    this.categories = response.data.Records
                    console.log(this.categories);

                })
                .catch(function(error) {

                    console.log(error);
                })

            //this.category_map = document.getElementById("category-mapping").value
            //this.unserialized = document.getElementById("unserialized").value,
            this.autoSyncList = document.getElementById("sync-list").value




            //get the mapping




        },

        watch: {
            messages: function(val, oldVal) {
                $.when(vm.isExisting).done(function(a1, a2, a3, a4) {

                    console.log('ajax stop')

                    $(".data-loader")
                        .removeClass(
                            "active")
                    // the code here will be executed when all four ajax requests resolve.
                    // a1, a2, a3 and a4 are lists of length 3 containing the response text,
                    // status, and jqXHR object for each of the four ajax calls respectively.
                });
            },
        },
    });


    $(document).ready(function() {


    

        // $.when(shopify.isExisting).done(function(a1, a2, a3, a4) {

        //     console.log('ajax stop')

        //     $(".data-loader")
        //         .removeClass(
        //             "active")
        //     // the code here will be executed when all four ajax requests resolve.
        //     // a1, a2, a3 and a4 are lists of length 3 containing the response text,
        //     // status, and jqXHR object for each of the four ajax calls respectively.
        // });

        // $(document).ajaxStop(function() {
        //     // place code to be executed on completion of last outstanding ajax call here


        // });


        // setTimeout(function() {
        //     $(".loading-message").removeClass("active");

        // }, 2500);

    
        var selectedProducts = [];
        var confirmModal =`<div class='popup-area cart-checkout-confirm' id ='plugin-popup'><div class='wrapper'> <div class='title-area text-capitalize'><h1>ARE YOU SURE YOU WANT TO PROCEED?</h1></div><div class='content-area'><span id ='main'>You are about to import <span id="total-items">  </span> products. An email notification will be sent once the import has started and completed.</span> </div><div class='btn-area'> <a href='javascript:void(0)' class='btn-black-cmn' id='btn-cancel'>Cancel</a> <a  class='add-cart-btn' id='btn-sync-all'>Sync</a></div></div></div>`;
        var validateModal =  `<div id="chkSync" class="modal x-boot-modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body un-inputs text-center">
                    <h4 class="modal-title">Unable to select item</h4>
                    <p>
                        This item has already been selected from a different location. To proceed, please unselect it
                        from the previous location before selecting it again."
                    </p>
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="black-btn" data-dismiss="modal">Okay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
        $('.footer').after(confirmModal);
        $('.footer').after(validateModal);

        // loadAllItemsUrl();
        $('#plugin-popup #btn-cancel').click(function() {
            $("#plugin-popup").fadeOut();
            $("#cover").fadeOut();
        });

        // $('input[type=checkbox]').click(function() {
        //     if (!$(this).is(':checked')) {
        //         $('#' + this.id).prop('checked', false);
        //     }
        // });


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


            responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
           
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            dom: 'Plfrtip',
            // searchPanes: {
            //     // clear: false,
            //     // initCollapsed: true,
            //     // layout: 'columns-3',
            //     // threshold: 1
            //     collapse: false
            // },
        
            columnDefs: [{
                targets: [13,14],
                visible: false
            
            },
            {
                searchPanes: {
                    //show: true
                    collapse: false
                },
                targets: [6,8],
            },
            // {
            //     searchPanes: {
            //         show: false
            //     },
            //     targets: ['_all'],
            // }
            
        ],
            "initComplete": function() {

             // configFilter(this, [4,5]);
      // Select the column whose header we need replaced using its index(0 based)
    //   this.api().column(4).every(function() {
    //     var column = this;
    //     // Put the HTML of the <select /> filter along with any default options 
    //     var select = $('<select class="form-control input-sm"><option value="">All</option><option value="Yes">Yes</option><option value="No">No</option></select>')
    //       // remove all content from this column's header and 
    //       // append the above <select /> element HTML code into it 
    //       .appendTo($(column.header()))
    //       // execute callback when an option is selected in our <select /> filter
    //       .on('change', function() {
    //         // escape special characters for DataTable to perform search
    //         var val = $.fn.dataTable.util.escapeRegex(
    //           $(this).val()
    //         );
    //         // Perform the search with the <select /> filter value and re-render the DataTable
    //         column
    //           .search(val ? '^' + val + '$' : '', true, false)
    //           .draw();
    //       });
    //     // fill the <select /> filter with unique values from the column's data
    //     column.data().unique().sort().each(function(d, j) {
    //      // select.append("<option value='" + d + "'>" + d + "</option>")
    //     });
    //   });

    //   this.api().column(5).every(function() {
    //     var column = this;
    //     // Put the HTML of the <select /> filter along with any default options 
    //     var select =  $('<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label>Select All</label><input type="checkbox" class="dt-checkboxes"><label>Yes</label></div>')
    //    // var select = $('<select class="form-control input-sm">option value=""></option><option value="">All</option><option value="Yes">Yes</option><option value="No">No</option></select>')
    //       // remove all content from this column's header and 
    //       // append the above <select /> element HTML code into it 
    //       .appendTo($(column.header()))
    //       // execute callback when an option is selected in our <select /> filter
    //       .on('change', function() {
    //         // escape special characters for DataTable to perform search
    //         var val = $.fn.dataTable.util.escapeRegex(
    //           $(this).val()
    //         );
    //         // Perform the search with the <select /> filter value and re-render the DataTable
    //         column
    //           .search(val ? '^' + val + '$' : '', true, false)
    //           .draw();
    //       });
    //     // fill the <select /> filter with unique values from the column's data
    //     column.data().unique().sort().each(function(d, j) {
    //     //  select.append("<option value='" + d + "'>" + d + "</option>")
    //     });
    //   });
    },


        });
        myDialog = $("#dialog").dialog({
            // dialog settings:
            //autoOpen : false,
            // ... 
        });
        myDialog.dialog("close");

        $('#logTable tbody').on('change', '.sync_product', function() {


            let dataId =  $(this).attr('data-id');
            console.log({dataId})

            let check_count = 0;
            $('.sync_product[data-id="'+ dataId +'"]').each(function(){
                console.log($(this).attr('id'));

                if ($(this).parents('tr').hasClass("selected")) {

                    console.log('hit')
                    check_count++;
                }
            //do your stuff here
            });



            if (check_count >= 1) {
                console.log('there is an exisiting check')
                if ($(this).is(":checked")) {
                    $('#chkSync').modal('show')
                    $('#chkSync').addClass('in')
                    $(this).parents('tr').removeClass('selected');
                    $(this).prop("checked", false);
                }else {
                    $(this).parents('tr').removeClass('selected');
                }
                

            }

            else {

                if ($(this).is(":checked")) {
                  $(this).parents('tr').addClass('selected');
                } else {
                //DT1.$('tr.selected').removeClass('selected');

                $(this).parents('tr').removeClass('selected');

                }

                //$('#chkSync').modal('hide')
                //$('#chkSync').removeClass('in')

               

            }

           
                   



                




           

            // var data = table.row(this).data();
            //  $(this).parents('tr').addClass('selected');
            //  alert('You clicked on ' + data[0] + "'s row");
        });

        //select / unselect each item to be sync
        document.querySelector("tbody").addEventListener("change", function(e) {
            if (e.target.type === 'checkbox') {
                var productId = e.target.parentNode.parentNode.parentNode.id;

                //e.target.parentNode.parentNode.parentNode.classList.add('.main-btn');

                // selectedProducts.push(productId);

                //do something with the product ID or get everything that's been checked,

                var DT1 = $("#logTable").DataTable();

                var ids = $.map(DT1.rows('.selected').data(), function(item) {
                    return { 'itemguid' : item[13], 'locationId' : item[14] }
                });

                console.log(ids);

                shopify.saveAutoSyncProducts(ids);

            }
        });


        //data table events
        // var table = $("#logTable").DataTable()
        // table.on('deselect', function(e, dt, type, indexes) {

        //     console.log(`deselected ${indexes}`)

        // });
        // table.on('select', function(e, dt, type, indexes) {


        //     console.log(`selected ${indexes}`)
        // });
    });


    function configFilter($this, colArray) {
            setTimeout(function () {
                var tableName = $this[0].id;
                var columns = $this.api().columns();
                $.each(colArray, function (i, arg) {
                    $('#' + tableName + ' th:eq(' + arg + ')').append('<i class="arrow-down filterIcon" onclick="showFilter(event,\'' + tableName + '_' + arg + '\')"></i>');
                });

                var template = '<div class="modalFilter">' +
                                 '<div class="modal-contents">' +
                                 '{0}</div>' +
                                 '<div class="modal-footer">' +
                                     '<a href="#!" onclick="clearFilter(this, {1}, \'{2}\');"  class=" btn left waves-effect waves-light">Clear</a>' +
                                     '<a href="#!" onclick="performFilter(this, {1}, \'{2}\');"  class=" btn right waves-effect waves-light">Ok</a>' +
                                 '</div>' +
                             '</div>';
                $.each(colArray, function (index, value) {
                    columns.every(function (i) {
                        if (value === i) {
                            // <input type="text" class="filterSearchText" onkeyup="filterValues(this)" /> <br/>
                            var column = this, content = '';
                            var columnName = $(this.header()).text().replace(/\s+/g, "_");
                            var distinctArray = [];
                            content += `<div><input type="checkbox"  id="select_all_${columnName}"><label for="select_all_${columnName}"> Select All</label></div>`;
                            column.data().each(function (d, j) {
                                if (distinctArray.indexOf(d) == -1) {
                                    var id = tableName + "_" + columnName + "_" + j; // onchange="formatValues(this,' + value + ');
                                    content += `<div><input type="checkbox" value=${d} id= ${ id }><label for=${id}> ${d}</label></div>`;
                                    distinctArray.push(d);
                                }
                               
                            });
                          
                            var newTemplate = $(template.replace('{0}', content).replace('{1}', value).replace('{1}', value).replace('{2}', tableName).replace('{2}', tableName));
                            $('body').append(newTemplate);
                            modalFilterArray[tableName + "_" + value] = newTemplate;
                           
                            content = '';
                        }
                    });
                });
            }, 50);
        }
        var modalFilterArray = {};
        //User to show the filter modal
        function showFilter(e, index) {
            $('.modalFilter').hide();
            $(modalFilterArray[index]).css({ left: 0, top: 0 });
            var th = $(e.target).parent();
            var pos = th.offset();
            console.log(th);
           // $(modalFilterArray[index]).width(th.width() * 0.75);
            $(modalFilterArray[index]).css({ 'left': pos.left, 'top': pos.top });
            $(modalFilterArray[index]).show();
            $('#mask').show();
            e.stopPropagation();
        }

        //This function is to use the searchbox to filter the checkbox
        function filterValues(node) {
            var searchString = $(node).val().toUpperCase().trim();
            var rootNode = $(node).parent();
            if (searchString == '') {
                rootNode.find('div').show();
            } else {
                rootNode.find("div").hide();
                rootNode.find("div:contains('" + searchString + "')").show();
            }
        }

        //Execute the filter on the table for a given column
        function performFilter(node, i, tableId) {
            var rootNode = $(node).parent().parent();
            var searchString = '', counter = 0;

            rootNode.find('input:checkbox').each(function (index, checkbox) {
                if (checkbox.checked) {
                    searchString += (counter == 0) ? checkbox.value : '|' + checkbox.value;
                    counter++;
                }
            });
            $('#' + tableId).DataTable().column(i).search(
                
                searchString,
                true, false
            ).draw();
            rootNode.hide();
            $('#mask').hide();
        }

        //Removes the filter from the table for a given column
        function clearFilter(node, i, tableId) {
            var rootNode = $(node).parent().parent();
            rootNode.find(".filterSearchText").val('');
            rootNode.find('input:checkbox').each(function (index, checkbox) {
                checkbox.checked = false;
                $(checkbox).parent().show();
            });
            $('#' + tableId).DataTable().column(i).search(
                '',
                true, false
            ).draw();
            rootNode.hide();
            $('#mask').hide();
        }








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

    function sync_shopify_product(id, shortId, indx) {

        console.log('syncing');

        var parentTr = $('#sync_product-' + shortId +'_' + indx).parents('tr');
        var locationId =  parentTr.attr('location-id')
        var indexId =  parentTr.attr('index')
        console.log({locationId });

        if ($('#sync_product-' + shortId +'_' + indx).is(":checked")) {
            var category_names = [];
            var category;
            var override_category_array = [];
            if ($(`#override_default_category-${shortId}`).is(":checked")) {

                //loop through override choices, which are the Arcadier category names
                // $("#override_default_category_select_" + shortId +
                //         "_chosen > div.chosen-drop > ul.chosen-results > li")
                //     .each(function(index, element) {
                //         if ($(element).attr("class") == "result-selected") {
                //             category_names.push($(element).context.innerHTML);
                //         }
                //     });

                // $(`#override_default_category_select_${shortId} option:selected`).map(function(i, e) {
                //     override_category_array.push(e.value);
                // })
                // console.log("Category Names: ", override_category_array);


                // // var override_category_array = [];
                // var options = $(`#override_default_category_select_${shortId}`).selectedOptions;

                // var values = Array.from(options).map(({
                //     value
                // }) => value);
                // console.log(values);

                selectedOptions = Array.from(document.getElementById(`override_default_category_select-${shortId}`)
                    .selectedOptions).map(({
                    value
                }) => value);
                //console.log("Category Names: ", category_names);

                // $(category_names).each(function(index1, element1) { //for each category name
                //     //get Arcadier all category ids
                //     var category_ids = <?php echo json_encode($arcadier_categories); ?>;


                //     $(category_ids).each(function(index, element) { //for all category IDs
                //         if (element.Name ==
                //             element1
                //         ) { //if chosen category names are found, pull their ID
                //             override_category_array.push(element.ID);
                //             console.log(override_category_array);
                //         }
                //     });
                // });

                category = selectedOptions; //override_category_array;
            } else {
                category = $(`#cat-${shortId}`).attr('cat-id').split(',');
            }
            console.log("Override Category: ", category);
            console.log($(`#cat-${shortId}`).attr('image-src'));
            data = {
                id,
                //name,
                'method': 'sync_one',
                'category': category,
                'images': $(`#cat-${shortId}`).attr('image-src'),
                'price': $(`#cat-${shortId}`).attr('price'),
                'qty': $(`#cat-${shortId}`).attr('qty'),
                'location-id' : locationId
            };
            // console.table(data);
            $('body').append(
                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');

            $.ajax({
                type: "POST",
                url: "shopify_single_sync.php",
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                   // removeClass('loadingDiv', 500);
                    $('#loadingDiv').remove();
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
        var configRowByMerchantGuid_min_sync_limit =
            '<?php echo $configRowByMerchantGuid["min_sync_limit"]; ?>';
        var configRowByMerchantGuid_min_sync_limit1 = parseInt(
            configRowByMerchantGuid_min_sync_limit);
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
            var override_default_category_select = $('#override_default_category_select-' + id)
                .val();
            var arc_user =
                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
            if ($('#override_default_category-' + id).is(":checked")) {
                if (override_default_category_select == null || override_default_category_select
                    .length ==
                    '0') {
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
                        var message =
                            "The following items did not have their categories mapped: " +
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

    
        waitForElement("#select_all_Draft", function() {
            $("#select_all_Draft").on("click", function(e) {
                        if ($(this).is(":checked")) {
                            $(this).parents('.modal-contents').find('input[type=checkbox]').prop("checked", true);
                        }else {
                            $(this).parents('.modal-contents').find('input[type=checkbox]').prop("checked", false);
                        }

            })
         })




        waitForElement("#select_all_Archived", function() {
            $("#select_all_Archived").on("click", function(e) {
                        if ($(this).is(":checked")) {
                            $(this).parents('.modal-contents').find('input[type=checkbox]').prop("checked", true);
                        }else {
                            $(this).parents('.modal-contents').find('input[type=checkbox]').prop("checked", false);
                        }

            })
        })


        var baseUrl = window.location.hostname;
        var token = getCookie('webapitoken');
        var user = $("#userGuid").val();
        var arc_user1 =
            '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
        if (($('#merchantId') && $('#merchantId').length) && (user == arc_user1)) {
            removeClass('loadingDiv1', 500);
            return false;
        } else {
            window.location.replace('https://' + baseUrl);
        }





        //radio button location events

      
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
        // $('.sync_product').change(function() {
        //     console.log($(this).attr('data-id'));
        //     var data_id = $(this).attr('data-id').split(",");

        //     if ($('#' + $(this).attr('id')).is(":checked")) {
        //         var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
        //         cart.push({
        //             sku1: data_id[0],
        //             name1: data_id[1],
        //             id1: data_id[2],
        //             create_arc_item_manual: 'create_arc_item_manual',
        //             arc_user: data_id[3]
        //         });
        //         localStorage.setItem("checked_data", JSON.stringify(cart));




        //     } else {
        //         var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
        //         var idToDelete = data_id[2];
        //         removeById(cart, idToDelete);
        //         localStorage.removeItem("checked_data");
        //         localStorage.setItem("checked_data", JSON.stringify(cart));



        //     }

        //     var checked_data1 = JSON.parse(localStorage.getItem('checked_data')) || [];
        //     console.log("checked_data1:");
        //     console.log(checked_data1);

        //     var arc_user2 =
        //         '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
        //     var data = {
        //         create_arc_item_all_slow: 'create_arc_item_all_slow',
        //         arc_user: arc_user2,
        //         checked_data: checked_data1
        //     };
        //     $.ajax({
        //         type: "POST",
        //         url: "ajaxrequest.php",
        //         contentType: 'application/json',
        //         data: JSON.stringify(data),
        //         success: function(response) {
        //             console.log(response);

        //         }
        //     });

        //     var arr = $('.sync_product').map((i, el) => el.checked).get();
        //     console.log(arr);
        //     localStorage.setItem("checked", JSON.stringify(arr));
        // });



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

    function waitForElement(elementPath, callBack) {
        window.setTimeout(function() {
            if ($(elementPath).length) {
                callBack(elementPath, $(elementPath));
            } else {
                waitForElement(elementPath, callBack);
            }
        }, 700);
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

    function loadprevious250items(cursor) {
        var apiUrl = 'load_prev_250.php';
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

    function loadAllItemsUrl() {


        var apiUrl = 'all_products_ajax.php';

        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            // data: JSON.stringify(data),
            success: function(results) {

                console.log(`result ${results}`);
                // var data;
                readTextFile(results, function(text) {
                    data = text;

                    if (data) {
                        var lines = data.split('\n');
                        // console.log(lines);

                        $.each(lines, function(key, model) {

                            console.log(JSON.parse(model));

                        })
                    }

                })

            },
            error: function(jqXHR, status, err) {
                //	toastr.error('Error!');
            }
        })


    }



    function readTextFile(file, callback) {
        var rawFile = new XMLHttpRequest();
        rawFile.overrideMimeType("application/json");
        rawFile.open("GET", file, true);
        rawFile.onreadystatechange = function() {
            if (rawFile.readyState === 4 && rawFile.status == "200") {
                callback(rawFile.responseText);
            }
        }
        rawFile.send(null);
    }


    function csvJSON(csv) {
        // var vm = this;

        var lines = csv.split("\n");
        // lines.unshift(counter);
        // csvcontent =  lines.shift();
        // vm.count = lines.length - 1;
        // vm.count = vm.count - 1;
        var result = [];
        //for failed results,
        // var failed_results = [];


        //   var headers = lines[0].split(",");
        //   vm.parse_header = lines[0].split(",");

        //   vm.csvcontent = lines;
        //   lines[0].split(",").forEach(function (key)
        //   {
        //     // counter++;
        //     vm.sortOrders[key] = 1;
        //   });

        lines.map(function(line, indexLine) {
            // if (indexLine < 1) return; // Jump header line

            var obj = {};
            var currentline; //= line.split(",");

            headers.map(function(header, indexHeader) {
                obj[header] = currentline[indexHeader];
            });

            // console.log(obj['Item Name']);

            // //validate if item name is empty
            // if (obj['Item Name'] == '' || obj['Category ID'] == '' || obj['Merchant ID'] == '' || obj[
            //     'Price'] == '') {
            //     failed_results.push(obj);
            //     vm.failedcount++;
            // }
            result.push(obj);

        });

        // result.pop() // remove the last item because undefined values

        return result; // JavaScript object
    }


    
    function checkAutoSyncList(arr,productId, locationId) {

        if (arr.length != 0) {
            return arr.some(function(el) {
            return el.itemguid === productId && el.locationId == parseInt(locationId);
             }); 
        }else {
            return null;
        }
  
    }
    </script>
</body>

</html>