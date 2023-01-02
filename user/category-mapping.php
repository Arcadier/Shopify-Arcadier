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
            error_log("Category List: ".json_encode($shopify_categories));

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
    <link href="css/shopify.css" rel="stylesheet" type="text/css">
    <link href="css/seller-style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/metismenu.min.css" rel="stylesheet" type="text/css">
    <link href="css/icons.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/chosen.css" />

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
    function removeClass(div_id, time) {
        $("#" + div_id).fadeOut(time, function() {
            $("#" + div_id).remove();
        });
    }


    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }

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

    function addLoader2() {
        // vm = this;
        $('body').append(
            '<div style="" id="loadingDiv1"><div class="loader">Successfully mapped category.</div></div>'
        );
    }
    //addLoader1();
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
                <div class="container-fluid" id="shopify-app">

                    <div class="sc-caegory-wraper">
                        <h5>Shopify Product Types</h5>

                        <input type="hidden" id="user-id" value="<?php echo $_GET['user'] ?>" />
                        <input type="hidden" id="package-id" value="<?php echo $plugin_id ?>" />


                        <div class="sc-caegory-flex">
                            <div class="sc-category-list">
                                <ul class="list">
                                    <li class="category-not-map" v-for="(shopify_cats,index) in shopify_categories">
                                        <label class="custom-checkbox"> {{shopify_cats}}
                                            <input type="checkbox" name="shopify_product_cat[]"
                                                class="shopify_product_cat" :data-category="index"
                                                :data-name="shopify_cats" checked="">
                                            <span class="custom-checkbox-checkmark"></span>
                                        </label>
                                    </li>
                                </ul>
                            </div>


                            <div class="sc-sub-category-list-content"
                                v-for="(shopify_cats, indexTop) in shopify_categories">

                                <div class="sc-sub-category-list" :data-sub-category="indexTop" :id="indexTop">
                                    <div class="sc-category-header">
                                        <p>Shopify product type <span
                                                class="text-blue font-weight-bold">{{shopify_cats}}</span> goes to which
                                            category?</p>
                                    </div>
                                    <!-- <div class="sc-sub-category">
                                        <ul class="sub-category">
                                            <li v-for="arcadier_cats in arcadier_categories"
                                                class="check-category parent-cat">
                                                <span class="cat-toggle"
                                                    v-if="arcadier_cats.ChildCategories.length > 0">
                                                    <i class="fa fa-angle-up up hide"></i>
                                                    <i class="fa fa-angle-down down"></i>
                                                </span>
                                                <input type="checkbox" name="shopify_product_sub_cat[]"
                                                    class="shopify_product_sub_cat" :arc-cat-id="arcadier_cats.ID">
                                                <label :for="arcadier_cats.ID" class="custom-checkbox">

                                                </label>
                                                <span class="custom-checkbox-checkmark">{{arcadier_cats.Name}}</span>
                                            </li>

                                        </ul>
                                    </div> -->

                                    <div class="sc-sub-category item-form-group un-inputs">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="item-upload-category-container required">
                                                            <div class="col-md-9">
                                                                <div class="row cat-search">
                                                                    <input type="text" class="categorySearch"
                                                                        name="category-name" value="" maxlength="130">
                                                                    <i class="fa fa-search" aria-hidden="true"></i>
                                                                </div>
                                                            </div>
                                                            <div class="checkbox-container">
                                                                <div class="col-md-12">
                                                                    <div class="row">
                                                                        <div class="col-md-9">
                                                                            <div class="row">
                                                                                <div class="checkbox-selection">
                                                                                    <span
                                                                                        class="pull-left selectAll">Select
                                                                                        all</span>
                                                                                    <span
                                                                                        class="pull-right selectNow">Select
                                                                                        none</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="checkbox-content">
                                                                            <ul>

                                                                                <li v-for="(arcadier_cats,index) in arcadier_categories"
                                                                                    class="check-category parent-cat has-child-sub">
                                                                                    <span class="cat-toggle"
                                                                                        v-if="arcadier_cats.ChildCategories.length > 0">
                                                                                        <i
                                                                                            class="fa fa-angle-up up hide"></i>
                                                                                        <i
                                                                                            class="fa fa-angle-down down"></i>

                                                                                    </span>
                                                                                    <input type="checkbox"
                                                                                        :id="arcadier_cats.ID + indexTop"
                                                                                        :arc-cat-id="arcadier_cats.ID"
                                                                                        class="shopify_product_sub_cat parent_category">
                                                                                    <label
                                                                                        :for="arcadier_cats.ID + indexTop"></label>
                                                                                    <span>{{arcadier_cats.Name}}</span>
                                                                                    <ul class="sub-cat"
                                                                                        style="display: none;">
                                                                                        <li v-for="level1 in arcadier_cats.ChildCategories"
                                                                                            class="check-category parent-sub-cat has-child-sub">
                                                                                            <input type="checkbox"
                                                                                                class="shopify_product_sub_cat level2 parent_category_sub"
                                                                                                :id="level1.ID + indexTop"
                                                                                                :arc-cat-id="level1.ID">
                                                                                            <label
                                                                                                :for="level1.ID + indexTop"></label>
                                                                                            <span>{{level1.Name}}</span>

                                                                                            <ul class="sub-sub-cat">
                                                                                                <li class="check-category parent-sub-cat has-child-sub"
                                                                                                    v-for="level2 in level1.ChildCategories">
                                                                                                    <input
                                                                                                        class="shopify_product_sub_cat parent_category_sub"
                                                                                                        type="checkbox"
                                                                                                        :id="level2.ID + indexTop"
                                                                                                        :arc-cat-id="level2.ID">
                                                                                                    <label
                                                                                                        :for="level2.ID + indexTop"></label>
                                                                                                    <span>{{level2.Name}}</span>
                                                                                                    <ul
                                                                                                        class="sub-sub-cat">
                                                                                                        <li class="check-category parent-sub-cat-3 has-child-sub"
                                                                                                            v-for="level3 in level2.ChildCategories">
                                                                                                            <input
                                                                                                                class="shopify_product_sub_cat level3"
                                                                                                                type="checkbox"
                                                                                                                :id="level3.ID + indexTop"
                                                                                                                :arc-cat-id="level3.ID">
                                                                                                            <label
                                                                                                                :for="level3.ID + indexTop"></label>
                                                                                                            <span>{{level3.Name}}</span>
                                                                                                            <ul
                                                                                                                class="sub-sub-cat">
                                                                                                                <li class="check-category parent-sub-cat has-child-sub"
                                                                                                                    v-for="level4 in level3.ChildCategories">
                                                                                                                    <input
                                                                                                                        class="shopify_product_sub_cat"
                                                                                                                        type="checkbox"
                                                                                                                        :id="level4.ID + indexTop"
                                                                                                                        :arc-cat-id="level4.ID">
                                                                                                                    <label
                                                                                                                        :for="level4.ID + indexTop"></label>
                                                                                                                    <span>{{level4.Name}}</span>

                                                                                                                    <ul
                                                                                                                        class="sub-sub-cat">
                                                                                                                        <li class="check-category parent-sub-cat has-child-sub"
                                                                                                                            v-for="level5 in level4.ChildCategories">
                                                                                                                            <input
                                                                                                                                class="shopify_product_sub_cat"
                                                                                                                                type="checkbox"
                                                                                                                                :id="level5.ID + indexTop"
                                                                                                                                :arc-cat-id="level5.ID">
                                                                                                                            <label
                                                                                                                                :for="level5.ID + indexTop"></label>
                                                                                                                            <span>{{level5.Name}}</span>

                                                                                                                            <ul
                                                                                                                                class="sub-sub-cat">
                                                                                                                                <li class="check-category parent-sub-sub-cat"
                                                                                                                                    v-for="level6 in level5.ChildCategories">
                                                                                                                                    <input
                                                                                                                                        class="shopify_product_sub_cat"
                                                                                                                                        type="checkbox"
                                                                                                                                        :id="level6.ID + indexTop"
                                                                                                                                        :arc-cat-id="level6.ID">
                                                                                                                                    <label
                                                                                                                                        :for="level6.ID + indexTop"></label>
                                                                                                                                    <span>{{level6.Name}}</span>

                                                                                                                                </li>

                                                                                                                                <div
                                                                                                                                    class="cat-line">
                                                                                                                                </div>

                                                                                                                            </ul>




                                                                                                                        </li>

                                                                                                                        <div
                                                                                                                            class="cat-line">
                                                                                                                        </div>

                                                                                                                    </ul>



                                                                                                                </li>

                                                                                                                <div
                                                                                                                    class="cat-line">
                                                                                                                </div>

                                                                                                            </ul>

                                                                                                            <div
                                                                                                                class="cat-line">
                                                                                                            </div>


                                                                                                        </li>
                                                                                                        <!-- 
                                                                                                        <li class="check-category parent-sub-sub-cat has-child-sub"
                                                                                                            style="display: none;">
                                                                                                            <input
                                                                                                                type="checkbox"
                                                                                                                id="checkSubSubCat3c">
                                                                                                            <label
                                                                                                                for="checkSubSubCat3c"></label>
                                                                                                            <span>Catgeory3-sub-sub</span>
                                                                                                            <ul
                                                                                                                class="sub-sub-cat">
                                                                                                                <li class="check-category parent-sub-sub-cat"
                                                                                                                    style="display: none;">
                                                                                                                    <input
                                                                                                                        type="checkbox"
                                                                                                                        id="checkSubSubCat3cc">
                                                                                                                    <label
                                                                                                                        for="checkSubSubCat3cc"></label>
                                                                                                                    <span>Catgeory3-sub-sub</span>
                                                                                                                </li>
                                                                                                                <li class="check-category parent-sub-sub-cat"
                                                                                                                    style="display: none;">
                                                                                                                    <input
                                                                                                                        type="checkbox"
                                                                                                                        id="checkSubSubCat3ccc">
                                                                                                                    <label
                                                                                                                        for="checkSubSubCat3ccc"></label>
                                                                                                                    <span>Catgeory3-sub-sub</span>
                                                                                                                </li>
                                                                                                            </ul>
                                                                                                            <div
                                                                                                                class="cat-line">
                                                                                                            </div>
                                                                                                        </li> -->
                                                                                                    </ul>
                                                                                                    <div
                                                                                                        class="cat-line">
                                                                                                    </div>

                                                                                                </li>

                                                                                            </ul>
                                                                                            <div class="cat-line"></div>
                                                                                        </li>

                                                                                    </ul>
                                                                                    <div class="cat-line"></div>
                                                                                </li>


                                                                            </ul>
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







                                    <div class="sc-category-footer">
                                        <p>Submit your choice for each Shopify category:</p>

                                        <button
                                            style="margin-left: 25px;border: #0e77d4;box-sizing: border-box;background-color: #333547;border-radius: 6px;color: white;padding: 5px 10px;font-size: 14px; cursor: pointer;"
                                            @click="onMap">Submit</button>

                                    </div>

                                    <div class="sc-category-footer">

                                        <p class="text-success" v-if="status == 1" v-text="notification"></p>

                                    </div>



                                </div>





                            </div>
                        </div>
                    </div>






















                </div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17-beta.0/vue.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js"></script>
                <script src="scripts/metisMenu.min.js"></script>
                <script src="scripts/jquery.slimscroll.js"></script>
                <script src="scripts/waves.min.js"></script>
                <script src="scripts/app.js"></script>


                <script>
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

                function waitForElement(elementPath, callBack) {
                    window.setTimeout(function() {
                        if ($(elementPath).length) {
                            callBack(elementPath, $(elementPath));
                        } else {
                            waitForElement(elementPath, callBack);
                        }
                    }, 500);
                }
                // vue js component

                var shopify = new Vue({
                    el: "#shopify-app",
                    data() {
                        return {

                            shopify_categories: "",
                            arcadier_categories: "",
                            getCategories: "get_categories.php",
                            saveCategories: "save_category_mapping.php",
                            bulkUrl: "",
                            productsData: "",
                            protocol: window.location.protocol,
                            baseURL: window.location.hostname,
                            userId: "",
                            packageId: "",
                            allMapped: [],
                            existingMaps: "",
                            status: '',
                            notification: ''


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
                        async getShopifyCategories() {
                            var vm = this;

                            getCategories = await axios({
                                    method: "post",
                                    url: vm.getCategories,

                                })
                                .then((response) => {

                                    vm.shopify_categories = JSON.parse(response.data);
                                    console.log(response);
                                })
                                .catch(function(response) {
                                    //handle error
                                    console.log(response);
                                });

                        },
                        async renderExistingMapping() {
                            console.log('render run')
                            waitForElement('.shopify_product_cat', function() {

                                $('.shopify_product_cat').each(function() {
                                    let parent = $(this);


                                    let category_name = $(this).attr('data-name');


                                    let category_temp_id = $(this).attr(
                                        'data-category');

                                    if (vm.existingMaps) {
                                        let rendered_category = vm.existingMaps
                                            .filter(
                                                name =>
                                                name
                                                .shopify_category ==
                                                category_name);

                                        console.log({
                                            rendered_category
                                        });

                                        let selected_arc_categories =
                                            rendered_category[0]
                                            .mapped_arc_categories;

                                        $(`#${category_temp_id} .shopify_product_sub_cat`)
                                            .each(
                                                function() {

                                                    if (selected_arc_categories
                                                        .length !=
                                                        0) {
                                                        // parent.parents('li').addClass('select')
                                                    }


                                                    if (selected_arc_categories
                                                        .includes($(
                                                                this)
                                                            .attr(
                                                                'arc-cat-id'))) {
                                                        $(this).prop("checked",
                                                            true);
                                                    }

                                                })

                                    }

                                    //use the temp category_temp_id to reference the arc cat div and get the selected arc categories


                                })

                            })

                        },
                        async onMap() {

                            vm = this;

                            vm.allMapped = [];

                            //get all the shopify categories li element
                            //let all_mapped = [];

                            $('.shopify_product_cat').each(function() {

                                console.log('here')
                                let category_name = $(this).attr('data-name');
                                let category_temp_id = $(this).attr('data-category');

                                let selected_arc_categories = [];
                                $(`#${category_temp_id} .shopify_product_sub_cat`).each(
                                    function() {
                                        if ($(this).is(':checked')) {
                                            let selected = $(this).attr('arc-cat-id');
                                            selected_arc_categories.push(selected);
                                        }

                                    })
                                //use the temp category_temp_id to reference the arc cat div and get the selected arc categories

                                let mapped_category = {
                                    'shopify_category': category_name,
                                    "mapped_arc_categories": selected_arc_categories
                                }

                                vm.allMapped.push(mapped_category);


                            });

                            console.log(vm.allMapped);
                            vm.onSaveMapped(vm.allMapped);

                        },
                        async onSaveMapped(everything) {


                            // addLoader2();
                            var vm = this;
                            vm.status = ""
                            var data = {
                                'user-id': vm.userId,
                                'mapping-data': everything,

                            };
                            saveMapped = await axios({
                                    method: "post",
                                    url: vm.saveCategories,
                                    data: JSON.stringify(data)

                                })
                                .then((response) => {
                                    console.log(response.data);
                                    // addLoader2();
                                    // $('loadingDiv1').remove();
                                    vm.status = 1;
                                    vm.notification = "Category mapping saved."
                                    ShowCustomDialog('Success', 'Map Saved');
                                })
                                .catch(function(response) {
                                    //handle error
                                    console.log(response);
                                });
                        }
                    },

                    mounted: function() {
                        //  
                        vm = this
                        this.userId = document.getElementById("user-id").value,
                            this.packageId = document.getElementById("package-id").value,

                            this.getShopifyCategories(),
                            this.getExistingMaps(vm.userId),
                            //arcadier categories
                            axios({
                                method: 'GET',
                                url: `${vm.protocol}//${vm.baseURL}/api/v2/categories/hierarchy`,

                            }).then(response => {
                                let result = response.data
                                vm.arcadier_categories = result
                                console.log(
                                    `hell0 ${vm.arcadier_categories}`
                                )


                            })
                            .catch(function(error) {

                                console.log(error);
                            }),

                            this.$nextTick(function() {

                                this.renderExistingMapping()

                                // Code that will run only after the
                                // entire view has been rendered
                            })
                        //get the mapping




                    },


                });


                $(window).load(function() {
                    $('td').each(function() {
                        var th = $(this).closest('table').find('th').eq(this.cellIndex);
                        var thContent = $(th).html();
                        $(this).attr('data-th', thContent);
                    });
                });
                $('body').on('click', '.delete_item', function() {
                    var id = $(this).data('id');
                    show_conformation(id, 'item');
                });
                $('body').on('click', '.cancel_remove', function() {
                    cancel_remove();
                });
                $('body').on('click', '.confirm_remove', function() {
                    confirm_remove(this);
                });

                function delete_item(id) {
                    show_conformation(id, 'item');
                }

                function cancel_remove() {
                    var target = jQuery(".popup-area.item-remove-popup");
                    var cover = jQuery("#cover");
                    target.fadeOut();
                    cover.fadeOut();
                    jQuery(".my-btn.btn-saffron").attr('data-id', '');
                    console.log("cancel remove item..");
                }

                function show_conformation(id, key) {
                    var target = jQuery(".popup-area.item-remove-popup");
                    var cover = jQuery("#cover");
                    target.fadeIn();
                    cover.fadeIn();
                    jQuery(".my-btn.btn-saffron").attr('data-key', key);
                    jQuery(".my-btn.btn-saffron").attr('data-id', id);
                }

                function confirm_remove(ele) {
                    var that = jQuery(ele);
                    var id = that.attr('data-id');
                    var key = that.attr('data-key');
                    target = ''
                    if (key == 'item') {
                        target = jQuery('.item-row[data-id=' + id + ']');
                    }
                    target.fadeOut(500, function() {
                        target.remove();
                        cancel_remove();
                    });
                }


                function confirm_Submit(ele) {
                    var $this = $(ele);
                    $('.category-not-map.select').addClass('category-mapped').removeClass('category-not-map');

                }
                $(document).ready(function() {
                    $('.onoffswitch input[type=checkbox]').prop("checked", false);

                    $('body').on('click', '.sc-category-list ul li', function() {

                        $('.sc-category-list ul li').removeClass('select');
                        $(this).addClass('select');


                    })

                    $('body').on('click', '.shopify_product_cat', function() {

                        $cat = $(this);
                        let cat_val = $cat.data("category");
                        // if ($cat.is(":checked")) {
                        $(".sc-sub-category-list").removeClass("active");
                        $(".sc-category-list ul").find(
                            '.shopify_product_cat[data-category=' +
                            cat_val + ']').closest("li").addClass("select");
                        $(".sc-sub-category-list-content div[data-sub-category=" +
                                cat_val +
                                "]")
                            .addClass("active");
                        // } else {
                        //     $(".sc-category-list ul").find('[data-category=' + cat_val +
                        //             ']')
                        //         .closest(
                        //             "li").removeClass("select");
                        //     $(".sc-sub-category-list").removeClass("active");
                        // }
                    });

                    $('body').on('click', '.sc-sub-category-list-content .active input',
                        function() {
                            var cat_val = $(this).closest('.sc-sub-category-list').attr(
                                'data-sub-category');
                            if ($(
                                    '.sc-sub-category-list.active .shopify_product_sub_cat:checked'
                                )
                                .length >
                                0) {
                                // $(".sc-category-list ul").find(
                                //     '.shopify_product_cat[data-category=' +
                                //     cat_val + ']').closest("li").addClass("select");
                            } else {
                                $(".sc-category-list ul").find('[data-category=' + cat_val +
                                        ']')
                                    .closest(
                                        "li").removeClass("select");
                            }
                        });



                    //category selection
                    $(document.body).on("keyup", ".categorySearch", function() {
                        //$("#categorySearch").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $(".checkbox-content li").filter(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(
                                value) > -1)
                        });
                    });

                    $("li.check-category").each(function() {
                        var $this = $(this);
                        if ($this.find('ul').length !== 0) {
                            $this.addClass("has-child-sub");
                            $this.find(".cat-toggle .up").removeClass("hide");
                        }
                    });


                    $("li.check-category.has-child-sub").each(function() {
                        var $ulFirst = $(this).find("ul").first();
                        var $ulliFirst1 = $ulFirst.children("li").last().find("label")
                            .first()
                            .innerHeight() * 1.5;
                        var $ulliFirst = $ulFirst.children("li").last().innerHeight();
                        console.log($ulliFirst);
                        var newHeight = $ulliFirst + $ulliFirst1;
                        $(this).append('<div class="cat-line"></div>');
                        //$(this).find(".cat-line:first").css("height", $(this).height() - newHeight + "px");
                    });

                    $(document.body).on("click", ".selectAll", function() {
                        // $("#selectAll").on("click", function() {

                        console.log('clicked');
                        $('.checkbox-content > ul > li input[type="checkbox"]').prop(
                            "checked", true);
                    });


                    $(document.body).on("click", ".selectNow", function() {
                        //$("#selectNow").on("click", function() {
                        $('.checkbox-content > ul > li input[type="checkbox"]').prop(
                            "checked", false);
                    });

                    // $(document.body).on("click", '.shopify_product_sub_cat',
                    //     function() {
                    //         // jQuery('.check-category input[type="checkbox"]').click(function() {
                    //         if (jQuery(this).is(":checked")) {
                    //             jQuery(this).parents('.parent-cat').find('.parent_category').prop(
                    //                 "checked",
                    //                 true);

                    //             jQuery(this).closest('.parent-sub-cat').find('.parent_category_sub')
                    //                 .prop(
                    //                     "checked",
                    //                     true);

                    //         }
                    //         //  else {
                    //         //       jQuery(this).parents('parent-cat').prop(
                    //         //         "checked",
                    //         //         false);
                    //         // }
                    //     });


                    $(document.body).on("click", '.shopify_product_sub_cat',

                        function() {
                            // jQuery('.check-category input[type="checkbox"]').click(function() {
                            var $this = $(this);
                            var allParents = $this.parents(".has-child-sub");
                            if ($this.is(":checked")) {
                                for (var i = 0; i < allParents.length; i++) {
                                    console.log($(allParents[i]).attr('class'));
                                    $(allParents[i]).find('>.shopify_product_sub_cat').prop(
                                        "checked",
                                        true);


                                }
                                $this.parents('.parent-cat').find('.parent_category').prop(
                                    "checked",
                                    true);

                            }

                        });



                    // $(document.body).on("click", '.shopify_product_sub_cat:has(".level3")',
                    //     function() {
                    //         // jQuery('.check-category input[type="checkbox"]').click(function() {
                    //         if (jQuery(this).is(":checked")) {
                    //             jQuery(this).parents('.parent-cat').find('.parent_category').prop(
                    //                 "checked",
                    //                 true);
                    //             jQuery(this).closest('.parent-sub-cat-3').find('.parent_category_sub')
                    //                 .prop(
                    //                     "checked",
                    //                     true);
                    //         }
                    //         //  else {
                    //         //       jQuery(this).parents('parent-cat').prop(
                    //         //         "checked",
                    //         //         false);
                    //         // }
                    //     });


                    $(document.body).on("click", '.cat-toggle > .up', function() {
                        // $(".cat-toggle > .up").on("click", function() {
                        var $this = $(this);
                        var $parent = $this.parents(".parent-cat");
                        var $findSubCat = $parent.find("ul.sub-cat");
                        $findSubCat.slideToggle();
                        $parent.find(".cat-toggle > .down").removeClass("hide");
                        $this.addClass("hide");
                    });


                    $(document.body).on("click", ".cat-toggle > .down", function() {
                        // $(".cat-toggle > .down").on("click", function() {
                        var $this = $(this);
                        var $parent = $this.parents(".parent-cat");
                        var $findSubCat = $parent.find("ul.sub-cat");
                        $findSubCat.slideToggle();
                        $parent.find(".cat-toggle > .up").removeClass("hide");
                        $this.addClass("hide");
                    });


                    $('body').on('click',
                        '.sc-sub-category-list-content .active input.shopify_product_sub_cat',
                        function() {

                            var cat_val = $(this).closest('.sc-sub-category-list').attr(
                                'data-sub-category');

                            // if ($(
                            //         '.sc-sub-category-list.active .shopify_product_sub_cat:checked'
                            //     )
                            //     .length >
                            //     0) {

                            //     $(".sc-category-list ul").find(
                            //         '.shopify_product_cat[data-category=' +
                            //         cat_val + ']').closest("li").addClass("select");

                            // } else {

                            //     $(".sc-category-list ul").find('[data-category=' + cat_val +
                            //         ']').closest(
                            //         "li").removeClass("select");

                            // }

                        });









                });
                </script>


                <script>
                var $j = jQuery.noConflict();
                $(document).ready(function() {
                    myDialog = $j("#dialog").dialog({
                        // dialog settings:
                        //autoOpen : false,
                        // ... 
                    });
                    myDialog.dialog("close");



                    var confirmModal =
                        `<div class='popup-area cart-checkout-confirm' id ='plugin-popup'><div class='wrapper'> <div class='title-area text-capitalize'><h1>Successfully mapped category.</h1></div><div class='btn-area'> <a href='javascript:void(0)' class='btn-black-cmn' id='btn-cancel'>OK</a> </div></div></div>`;
                    $('.footer').after(confirmModal);

                    // loadAllItemsUrl();
                    $('#plugin-popup #btn-cancel').click(function() {
                        $("#plugin-popup").fadeOut();
                        $("#cover").fadeOut();
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

                $(".save_map").click(function() {
                    console.log("Shopify Product Type: " + this.id);
                    var shopify_category_id = this.id

                    var isMerchantAuth = '<?php echo  $isMerchantAuth; ?>';

                    if (isMerchantAuth == 'Yes') {
                        addLoader();

                        if (shopify_category_id.includes(">")) {
                            shopify_category_name = shopify_category_id.split('>')[0];
                            shopify_div = shopify_category_id.split('>')[1];
                            shopify_div = shopify_div.replace("'", "~")

                            shopify_category_name = shopify_category_name.replace("_", " ");
                            shopify_category_name = shopify_category_name.replace("And", "&");
                            shopify_category_name = shopify_category_name.replace("~", "'");

                        } else {
                            shopify_div = shopify_category_id;
                            shopify_category_name = shopify_category_id;
                        }

                        console.log("Div id: " + shopify_div);
                        console.log("Category name: " + shopify_category_name);

                        var selected = [];
                        if (shopify_div.includes("'")) {
                            shopify_div = shopify_div.replace("'", "~");
                        }
                        $("#divison" + shopify_div + " input:checked").each(function() {
                            selected.push($(this).attr('id'));
                        });

                        var arcadier_guid = selected.join(",");
                        console.log("Arcadier GUIDs: ", arcadier_guid);
                        var arc_user =
                            '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';

                        var data = {
                            shopify_category_id: shopify_category_name,
                            arcadier_guid: arcadier_guid,
                            cat_map: 'cat_map',
                            arc_user: arc_user
                        };
                        console.log("Map data: " + JSON.stringify(data));
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
                });
                </script>
            </div>
        </div>
</body>

</html>