<?php
include 'magento_functions.php';
include 'api.php';
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
$isMerchant = false;
//$trial_list=$arc->listTableData($pack_id, 'Tanoo');
//echo "<pre>"; print_r($trial_list); die;
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
    
    //$authListById=$arc->searchTable($pack_id, 'auth', $data_auth);
    $paying_users_list=$arc->listTableData($pack_id, 'PayingUsers');
	//echo "<pre>"; print_r($trial_list); die;
    //$configListById=$arc->searchTable($pack_id, 'config', $data1);
    //$create_arc_item_slowListById=$arc->searchTable($pack_id, 'create_arc_item_slow', $data1);

    
    /* if(!empty($authListById['Records'])){
        if($authListById['Records'][0]['auth_status'] == '1'){
            
            $authRowByMerchantGuid=$authListById['Records'][0];
            
            $data_create_arc_item_slow = [
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
            $create_arc_item_slowListById=$arc->searchTable($pack_id, 'create_arc_item_slow', $data_create_arc_item_slow);
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
            if(!empty($create_arc_item_slowListById['Records'])){
                $create_arc_item_slowRowByMerchantGuid=$create_arc_item_slowListById['Records'][0];
            }else{
                $create_arc_item_slowRowByMerchantGuid=array();
            }


            $mag_product=$mag->magento_products_all($authRowByMerchantGuid['domain'],$authRowByMerchantGuid['token']);
            $mag_product1 = json_decode($mag_product, true);
            $mag_product1_count = count($mag_product1['items']);
            //echo "<pre>"; print_r($create_arc_item_slowRowByMerchantGuid); die;
            $mag_cat_arr=$mag->get_categories($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['token']);
            if(empty($mag_cat_arr->items)){
                $response = $mag->magento_auth($authRowByMerchantGuid['domain'], $authRowByMerchantGuid['username'], $authRowByMerchantGuid['password']);
                $mag_token = json_decode($response);
               
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


            // $file = file_get_contents('item_control_data.json');
            // $sync_data = json_decode($file,true);
            // $data_by_arcadier_guid = array();
            // foreach($sync_data as $sync_dat){
            // if($sync_dat['arcadier_guid'] == $authRowByMerchantGuid['merchant_guid']){
            //     $data_by_arcadier_guid[] = $sync_dat;
            // }
            // }

            
        }else{
            header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
        }
    } */
    
}else{
    //header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
}

}else{
    header('location:'.$_COOKIE['protocol'].'://'.$_COOKIE['marketplace'].'/admin');
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
}else{
        //header('location:configuration.php');
    } */
    //echo "<pre>"; print_r($mag_product1); die;
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
        <!-- <script type="text/javascript">
            //var jQuery_3_3_1 = $.noConflict(true);
        </script> -->
		
		
		
		
		<script src="scripts/jquery-2.1.3.min.js"></script>
        <!-- <script type="text/javascript">
            //var jQuery_2_1_3 = $.noConflict(true);
        </script> -->
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
		<script type="text/javascript" src="scripts/jquery.dataTables.min.js"></script>

		<script src="scripts/chosen.jquery.min.js" ></script>
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
        input[type=checkbox], input[type=radio] {
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
        /* div.footer {
            display: none;
        } */
        table.dataTable thead th, table.dataTable thead td {
            padding: 8px 10px;
        }

        .foot-plugin-footer .footer {
           padding-bottom: 10px;
           padding-top: 30px;
       }
       .foot-plugin-footer .footer .footer-navigation ul>li>a {
           padding-right: 79px;
       }
        .foot-plugin-footer ul.footer-social-media, .foot-plugin-footer .footer-bottom {
            display: none;
        }
        div#logTable_wrapper {
    min-height: 410px;
}
        #wrapper {
            overflow: unset;
        }
        .content-page {
            /* margin-left: 220px; */
            margin-top: -20px;
        }
        .side-menu {
          width: 170px;
           }
           #main {
               margin-left: 340px;
            }
            .content-page {
                margin-left: 170px;
            }
            .page-content {
                 padding-right: 0;
                    }


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
            function addLoader(){
			    $('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
            }
            function removeClass(div_id,time){
                $( "#"+div_id ).fadeOut(time, function() {
                $( "#"+div_id ).remove(); 
            });  
            }
            function addLoader1(){
			    $('body').append('<div style="" id="loadingDiv1"><div class="loader">Loading...</div></div>');
            }
            addLoader1();
        </script>
      <div class="gutter-wrapper" id="wrapper">
        <!-- <% include ./Partials/adminHeader  %> -->
        <!--<div class="topbar">
            
            <div class="topbar-left">
                <a href="index" class="logo">
                </a>
            </div>
        
            <nav class="navbar-custom">
                <ul class="navbar-right d-flex list-inline float-right mb-0">
                    <li class="dropdown notification-list d-none d-md-block">
                    </li>
               
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
                    <a href="trial-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect">   <img src="images/list-icon.png"> <span> Trial List </span></a>
                 </li>
                 <li>
                    <a href="paid-list.php?user=<?php echo $_GET['user']; ?>" class="waves-effect">   <img src="images/list-icon.png"> <span> Paid List </span></a>
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

                        <div class="col-sm-6" id="flash_message">
                            <h4 class="page-title">Paid List</h4>   
                    </div>
					
					<div id="dialog" title="Alert message" style="display: none">
								<div class="ui-dialog-content ui-widget-content">
									<p>
										<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0"></span>
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

        <!--<div class="col-sm-6">
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
    <div class="col-12 p-3 bg-white shadow rounded">
        <div class="table-responsive">
            <!--<table class="table table-bordered table-striped" id="logTable" >-->
            <table class="table hover" id="logTable" >
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>CreatedDateTime</th>
                        <th>ModifiedDateTime</th>
                        <th>subscriptionId</th>
                        <th>BaseURL</th>
                      

                    </tr>
                </thead>
                <tbody>
				<?php foreach($paying_users_list['Records'] as $paying_users_lists){
                    //echo "<pre>"; print_r($trial_lists);
                    ?>
                    <tr id="mag-<?php echo $trial_lists['Id']; ?>">
                        <td><?php echo $paying_users_lists['Id']; ?></td>
                        <td><?php echo $arc->timestamp_to_datetime($paying_users_lists['CreatedDateTime'], '+5 hour +30 minutes'); ?></td>
                        <td><?php echo $arc->timestamp_to_datetime($paying_users_lists['ModifiedDateTime'], '+5 hour +30 minutes'); ?></td>
                        <td><?php echo $paying_users_lists['subscriptionId']; ?></td>
                        <td><?php echo $paying_users_lists['payingUser']; ?></td>
                        
                    </tr>
				<?php } ?>

                        
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- end row -->
<!-- <footer class="footer text-center">
    © 2021.
</footer> -->




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
    $('input[type=checkbox]').click(function () {
		if (!$(this).is(':checked')) {
			$('#'+this.id).prop('checked',false);
		}
	});
    $.noConflict();
    $(".chosen-select").chosen({width: "125px"});
	
	$.extend($.fn.dataTable.defaults, {
     	 //searching: false,
     	ordering:  false, 
     	//lengthMenu:false,
     	//paging:false,
     	//info:false
     });  
      //$('table.table').DataTable();
      
      $('#logTable').DataTable( {
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
	myDialog = $("#dialog").dialog({
						// dialog settings:
						//autoOpen : false,
						// ... 
					});
					myDialog.dialog("close");
					
					
                  
	
	
});


 
				

				


			function ShowCustomDialog(dialogtype,dialogmessage)
			{
                
			ShowDialogBox(dialogtype,dialogmessage,'Ok','', 'GoToAssetList',null);
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
                    hide: { effect: 'scale', duration: 400 },

                    buttons: [
                                    {
                                        text: btn1text,
                                        "class": btn1css,
                                        click: function () {
											myDialog.dialog("close");

                                        }
                                    }
                                ]
                });
            }


  


