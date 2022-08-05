<?php
require '../license/license.php';
require 'callAPI.php';
require 'admin_token.php';

//include 'api.php';

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();




//$arc = new ApiSdk();



$licence = new License();
if ($licence->isValid()) {
   // echo 'test'; die;
    //$location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    //header('Location: ' . $location);
    ?>
<!-- Your plug-in's index.html
<p> Payment done. You can now use the Plug-In</p>
<script>
	toastr.success("Welcome", "Success");
</script> -->

<!--<script>
	toastr.success("Welcome", "Success");
</script>-->
<head>
    <script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal_pre/js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal_pre/js/jquery.js"></script>

    <link href="css/adminstyle.css" rel="stylesheet" type="text/css">
    <link href="css/verified-item.css" rel="stylesheet" type="text/css">
    <link href="css/subscription-plugin-in.css" rel="stylesheet" type="text/css">
</head>
<div class="gutter-wrapper">
    <div class="page-content">
      <div class="gutter-wrapper">
        <div class="panel-box">
            <div class="page-content-top page-content-flex">
                <div class="subscription-title">
                    <h4>Magento</h4> 
                    <h5>This plugin enables merchants to synchronise items from their magento shop to the seller account.</h5>
                </div>
                <div class="private-setting-switch">
                    <div class="onoffswitch">
                      <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox switch-item-verification-checkbox" id="myonoffswitch">
                      <label class="onoffswitch-label" for="myonoffswitch"> <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span> </label>
                    </div>
            </div>
            </div>
        </div>

        <div class="panel-box">
            <div class="page-content-top page-content-flex">
                <div class="subscription-title">
                    <h4>Success</h4> 
                    <h5>You can now use the Plug-In. </h5>
                </div>
                <div class="private-setting-switch">
                    <div class="onoffswitch">
                      <?php 
                      $getMarketplaceDomain = getMarketplaceDomain();
                      $super_admin_domain_array = explode(",", SUPER_ADMIN_DOMAIN);
                      if(in_array($getMarketplaceDomain, $super_admin_domain_array)){

                        $_GET['user'] = '';
                        $UserInfo = getUserInfo($_GET['user']);
                        // echo $_GET['user'];
                        // echo "<pre>"; print_r($UserInfo); 
                        $user_id = '';
                        if(!empty($UserInfo)){
                            $user_id = $UserInfo['ID'];
                        }


                      ?>
                      <a href="configuration.php?user=<?php echo $user_id; ?>">Login as Superadmin</a>
                      <?php }else{ ?>
                        
                      <?php } ?>
                    </div>
            </div>
            </div>
        </div>

    </div>
    <div class="clearfix"></div>
</div>

<script type="text/javascript">

    function checkHide(){
        if(jQuery('.private-setting-switch #myonoffswitch').is(':checked'))
            {
                $('.verified-item-form .disabled-overlay').remove();
            }
        else
            {
                $('.verified-item-form .disabled-overlay').remove();
                $('.verified-item-form').append('<div class="disabled-overlay"></div>');
            }
        }
        jQuery(document).ready(function() {
            checkHide();
            jQuery('.private-setting-switch #myonoffswitch').change(function(){ 
                if(jQuery(this).is(':checked')){
                    $('.verified-item-form .disabled-overlay').remove();
                } else {
                    $('.verified-item-form .disabled-overlay').remove();                 
                    $('.verified-item-form').append('<div class="disabled-overlay"></div>');
                }
            });


        });
</script> 

<script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal_pre/js/custom-nicescroll.js"></script>
<script type="text/javascript" src="scripts/magento.js"></script>



<?php
} else {
    $location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    error_log($location);
    header('Location: ' . $location);
}
?>
