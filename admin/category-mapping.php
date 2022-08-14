<?php
include 'magento_functions.php';
include 'api.php';
require '../license/license.php';
require_once("shopify_functions.php");

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();

$shop = "codemafia-2";
$token = "shpua_ceb145079023685a85fe46fee76c9208";

$licence = new License();
// if (!$licence->isValid()) {
//     echo "<script>alert('Please subscribe first to use this plugin');</script>";
//     $location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
//     header('Location: ' . $location);
//     exit();
// }
$getMarketplaceDomain = getMarketplaceDomain();
$super_admin_domain_array = explode(",", SUPER_ADMIN_DOMAIN);
if(!in_array($getMarketplaceDomain, $super_admin_domain_array)){
    echo "<script>alert('Unauthorised Host');</script>";
    exit();
}
$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$UserInfo = $arc->getUserInfo($_GET['user']);
$isMerchant = false;
$isMerchantAuth = 'No';
if(!empty($UserInfo)){
foreach($UserInfo['Roles'] as $UserInfoRoles){
    if($UserInfoRoles == 'Admin'){
        $isMerchant = true;
    }
}
}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
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
       // if(!empty($authListById['Records'])){
            //if($authListById['Records'][0]['auth_status'] == '1'){
                //$isMerchantAuth = true;
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


                $shopifyCategories =  shopify_categories($token, $shop);
                echo $shopifyCategories;
                



                $count=count($mag_cat_arrr->items);
                $mag_cat_arr1 = json_decode(json_encode($mag_cat_arrr), true);
                $mag_cat_arr2 = $mag_cat_arr1['items'];
                unset($mag_cat_arr2[0]); 
                unset($mag_cat_arr2[1]);
                $mag_cat_arr3 = array_values($mag_cat_arr2);
                $arc_cat_arr = $arc->getCategories();
            if($authListById['Records'][0]['auth_status'] == '1'){
                $isMerchantAuth = 'Yes';
            }else{
                $isMerchantAuth = 'No';
                //header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace']);
            }
       // }
		
    }else{
        //header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
    }
}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
}
/* $pack_id = getPackageID();
if(isset($_COOKIE['m_domain']) && isset($_COOKIE['auth']) && isset($_COOKIE['mag_user']) && isset($_COOKIE['mag_pass']) && isset($_COOKIE['mag_domain'])  && isset($_COOKIE['arc_access_token']))
    {
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
    <link rel="stylesheet" href="css/category.css">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/metismenu.min.css" rel="stylesheet" type="text/css">
    <link href="css/icons.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">


    <script src="scripts/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />



    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>

    <script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css" />
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
        padding: 0 15px 10px 30px;
        margin-top: 20px;
    }

    .foot-plugin-footer .content-page .content {
        margin-bottom: 30px;
    }

    .foot-plugin-footer .footer {
        padding: 0;
        position: absolute;
        bottom: 0;
        width: inherit;
        /* margin: auto; */
        padding-left: 240px;
    }

    .foot-plugin-footer ul.footer-social-media {
        display: none;
    }

    /* div.footer {
            display: none;
        } */
    #wrapper {
        overflow: unset;
    }

    .content-page {
        /* margin-left: 220px; */
        margin-top: -20px;
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
                            <a href="configuration.php?user=<?php echo $_GET['user']; ?>" class="waves-effect">
                                <img src="images/home-icon.png"> <span> Configuration </span>
                            </a>
                        </li>
                        <!--<li>
                    <a href="category-mapping.php?user=<?php //echo $_GET['user']; ?>" class="waves-effect">   <img src="images/list-icon.png"> <span> Category Mapping </span></a>
                </li>-->

                        <li>
                            <a href="trial-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/setting-icon.png"> <span> Trial List </span></a>
                        </li>

                        <li>
                            <a href="paid-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/synchronize-icon.png"> <span> Paid List </span></a>
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
                                <h4 class="page-title">Category Mapping</h4>

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

                    <div class="row category h-100">
                        <div class="col-6 font-weight-bolder">
                            Magento
                        </div>
                        <div class="col-6 font-weight-bolder">
                            Arcadier
                        </div>

                        <div class="col-6 p-0 mt-2">
                            <ul class="nav flex-column" role="tablist">
                                <?php foreach($mag_cat_arr3 as $mag_cat_arr333){ 
		if(!empty($mag_cat_arr333['children'])){
			$children=explode(',',$mag_cat_arr333['children']);?>
                                <h6><?php echo $mag_cat_arr333['name']; ?></h6>

                                <?php foreach($mag_cat_arr3 as $mag_cat_arr3333){ 
			if(empty($mag_cat_arr3333['children'])){
				foreach($children as $child){
					if($child==$mag_cat_arr3333['id']){?>
                                <li class="nav-item ">
                                    <a class="nav-link <?php if($mag_cat_arr3333['id'] == 4){echo active;} ?>"
                                        data-toggle="tab" href="#a<?php echo $mag_cat_arr3333['id']; ?>"><?php 
              echo $mag_cat_arr3333['name']; 
              ?></a>
                                </li>
                                <?php } } }?>
                                <?php }?>

                                <?php }?>
                                <?php }?>
                            </ul>

                        </div>
                        <div class="col-6 p-0 tab-content-box mt-2">
                            <div class="tab-content">
                                <?php foreach($mag_cat_arr3 as $mag_cat_arr33333){ 
		$mag_cat_id = $mag_cat_arr33333['id'];
		if(empty($mag_cat_arr33333['children'])){
			?>
                                <div id="a<?php echo $mag_cat_arr33333['id']; ?>"
                                    class="container tab-pane <?php if($mag_cat_arr33333['id'] == 4){echo active;} ?>">
                                    <form class="save_map_form">
                                        <!--<div class="" style="margin-left: 24px; padding-top:15px;">
                    <input type="checkbox" onclick="select_all()"  id="select_all"/>
                    <label class="" for="">Select All</label>
                  </div>-->


                                        <?php
            if(!empty($arc_cat_arr)){
            foreach($arc_cat_arr['Records'] as $arc_cat_arr1){
				
				
                ?>
                                        <div class="custom-control custom-checkbox mt-3 mb-3"
                                            id="divison<?php echo $mag_cat_arr33333['id']; ?>">
                                            <input type="checkbox" <?php $data1 = [
							[
								'Name' => 'merchant_guid',
								'Operator' => 'equal',
								'Value' => $_GET['user'],
							],
                            [
								'Name' => 'domain',
								'Operator' => 'equal',
								'Value' => $authRowByMerchantGuid['domain'],
							]
						];
				$response = $arc->searchTable($pack_id, 'map', $data1);
				if ($response['Records'][0]['merchant_guid'] == $_GET['user']) {
				$map_arr_unserialize = unserialize($response['Records'][0]['map']);
				$list = $map_arr_unserialize['list'];
				foreach($list as $li){ if($li['magento_cat'] == $mag_cat_arr33333['id']){if(in_array($arc_cat_arr1['ID'],$li['arcadier_guid'])){foreach($li['arcadier_guid'] as $arc_guid){if($arc_guid ==$arc_cat_arr1['ID']){echo checked;}}}} }
				} ?> name="arc_category[]" class="arc_category" id="<?php echo $arc_cat_arr1['ID'];?>" />
                                            <label class="" for=""><?php echo $arc_cat_arr1['Name']; ?></label>
                                        </div>
                                        <?php } } 
			?>

                                        <a id="save_map" onclick="save_mapp('<?php echo $mag_cat_id; ?>');"
                                            style="margin-left: 25px;border: #0e77d4;box-sizing: border-box;background-color: #333547;border-radius: 6px;color: white;padding: 5px 10px;font-size: 14px; cursor: pointer;">Submit</a>
                                        <div><?php echo implode(", ",$arr);
	echo "<br/><br/>";?></div>
                                    </form>
                                </div>
                                <?php } } 
			?>


                            </div>
                        </div>
                    </div>


                    <!-- <footer class="footer text-center">
    © 2021.
</footer> -->
                    <!--<script src="public/assets/scripts/jquery.min.js"></script>
<script src="public/assets/scripts/bootstrap.bundle.min.js"></script>
<script src="public/assets/scripts/metisMenu.min.js"></script>
<script src="public/assets/scripts/jquery.slimscroll.js"></script>
<script src="public/assets/scripts/waves.min.js"></script>
<script src="public/assets/scripts/app.js"></script>-->

                    <!--<script src="public/assets/pages/dashboard.js"></script>-->
                </div>
            </div>

            <script src="scripts/metisMenu.min.js"></script>
            <script src="scripts/jquery.slimscroll.js"></script>
            <script src="scripts/waves.min.js"></script>
            <script src="scripts/app.js"></script>
            <!--<script src="scripts/dashboard.js"></script>-->

            <script src="scripts/metisMenu.min.js"></script>
            <script src="scripts/jquery.slimscroll.js"></script>
            <script src="scripts/waves.min.js"></script>


            <!--<script src="public/plugins/countdown/jquery.countdown.min.js"></script>-->
            <!--<script src="scripts/countdown.int.js"></script>-->

            <script src="scripts/app.js"></script>

            <script>
            var $j = jQuery.noConflict();
            $(document).ready(function() {

                myDialog = $j("#dialog").dialog({
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

                $j("#dialog").dialog({
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

            function save_mapp(mag_cat_id) {

                var isMerchantAuth = '<?php echo  $isMerchantAuth; ?>';
                // alert(isMerchantAuth);
                // console.log(typeof(isMerchantAuth));
                if (isMerchantAuth == 'Yes') {
                    addLoader();
                    mag_cat_id1 = mag_cat_id;
                    //alert(mag_cat_id1);
                    var selected = [];
                    $('#divison' + mag_cat_id1 + ' input:checked').each(function() {
                        //selected.push($(this).attr('name'));
                        selected.push($(this).attr('id'));
                    });
                    var arcadier_guid = selected.join(",");
                    var arc_user =
                        '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';

                    var data = {
                        mag_cat_id1: mag_cat_id1,
                        arcadier_guid: arcadier_guid,
                        cat_map: 'cat_map',
                        arc_user: arc_user
                    };
                    $.ajax({
                        type: "POST",
                        //url: "save_map.php",
                        url: "ajaxrequest.php",
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        //data: {mag_cat_id1:mag_cat_id1,arcadier_guid:arcadier_guid},
                        success: function(data) {
                            removeClass('loadingDiv', 500);
                            if (data == 'Mapped') {
                                //alert('Mapped Successfully');
                                ShowCustomDialog('Alert', 'Mapped Successfully');
                            }
                            if (data == 'UnMapped') {
                                //alert('UnMapped Successfully');
                                ShowCustomDialog('Alert', 'UnMapped Successfully');
                            } else {
                                console.log('Unable to Map');
                            }
                        }
                    });
                } else {
                    ShowCustomDialog('Alert', 'Please authenticate first in configuration.');
                }
            }

            function clearAll() {
                document.getElementById('usr').value = '';
                document.getElementById('pwd').value = '';
                document.getElementById('endpoint').value = '';
                document.getElementById('myDate').value = '';
            }

            function butonPerform() {
                if (Math.floor(Math.random() * 10) > 5) {
                    console.log(Math.floor(Math.random() * 10));
                    $("#testSuccess").css('display', 'block');
                    $("testFail").css('display', 'none');
                    myFunction();
                } else {
                    console.log('Lesser Than 5');
                    $("#testSuccess").css('display', 'none');
                    $("testFail").css('display', 'block');
                }
            };

            function myFunction() {
                const x = new Date();
                console.log(x);
                console.log(x.getDate(), x.getMonth(), x.getFullYear());
                document.getElementById("myDate").value = (x.getDate()).toString() + "-" + (x.getMonth() + 1)
                    .toString() + '-' + (x.getFullYear()).toString();
            };

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
                    //window.location.replace('https://' + baseUrl +'/admin');
                    removeClass('loadingDiv1', 500);
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
            <!-- <% include ./Partials/FooterScript  %> -->
            <!-- <%- FooterJs %> -->
            <!-- App js -->
            <!-- <script src="public/assets/scripts/app.js"></script> -->
            <!-- <%- BottomJs %> -->
            <!--<script src="public/assets/scripts/jquery.min.js"></script>
<script src="public/assets/scripts/bootstrap.bundle.min.js"></script>
<script src="public/assets/scripts/metisMenu.min.js"></script>
<script src="public/assets/scripts/jquery.slimscroll.js"></script>
<script src="public/assets/scripts/waves.min.js"></script>


<script src="public/plugins/countdown/jquery.countdown.min.js"></script>
<script src="public/assets/pages/countdown.int.js"></script>

<script src="public/assets/scripts/app.js"></script>
<script src="test/scripts/scripts.js"></script>
<script src="test/scripts/modal.js"></script>
<script src="test/scripts/import.js"></script>-->

</body>

</html>