function clear_form_elements(class_name) {
  $("."+class_name).find(':input').each(function() {
    switch(this.type) {
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

		function removeClass(div_id,time){
			$( "#"+div_id ).fadeOut(time, function() {
			  $( "#"+div_id ).remove(); 
		  });  
		}

function sync_product(sku,name,id){
    var configRowByMerchantGuid_min_sync_limit = '<?php echo $configRowByMerchantGuid["min_sync_limit"]; ?>';
    var configRowByMerchantGuid_min_sync_limit1 = parseInt(configRowByMerchantGuid_min_sync_limit);
    var mag_product1_count = '<?php echo $mag_product1_count; ?>';
    var mag_product1_count1 = parseInt(mag_product1_count);
    console.log(configRowByMerchantGuid_min_sync_limit);
    console.log(configRowByMerchantGuid_min_sync_limit1);
    console.log(mag_product1_count);
    console.log(mag_product1_count);
    
	sku1=sku;
	name1=name;
	id1=id;
	
	if($('#sync_product-'+id).is(":checked")){


                
    
    if(mag_product1_count1 >= configRowByMerchantGuid_min_sync_limit1){

	var override_default_category_select = $('#override_default_category_select-'+id).val();
    var arc_user = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
	if($('#override_default_category-'+id).is(":checked")){
	//if(override_default_category_select.length == '0'){
	if(override_default_category_select == null || override_default_category_select.length == '0'){
		alert("Please Select Override Category");
		return false;
	}
		data = {sku1:sku1,name1:name1,id1:id1,override_default_category_select:override_default_category_select,create_arc_item:'create_arc_item',arc_user:arc_user};
	}else{
		data = {sku1:sku1,name1:name1,id1:id1,create_arc_item:'create_arc_item',arc_user:arc_user};
	}
	$('body').append('<div style="" id="loadingDiv"><div class="loader">Loading...</div></div>');
		
	console.log(data);

	$.ajax({
			   type: "POST",
			   url: "ajaxrequest.php",
               contentType: 'application/json',
                data: JSON.stringify(data),
			   success: function(response)
			   {
				removeClass('loadingDiv',500);
				console.log(JSON.parse(response));
                response = JSON.parse(response);
                if(response.message == 1){
                    
					// $("#override_default_category_select"+id).val("");
					// $('#sync_product'+id).prop('checked',false);
					// $('#override_default_category'+id).prop('checked',false);
					//$('#mag'+id1).css("display","none");
                    $("tr#mag-"+id1+" td:nth-child(4)").html("<b>Yes</b> at "+response.data.sync_date);
					var message = 'Sync successfully';
					ShowCustomDialog('Alert',message);
                }
                else{
					
                    //response = JSON.parse(response);
                    response = JSON.parse(JSON.stringify(response));
					var message1 = response.toString();
					var message = "The following items did not have their categories mapped: " +message1+", and were not created.";
					ShowCustomDialog('Alert',message);
                }
			   }
			 });
    
    }else{
        //alert('Cannot sync, Please increase sync limit from configuration');
        var message = 'Cannot sync, Please increase sync limit from configuration';
		ShowCustomDialog('Alert',message);
    }

	}else{
		alert('Please check Synchronize');
	}

    
	
    }



    const removeById = (arr, id1) => {
        const requiredIndex = arr.findIndex(el => {
            return el.id1 === String(id1);
        });
        if(requiredIndex === -1){
            return false;
        };
        return !!arr.splice(requiredIndex, 1);
    };

    const checkIdExists = (arr, id) => {
        const requiredIndex1 = arr.findIndex(el => {
            //return el.id === String(id);
            return el.id === id;
        });
        if(requiredIndex1 === -1){
            return false;
        }else{
            return true;
        }
        //return !!arr.splice(requiredIndex1, 1);
    };
    $(document).ready(function(){
                    var baseUrl = window.location.hostname;
                    var token = getCookie('webapitoken');
                    var user = $("#userGuid").val();
                    var arc_user1 = '<?php if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                    if(($('#merchantId') && $('#merchantId').length) && (user == arc_user1)){
                        removeClass('loadingDiv1',500);
                        return false;
                    }else{
                        //window.location.replace('https://' + baseUrl +'/admin');
                        removeClass('loadingDiv1',500);
                    } 
                });

                
                
                /* jQuery($ => {
                    //var checked_data = [];
                    var magento_products1 = '<?php //echo json_encode($mag_product1['items']); ?>';
                    var magento_products = JSON.parse(magento_products1);
                    console.log(magento_products);
                    
                    






                    var checked_data = JSON.parse(localStorage.getItem('checked_data')) || [];
                    console.log("checked_data:");
                    console.log(checked_data);

                    $.each(checked_data,function(index,checked_dataByIndex){
                        if(checkIdExists(magento_products, parseInt(checked_dataByIndex.id1))){
                            console.log('yes');
                        }else{
                            console.log('no');
                            var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                            var idToDelete = parseInt(checked_dataByIndex.id1);
                            removeById(cart, idToDelete);
                            localStorage.removeItem("checked_data");
                            localStorage.setItem("checked_data", JSON.stringify(cart));
                        }
                    });


                    var arr = JSON.parse(localStorage.getItem('checked')) || [];
                    console.log("arr1:"+arr);
                    arr.forEach((c, i) => $('.sync_product').eq(i).prop('checked', c));

                    //$(".sync_product").click((elem) => { 
					$('.sync_product').change(function() {
					console.log($(this).attr('data-id'));
                        var data_id = $(this).attr('data-id').split(",");
                        
                        if($('#'+$(this).attr('id')).is(":checked")){
                            var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                            cart.push({sku1:data_id[0],name1:data_id[1],id1:data_id[2],create_arc_item_manual:'create_arc_item_manual',arc_user:data_id[3]});
                            localStorage.setItem("checked_data", JSON.stringify(cart));
                            



                        }else{
                            var cart = JSON.parse(localStorage.getItem('checked_data')) || [];
                            var idToDelete = data_id[2];
                            removeById(cart, idToDelete);
                            localStorage.removeItem("checked_data");
                            localStorage.setItem("checked_data", JSON.stringify(cart));

                            

                        }

                        var checked_data1 = JSON.parse(localStorage.getItem('checked_data')) || [];
                        console.log("checked_data1:");
                        console.log(checked_data1);

                        var arc_user2 = '<?php //if(isset($_GET["user"])){ if(!empty($_GET["user"])){ echo $_GET["user"]; } } ?>';
                        var data = {create_arc_item_all_slow:'create_arc_item_all_slow',arc_user:arc_user2, checked_data:checked_data1};
                            $.ajax({
                                type: "POST",
                                url: "ajaxrequest.php",
                                contentType: 'application/json',
                                data: JSON.stringify(data),
                                success: function(response)
                                {
                                    console.log(response);
                                   
                                }
                            });

                        var arr = $('.sync_product').map((i, el) => el.checked).get();
                        console.log(arr);
                        localStorage.setItem("checked", JSON.stringify(arr));
                   });
				   var arr1 = JSON.parse(localStorage.getItem('sync_product1')) || [];
                    console.log("arr11:"+arr1);
                    arr1.forEach((c, i) => $('.sync_product1').eq(i).prop('checked', c));

                    $(".sync_product1").click((elem) => {  
                        var arr1 = $('.sync_product1').map((i, el) => el.checked).get();
                        console.log("arr22:"+arr1);
                        localStorage.setItem("sync_product1", JSON.stringify(arr1));
                   });

                   var create_arc_item_slowRowByMerchantGuid = '<?php  //if(!empty($create_arc_item_slowRowByMerchantGuid)){ echo json_encode(unserialize($create_arc_item_slowRowByMerchantGuid['checked_data'])); }  ?>';
                   var create_arc_item_slowRowByMerchantGuid1 = JSON.parse(create_arc_item_slowRowByMerchantGuid);
                   console.log('create_arc_item_slowRowByMerchantGuid:');
                   console.log(create_arc_item_slowRowByMerchantGuid);
                   console.log(create_arc_item_slowRowByMerchantGuid1);

                 });  */

                function getCookie(name){
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