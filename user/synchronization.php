<?php
include 'magento_functions.php';
include 'api.php';
require '../license/license.php';
$licence = new License();
// if (!$licence->isValid()) {
//     echo "<script>alert('Please subscribe first from admin to use this plugin');</script>";
//     exit();
// }
$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$auth = false;
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
    $data1 = [
        [
          'Name'=> 'merchant_guid',
          'Operator'=> 'equal',
          'Value'=> $_GET['user']
        ]
      ];
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
    //$configListById=$arc->searchTable($pack_id, 'config', $data1);
    if(!empty($authListById['Records'])){
        if($authListById['Records'][0]['auth_status'] == '1'){

            $getAllSchedulers=$arc->getAllSchedulers();
            $getSchedulerById = array();
            $getSchedulerFastById = array();
            foreach($getAllSchedulers as $getAllSchedulerKey=>$getAllSchedulerValue){
                if (strpos($getAllSchedulerValue['Url'], 'create_arc_item_slow_cron.php') !== false && strpos($getAllSchedulerValue['Url'], $authListById['Records'][0]['domain']) !== false && strpos($getAllSchedulerValue['Url'], 'schedule_type') !== false && strpos($getAllSchedulerValue['Url'], 's_schedule_slow') !== false) {
                    $getSchedulerById = $getAllSchedulers[$getAllSchedulerKey];
                }
                if (strpos($getAllSchedulerValue['Url'], 'create_arc_item_fast_cron.php') !== false && strpos($getAllSchedulerValue['Url'], $authListById['Records'][0]['domain']) !== false && strpos($getAllSchedulerValue['Url'], 'schedule_type') !== false && strpos($getAllSchedulerValue['Url'], 's_schedule_fast') !== false) {
                    $getSchedulerFastById = $getAllSchedulers[$getAllSchedulerKey];
                }
            }
            $url = '';
            $s_orders = '';
            $s_quantity = '';
            $s_details = '';
            $s_prices = '';
            $s_schedule = '';
            $s_user = '';
            
            if(!empty($getSchedulerById)){
                $url = $getSchedulerById['Url'];

                $needle = 'create_arc_item_slow_cron.php?'; 
                $str = substr($url, strpos($url, $needle) + strlen($needle));
                $str_array = explode("&", $str);
                foreach($str_array as $str_arrays){
                    $str_array1 = explode("=", $str_arrays);
                    if($str_array1[0] == 'user'){
                        $s_user = $str_array1[1];
                    }elseif($str_array1[0] == 'm_orders'){
                        $s_orders = $str_array1[1];
                    }elseif($str_array1[0] == 'm_quantity'){
                        $s_quantity = $str_array1[1];
                    }elseif($str_array1[0] == 'm_details'){
                        $s_details = $str_array1[1];
                    }elseif($str_array1[0] == 'm_prices'){
                        $s_prices = $str_array1[1];
                    }
                }
                $s_schedules = $getSchedulerById['CronSchedule'];
                if($s_schedules == "0 0/15 * * * ?"){
                    $s_schedule = 1;
                }elseif($s_schedules == "0 0 * * * ?"){
                    $s_schedule = 2;
                }elseif($s_schedules == "0 0 12 * * ?"){
                    $s_schedule = 3;
                }
            }


            $f_url = '';
            $f_orders = '';
            $f_quantity = '';
            $f_details = '';
            $f_prices = '';
            $f_schedule = '';
            $f_user = '';
            if(!empty($getSchedulerFastById)){
                $f_url = $getSchedulerFastById['Url'];

                $f_needle = 'create_arc_item_fast_cron.php?'; 
                $f_str = substr($f_url, strpos($f_url, $f_needle) + strlen($f_needle));
                $f_str_array = explode("&", $f_str);
                foreach($f_str_array as $f_str_arrays){
                    $f_str_array1 = explode("=", $f_str_arrays);
                    if($f_str_array1[0] == 'user'){
                        $f_user = $f_str_array1[1];
                    }elseif($f_str_array1[0] == 'm_orders'){
                        $f_orders = $f_str_array1[1];
                    }elseif($f_str_array1[0] == 'm_quantity'){
                        $f_quantity = $f_str_array1[1];
                    }elseif($f_str_array1[0] == 'm_details'){
                        $f_details = $f_str_array1[1];
                    }elseif($f_str_array1[0] == 'm_prices'){
                        $f_prices = $f_str_array1[1];
                    }
                }
                $f_schedules = $getSchedulerFastById['CronSchedule'];
                if($f_schedules == "0 0/15 * * * ?"){
                    $f_schedule = 1;
                }elseif($f_schedules == "0 0 * * * ?"){
                    $f_schedule = 2;
                }elseif($s_schedules == "0 0 12 * * ?"){
                    $f_schedule = 3;
                }
            }

            

            $auth = true;
            $authRowByMerchantGuid=$authListById['Records'][0];
            $data_config = [
                [
                  'Name'=> 'merchant_guid',
                  'Operator'=> 'equal',
                  'Value'=> $_GET['user']
                ],
                [
                    'Name'=> 'domain',
                    'Operator'=> 'equal',
                    'Value'=> $authRowByMerchantGuid['domain']
                ]
              ];
            $configListById=$arc->searchTable($pack_id, 'config', $data_config);
            $configRowByMerchantGuid=$configListById['Records'][0];

            $create_arc_item_eventListByIdAfter=$arc->searchTable($pack_id, 'create_arc_item_event', $data_config);
            if(!empty($create_arc_item_eventListByIdAfter['Records'])){
                $create_arc_item_eventListByIdAfter1 = $create_arc_item_eventListByIdAfter['Records'][0];
                $baseURL = $arc->baseUrl11($authRowByMerchantGuid['arc_domain']);
                $event_url = $baseURL."/user/plugins/".$pack_id."/create_arc_item_event.php?user=".$create_arc_item_eventListByIdAfter1['merchant_guid']."&domain=".$create_arc_item_eventListByIdAfter1['domain']."";
                $e_orders = $create_arc_item_eventListByIdAfter1['orders'];
                $e_quantity = $create_arc_item_eventListByIdAfter1['quantity'];
                $e_details = $create_arc_item_eventListByIdAfter1['details'];
                $e_prices = $create_arc_item_eventListByIdAfter1['prices'];
                $e_categories = $create_arc_item_eventListByIdAfter1['categories'];
                $e_items = $create_arc_item_eventListByIdAfter1['items'];
            }else{
                $event_url = "Event Url Not generated, Please generate First";
                $e_orders = '';
                $e_quantity = '';
                $e_details = '';
                $e_prices = '';
                $e_categories = '';
                $e_items = '';
            }

            $mag_product=$mag->magento_products($authRowByMerchantGuid['domain'],$authRowByMerchantGuid['token']);
            $mag_product1 = json_decode($mag_product, true);
            $mag_cat_arr=$mag->get_categories($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token']);
            if(empty($mag_cat_arr->items)){
                $response = $mag->magento_auth($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['username'], $authRowByMerchantGuid['password']);
                $mag_token = json_decode($response);
                /* unset($_COOKIE['mag_token']);
                setcookie("mag_token", $mag_token, time() + (10 * 365 * 24 * 60 * 60)); */
                $data = [
                    'token' => $mag_token
                ];
                $UpdateRowInauth=$arc->editRowEntry($pack_id, 'auth', $authRowByMerchantGuid['Id'], $data);
                $mag_cat_arrr=$mag->get_categories($authRowByMerchantGuid['domain'], $mag_token);
            }else{
                $mag_cat_arrr=$mag_cat_arr;
            }
            
            $count=count($mag_cat_arrr->items);
            $mag_cat_arr1 = json_decode(json_encode($mag_cat_arrr), true);
            $mag_cat_arr2 = $mag_cat_arr1['items'];
            unset($mag_cat_arr2[0]); 
            unset($mag_cat_arr2[1]);
            $mag_cat_arr3 = array_values($mag_cat_arr2);
            $arc_cat_arr = $arc->getCategories();
            
            
            
            
            $data11 = [
                [
                'Name'=> 'merchant_guid',
                'Operator'=> 'equal',
                'Value'=> $_GET['user']
                ]
            ];
            
            $configListById=$arc->searchTable($pack_id, 'config', $data11);
            if(!empty($configListById['Records'])){
            $configRowByMerchantGuid=$configListById['Records'][0];
            }
            
            
            /* $file = file_get_contents('data.json');
            $sync_data = json_decode($file,true);
            $data_by_arcadier_guid = array();
            foreach($sync_data as $sync_dat){
            if($sync_dat['arcadier_guid'] == $authRowByMerchantGuid['merchant_guid']){
            //if($sync_dat['arcadier_guid'] == $_COOKIE['merchant_arc_guid'] && $sync_dat['sync_trigger'] == 'Manual'){
                $data_by_arcadier_guid[] = $sync_dat;
            }

            } */
            $sync_data_logdata1 = [
                [
                  'Name'=> 'arcadier_guid',
                  'Operator'=> 'equal',
                  'Value'=> $_GET['user']
                ],
                [
                    'Name'=> 'domain',
                    'Operator'=> 'equal',
                    'Value'=> $authRowByMerchantGuid['domain']
                ]
              ];

              
            
            $sync_data_logListById=$arc->searchTable($pack_id, 'sync_data_log', $sync_data_logdata1);
            //$sync_data_logListById=$arc->listTableData($pack_id, 'sync_data_log');
            if(!empty($sync_data_logListById['Records'])){
                $data_by_arcadier_guid=$sync_data_logListById['Records'];
                function sortByOrder($a, $b) {
                    return $b['CreatedDateTime'] - $a['CreatedDateTime'];
                  }
                  
                  usort($data_by_arcadier_guid, 'sortByOrder');
                //array_multisort($data_by_arcadier_guid ,SORT_DESC);
            }else{
                $data_by_arcadier_guid=array();
            }
            
            // $data_by_arcadier_guid_count = count($data_by_arcadier_guid);
            // if($data_by_arcadier_guid_count > 0){
            // 	$data_by_arcadier_guid_sync_date = $data_by_arcadier_guid[0]['sync_date'];
            // } 
            //echo '<pre>'; print_r($sync_data_logListById); die;

        }else{
            header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
        }
    }
    
}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
}

}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
}
/* if(isset($_COOKIE['m_domain']) && isset($_COOKIE['auth']) && isset($_COOKIE['mag_user']) && isset($_COOKIE['mag_pass']) && isset($_COOKIE['mag_domain'])  && isset($_COOKIE['arc_access_token'])){
$mag_product=$mag->magento_products($_COOKIE['mag_domain'],$_COOKIE['mag_token']);
$mag_product1 = json_decode($mag_product, true);
$mag_cat_arr=$mag->get_categories($_COOKIE['mag_domain'], $_COOKIE['mag_token']);
		if(empty($mag_cat_arr->items)){
			$response = $mag->magento_auth($_COOKIE['mag_domain'], $_COOKIE['mag_user'], $_COOKIE['mag_pass']);
			$mag_token = json_decode($response);
			unset($_COOKIE['mag_token']);
			setcookie("mag_token", $mag_token, time() + (10 * 365 * 24 * 60 * 60));
			$mag_cat_arrr=$mag->get_categories($_COOKIE['mag_domain'], $mag_token);
		}else{
			$mag_cat_arrr=$mag_cat_arr;
		}
		
        $count=count($mag_cat_arrr->items);
		$mag_cat_arr1 = json_decode(json_encode($mag_cat_arrr), true);
		$mag_cat_arr2 = $mag_cat_arr1['items'];
		unset($mag_cat_arr2[0]); 
		unset($mag_cat_arr2[1]);
		$mag_cat_arr3 = array_values($mag_cat_arr2);
        $arc_cat_arr = $arc->getCategories();
		
		
		
		
		$data1 = [
            [
              'Name'=> 'merchant_guid',
              'Operator'=> 'equal',
              'Value'=> $_COOKIE['merchant_arc_guid']
            ]
          ];
		
		$configListById=$arc->searchTable($pack_id, 'config', $data1);
        if(!empty($configListById['Records'])){
		$configRowByMerchantGuid=$configListById['Records'][0];
        }
		
		
		$file = file_get_contents('data.json');
		$sync_data = json_decode($file,true);
		$data_by_arcadier_guid = array();
		foreach($sync_data as $sync_dat){
		if($sync_dat['arcadier_guid'] == $_COOKIE['merchant_arc_guid']){
		//if($sync_dat['arcadier_guid'] == $_COOKIE['merchant_arc_guid'] && $sync_dat['sync_trigger'] == 'Manual'){
			$data_by_arcadier_guid[] = $sync_dat;
		}

		}
		array_multisort($data_by_arcadier_guid ,SORT_DESC);
		// $data_by_arcadier_guid_count = count($data_by_arcadier_guid);
		// if($data_by_arcadier_guid_count > 0){
		// 	$data_by_arcadier_guid_sync_date = $data_by_arcadier_guid[0]['sync_date'];
		// } 
		//echo '<pre>'; print_r($data_by_arcadier_guid); die;
}else{
        //header('location:configuration.php');
    } */
