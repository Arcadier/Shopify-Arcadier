<?php
include 'callAPI.php';
include 'magento_functions.php';
include 'api.php';

$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$UserInfo = $arc->getUserInfo($_GET['user']);
$isMerchant = false;


//retrieve auth details
$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();

$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];
$packageId = getPackageID();

$auth = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop = $authDetails['Records'][0]['shop'];
$access_token = $authDetails['Records'][0]['access_token'];

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
        //echo "<pre>"; print_r($authListById);
        //echo "<pre>"; print_r($configListById);
        if(!empty($authListById['Records'])){
		$row=$authListById['Records'][0];
        $data_config = [
            [
              'Name'=> 'merchant_guid',
              'Operator'=> 'equal',
              'Value'=> $_GET['user']
            ],
            [
                'Name'=> 'shop',
                'Operator'=> 'equal',
                'Value'=> $row['shop']
            ]
          ];
        $configListById=$arc->searchTable($pack_id, 'config', $data_config);
		$configRowByMerchantGuid=$configListById['Records'][0];
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
    <title>Wireframe Designs - BootStrap</title>
    <meta content="Admin Dashboard" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="images/favicon.ico">

    <!-- css -->
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/metismenu.min.css" rel="stylesheet" type="text/css">
    <link href="css/icons.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
        integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css" />

    <!-- js -->
    <script src="scripts/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
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

    .foot-plugin-footer .footer {
        padding: 0;
        position: absolute;
        bottom: 0;
        width: inherit;
        /* margin: auto; */
        padding-left: 240px;
    }

    /* div.footer {
                display: none;
            } */

    .row {
        margin-left: 0px;
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

                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-12 p-2">
                                    <div class="mb-2">
                                        <span class="font-weight-bolder ">For this Merchant</span>
                                    </div>
                                    <div class="bg-white rounded pt-3 pb-3 pl-3 shadow">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="enable" name="status"
                                                value="1" checked
                                                <?php if(!empty($configRowByMerchantGuid['enabled'])){ if($configRowByMerchantGuid['enabled'] == '1'){echo 'checked';} }?>>
                                            <label class="custom-control-label" for="enable">Enable</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="disable" name="status"
                                                value="0"
                                                <?php if(!empty($configRowByMerchantGuid['enabled'])){ if($configRowByMerchantGuid['enabled'] == '0'){echo 'checked';} }?>>
                                            <label class="custom-control-label" for="disable">Disable</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-12 p-2">
                                <div class="row mt-3">
                                    <div class="col-3 mt-2">
                                        <label for="pwd" style="width: 107px;">Shopify Store name: </label>
                                    </div>
                                    <div class="col-8 pr-5 mt-2">
                                        <input type="text" class="form-control" id="store-name"
                                            placeholder="your-store.myshopify.com"
                                            value="<?php if(!empty($shop)) { echo $shop; } else { echo ''; } ?>"
                                            style="width: 113.5%; margin-bottom: 10px;">
                                    </div>
                                </div>
                                <div class="col-5 pr-5 mt-2">
                                    <button class="btn btn-info" type="submit" id="shopify-connect">Connect</button>
                                </div>

                                <div class="pr-5 mt-2"> Connection to
                                    <?php if(!empty($shop) && !empty($access_token)) { echo $shop.'.myshopify.com established.'; } else { echo 'any shop not found.'; } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/scripts.js"></script>
    <script src="scripts/metisMenu.min.js"></script>
    <script src="scripts/jquery.slimscroll.js"></script>
    <script src="scripts/waves.min.js"></script>
    <script src="scripts/app.js"></script>

    <script>
    var $j = jQuery.noConflict();
    $(document).ready(function() {

        console.log('new zip on staging')

        var min_sync_limit1 =
            '<?php if(!empty($configRowByMerchantGuid["min_sync_limit"])){echo $configRowByMerchantGuid["min_sync_limit"]; } ?>';
        $("#min_sync_limit").val(min_sync_limit1);

        myDialog = $j("#dialog").dialog({
            // dialog settings:
            //autoOpen : false,
            //
        });
        myDialog.dialog("close");

        $("#min_sync_limit").on("keyup", function(e) {
            e.preventDefault();
            //addLoader();
            var min_sync_limit = $("#min_sync_limit").val();
            var Id =
                '<?php if(!empty($configRowByMerchantGuid["Id"])){echo $configRowByMerchantGuid["Id"]; } ?>';
            var merchant_guid =
                '<?php if(!empty($configRowByMerchantGuid["merchant_guid"])){ echo $configRowByMerchantGuid["merchant_guid"]; }?>';

            var data = {
                min_sync_limit: min_sync_limit,
                Id: Id,
                merchant_guid: merchant_guid
            };
            $.ajax({
                async: false,
                url: 'ajaxrequest.php',
                type: "POST",
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(data) {
                    //removeClass('loadingDiv',500);

                    console.log(data);
                    if (data == 'min_sync_limit') {

                        $("#min_sync_limit_alert").css('display', 'inline');
                        $("#min_sync_limit_alert").text(
                            'Sync Limit saved Successfully');

                        //ShowCustomDialog('Alert','Sync Limit saved Successfully');
                    } else {
                        $("#min_sync_limit_alert").css('display', 'inline');
                        $("#min_sync_limit_alert").text(data);

                        //ShowCustomDialog('Alert',data);
                    }

                    setTimeout(function() {
                        $("#min_sync_limit_alert").css('display', 'none');
                    }, 7000);

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


    $(document).ready(function() {

        $("#hide_show").on('click', function() {
            var inputPasswordType = $('#pwd').attr('type');
            if (inputPasswordType == 'password') {
                $('#pwd').attr('type', 'text');
            } else {
                $('#pwd').attr('type', 'password');
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

    </div>
    </div>
</body>

</html>