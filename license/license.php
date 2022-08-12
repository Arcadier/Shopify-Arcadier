<?php
require 'APIcall.php';
require '../admin/defines.php';

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$baseUrl = fetchMarketplaceBaseUrl();
$packageId = fetchPackageID();


require_once '../vendors/stripe-php/init.php';

class License {
    private $isValid = false;
    //private $stripeKey = "sk_test_51Jya24SHghGIp8I7p7AuI8OJ6wp9iKtJCmiW0l0i7kn7YcUnb8rNg87N059qJCYQBrHUrmXDXrFyYxamsktZKMWq00EcChmB6e"; // #1
    //private $stripeKey = STRIPE_SECRET_KEY; // #1
    //private $planId = 'plan_FKvAnxKEKUC0xe'; // #2
    //private $planId = 'prod_Kf0bnbH7Ps0hVe'; // #2
    //private $planId = 'price_1JzgZpSHghGIp8I7dj6cyNy7'; // #2
    //private $planId = STRIPE_SUBSCRIPTION_PLAN_ID; // #2
    private $trial_file = '../license/trial-expire.json';
    private $stripe_subscription_id = '../license/stripe-user.json';
    private $phpExit = '<?php exit(); ?>';
    public function __construct() {
        $baseUrl1 = fetchMarketplaceBaseUrl();
        $packageId1 = fetchPackageID();
        $plugin_license_config_url = $baseUrl1.'/api/v2/plugins/'.$packageId1.'/custom-tables/plugin_license_config/';
        $plugin_license_config1 = APIcall("GET",null,$plugin_license_config_url,false);
        if(!empty($plugin_license_config1['Records'])){
            $plugin_license_config = $plugin_license_config1['Records'][0];
            
            $stripeKey = $plugin_license_config['stripe_secret_key'];
            $planId = $plugin_license_config['stripe_subscription_plan_id'];
        }else{
            
            $stripeKey = STRIPE_SECRET_KEY;
            $planId = STRIPE_SUBSCRIPTION_PLAN_ID;
        } 
        $this->isValid = false;
        $this->stripeKey = $stripeKey;
        $this->planId = $planId;
    }
    
