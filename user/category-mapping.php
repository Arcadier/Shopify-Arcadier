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
    <link href="css/shopify.css" rel="stylesheet" type="text/css">

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
                <div class="container-fluid" id="shopify-app">

                    <div class="sc-caegory-wraper">
                        <h5>Shopify Product Types</h5>

                        <input type="hidden" id="user-id" value="<?php echo $_GET['user'] ?>" />
                        <input type="hidden" id="package-id" value="<?php echo $plugin_id ?>" />

                        <input type="hidden" id="arcadier-categories"
                            value='<?php echo json_encode($arcadier_categories) ?>' />

                        <div class="sc-caegory-flex">
                            <div class="sc-category-list">
                                <ul class="list">
                                    <li class="" v-for="(shopify_cats,index) in shopify_categories">
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
                                v-for="(shopify_cats, index) in shopify_categories">

                                <div class="sc-sub-category-list" :data-sub-category="index" :id="index">
                                    <div class="sc-category-header">
                                        <p>Shopify product type <span
                                                class="text-blue font-weight-bold">{{shopify_cats}}</span> goes to which
                                            category?</p>
                                    </div>
                                    <div class="sc-sub-category">
                                        <ul class="sub-category">
                                            <li v-for="arcadier_cats in arcadier_categories">
                                                <label class="custom-checkbox"> {{arcadier_cats.Name}}
                                                    <input type="checkbox" name="shopify_product_sub_cat[]"
                                                        class="shopify_product_sub_cat" :arc-cat-id="arcadier_cats.ID">
                                                    <span class="custom-checkbox-checkmark"></span>
                                                </label>
                                            </li>

                                        </ul>
                                    </div>
                                    <div class="sc-category-footer">
                                        <p>Submit your choice for each Shopify category:</p>
                                        <input type="submit" class="btn btn-dark" value="Submit" @click="onMap">
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
                            existingMaps: ""


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

                                    vm.existingMaps = JSON.parse(categoryMapping);

                                    console.log(vm.existingMaps);

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

                            $('.shopify_product_cat').each(function() {


                                let category_name = $(this).attr('data-name');


                                let category_temp_id = $(this).attr('data-category');
                                let rendered_category = vm.existingMaps.filter(name => name
                                    .shopify_category ==
                                    category_name);

                                console.log({
                                    rendered_category
                                });

                                let selected_arc_categories = rendered_category
                                    .mapped_arc_categories;

                                $(`#${category_temp_id} .shopify_product_sub_cat`).each(function() {

                                    if (selected_arc_categories.includes($(this).attr(
                                            'arc-cat-id'))) {
                                        $(this).prop("checked", true);
                                    }

                                })
                                //use the temp category_temp_id to reference the arc cat div and get the selected arc categories


                            })

                        },

                        async onMap() {

                            vm = this;

                            //get all the shopify categories li element
                            //let all_mapped = [];

                            $('.shopify_product_cat').each(function() {
                                let category_name = $(this).attr('data-name');
                                let category_temp_id = $(this).attr('data-category');

                                let selected_arc_categories = [];
                                $(`#${category_temp_id} .shopify_product_sub_cat`).each(function() {
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

                            var vm = this;
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
                                url: `${vm.protocol}//${vm.baseURL}/api/v2/categories?pageSize=1000`,

                            }).then(response => {
                                let result = response.data
                                vm.arcadier_categories = result.Records
                                console.log(
                                    `${vm.arcadier_categories}`
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

                    // watch: {
                    //     // messages: function(val, oldVal) {
                    //     //     $(".table").find("tbody tr:last").hide();
                    //     //     //Scroll to bottom
                    //     // },
                    // },
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
                $(document).ready(function() {
                    $('.onoffswitch input[type=checkbox]').prop("checked", false);

                    $('body').on('click', '.shopify_product_cat', function() {
                        $cat = $(this);
                        let cat_val = $cat.data("category");
                        if ($cat.is(":checked")) {
                            $(".sc-sub-category-list").removeClass("active");
                            $(".sc-category-list ul").find('.shopify_product_cat[data-category=' +
                                cat_val + ']').closest("li").addClass("select");
                            $(".sc-sub-category-list-content div[data-sub-category=" + cat_val +
                                    "]")
                                .addClass("active");
                        } else {
                            $(".sc-category-list ul").find('[data-category=' + cat_val + ']')
                                .closest(
                                    "li").removeClass("select");
                            $(".sc-sub-category-list").removeClass("active");
                        }
                    });

                    $('body').on('click', '.sc-sub-category-list-content .active input', function() {
                        var cat_val = $(this).closest('.sc-sub-category-list').attr(
                            'data-sub-category');
                        if ($('.sc-sub-category-list.active .shopify_product_sub_cat:checked')
                            .length >
                            0) {
                            $(".sc-category-list ul").find('.shopify_product_cat[data-category=' +
                                cat_val + ']').closest("li").addClass("select");
                        } else {
                            $(".sc-category-list ul").find('[data-category=' + cat_val + ']')
                                .closest(
                                    "li").removeClass("select");
                        }
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