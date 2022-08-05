<?php
require '../license/license.php';
require 'callAPI.php';
require 'admin_token.php';

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();

$licence = new License();
if (!$licence->isValid()) {
    //$baseUrl1 = getMarketplaceDomain();
    //$packageId1 = getPackageID();
    $plugin_license_config_url = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/plugin_license_config/';
    $plugin_license_config1 = callAPI("GET",null,$plugin_license_config_url,false);
    //print_r($plugin_license_config1);
    if(!empty($plugin_license_config1['Records'])){
        $plugin_license_config = $plugin_license_config1['Records'][0];
        $total_trial_days = (int) $plugin_license_config['total_trial_days'];
        $subscription_amount = (int) $plugin_license_config['subscription_amount'];
        $stripe_publishable_key = $plugin_license_config['stripe_publishable_key'];
    }else{
        $total_trial_days = TOTAL_TRIAL_DAYS;
        $subscription_amount = SUBSCRIPTION_AMOUNT;
        $stripe_publishable_key = STRIPE_PUBLISHABLE_KEY;
    } 
    ?>
<!-- begin header -->
<link href="css/style.css" rel="stylesheet">
<!-- end header -->
<div class="gutter-wrapper subscription-container">
    <p>
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
                      <a href="configuration.php?user=<?php echo $user_id; ?>" style="color: #3fa9e5;">Click here to login as Superadmin to configure your plugin</a>
                      <?php }else{ ?>
                        
                      <?php } ?>
                      </p>
    <h2>Plug-In Subscription page</h2>
    <div class="subscription-content">
        <p>You can try this package for <?php echo $total_trial_days; ?> day. After that, it is $<?php echo $subscription_amount; ?>/year.</p>
        <div class="btn-subscribe">
            <form action="" method="POST" id="subscription-form" enctype="application/x-www-form-urlencoded">
                
               <!-- <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" 
                data-key="pk_test_51Jya24SHghGIp8I7eHfuoDnhySJjkTwK9GQjyWzlcx7raCLjWKq1DfyC1gIqkuOmwLYvQBT47sZraIKlrhUca2x200iTDYaDXz" 
                data-name="SBasedBilling" 
                data-description="Subscription for 1 year"
                data-image="https://stripe.com/img/v3/home/twitter.png" 
                data-amount="10000"
                data-label="Subscribe">
                </script>-->
            </form>
            <form action="" method="POST" id="subscription-form1" enctype="application/x-www-form-urlencoded">
                <a id="continue-trial" href="#">Continue trial</a>
            </form>
        </div>
          <div class="btn-subscribe">

        </div>
    </div>
</div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/subscription.js"></script>
<script>
    $(document).ready(function(){
        var s_p_k = '<?php echo $stripe_publishable_key; ?>';
        var subscription_amount = parseInt('<?php echo $subscription_amount; ?>');
        subscription_amount = subscription_amount * 100;
        //console.log(subscription_amount);
        //console.log(typeof(subscription_amount));
        $("#subscription-form").append('<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="'+s_p_k+'"  data-name="SBasedBilling"   data-description="Subscription for 1 year"  data-image="https://stripe.com/img/v3/home/twitter.png" data-amount="'+subscription_amount+'" data-label="Subscribe">');
        
    });
</script>
<!-- end footer -->
<?php
} else {
    $location = $baseUrl . '/admin/plugins/' . $packageId . '/index.php';
    header('Location: ' . $location);
}
?>