    function isValid() {
        //Bought, but uninstalled and reinstalled
        global $baseUrl;
        global $packageId;
        $getURL = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/PayingUsers/';
        
        $checkIfPaid = APIcall("GET", null, $getURL, false);

        foreach ($checkIfPaid['Records'] as $entry){
            if($entry['payingUser'] == $baseUrl){


                /* $file_pointer = "../license/stripe-user.json"; 
    
                // Use unlink() function to delete a file 
                if (!unlink($file_pointer)) { 
                    echo ("$file_pointer cannot be deleted due to an error<br>"); 
                } 
                else { 
                    echo ("$file_pointer has been deleted<br>"); 
                } 
                if (file_exists($file_pointer) == false) {
                    echo ("No<br>");
                }else{
                    echo ("Yes<br>");
                } 

                $url = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/PayingUsers/rows/'.$entry['Id'];
                //$response = $this->callAPI("DELETE", null, $url, null);
                $delete = callAPI("DELETE", null, $url, null); */
                
                
                $subId = $entry['subscriptionId'];
            try{
                \Stripe\Stripe::setApiKey($this->stripeKey);
                $subscription = \Stripe\Subscription::retrieve($subId);
                file_put_contents("../admin/error.php", $subscription);
                $subscription = json_decode($subscription);
                // 
                if($subscription['error']['code'] == "resource_missing"){
                    $this->isValid = false;
                    return $this->isValid;
                }

                if($subscription->id == $subId){
                    //file_put_contents($this->stripe_subscription_id, $phpExit . $subscription->id);
                    file_put_contents($this->stripe_subscription_id, json_encode(array("subscriptionId"=>$subscription->id)));
                    $this->isValid = true;
                    return $this->isValid;
                } 

            } /* catch (Exception $e) {
                 $getMarketplaceDomain = fetchMarketplaceDomain();
                $super_admin_domain_array = explode(",", SUPER_ADMIN_DOMAIN);
                if(!in_array($getMarketplaceDomain, $super_admin_domain_array)){
                   // echo "<script>alert('Unauthorised Host');</script>";
                    //exit();
                    return $this->isValid;
                }else{
                    return $this->isValid;
                } 
            } */
            catch (Exception $e) {
                error_log(json_encode($e));
                file_put_contents("../admin/error.php", $e);
                return null;
            }
            
            


            }
        }
        

        //No Trial, No buy
        if (file_exists($this->trial_file) == false && file_exists($this->stripe_subscription_id) == false) {
            $this->isValid = false;
            return $this->isValid;
        }

        //Bought
        if (file_exists($this->stripe_subscription_id) == true) {
            $subscriptionId = file_get_contents($this->stripe_subscription_id);
            //$subscriptionId = str_replace($phpExit, '', $subscriptionId);
            $subscriptionId = json_decode($subscriptionId,true);
            $subscriptionId = $subscriptionId["subscriptionId"];
            if ($subscriptionId != null) {
                try{
                    \Stripe\Stripe::setApiKey($this->stripeKey);
                    $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                    error_log(json_encode($subscription));

                    if ($subscription->ended_at != null) {
                        $left = $subscription->ended_at - time();
                        $this->isValid = ($left > 0);
                    } else {
                        $this->isValid = true;
                    }
                    return $this->isValid;
                } catch (Exception $e) {
                    error_log(json_encode($e));
                    file_put_contents("../admin/error.php", $e);
                    return null;
                }
            }
        }

        //Trial only
        if (file_exists($this->trial_file) == true) {
            $time = file_get_contents($this->trial_file);
            //$time = str_replace($phpExit, '', $time);
            $time = json_decode($time,true);
            $time = $time["time"];
            // $left = (int) $time - time();
            // //$this->isValid = ($left > 0);
            // if($left > 0) $this->isValid = true;
            if($time > time()){
                $this->isValid = true;
            }
            return $this->isValid;
        }

        return $this->isValid;
    }

    function activate($email, $source) {
        if (!$this->isValid() || file_exists($this->stripe_subscription_id) == false) {
           
            try{
            \Stripe\Stripe::setApiKey($this->stripeKey); //#1
            /* try
            { */
                $customer = \Stripe\Customer::create([
                    'email' => $email,
                    'source' => $source,
                ]);

                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer->id,
                    'items' => [['plan' => $this->planId]],
                    'trial_from_plan' => true,
                ]);

                global $baseUrl;
                global $packageId;
                $url = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/PayingUsers/rows';
                //file_put_contents("../admin/error.php", $url);
                $data = [
                    'payingUser' => $baseUrl,
                    'subscriptionId' => $subscription->id
                ];
                $create_new_payingUser = APIcall("POST", null, $url, $data);


                error_log(json_encode($subscription));
                if ($subscription->id != null) {
                    //file_put_contents($this->stripe_subscription_id, $phpExit . $subscription->id);
                    file_put_contents($this->stripe_subscription_id, json_encode(array("subscriptionId"=>$subscription->id)));
                }
            } catch (Exception $e) {
                error_log(json_encode($e));
                file_put_contents("../admin/error.php", $e);
                return null;
            }
        }
    }

    function deactivate() {
        if ($this->isValid()) {
            // TODO:
            // Call to your service to de-activate this account
            // In this sample code, we will check with Stripe server

            if (file_exists($this->stripe_subscription_id) == true) {
                $subscriptionId = file_get_contents($this->stripe_subscription_id);
                //$subscriptionId = str_replace($phpExit, '', $subscriptionId);
                $subscriptionId = json_decode($subscriptionId,true);
                $subscriptionId = $subscriptionId["subscriptionId"];
                if ($subscriptionId != null) {
                    \Stripe\Stripe::setApiKey($this->stripeKey);
                    $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                    if ($subscription->ended_at == null) {
                        $subscription->cancel();
                    }
                }
            }
        }
    }
}
?>
