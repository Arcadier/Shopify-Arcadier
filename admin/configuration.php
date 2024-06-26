<?php
   include 'magento_functions.php';
   include 'api.php';
   //include 'connect.php';
   require '../license/license.php';
   
   $baseUrl = getMarketplaceBaseUrl();
   $packageId = getPackageID();
   
   
   /* $licence = new License();
   if (!$licence->isValid()) {
       echo "<script>alert('Please subscribe first to use this plugin');</script>";
       $location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
       header('Location: ' . $location);
       exit();
   } */
   
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
   // echo $_GET['user'];
   // echo "<pre>"; print_r($UserInfo); die;
   $isMerchant = false;
   if(!empty($UserInfo)){
   foreach($UserInfo['Roles'] as $UserInfoRoles){
       if($UserInfoRoles == 'Admin'){
           $isMerchant = true;
       }
   }
   }else{
       header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
   }
   //die;
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
   		
   		//$authListById=$arc->searchTable($pack_id, 'auth', $data_auth);
   		$plugin_license_config1=$arc->listTableData($pack_id, 'plugin_license_config');
         if(!empty($plugin_license_config1['Records'])) $plugin_license_config = $plugin_license_config1['Records'][0];
         else $plugin_license_config = array();
   		//$configListById=$arc->searchTable($pack_id, 'config', $data1);
           //echo "<pre>"; print_r($authListById); die;
           //echo "<pre>"; print_r($configListById);
           /* if(!empty($authListById['Records'])){
   		$row=$authListById['Records'][0];
           $data_config = [
               [
                 'Name'=> 'merchant_guid',
                 'Operator'=> 'equal',
                 'Value'=> $_GET['user']
               ],
               [
                   'Name'=> 'domain',
                   'Operator'=> 'equal',
                   'Value'=> $row['domain']
               ]
             ];
           $configListById=$arc->searchTable($pack_id, 'config', $data_config);
   		$configRowByMerchantGuid=$configListById['Records'][0];
           } */
   		
       }else{
           //header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
       }
   }else{
       header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
   }
   
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

    .merchant_credentials {
        margin-bottom: 30px;
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
        margin-top: 20px;
    }

    .foot-plugin-footer .content-page .content {
        margin-bottom: 30px;
    }

    .foot-plugin-footer .footer {
        padding: 0;
    }

    .foot-plugin-footer ul.footer-social-media {
        display: none;
    }

    /* div.footer {
         display: none;
         } */
    .row {
        margin-left: 0px;
    }

    #wrapper {
        overflow: unset;
    }

    .side-menu {
        width: 170px;
    }


    .content>.container-fluid {
        padding: 0;
    }

    .page-content {
        padding-right: 0;
    }

    /* .sidebar {
    flex: unset;
    max-width: 240px;
    width: 240px;
}
div#wrapper {
    display: block;
}
.main-content.active {
    margin-left: 20px !important;
}
.main-content {
    margin-left: 230px !important;
}
div#main {
    max-width: 100%;
} */

    div#wrapper .leftp.side-menup {
        position: absolute;
    }

    div#wrapper {
        padding-left: 0;
        padding-right: 0;
    }

    div#main {
        flex: unset;
        max-width: 100%;
        margin-left: 340px;
        background: #f1f1f1;
    }

    ul.section-links li a {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .content-page {
        margin-top: -20px;
        margin-left: 240px;
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

    <div class="gutter-wrapper" id="wrapper">
        <!-- <% include ./Partials/adminHeader  %> -->
        <!--<div class="topbar">
            
            <div class="topbarp-left">
               <a href="index" class="logo">
               </a>
            </div>
            <nav class="navbar-custom">
               <ul class="navbar-right d-flex list-inline float-right mb-0">
                  <li class="dropdown notification-list d-none d-md-block">
                  </li>
                  \
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
         </div>-->
        <!-- <% include ./Partials/Sidebar  %> -->
        <!-- ========== Left Sidebar Start ========== -->
        <div class="leftp side-menup">
            <div class="slimscroll-menu" id="remove-scroll">
                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu" id="side-menu">
                        <!--<li class="menu-title">Main</li>-->

                        <li>
                            <a href="index.php" class="waves-effect">
                                <img src="images/home-icon.png"> <span> Home </span>
                            </a>
                        </li>
                        <li>
                            <a href="configuration.php?user=<?php echo $_GET['user']; ?>" class="waves-effect">
                                <img src="images/setting-icon.png"> <span> Configuration </span>
                            </a>
                        </li>

                        <li>
                            <a href="trial-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/list-icon.png"> <span> Trial List </span></a>
                        </li>
                        <li>
                            <a href="paid-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect"> <img
                                    src="images/list-icon.png"> <span> Paid List </span></a>
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
                                <h4 class="page-title">Configuration</h4>
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

                        </div>
                    </div>
                    <!-- end row -->
                    <!-- <%- body %> -->
                    <div class="row">
                        <!--<div class="col-12">
                        <div class="row">
                           <div class="col-12 col-md-12 p-2">
                              <div class="mb-2">
                                 <span class="font-weight-bolder ">For this Merchant</span>
                              </div>
                              <div class="bg-white rounded pt-3 pb-3 pl-3 shadow">
                                 <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="enable" name="status" value="1" <?php if(!empty($configRowByMerchantGuid['enabled'])){ if($configRowByMerchantGuid['enabled'] == '1'){echo 'checked';} }?> >
                                    <label class="custom-control-label" for="enable">Enable</label>
                                 </div>
                                 <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="disable" name="status" value="0" <?php if(!empty($configRowByMerchantGuid['enabled'])){ if($configRowByMerchantGuid['enabled'] == '0'){echo 'checked';} }?>>
                                    <label class="custom-control-label" for="disable">Disable</label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-12 mb-2">
                        <div class="row">
                           <div class="col-12 col-md-12 p-2">
                              <div class="mb-2 ">
                                 <span class="font-weight-bolder ">Mode</span>
                              </div>
                              <div class="col-12 bg-white p-3 rounded shadow">
                                 <div class="mb-2">
                                    <span class="font-weight-bolder">Mode</span>
                                 </div>
                                 <div class="row pl-2">
                                    <div class="col-12 col-md-8 mb-3 bg-light p-2 rounded">
                                       <div class="row w-100">
                                          <div class="col-1">
                                             <div class="custom-control custom-radio text-center mt-3" >
                                                <input type="radio" class="custom-control-input" id="ma" name="mode" value="0" checked <?php //if(!empty($configRowByMerchantGuid['mode'])){if($configRowByMerchantGuid['mode'] == '0'){echo 'checked';} }?> >
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
                                                <span class="font-weight-bolder">Arcadier -> Magento</span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-12 col-md-8 bg-light p-2 rounded">
                                       <div class="row w-100">
                                          
                                          <div class="col-7">
                                             Contact the developer to obtain a license for this version
                                          </div>
                                       </div>
                                      
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>-->
                        <div class="row">
                            <div class="col-12 col-md-12 p-2">
                                <div class="mb-2">
                                    <span class="font-weight-bolder ">License Credentials</span>
                                </div>


                                <div class="bg-white rounded pt-3 pb-3 pl-3 shadow">

                                    <div class="mb-2">
                                        <span class="font-weight-bolder ">Subscription Credentials</span>
                                    </div>
                                    <div class="row mt-3 merchant_credentials">
                                        <div class="col-3">
                                            <label for="total_trial_days">Total Trial Days: </label>
                                        </div>
                                        <div class="col-9 pr-5">
                                            <input type="text" class="form-control" id="total_trial_days"
                                                value="<?php if(!empty($plugin_license_config)) { echo $plugin_license_config["total_trial_days"]; }else{ echo TOTAL_TRIAL_DAYS; } ?>">
                                        </div>

                                        <div class="col-3 mt-2">
                                            <label for="subscription_amount">Subscription Amount: </label>
                                        </div>
                                        <div class="col-9 pr-5 mt-2">
                                            <input type="text" class="form-control" id="subscription_amount"
                                                value="<?php if(!empty($plugin_license_config)) { echo $plugin_license_config["subscription_amount"]; }else{ echo SUBSCRIPTION_AMOUNT; } ?>">
                                        </div>
                                        <!--<div class="col-3 mt-2">
                                      <label for="pwd">Domain: </label>
                                  </div>
                                  <div class="col-9 pr-5 mt-2">
                                      <input type="text" class="form-control" id="merchant_domain" value="<?php //if(!empty($row)) { echo $row["arc_domain"]; } ?>">
                                  </div>-->









                                    </div>
                                    <div class="mb-2">
                                        <span class="font-weight-bolder ">Stripe Credentials</span>
                                    </div>
                                    <div class="row mt-3">
                                        <!--<div class="col-3" style="display:none;">
                                 <label for="stripe_publishable_key1">Publishable Key: </label>
                              </div>
                              <div class="col-9 pr-5" style="display:none;">
                                 <input type="text" class="form-control" id="stripe_publishable_key1" value="<?php //if(!empty($row)) { echo $row["username"]; } ?>">
                              </div>
                              <div class="col-3 mt-2" style="display:none;">
                                 <label for="stripe_secret_key1">Secret Key: </label>
                              </div>
                              <div class="col-8 pr-5 mt-2" style="display:none;">
                                 <input type="password" class="form-control" id="stripe_secret_key1" value="<?php //if(!empty($row)) { echo $row["password"]; } ?>" style="width: 113.5%;">
                              </div>
                              <div class="col-1 pr-5 mt-3" style="display:none;">
                                 <a class="waves-effect" id="hide_show1">   <img src="images/view_icon.png" style="border: 1px solid; padding: 2px;"> <span> </span></a>
                              </div>
                              <div class="col-3 mt-2" style="display:none;">
                                 <label for="subscription_plan_id1">Subscription Plan Id: </label>
                              </div>
                              <div class="col-9 pr-5 mt-2" style="display:none;">
                                 <input type="text" class="form-control" id="subscription_plan_id1" value="<?php //if(!empty($row)) { echo $row["domain"]; } ?>">
                              </div>-->


                                        <div class="col-3">
                                            <label for="stripe_publishable_key">Publishable Key: </label>
                                        </div>
                                        <div class="col-9 pr-5">
                                            <input type="text" class="form-control" id="stripe_publishable_key"
                                                value="<?php if(!empty($plugin_license_config)) { echo $plugin_license_config["stripe_publishable_key"]; }else{ echo STRIPE_PUBLISHABLE_KEY; } ?>">
                                        </div>
                                        <div class="col-3 mt-2">
                                            <label for="stripe_secret_key">Secret Key: </label>
                                        </div>
                                        <div class="col-8 pr-5 mt-2">
                                            <input type="password" class="form-control" id="stripe_secret_key"
                                                value="<?php if(!empty($plugin_license_config)) { echo $plugin_license_config["stripe_secret_key"]; }else{ echo STRIPE_SECRET_KEY; } ?>"
                                                style="width: 113.5%;">
                                        </div>
                                        <div class="col-1  pr-5 mt-3 px-0">
                                            <a class="waves-effect" id="hide_show"> <img src="images/view_icon.png"
                                                    style="border: 1px solid; padding: 2px;"> <span> </span></a>
                                        </div>
                                        <div class="col-3 mt-2">
                                            <label for="subscription_plan_id">Subscription Plan Id: </label>
                                        </div>
                                        <div class="col-9 pr-5 mt-2">
                                            <input type="text" class="form-control" id="subscription_plan_id"
                                                value="<?php if(!empty($plugin_license_config)) { echo $plugin_license_config["stripe_subscription_plan_id"]; }else{ echo STRIPE_SUBSCRIPTION_PLAN_ID; } ?>">
                                        </div>

                                        <!--<div class="col-3 mt-2">
                                 <label for="test">Test: </label>
                              </div>
                              <div class="col-5 pr-5 mt-2">
                                 <button class="btn btn-info" type="submit" id="testBtn" onclick="testPerform()">TEST</button>
                              </div>
                              <div class="col-4 pr-5 mt-2">
                                 <//?php
                                 if(isset($_COOKIE['mag_token'])){
                                 ?>
                                 <button id="testSuccess" class="btn btn-success" >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                       <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                    </svg>
                                 </button>
                                 <//?php
                                 }else{
                                 ?>
                                 
                                 <button id="testFail" class="btn btn-danger"    >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                       <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                    </svg>
                                 </button>
                                 <//?php
                                 }
                                 ?>
                                 <//?php
                                 if(isset($_COOKIE['mag_token'])){
                                 ?>
                                 
                                 <//?php
                                 }
                                 ?>
                                 
                                 <//?php
                                 }
                                 ?>
                              </div>
                              <div class="col-3 mt-2">
                                 <label for="test">Connect: </label>
                              </div>
                              <div class="col-5 pr-5 mt-2">
                                 <button class="btn btn-info" type="submit" id="testBtn" onclick="connectPerform()">Connect</button>
                              </div>
                              <div class="col-4 pr-5 mt-2">
                                 <//?php
                                 if(isset($_COOKIE['auth'])){
                                 ?>
                                 <button id="connectSuccess" class="btn btn-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                       <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                    </svg>
                                 </button>
                                 <//?php
                                 }else{
                                 ?>
                                 
                                 <button id="connectFail" class="btn btn-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                       <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                    </svg>
                                 </button>
                                 <//?php
                                 }
                                 ?>
                                 <//?php
                                 if(isset($_COOKIE['auth'])){
                                 ?>
                                 
                                 <//?php
                                 }
                                 ?>
                                 
                                 <//?php
                                 }
                                 ?>
                              </div>
                              <div class="col-3 mt-2">
                                 <label for="myDate">Connect: </label>
                              </div>
                              <div class="form-group mx-sm-3 mb-2 mt-2">
                                 <input type="text" class="form-control" value="<?php // if(!empty($row)) { if($row["auth_status"] == '1'){ echo $arc->timestamp_to_datetime($row["ModifiedDateTime"],'+5 hour +30 minutes'); }  } ?>" id="myDate" readonly>
                              </div>-->

                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-3">
                                            <label for="test">Save: </label>
                                        </div>
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-primary mb-2"
                                                onclick="savePerform()">Save</button>
                                            <!--<div class="form-group mx-sm-3 mb-2">
                                    <input type="checkbox" id="deleteField"> 
                                    <label for="deleteField">Delete</label>
                                 </div>-->
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!--<div class="row">
                     <div class="col-12 col-md-12 p-2">
                        <div class="bg-white rounded pt-3 pb-3 pl-3 shadow">
                           <div class="text-center">
                              DO NOT SYNC ITEMS WITH LESS THAN <span>
                              <input type="text" style="width: 35px; border-radius: 5px;" name="min_sync_limit" id="min_sync_limit" value="">
                              </span> IN STOCK. <span class="alert alert-success" style="display:none;" id="min_sync_limit_alert">Min Sync Limit Changed Successfully</span>
                           </div>
                           
                        </div>
                     </div>
                  </div>-->
                    </div>
                </div>
            </div>
            <!-- </div> -->
            <!-- </div>
            </div> -->
            <!-- <% include ./Partials/Footer %> -->
            <!--- <footer class="footer text-center">
            © 2021.
            </footer>--->
            <!--<script src="public/assets/scripts/jquery.min.js"></script>
            <script src="public/assets/scripts/bootstrap.bundle.min.js"></script>
            <script src="public/assets/scripts/metisMenu.min.js"></script>
            <script src="public/assets/scripts/jquery.slimscroll.js"></script>
            <script src="public/assets/scripts/waves.min.js"></script>
            <script src="public/assets/scripts/app.js"></script>-->
            <script src="scripts/metisMenu.min.js"></script>
            <script src="scripts/jquery.slimscroll.js"></script>
            <script src="scripts/waves.min.js"></script>
            <script src="scripts/app.js"></script>
            <!--<script src="scripts/dashboard.js"></script>-->
            <!--<script src="public/assets/scripts/jquery.min.js"></script>
            <script src="public/assets/scripts/bootstrap.bundle.min.js"></script>-->
            <script src="scripts/metisMenu.min.js"></script>
            <script src="scripts/jquery.slimscroll.js"></script>
            <script src="scripts/waves.min.js"></script>
            <!-- countdown -->
            <!--<script src="public/plugins/countdown/jquery.countdown.min.js"></script>-->
            <!--<script src="scripts/countdown.int.js"></script>-->
            <!-- App js -->
            <script src="scripts/app.js"></script>
            <script>
            var $j = jQuery.noConflict();
            $(document).ready(function() {

                var min_sync_limit1 =
                    '<?php if(!empty($configRowByMerchantGuid["min_sync_limit"])){echo $configRowByMerchantGuid["min_sync_limit"]; } ?>';
                $("#min_sync_limit").val(min_sync_limit1);



                //$(".chosen-select").chosen({width: "125px"});

                /* $('input[type=checkbox]').click(function () {
                	if (!$(this).is(':checked')) {
                		$('#'+this.id).prop('checked',false);
                	}
                }); */

                myDialog = $j("#dialog").dialog({
                    // dialog settings:
                    //autoOpen : false,
                    // ... 
                });
                myDialog.dialog("close");


                /* $("#min_sync_limit").on("keyup", function(e) {
                                    e.preventDefault();
                                //addLoader();
                                var min_sync_limit = $("#min_sync_limit").val();
                                var Id='<?php if(!empty($configRowByMerchantGuid["Id"])){echo $configRowByMerchantGuid["Id"]; } ?>';
            					var merchant_guid='<?php if(!empty($configRowByMerchantGuid["merchant_guid"])){ echo $configRowByMerchantGuid["merchant_guid"]; }?>';
                               
                                var data = {min_sync_limit:min_sync_limit,Id:Id,merchant_guid:merchant_guid};
            					$.ajax({
                                    async: false,
            						url: 'ajaxrequest.php',
            						type: "POST",
                                    contentType: 'application/json',
                                    data: JSON.stringify(data),
            						success: function(data) {
            							//removeClass('loadingDiv',500);
                                        
                                        console.log(data);
            							if(data=='min_sync_limit'){
                                            
                                            $("#min_sync_limit_alert").css('display','inline');
                                            $("#min_sync_limit_alert").text('Sync Limit saved Successfully');
                                            
            								//ShowCustomDialog('Alert','Sync Limit saved Successfully');
            							}else{
                                            $("#min_sync_limit_alert").css('display','inline');
                                            $("#min_sync_limit_alert").text(data);
                                            
            								//ShowCustomDialog('Alert',data);
            							} 
            							 
                                        setTimeout(function(){ $("#min_sync_limit_alert").css('display','none'); }, 7000);
            							
            						}
            					});
                            }); */


                var auth1 =
                    "<?php if(!empty($row)){if($row['auth_status'] == '1'){ echo 'auth'; }else{ echo ''; } } ?>";
                if (auth1 != '') {
                    $('#connectFail').css("display", "none");
                    $('#connectSuccess').css("display", "block");
                    $('#testFail').css("display", "none");
                    $('#testSuccess').css("display", "block");
                } else {
                    $('#connectFail').css("display", "block");
                    $('#connectSuccess').css("display", "none");
                    $('#testFail').css("display", "block");
                    $('#testSuccess').css("display", "none");
                }





                var disabled = $('#disable').val();
                var enabled = $('#enable').val();
                var Id =
                    '<?php if(!empty($configRowByMerchantGuid["Id"])){echo $configRowByMerchantGuid["Id"]; } ?>';
                var merchant_guid =
                    '<?php if(!empty($configRowByMerchantGuid["merchant_guid"])){ echo $configRowByMerchantGuid["merchant_guid"]; }?>';
                $(document).on("click", "#disable", function() {
                    addLoader();
                    var data = {
                        disabled: disabled,
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
                            if (data == 'Disabled') {

                                $('#disable').prop("checked", true);
                                $('#enable').prop("checked", false);


                                //alert('Disabled Successfully');
                                ShowCustomDialog('Alert', 'Disabled Successfully');

                            } else {
                                //alert('Unable to disable');
                                //alert(data);
                                ShowCustomDialog('Alert', data);


                            }


                        }
                    });
                });
                $(document).on("click", "#enable", function() {
                    addLoader();
                    var data = {
                        enabled: enabled,
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
                            if (data == 'Enabled') {

                                $('#enable').prop('checked', true);
                                $('#disable').prop('checked', false);

                                //alert('Enabled Successfully');
                                ShowCustomDialog('Alert', 'Enabled Successfully');

                            } else {
                                //alert('Unable to enable');
                                //alert(data);
                                ShowCustomDialog('Alert', data);


                            }
                        }
                    });
                });

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
                                    'Magento To Arcadier Mode Done Successfully');


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
                                    'Arcadier To Magento Mode Done Successfully');


                            } else {
                                //alert('Unable to Change Mode AM');
                                //alert(data);
                                ShowCustomDialog('Alert', data);


                            }
                        }
                    });
                });





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


            function clearAll() {
                addLoader();
                // var m_username=$('#merchant_username').val();
                // var m_password=$('#merchant_password').val();
                // var m_domain=$('#merchant_domain').val();
                var username = $('#usr').val();
                var password = $('#pwd').val();
                var domain1 = $('#domain').val();
                var del = $('#deleteField').is(":checked");
                var arc_user = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';

                if (del == true) {
                    //data1={username:username,password:password,m_domain:m_domain,domain1:domain1,m_username:m_username,m_password:m_password,del:del,deauth:'deauth',arc_user:arc_user};
                    data1 = {
                        username: username,
                        password: password,
                        domain1: domain1,
                        del: del,
                        deauth: 'deauth',
                        arc_user: arc_user
                    };
                    //data1={username:username,password:password,domain1:domain1,del:del};
                } else {
                    //data1={username:username,password:password,domain1:domain1,m_username:m_username,m_password:m_password,deauth:'deauth',arc_user:arc_user};
                    data1 = {
                        username: username,
                        password: password,
                        domain1: domain1,
                        deauth: 'deauth',
                        arc_user: arc_user
                    };
                    //data1={username:username,password:password,domain1:domain1};
                }
                //return;
                $.ajax({
                    //url: 'authentication.php',
                    url: 'ajaxrequest.php',
                    type: "POST",
                    contentType: 'application/json',
                    data: JSON.stringify(data1),
                    success: function(data) {
                        removeClass('loadingDiv', 500);
                        console.log(data);
                        if (data == 'Disconnected') {
                            if (del == true) {
                                document.getElementById('usr').value = '';
                                document.getElementById('pwd').value = '';
                                document.getElementById('domain').value = '';
                                // document.getElementById('merchant_username').value = '';
                                // document.getElementById('merchant_password').value = '';
                                // document.getElementById('merchant_domain').value = '';
                                document.getElementById('myDate').value = '';
                            }
                            $("#connectSuccess").css('display', 'none');
                            $("#connectFail").css('display', 'block');
                            $("#testSuccess").css('display', 'none');
                            $("#testFail").css('display', 'block');
                            //alert('Disconnected');
                            var message = 'Disconnected successfully';
                            ShowCustomDialog('Alert', message);
                        } else {
                            //alert('Not Disconnected');
                            var message = 'Unable to Disconnect';
                            ShowCustomDialog('Alert', message);
                        }
                    }
                });
            }

            function timestamp_to_datetime(timestamp, hours, minutes) {
                var date = new Date(timestamp * 1000);
                date.setHours(date.getHours() + hours);
                date.setMinutes(date.getMinutes() + minutes);
                var iso = date.toISOString().match(/(\d{4}\-\d{2}\-\d{2})T(\d{2}:\d{2}:\d{2})/);
                var myDate = iso[1] + ' ' + iso[2];
                return myDate;
            }

            function testPerform() {
                addLoader();
                // var m_username=$('#merchant_username').val();
                // var m_password=$('#merchant_password').val();
                // var m_domain=$('#merchant_domain').val();
                var usr = $('#usr').val();
                var pwd = $('#pwd').val();
                var domain = $('#domain').val();
                if (usr == '' || pwd == '' || domain == '') {
                    var alert_message = 'Please Fill These Fields: <br>';
                    var newLine = "\r\n";
                    alert_message += newLine;

                    // if(m_username==''){
                    // alert_message += "Merchant username can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    // if(m_password==''){
                    // alert_message += "Merchant password can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    // if(m_domain==''){
                    // alert_message += "Merchant domain can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    if (usr == '') {
                        alert_message += "Username can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (pwd == '') {
                        alert_message += "Password can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (domain == '') {
                        alert_message += "Domain can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    //console.log(alert_message);
                    ShowCustomDialog('Alert', alert_message);
                    removeClass('loadingDiv', 500);
                    return false;
                }
                //var pwd1=$('#pwd1').val();

                //var data = {usr:usr,pwd:pwd,domain:domain,m_username:m_username,m_password:m_password,m_domain:m_domain,test:'test'};
                var data = {
                    usr: usr,
                    pwd: pwd,
                    domain: domain,
                    test: 'test'
                };
                $.ajax({
                    //url: 'authentication.php',
                    url: 'ajaxrequest.php',
                    type: "POST",
                    //data: {usr:usr,pwd:pwd,domain:domain},
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(data) {
                        removeClass('loadingDiv', 500);
                        var obj = JSON.parse(data);
                        console.log(obj);
                        if (obj.message == 'Successful') {
                            $("#testSuccess").css('display', 'block');
                            $("#testFail").css('display', 'none');
                            //$('#myDate').val(obj.row1['ModifiedDateTime']);
                            //alert('Successful');
                            var message = 'All credentials verfied. You can now proceed to connect.';
                            ShowCustomDialog('Alert', message);
                        } else {
                            $("#testSuccess").css('display', 'none');
                            $("#testFail").css('display', 'block');
                            //alert('Unable to Authenticate');
                            //alert(obj.message);
                            var message = obj.message;
                            ShowCustomDialog('Alert', message);
                        }
                    }
                });
            };

            function connectPerform() {
                addLoader();
                // var m_username=$('#merchant_username').val();
                // var m_password=$('#merchant_password').val();
                // var m_domain=$('#merchant_domain').val();
                var usr = $('#usr').val();
                var pwd = $('#pwd').val();
                var domain = $('#domain').val();
                if (usr == '' || pwd == '' || domain == '') {
                    var alert_message = 'Please Fill These Fields: <br>';
                    var newLine = "\r\n";
                    alert_message += newLine;

                    // if(m_username==''){
                    // alert_message += "Merchant username can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    // if(m_password==''){
                    // alert_message += "Merchant password can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    // if(m_domain==''){
                    // alert_message += "Merchant domain can not be left blank. <br>";
                    // alert_message += newLine;
                    // }
                    if (usr == '') {
                        alert_message += "Username can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (pwd == '') {
                        alert_message += "Password can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (domain == '') {
                        alert_message += "Domain can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    //console.log(alert_message);
                    ShowCustomDialog('Alert', alert_message);
                    removeClass('loadingDiv', 500);
                    return false;
                }
                //var pwd1=$('#pwd1').val();
                var arc_user = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                //var data = {usr:usr,pwd:pwd,domain:domain,m_username:m_username,m_password:m_password,m_domain:m_domain,auth:'auth',arc_user:arc_user};
                var data = {
                    usr: usr,
                    pwd: pwd,
                    domain: domain,
                    auth: 'auth',
                    arc_user: arc_user
                };
                $.ajax({
                    //url: 'authentication.php',
                    url: 'ajaxrequest.php',
                    type: "POST",
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    //data: {usr:usr,pwd:pwd,domain:domain},
                    success: function(data) {
                        removeClass('loadingDiv', 500);
                        //console.log(data);  return false;
                        var obj = JSON.parse(data);


                        if (obj.message == 'Authenticated') {
                            $("#connectSuccess").css('display', 'block');
                            $("#connectFail").css('display', 'none');
                            $("#testSuccess").css('display', 'block');
                            $("#testFail").css('display', 'none');
                            //  $('#merchant_username').val(obj.row1['arc_username']);
                            //  $('#merchant_password').val(obj.row1['arc_password']);
                            //  $('#merchant_domain').val(obj.row1['arc_domain']);
                            $('#usr').val(obj.row1['username']);
                            $('#pwd').val(obj.row1['password']);
                            $('#domain').val(obj.row1['domain']);

                            var timestamp = obj.row1['ModifiedDateTime'];
                            var myDate = timestamp_to_datetime(timestamp, 5, 30);

                            //$('#myDate').val(obj.row1['ModifiedDateTime']);
                            $('#myDate').val(myDate);
                            //alert('Authenticated');
                            var message = 'Connection Established';
                            ShowCustomDialog('Alert', message);
                        } else {
                            $("#connectSuccess").css('display', 'none');
                            $("#connectFail").css('display', 'block');
                            $("#testSuccess").css('display', 'none');
                            $("#testFail").css('display', 'block');
                            //alert('Unable to Authenticate');
                            //alert(obj.message);
                            var message = obj.message;
                            ShowCustomDialog('Alert', message);
                        }
                    }
                });
            };

            function myFunction() {
                const x = new Date();
                console.log(x);
                console.log(x.getDate(), x.getMonth(), x.getFullYear());
                document.getElementById("myDate").value = (x.getDate()).toString() + "-" + (x.getMonth() + 1)
                    .toString() + '-' + (x.getFullYear()).toString();
            };

            function deleteIt() {

            };




            function savePerform() {
                addLoader();
                var total_trial_days = $('#total_trial_days').val();
                var subscription_amount = $('#subscription_amount').val();
                var stripe_publishable_key = $('#stripe_publishable_key').val();
                var stripe_secret_key = $('#stripe_secret_key').val();
                var subscription_plan_id = $('#subscription_plan_id').val();
                if (total_trial_days == '' || subscription_amount == '' || stripe_publishable_key == '' ||
                    stripe_secret_key == '' || subscription_plan_id == '') {
                    var alert_message = 'Please Fill These Fields: <br>';
                    var newLine = "\r\n";
                    alert_message += newLine;

                    if (total_trial_days == '') {
                        alert_message += "total_trial_days can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (subscription_amount == '') {
                        alert_message += "subscription_amount can not be left blank. <br>";
                        alert_message += newLine;
                    }

                    if (stripe_publishable_key == '') {
                        alert_message += "stripe_publishable_key can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (stripe_secret_key == '') {
                        alert_message += "stripe_secret_key can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    if (subscription_plan_id == '') {
                        alert_message += "subscription_plan_id can not be left blank. <br>";
                        alert_message += newLine;
                    }
                    //console.log(alert_message);
                    ShowCustomDialog('Alert', alert_message);
                    removeClass('loadingDiv', 500);
                    return false;
                }


                var data = {
                    s_p_k: stripe_publishable_key,
                    s_s_k: stripe_secret_key,
                    s_s_p_id: subscription_plan_id,
                    s_amount: subscription_amount,
                    t_t_days: total_trial_days,
                    plugin_license_config_req: 'plugin_license_config_req'
                };
                $.ajax({
                    url: 'ajaxrequest.php',
                    type: "POST",
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        removeClass('loadingDiv', 500);
                        ShowCustomDialog('Alert', response);
                    }
                });

            };






            $(document).ready(function() {
                //$('#stripe_publishable_key').val('');
                //$('#stripe_secret_key').val('');

                $("#hide_show").on('click', function() {
                    var inputPasswordType = $('#stripe_secret_key').attr('type');
                    if (inputPasswordType == 'password') {
                        $('#stripe_secret_key').attr('type', 'text');
                    } else {
                        $('#stripe_secret_key').attr('type', 'password');
                    }
                });



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
            <!-- <script src="public/assets/pages/dashboard.js"></script>-->
        </div>

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
         
         <script src="public/assets/scripts/app.js"></script>-->
</body>

</html>