?>
<!DOCTYPE html>
<html lang="en" class="foot-plugin-footer">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Wireframe Designs - BootStrap</title>
    <meta content="Admin Dashboard" name="description" />
    <meta content="Themesbrand" name="author" />
    <!--<link rel="stylesheet" href="public/plugins/chartist/css/chartist.min.css">-->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/metismenu.min.css" rel="stylesheet" type="text/css">
    <link href="css/icons.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">



    <!--<script src="scripts/jquery.min.js"></script>
		
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
		<script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" ></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
		
		
		
		<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
 
		<script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>

		<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css" />
		<script src="scripts/bootstrap.bundle.min.js"></script>-->

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
        text-indent: -9999em;
        border-top: 1.1em solid rgba(255, 255, 255, 0.2);
        border-right: 1.1em solid rgba(255, 255, 255, 0.2);
        border-bottom: 1.1em solid rgba(255, 255, 255, 0.2);
        border-left: 1.1em solid #ffffff;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-animation: load8 1.1s infinite linear;
        animation: load8 1.1s infinite linear;
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

    /* 
        #wrapper {
            width: unset;
        }
        .col-sm-3 {
            flex: 0 0 25%;
            max-width: 18%;
        }
         */
    input[type=checkbox],
    input[type=radio] {
        visibility: unset;
    }

    .custom-control-input#m_orders

    /* ,
         .custom-control-input#m_quantity, .custom-control-input#m_details, .custom-control-input#m_prices,
        .custom-control-input#s_orders, .custom-control-input#s_quantity, .custom-control-input#s_details, .custom-control-input#s_prices */
        {
        position: absolute;
        z-index: 1;
        opacity: 0;
        left: 0px;
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

    /* div.footer {
            display: none;
        } */
    table.dataTable thead th,
    table.dataTable thead td {
        padding: 8px 10px;
    }

    .foot-plugin-footer .footer .footer-navigation ul>li>a {
        padding-right: 79px;
    }

    .foot-plugin-footer ul.footer-social-media,
    .foot-plugin-footer .footer-bottom {
        display: none;
    }

    .foot-plugin-footer .footer {
        padding-bottom: 10px;
        padding-top: 30px;
    }
    </style>
</head>

<body>
    <script>
    function addLoader() {
        $('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
    }

    function removeClass(div_id, time) {
        $("#" + div_id).fadeOut(time, function() {
            $("#" + div_id).remove();
        });
    }

    function addLoader1() {
        $('body').append('<div style="" id="loadingDiv1"><div class="loader">Loading...</div></div>');
    }
    addLoader1();
    </script>
    <div id="wrapper">
        <!-- <% include ./Partials/adminHeader  %> -->
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
                            <a href="index.php?user=<?php echo $_GET['user']; ?>" class="waves-effect">
                                <img src="images/home-icon.png"> <span> Configuration </span>
                            </a>
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

                    <!-- <% include ./Partials/Settings  %> -->

                    <div class="page-title-box">
                        <div class="row align-items-center">

                            <div class="col-sm-6">
                                <h4 class="page-title">Synchronization</h4>
                            </div>

                            <div id="dialog" title="Alert message" style="display: none">
                                <div class="ui-dialog-content ui-widget-content">
                                    <p>
                                        <span class="ui-icon ui-icon-alert"
                                            style="float: left; margin: 0 7px 20px 0"></span>
                                        <label id="lblMessage">
                                        </label>
                                    </p>
                                </div>
                            </div>

                            <!-- <div class="col-sm-6">
            <h4 class="page-title">Calendar</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Veltrix</a></li>
                <li class="breadcrumb-item active">Calendar</li>
            </ol>
        </div> -->

                            <!-- <div class="col-sm-6">
            <div class="float-right d-none d-md-block">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle arrow-none waves-effect waves-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-settings mr-2"></i> Settings
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Separated link</a>
                    </div>                 
                </div>
            </div> 
        </div>-->
                        </div>
                    </div>
                    <!-- end row -->
                    <div class="row">
                        <?php
											if($auth == true){
											?>
                        <div class="col-12 mb-2">
                            <span class="font-weight-bolder mr-4">Status</span>
                            <span class="font-weight-bolder text-success mr-2">Ok</span>
                        </div>
                        <?php
											}else{
											?>
                        <div class="col-12 mb-2">
                            <span class="font-weight-bolder mr-4">Status</span>
                            <span class="font-weight-bolder text-danger mr-2">Error</span>
                        </div>
                        <?php
											}
											?>
                        <!--<div class="col-12 mb-2">
        <span class="font-weight-bolder mr-4">Status</span>
        <span class="font-weight-bolder "> -</span>
    </div>-->
                        <div class="col-12 bg-white p-2 mb-2 rounded shadow">
                            <div class="table-responsive">
                                <!--<table class="table table-bordered table-striped" id="logTable" >-->
                                <table class="table hover" id="logTable">
                                    <thead>
                                        <tr>
                                            <th>Date Time</th>
                                            <th>Type</th>
                                            <th>Trigger</th>
                                            <th>Created</th>
                                            <th>Unchanged</th>
                                            <th>Changed</th>
                                            <!--<th>Deleted</th>-->
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
				foreach($data_by_arcadier_guid as $data_by_arcadier_guid1){
				?>
                                        <tr>
                                            <td><?php echo $data_by_arcadier_guid1['sync_date'] ?></td>
                                            <td><?php echo $data_by_arcadier_guid1['sync_type'] ?></td>
                                            <td>
                                                <?php echo $data_by_arcadier_guid1['sync_trigger'] ?>
                                            </td>
                                            <td>
                                                <?php echo $data_by_arcadier_guid1['sync_created'] ?>
                                            </td>
                                            <td>
                                                <?php echo $data_by_arcadier_guid1['sync_unchanged'] ?>
                                            </td>
                                            <td>
                                                <?php echo $data_by_arcadier_guid1['sync_changed'] ?>
                                            </td>
                                            <!--<td>
                            3
                        </td>-->
                                            <td>
                                                <?php echo $data_by_arcadier_guid1['sync_status'] ?>
                                            </td>
                                        </tr>
                                        <?php 
				}
				?>
                                        <!--<tr>
                        <td>20-03-2021</td>
                        <td>Order</td>
                        <td>
                            Fast
                        </td>
                        <td>
                            2
                        </td>
                        <td>
                            5
                        </td>
                        <td>
                            1
                        </td>
                        <td>
                            3
                        </td>
                        <td>
                            In Progress
                        </td>
                    </tr>

                    <tr>
                        <td>20-03-2021</td>
                        <td>Item</td>
                        <td>
                            Slow
                        </td>
                        <td>
                            3
                        </td>
                        <td>
                            1
                        </td>
                        <td>
                            0
                        </td>
                        <td>
                            5
                        </td>
                        <td>
                            Completed
                        </td>
                    </tr>

                    <tr>
                        <td>20-03-2021</td>
                        <td>Item</td>
                        <td>
                            Manual
                        </td>
                        <td>
                            1
                        </td>
                        <td>
                            3
                        </td>
                        <td>
                            0
                        </td>
                        <td>
                            4
                        </td>
                        <td>
                            In Progress
                        </td>
                    </tr>

                    <tr>
                        <td>20-03-2021</td>
                        <td>Order</td>
                        <td>
                            Triggered
                        </td>
                        <td>
                            2
                        </td>
                        <td>
                            5
                        </td>
                        <td>
                            0
                        </td>
                        <td>
                            4
                        </td>
                        <td>
                            Completed
                        </td>
                    </tr>-->
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <div class="col-12 mb-2 bg-white p-3 rounded shadow">
                            <div class="mb-2">
                                <span class="font-weight-bolder">Mode</span>
                            </div>
                            <div class="row pl-2">
                                <div class="col-12 col-md-8 mb-3 bg-light p-2 rounded">
                                    <div class="row w-100">
                                        <div class="col-1">
                                            <div class="custom-control custom-radio text-center mt-3">
                                                <input type="radio" class="custom-control-input" id="ma" name="mode"
                                                    value="0"
                                                    <?php if($configRowByMerchantGuid['mode']==0){echo 'checked';} ?>>
                                                <label class="custom-control-label" for="ma"></label>
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="mb-2">
                                                <span class="font-weight-bolder">Items:</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="font-weight-bolder">Orders:</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="mb-2">
                                                <span class="font-weight-bolder">Magento -> Arcadier</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="font-weight-bolder"> Arcadier -> Magento </span>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <!-- <div class="col-12 col-md-8 bg-light p-2 rounded">
                <div class="row w-100">
                    <div class="col-1">
                        <div class="custom-control custom-radio text-center mt-3" >
                                                            <input type="radio" class="custom-control-input" id="am" name="mode" value="1" <?php if($configRowByMerchantGuid['mode']==1){echo 'checked';} ?>>
                                                            <label class="custom-control-label" for="am"></label>
                                                          </div>  
                    </div>
                    <div class="col-2">
                        <div class="mb-2">
                            <span class="font-weight-bolder">Items:</span>
                        </div>
                        <div class="mb-2">
                            <span class="font-weight-bolder">Orders:</span>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="mb-2">
                            <span class="font-weight-bolder"> Arcadier -> Magento </span>
                        </div>
                        <div class="mb-2">
                            <span class="font-weight-bolder"> Magento -> Arcadier </span>
                        </div>
                    </div>

                </div>
            </div> -->
                            </div>

                        </div>


                        <div class="col-12 mb-2 bg-white p-3 rounded shadow">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#menu">Manual</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#event">Event</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#fast">Fast</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#slow">Slow</a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane container active" id="menu">
                                    <div class="row p-3 ">
                                        <div class="col-6">
                                            <!-- <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="data" name="data">
                            <label class="custom-control-label" for="data">Orders</label>
                          </div>-->

                                            <!--<div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="synchronizeitems" name="items">
                            <label class="custom-control-label" for="items">Items</label>
                          </div>-->

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="m_orders"
                                                    name="m_orders">
                                                <label class="custom-control-label" for="m_orders">Orders</label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="m_quantity"
                                                    name="m_quantity">
                                                <label class="custom-control-label" for="m_quantity">Quantity</label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="m_details"
                                                    name="m_details">
                                                <label class="custom-control-label" for="m_details">Details</label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="m_prices"
                                                    name="m_prices">
                                                <label class="custom-control-label" for="m_prices">Prices</label>
                                            </div>


                                            <!-- <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="price" name="price">
                            <label class="custom-control-label" for="price">Prices</label>
                          </div>-->
                                        </div>
                                        <div class="col-6 justify-content-center mx-auto text-center">
                                            <button class="btn btn-primary mt-5" onclick="sync_product_manual();">Run
                                                Now</button>
                                        </div>

                                    </div>
                                </div>
                                <div class="tab-pane container fade" id="event">
                                    <div class="row p-3">
                                        <div class="col-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="e_orders"
                                                    name="e_orders"
                                                    <?php if(!empty($e_orders)){if($e_orders == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="e_orders">Orders</label>
                                            </div>

                                            <!--<div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="e_items" name="e_items" <?php // if(!empty($e_items)){if($e_items == 1){echo 'checked';}}?> >
                            <label class="custom-control-label" for="e_items">Items</label>
                          </div>

                          <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="e_categories" name="e_categories" <?php // if(!empty($e_categories)){if($e_categories == 1){echo 'checked';}}?> >
                            <label class="custom-control-label" for="e_categories">Categories</label>
                          </div>-->

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="e_quantity"
                                                    name="e_quantity"
                                                    <?php if(!empty($e_quantity)){if($e_quantity == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="e_quantity">Quantity</label>
                                            </div>

                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="e_details"
                                                    name="e_details"
                                                    <?php if(!empty($e_details)){if($e_details == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="e_details">Details</label>
                                            </div>


                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="e_prices"
                                                    name="e_prices"
                                                    <?php if(!empty($e_prices)){if($e_prices == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="e_prices">Prices</label>
                                            </div>
                                        </div>
                                        <div class="col-9">
                                            <div id="event_url" style="overflow-x: scroll;"><?php echo $event_url; ?>
                                            </div>
                                            <div>
                                                <button class="btn btn-primary mt-5"
                                                    onclick="sync_product_event();">Generate Now</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!--<div class="tab-pane container fade" id="fast">
                <div class="row p-3">
                    <div class="col-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="f_orders" name="f_orders">
                            <label class="custom-control-label" for="f_orders">Orders</label>
                          </div>
                          <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="f_quantity" name="f_quantity">
                            <label class="custom-control-label" for="f_quantity">Quantity</label>
                          </div>
                          <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="f_prices" name="f_prices">
                            <label class="custom-control-label" for="f_prices">Prices</label>
                          </div>
                    </div>
                    <div class="col-6 justify-content-center mx-auto text-center">
                      
                        Every <input type="text" style="width: 35px; border-radius: 5px;" placeholder="5" name="f_schedule"> Minutes
                    </div>
                </div>
            </div>-->
                                <div class="tab-pane container fade" id="fast">
                                    <div class="row p-3">
                                        <div class="col-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="f_orders"
                                                    name="f_orders"
                                                    <?php if(!empty($f_orders)){if($f_orders == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="f_orders">Orders</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="f_quantity"
                                                    name="f_quantity"
                                                    <?php if(!empty($f_quantity)){if($f_quantity == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="f_quantity">Quantity</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="f_details"
                                                    name="f_details"
                                                    <?php if(!empty($f_details)){if($f_details == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="f_details">Details</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="f_prices"
                                                    name="f_prices"
                                                    <?php if(!empty($f_prices)){if($f_prices == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="f_prices">Prices</label>
                                            </div>
                                        </div>
                                        <div class="col-6 justify-content-center mx-auto text-center">
                                            <!-- <button class="btn btn-primary mt-5">Run Now</button> -->
                                            <!--Every <input type="text" style="width: 35px; border-radius: 5px;" placeholder="120" name="s_schedule" id="s_schedule"> Minutes-->
                                            <select class="" name="f_schedule" id="f_schedule"
                                                style="width: 200px; border-radius: 5px;">

                                                <!--<option value="">Select Schedule Time</option>-->
                                                <option value="1"
                                                    <?php //if(!empty($s_schedule)){if($s_schedule == 1){echo 'selected';}}?>>
                                                    Every 15 Minutes</option>
                                                <!--<option value="2" <?php //if(!empty($s_schedule)){if($s_schedule == 2){echo 'selected';}}?> >Every 1 hour</option>
                                                            <option value="3" <?php //if(!empty($s_schedule)){if($s_schedule == 3){echo 'selected';}}?> >Every 12 Noon</option>-->


                                            </select>
                                            <div>
                                                <button class="btn btn-primary mt-5"
                                                    onclick="sync_product_fast();">Schedule Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane container fade" id="slow">
                                    <div class="row p-3">
                                        <div class="col-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="s_orders"
                                                    name="s_orders"
                                                    <?php if(!empty($s_orders)){if($s_orders == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="s_orders">Orders</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="s_quantity"
                                                    name="s_quantity"
                                                    <?php if(!empty($s_quantity)){if($s_quantity == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="s_quantity">Quantity</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="s_details"
                                                    name="s_details"
                                                    <?php if(!empty($s_details)){if($s_details == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="s_details">Details</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="s_prices"
                                                    name="s_prices"
                                                    <?php if(!empty($s_prices)){if($s_prices == 1){echo 'checked';}}?>>
                                                <label class="custom-control-label" for="s_prices">Prices</label>
                                            </div>
                                        </div>
                                        <div class="col-6 justify-content-center mx-auto text-center">
                                            <!-- <button class="btn btn-primary mt-5">Run Now</button> -->
                                            <!--Every <input type="text" style="width: 35px; border-radius: 5px;" placeholder="120" name="s_schedule" id="s_schedule"> Minutes-->
                                            <select class="" name="s_schedule" id="s_schedule"
                                                style="width: 200px; border-radius: 5px;">

                                                <!--<option value="">Select Schedule Time</option>
															<option value="1" <?php //if(!empty($s_schedule)){if($s_schedule == 1){echo 'selected';}}?> >Every 15 Minutes</option>-->
                                                <option value="2"
                                                    <?php //if(!empty($s_schedule)){if($s_schedule == 2){echo 'selected';}}?>>
                                                    Every 1 hour</option>
                                                <!--<option value="3" <?php //if(!empty($s_schedule)){if($s_schedule == 3){echo 'selected';}}?> >Every 12 Noon</option>-->


                                            </select>
                                            <div>
                                                <button class="btn btn-primary mt-5"
                                                    onclick="sync_product_slow();">Schedule Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- end row -->
                    <!-- <footer class="footer text-center">
    © 2021.
</footer> -->
                    <!--<script src="public/assets/js/jquery.min.js"></script>
<script src="public/assets/js/bootstrap.bundle.min.js"></script>
<script src="public/assets/js/metisMenu.min.js"></script>
<script src="public/assets/js/jquery.slimscroll.js"></script>
<script src="public/assets/js/waves.min.js"></script>
<script src="public/assets/js/app.js"></script>
<script src="public/assets/pages/dashboard.js"></script>
<script src="public/assets/js/jquery.min.js"></script>
<script src="public/assets/js/bootstrap.bundle.min.js"></script>
<script src="public/assets/js/metisMenu.min.js"></script>
<script src="public/assets/js/jquery.slimscroll.js"></script>
<script src="public/assets/js/waves.min.js"></script>


<script src="public/plugins/countdown/jquery.countdown.min.js"></script>
<script src="public/assets/pages/countdown.int.js"></script>

<script src="public/assets/js/app.js"></script>-->

                    <script src="scripts/metisMenu.min.js"></script>
                    <script src="scripts/jquery.slimscroll.js"></script>
                    <script src="scripts/waves.min.js"></script>
                    <script src="scripts/app.js"></script>
                    <!--<script src="scripts/dashboard.js"></script>-->
                    <!--<script src="public/assets/js/jquery.min.js"></script>
<script src="public/assets/js/bootstrap.bundle.min.js"></script>-->
                    <script src="scripts/metisMenu.min.js"></script>
                    <script src="scripts/jquery.slimscroll.js"></script>
                    <script src="scripts/waves.min.js"></script>

                    <!-- countdown -->
                    <!--<script src="public/plugins/countdown/jquery.countdown.min.js"></script>-->
                    <!--<script src="scripts/countdown.int.js"></script>-->
                    <!-- App js -->
                    <script src="scripts/app.js"></script>

                    <script>
                    //var $j = jQuery.noConflict();
                    $(document).ready(function() {
                        $.noConflict();
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

                        //$(".chosen-select").chosen({width: "125px"});

                        /* $('input[type=checkbox]').click(function () {
                        	if (!$(this).is(':checked')) {
                        		$('#'+this.id).prop('checked',false);
                        	}
                        }); */

                        myDialog = $("#dialog").dialog({
                            // dialog settings:
                            autoOpen: true,
                            // ... 
                        });
                        myDialog.dialog("close");


                        var Id =
                            '<?php if(!empty($configRowByMerchantGuid["Id"])){echo $configRowByMerchantGuid["Id"]; } ?>';
                        var merchant_guid =
                            '<?php if(!empty($configRowByMerchantGuid["merchant_guid"])){ echo $configRowByMerchantGuid["merchant_guid"]; } ?>';
                        var ma = $('#ma').val();
                        var am = $('#am').val();
                        $(document).on("click", "#ma", function() {
                            addLoader();
                            var data = {
                                ma: ma,
                                Id: Id,
                                merchant_guid: merchant_guid
                            };
                            $.ajax({
                                url: 'ajaxrequest.php',
                                type: "POST",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(data) {
                                    removeClass('loadingDiv', 500);
                                    if (data == 'ma') {

                                        $('#ma').prop("checked", true);
                                        $('#am').prop("checked", false);


                                        //alert('Magento To Arcadier Mode Done Successfully');
                                        ShowCustomDialog('Alert',
                                            'Magento To Arcadier Mode Done Successfully'
                                        );


                                    } else {
                                        //alert('Unable to Change Mode MA');
                                        //alert(data);
                                        ShowCustomDialog('Alert', data);


                                    }


                                }
                            });
                        });
                        $(document).on("click", "#am", function() {
                            addLoader();
                            var data = {
                                am: am,
                                Id: Id,
                                merchant_guid: merchant_guid
                            };
                            $.ajax({
                                url: 'ajaxrequest.php',
                                type: "POST",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(data) {
                                    removeClass('loadingDiv', 500);
                                    if (data == 'am') {

                                        $('#am').prop('checked', true);
                                        $('#ma').prop('checked', false);

                                        //alert('Arcadier To Magento Mode Done Successfully');
                                        ShowCustomDialog('Alert',
                                            'Arcadier To Magento Mode Done Successfully'
                                        );


                                    } else {
                                        //alert('Unable to Change Mode AM');
                                        //alert(data);
                                        ShowCustomDialog('Alert', data);


                                    }
                                }
                            });
                        });

                        /* console.log(data_json1());
                  setInterval(function(){
					 //console.log('test');
					 data_json1(17);
					}, 5000);  */


                    });

                    //jQuery($ => {
                    // var arr = JSON.parse(localStorage.getItem('checked')) || [];
                    // var checked_data = JSON.parse(localStorage.getItem('checked_data')) || [];
                    // console.log("arr1:"+arr);
                    // console.log("checked_data:");
                    // console.log(checked_data);
                    //arr.forEach((c, i) => $('.sync_product').eq(i).prop('checked', c));

                    // $(".sync_product").click(() => {  
                    //     var arr = $('.sync_product').map((i, el) => el.checked).get();
                    //     console.log("arr2:"+arr);
                    //     localStorage.setItem("checked", JSON.stringify(arr));
                    // });
                    //});

                    function data_json1(count = '', date = '') {

                        data = {
                            data_json: 'data_json',
                            count: count,
                            date: date
                        };
                        $.ajax({
                            type: "POST",
                            url: "data_json.php",
                            contentType: 'application/json',
                            data: JSON.stringify(data),
                            data: data,
                            success: function(response) {
                                //console.log(response);
                                return response;
                                /* console.log(JSON.parse(response));
                                console.log(response);
                                response = JSON.parse(response);
                                return response; */
                                //if(response.message == 1){

                                /* $("#override_default_category_select"+id).val("");
                                $('#sync_product'+id).prop('checked',false);
                                $('#override_default_category'+id).prop('checked',false);
                                $('#mag'+id1).css("display","none"); */
                                //var tablerow = '<tr><td>'+response.data.sync_date+'</td><td>'+response.data.sync_type+'</td><td>'+response.data.sync_trigger+'</td><td>'+response.data.sync_created+'</td><td>'+response.data.sync_unchanged+'</td><td>'+response.data.sync_changed+'</td><td>'+response.data.sync_status+'</td></tr>';
                                /* $('#logTable > tbody:last-child').append(tablerow);
                                $(tablerow).prependTo("#logTable > tbody");
                                $(tablerow).insertBefore('#logTable > tbody > tr:first'); */
                                //$('#logTable > tbody > tr:first').before(tablerow);
                                /* var message = 'Sync successfully';
                                ShowCustomDialog('Alert',message); */
                                // }
                                //else{


                                //var message1 = response.toString();
                                //var message = "The following items did not have their categories mapped: " +message1+", and were not created.";
                                /* var message = message1;
                                ShowCustomDialog('Alert',message); */
                                //}
                            }
                        });
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
                        jQuery("." + class_name).find(':input').each(function() {
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
                                    jQuery(this).val('');
                                    break;
                                case 'checkbox':
                                case 'radio':
                                    this.checked = false;
                                    break;
                            }
                        });
                    }

                    function addLoader() {
                        $('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
                    }

                    function removeClass(div_id, time) {
                        $("#" + div_id).fadeOut(time, function() {
                            $("#" + div_id).remove();
                        });
                    }


                    function sync_product_all() {
                        var dt = new Date();
                        dt.setHours(dt.getHours() + 5);
                        dt.setMinutes(dt.getMinutes() + 30);
                        var date = dt.toISOString().replace(/T/, ' ').replace(/\..+/, '');


                        if ($('#m_orders').is(":checked")) {
                            var tablerowbefore = '<tr><td>' + date + '</td><td>' + 'Item' + '</td><td>' + 'Manual' +
                                '</td><td>' + '0' + '</td><td>' + '0' + '</td><td>' + '0' + '</td><td>' +
                                'In Progress' + '</td></tr>';
                            $('#logTable > tbody > tr:first').before(tablerowbefore);
                            var arc_user =
                                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                            var data = {
                                create_arc_item_all: 'create_arc_item_all',
                                arc_user: arc_user
                            };
                            $('body').append(
                                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');



                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response) {
                                    removeClass('loadingDiv', 500);
                                    console.log(JSON.parse(response));
                                    console.log(response);
                                    response = JSON.parse(response);
                                    $('#logTable > tbody > tr:first').remove();
                                    if (response.message == 1) {

                                        /* $("#override_default_category_select"+id).val("");
                                        $('#sync_product'+id).prop('checked',false);
                                        $('#override_default_category'+id).prop('checked',false);
                                        $('#mag'+id1).css("display","none"); */
                                        var tablerow = '<tr><td>' + response.data.sync_date + '</td><td>' +
                                            response.data.sync_type + '</td><td>' + response.data
                                            .sync_trigger + '</td><td>' + response.data.sync_created +
                                            '</td><td>' + response.data.sync_unchanged + '</td><td>' +
                                            response.data.sync_changed + '</td><td>' + response.data
                                            .sync_status + '</td></tr>';
                                        /* $('#logTable > tbody:last-child').append(tablerow);
                                        $(tablerow).prependTo("#logTable > tbody");
                                        $(tablerow).insertBefore('#logTable > tbody > tr:first'); */
                                        $('#logTable > tbody > tr:first').before(tablerow);
                                        var message = 'Sync successfully';
                                        ShowCustomDialog('Alert', message);
                                    } else {


                                        var message1 = response.toString();
                                        //var message = "The following items did not have their categories mapped: " +message1+", and were not created.";
                                        var message = message1;
                                        ShowCustomDialog('Alert', message);
                                    }
                                }
                            });
                        } else {
                            alert('Please check Items');
                        }

                    }


                    function sync_product_manual() {
                        var arr = JSON.parse(localStorage.getItem('checked')) || [];
                        var checked_data = JSON.parse(localStorage.getItem('checked_data')) || [];
                        //console.log("arr1:"+arr);
                        console.log("checked_data:");
                        console.log(checked_data);
                        //return false;
                        var dt = new Date();
                        dt.setHours(dt.getHours() + 5);
                        dt.setMinutes(dt.getMinutes() + 30);
                        var date = dt.toISOString().replace(/T/, ' ').replace(/\..+/, '');


                        if ($('#m_orders').is(":checked") || $('#m_quantity').is(":checked") || $('#m_details').is(
                                ":checked") || $('#m_prices').is(":checked")) {
                            var m_orders = 0;
                            var m_quantity = 0;
                            var m_details = 0;
                            var m_prices = 0;
                            if ($('#m_orders').is(":checked")) m_orders = 1;
                            if ($('#m_quantity').is(":checked")) m_quantity = 1;
                            if ($('#m_details').is(":checked")) m_details = 1;
                            if ($('#m_prices').is(":checked")) m_prices = 1;

                            var tablerowbefore = '<tr><td>' + date + '</td><td>' + 'Item' + '</td><td>' + 'Manual' +
                                '</td><td>' + '0' + '</td><td>' + '0' + '</td><td>' + '0' + '</td><td>' +
                                'In Progress' + '</td></tr>';
                            $('#logTable > tbody > tr:first').before(tablerowbefore);
                            var arc_user =
                                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                            var data = {
                                create_arc_item_all_manual: 'create_arc_item_all_manual',
                                arc_user: arc_user,
                                m_orders: m_orders,
                                m_quantity: m_quantity,
                                m_details: m_details,
                                m_prices: m_prices,
                                checked_data: checked_data
                            };
                            //var data = {create_arc_item_all:'create_arc_item_all',arc_user:arc_user};
                            $('body').append(
                                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');



                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response) {
                                    //console.log(response);
                                    removeClass('loadingDiv', 500);
                                    console.log(JSON.parse(response));
                                    console.log(response);
                                    response = JSON.parse(response);
                                    $('#logTable > tbody > tr:first').remove();
                                    if (response.message == 1) {

                                        // $("#override_default_category_select"+id).val("");
                                        // $('#sync_product'+id).prop('checked',false);
                                        // $('#override_default_category'+id).prop('checked',false);
                                        // $('#mag'+id1).css("display","none"); 
                                        var tablerow = '<tr><td>' + response.data.sync_date + '</td><td>' +
                                            response.data.sync_type + '</td><td>' + response.data
                                            .sync_trigger + '</td><td>' + response.data.sync_created +
                                            '</td><td>' + response.data.sync_unchanged + '</td><td>' +
                                            response.data.sync_changed + '</td><td>' + response.data
                                            .sync_status + '</td></tr>';
                                        // $('#logTable > tbody:last-child').append(tablerow);
                                        // $(tablerow).prependTo("#logTable > tbody");
                                        // $(tablerow).insertBefore('#logTable > tbody > tr:first');
                                        $('#logTable > tbody > tr:first').before(tablerow);
                                        var message = 'Sync successfully';
                                        ShowCustomDialog('Alert', message);
                                    } else {


                                        var message1 = response.toString();
                                        //var message = "The following items did not have their categories mapped: " +message1+", and were not created.";
                                        var message = message1;
                                        ShowCustomDialog('Alert', message);
                                    }
                                }
                            });
                        } else {
                            alert('Please check atleast one');
                        }

                    }


                    function sync_product_slow() {
                        var s_schedule = $("#s_schedule").val();
                        if ($('#s_orders').is(":checked") || $('#s_quantity').is(":checked") || $('#s_details').is(
                                ":checked") || $('#s_prices').is(":checked")) {
                            var s_orders = 0;
                            var s_quantity = 0;
                            var s_details = 0;
                            var s_prices = 0;

                            if ($('#s_orders').is(":checked")) s_orders = 1;
                            if ($('#s_quantity').is(":checked")) s_quantity = 1;
                            if ($('#s_details').is(":checked")) s_details = 1;
                            if ($('#s_prices').is(":checked")) s_prices = 1;
                            if (s_schedule == '') {
                                alert('Please insert schedule time also');
                                return false;
                            }

                            var arc_user =
                                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                            var data = {
                                create_arc_item_all_slow_schedule: 'create_arc_item_all_slow_schedule',
                                arc_user: arc_user,
                                s_orders: s_orders,
                                s_quantity: s_quantity,
                                s_details: s_details,
                                s_prices: s_prices,
                                s_schedule: s_schedule,
                                schedule_type: 's_schedule_slow'
                            };
                            $('body').append(
                                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');



                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response) {
                                    console.log(response);
                                    removeClass('loadingDiv', 500);
                                    ShowCustomDialog('Alert', response);
                                }
                            });
                        } else {
                            alert('Please check atleast one');
                        }

                    }


                    function sync_product_fast() {
                        var f_schedule = $("#f_schedule").val();
                        if ($('#f_orders').is(":checked") || $('#f_quantity').is(":checked") || $('#f_details').is(
                                ":checked") || $('#f_prices').is(":checked")) {
                            var f_orders = 0;
                            var f_quantity = 0;
                            var f_details = 0;
                            var f_prices = 0;

                            if ($('#f_orders').is(":checked")) f_orders = 1;
                            if ($('#f_quantity').is(":checked")) f_quantity = 1;
                            if ($('#f_details').is(":checked")) f_details = 1;
                            if ($('#f_prices').is(":checked")) f_prices = 1;
                            if (f_schedule == '') {
                                alert('Please insert schedule time also');
                                return false;
                            }

                            var arc_user =
                                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                            var data = {
                                create_arc_item_all_fast_schedule: 'create_arc_item_all_fast_schedule',
                                arc_user: arc_user,
                                s_orders: f_orders,
                                s_quantity: f_quantity,
                                s_details: f_details,
                                s_prices: f_prices,
                                s_schedule: f_schedule,
                                schedule_type: 's_schedule_fast'
                            };
                            $('body').append(
                                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');



                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response) {
                                    console.log(response);
                                    removeClass('loadingDiv', 500);
                                    ShowCustomDialog('Alert', response);
                                }
                            });
                        } else {
                            alert('Please check atleast one');
                        }

                    }



                    function sync_product_event() {

                        if ($('#e_orders').is(":checked") || $('#e_quantity').is(":checked") || $('#e_details').is(
                                ":checked") || $('#e_prices').is(":checked")) {
                            var e_orders = 0;
                            var e_quantity = 0;
                            var e_details = 0;
                            var e_prices = 0;
                            //var e_items = 0;
                            //var e_categories = 0;

                            if ($('#e_orders').is(":checked")) e_orders = 1;
                            if ($('#e_quantity').is(":checked")) e_quantity = 1;
                            if ($('#e_details').is(":checked")) e_details = 1;
                            if ($('#e_prices').is(":checked")) e_prices = 1;
                            //if($('#e_items').is(":checked")) e_items = 1;
                            //if($('#e_categories').is(":checked")) e_categories = 1;


                            var arc_user =
                                '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                            var data = {
                                create_arc_item_event: 'create_arc_item_event',
                                arc_user: arc_user,
                                s_orders: e_orders,
                                s_quantity: e_quantity,
                                s_details: e_details,
                                s_prices: e_prices
                            };
                            $('body').append(
                                '<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');



                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response) {
                                    console.log(response);
                                    removeClass('loadingDiv', 500);
                                    response = JSON.parse(response);
                                    console.log(response);
                                    if (response.code == 'success') {
                                        $("#event_url").css("display", "block");
                                        $("#event_url").text(response.url);
                                        ShowCustomDialog('Alert', response.message);
                                    } else if (response.code == 'error') {
                                        ShowCustomDialog('Alert', response.message);
                                    } else if (response.code == 'error1') {
                                        ShowCustomDialog('Alert', response.message);
                                    }

                                }
                            });
                        } else {
                            alert('Please check atleast one');
                        }

                    }




                    $(document).ready(function() {
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
                    });

                    function getCookie(name) {
                        var value = '; ' + document.cookie;
                        var parts = value.split('; ' + name + '=');
                        if (parts.length === 2) {
                            return parts.pop().split(';').shift();
                        }
                    }
                    </script>
                    <!-- <script src="scripts/user_check.js"></script> -->
</body>

</html>