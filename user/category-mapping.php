<?php
include 'shopify_functions.php';
include_once 'api.php';

$arcadier = new ApiSdk();
$plugin_id = getPackageID();
$UserInfo = $arcadier->getUserInfo($_GET['user']);
$isMerchant = false;
$isMerchantAuth = 'No';

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
                'Value'=> '1'
            ]
        ];
        
        //get Merchant credentials
        $authListById = $arcadier->searchTable($plugin_id, 'auth', $data_auth);
        
        if(!empty($authListById['Records'])){
            
            $credentials = $authListById['Records'][0];

            //get Shopify ProductTypes
            $shopify_categories = shopify_categories($credentials['access_token'], $credentials['shop']);
            //error_log("Category List: ".json_encode($shopify_categories));

            $count = count($shopify_categories);
            
            //get Arcadier Categories
            $arcadier_categories = $arcadier->getCategories();

            //get Shopify-Arcadier Category Map
            $data1 = [
                [
                    'Name' => 'merchant_guid',
                    'Operator' => 'equal',
                    'Value' => $_GET['user'],
                ],
                [
                    'Name' => 'shop',
                    'Operator' => 'equal',
                    'Value' => $credentials['shop'],
                ],
            ];

            $map = $arcadier->searchTable($plugin_id, 'map', $data1);


            if($authListById['Records'][0]['auth_status'] == '1'){
                $isMerchantAuth = 'Yes';
            }else{
                $isMerchantAuth = 'No';
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
    <title>Wireframe Designs - BootStrap</title>
    <meta content="Admin Dashboard" name="description" />
    <meta content="Themesbrand" name="author" />
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

    input[type=checkbox],
    input[type=radio] {
        visibility: unset;
    }

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

    .nav-link:active {
        background-color: white;
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
        </div>
        <!-- Left Sidebar End -->
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
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
                        </div>
                    </div>

                    <div class="row category h-100">
                        <div class="col-6 font-weight-bolder" style="font-size: 20px; text-align: center;">
                            Shopify Product Types
                        </div>
                        <div class="col-6 font-weight-bolder" style="font-size: 20px; text-align: center;">
                            Map to Arcadier Categories
                        </div>

                        <!-- Display Shopify Product Types -->
                        <div style="overflow: auto;" class="col-6 p-0 mt-2">
                            <ul class="nav flex-column" role="tablist">
                                <?php 
                                    foreach($shopify_categories as $shopify_category){ 
                                        if(1){
                                            ?>
                                <a style="margin-left: 7px;"
                                    class="nav-link mt-3 mb-3 <?php if(!next($shopify_categories)){ echo active; } ?>"
                                    data-toggle="tab" href="#a<?php 
                                                //removes whitespaces and symbols, if any 
                                                if(preg_match('/\s/',$shopify_category)){
                                                    $shopify_category_nospace = str_replace(' ', '_', $shopify_category);
                                                    $shopify_category_nospace = str_replace('&', 'And', $shopify_category_nospace);
                                                    $shopify_category_nospace = str_replace("'", "-", $shopify_category_nospace);
                                                    echo $shopify_category_nospace.'_category';
                                                }
                                                else{
                                                    echo $shopify_category.'_category'; 
                                                }
                                                ?>">
                                    <?php 
                                                    echo $shopify_category; 
                                                ?>
                                </a>
                                <?php 
                                        }?>
                                <?php 
                                }?>
                            </ul>
                        </div>

                        <div class="col-6 p-0 tab-content-box mt-2">
                            <div class="tab-content" style="height: inherit;">
                                <?php foreach($shopify_categories as $shopify_category){
                                    //removes whitespaces and symbols, if any 
                                    if(preg_match('/\s/',$shopify_category)){
                                        $shopify_div_ids = str_replace(' ', '_', $shopify_category);
                                        $shopify_div_ids = str_replace('&', 'And', $shopify_div_ids);
                                        $shopify_div_ids = str_replace("'", "-", $shopify_div_ids);
                                        $shopify_div_ids = $shopify_div_ids.'_category';
                                    }
                                    else{
                                        $shopify_div_ids = $shopify_category.'_category';
                                    }
                                    
                                    $shopify_category_id = $shopify_category.'_category';
                                    $shopify_category_id = str_replace(' ', '_',$shopify_category_id);
                                    $shopify_category_id = str_replace('&', 'And', $shopify_category_id);
                                    $shopify_category_id = str_replace("'", "-", $shopify_category_id);
                                    
                                    if(1){?>
                                <div id="a<?php echo $shopify_div_ids ?>" class="container tab-pane">
                                    <div class="font-weight-bolder mt-3 mb-3">
                                        <?php echo "Shopify product type ".$shopify_category." goes to which category?"; ?>
                                    </div>
                                    <form class="save_map_form category-div">
                                        <?php
                                                if(!empty($arcadier_categories)){
                                                    foreach($arcadier_categories['Records'] as $arcadier_category){
                                                        ?>
                                        <div class="custom-control custom-checkbox mt-3 mb-3"
                                            id="divison<?php echo $shopify_div_ids; ?>">
                                            <input type="checkbox" <?php 
                                                                if ($map['Records'][0]['merchant_guid'] == $_GET['user']) {
                                                                    $map_arr_unserialize = unserialize($map['Records'][0]['map']);
                                                                    $list = $map_arr_unserialize['list'];
                                                                    foreach($list as $li){ 
                                                                        if(preg_match('/-/',$li['shopify_category'])){
                                                                            $apostrophe_check = str_replace("-", "'", $li['shopify_category']);
                                                                        }
                                                                        else{
                                                                            $apostrophe_check = $shopify_category_id;
                                                                        }
                                                                        if($li['shopify_category'] == $apostrophe_check){
                                                                            foreach($li['arcadier_guid'] as $arcadier_id){
                                                                                if($arcadier_category['ID'] == $arcadier_id){
                                                                                    echo checked;
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }?> name="arc_category[]" class="arc_category"
                                                id="<?php echo $arcadier_category['ID'];?>" />
                                            <label class="" for=""><?php echo $arcadier_category['Name']; ?></label>
                                        </div>
                                        <?php   
                                                    } 
                                                }
                                                ?>
                                        <div class="div">

                                            <div class="div"></div>
                                        </div>
                                        <div style="position: absolute;bottom:20px;">
                                            <div class="mt-5 mb-3" style="font-weight: 500;">
                                                Submit your mapping choice
                                                for each Shopify category:</div>
                                            <a id="save_map"
                                                <?php 
                                                    if(preg_match('/\s/',$shopify_category)){ 
                                                        $escaped_category_name = $shopify_category_id.'>'.$shopify_div_ids; 
                                                    } else { 
                                                        $escaped_category_name = $shopify_category_id;
                                                    } 
                                                ?>
                                                onclick="save_mapp('<?php echo $escaped_category_name; ?>');"
                                                style="border: #0e77d4;box-sizing: border-box;background-color: #333547;border-radius: 6px;color: white;padding: 5px 10px;font-size: 14px; cursor: pointer;">Submit
                                                Mapping for <?php echo $shopify_category ?></a>
                                        </div>
                                    </form>
                                </div>
                                <?php 
                                    } 
                                } 
			                    ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="scripts/metisMenu.min.js"></script>
            <script src="scripts/jquery.slimscroll.js"></script>
            <script src="scripts/waves.min.js"></script>
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

            function removeClass(div_id, time) {
                $("#" + div_id).fadeOut(time, function() {
                    $("#" + div_id).remove();
                });
            }

            function save_mapp(shopify_category_id) {
                console.log(shopify_category_id);
                var isMerchantAuth = '<?php echo  $isMerchantAuth; ?>';

                if (isMerchantAuth == 'Yes') {
                    addLoader();

                    if (shopify_category_id.includes(">")) {
                        shopify_category_name = shopify_category_id.split('>')[0];
                        shopify_div = shopify_category_id.split('>')[1];
                        shopify_div = shopify_div.replace("'", "-")

                        shopify_category_name = shopify_category_name.replace("_", " ");
                        shopify_category_name = shopify_category_name.replace("And", "&");
                        shopify_category_name = shopify_category_name.replace("-", "'");

                    } else {
                        shopify_div = shopify_category_id;
                        shopify_category_name = shopify_category_id;
                    }

                    console.log("Div id: " + shopify_div);
                    console.log("Category name: " + shopify_category_name);



                    var selected = [];
                    $("#divison" + shopify_div + " input:checked").each(function() {
                        selected.push($(this).attr('id'));
                    });
                    var arcadier_guid = selected.join(",");
                    console.log(arcadier_guid);
                    var arc_user =
                        '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';

                    var data = {
                        shopify_category_id: shopify_category_name,
                        arcadier_guid: arcadier_guid,
                        cat_map: 'cat_map',
                        arc_user: arc_user
                    };
                    console.log("Map data: " + data);
                    $.ajax({
                        type: "POST",
                        url: "ajaxrequest.php",
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function(data) {
                            removeClass('loadingDiv', 500);
                            if (data == 'Mapped') {
                                ShowCustomDialog('Success', 'Map Saved');
                            } else if (data == 'UnMapped') {
                                ShowCustomDialog('Alert',
                                    'There was a problem saving your mapping. Please contact marketplace admin.'
                                );
                            } else {
                                ShowCustomDialog('Alert',
                                    'There was a problem saving your mapping. Please contact marketplace admin.'
                                );
                            }
                        }
                    });
                } else {
                    ShowCustomDialog('Alert', 'Please authenticate first in configuration.');
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
        </div>
    </div>
</body>

</html>