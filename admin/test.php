<?php
die;
/* include 'magento_functions.php';
include 'api.php';
$arc = new ApiSdk();
$mag = new MagSdk();
$pack_id = getPackageID();
$marketplace_domain = getMarketplaceDomain();
date_default_timezone_set(TIME_ZONE_PLUGIN);
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$getAllMerchants = $arc->getAllMerchants();
echo "<pre>"; print_r($getAllMerchants); die; */
?>
<?php
require '../license/license.php';
require 'callAPI.php';
require 'admin_token.php';
$baseUrl = $_COOKIE["marketplace"];
$GETurl = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/?pageSize=100000&sort=CreatedDateTime';
    $table = callAPI("GET",null,$GETurl,false);
    $adminrowID = false;
    $hasUsed = false;
    foreach($table['Records'] as $row){
        if($row['BaseURL']==$baseUrl)
        {
            $adminrowID = $row['Id'];
            $hasUsed = $row['HasUsedTrial'];
        }
    }
$file_pointer = "../license/trial-expire.json"; 
   
            // Use unlink() function to delete a file 
            if (!unlink($file_pointer)) { 
                echo ("$file_pointer cannot be deleted due to an error<br>"); 
            } 
            else { 
                echo ("$file_pointer has been deleted<br>"); 
            } 
            if (file_exists('../license/trial-expire.json') == false) {
                echo ("No<br>");
            }else{
                echo ("Yes<br>");
            } 
            foreach($table['Records'] as $row){
                
                    $adminrowID = $row['Id'];
            $url = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/rows/'.$adminrowID;
            //$response = $this->callAPI("DELETE", null, $url, null);
            $delete = callAPI("DELETE", null, $url, null); 
            }
 
$baseUrl = 'https://'.$_COOKIE["marketplace"];
$GETurl = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/PayingUsers/?pageSize=100000&sort=CreatedDateTime';
    $table = callAPI("GET",null,$GETurl,false);
    $adminrowID = false;
    $hasUsed = false;
    foreach($table['Records'] as $row){
        if($row['payingUser']==$baseUrl)
        {
            $adminrowID = $row['Id'];
            $hasUsed = $row['subscriptionId'];
        }
    }
$file_pointer = "../license/stripe-user.json"; 
   
            // Use unlink() function to delete a file 
            if (!unlink($file_pointer)) { 
                echo ("$file_pointer cannot be deleted due to an error<br>"); 
            } 
            else { 
                echo ("$file_pointer has been deleted<br>"); 
            } 
            if (file_exists('../license/trial-expire.json') == false) {
                echo ("No<br>");
            }else{
                echo ("Yes<br>");
            } 
            foreach($table['Records'] as $row){
                
                    $adminrowID = $row['Id'];
            $url = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/PayingUsers/rows/'.$adminrowID;
            //$response = $this->callAPI("DELETE", null, $url, null);
            $delete = callAPI("DELETE", null, $url, null);
            }






/* 
            $baseUrl = 'https://'.$_COOKIE["marketplace"];
            $GETurl = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/plugin_license_config/?pageSize=100000&sort=CreatedDateTime';
            $table = callAPI("GET",null,$GETurl,false);
            foreach($table['Records'] as $row){
                
                    $adminrowID = $row['Id'];
            $url = $baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/plugin_license_config/rows/'.$adminrowID;
            //$response = $this->callAPI("DELETE", null, $url, null);
            $delete = callAPI("DELETE", null, $url, null);
            } */


            // $baseUrl = getMarketplaceBaseUrl();
            // $packageId = getPackageID();
                $file_pointer = "../license/stripe-user.json";
                /* if (!unlink($file_pointer)) { 
                    echo ("$file_pointer cannot be deleted due to an error<br>"); 
                } 
                else { 
                    echo ("$file_pointer has been deleted<br>"); 
                } */ 
                if (file_exists($file_pointer) == false) {
                    echo ("No<br>");
                }else{
                    echo ("Yes<br>");
                } 
$licence = new License();
if ($licence->isValid()) {
    //$location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    //header('Location: ' . $location); 
    ?>
<!-- Your plug-in's index.html
<p> Payment done. You can now use the Plug-In</p>
<script>
	toastr.success("Welcome", "Success");
</script> -->
<?php
 } else {
    $time = file_get_contents('../license/trial-expire.json');
//$time = str_replace($phpExit, '', $time);
echo "<pre>"; print_r($time);
$time = json_decode($time,true);
echo "<pre>"; print_r($time);
//echo $time."<br>";
$time = $time["time"];
echo $time."<br>";
    echo time()."<br>"; 
    echo 'test';

                $file_pointer = "license/stripe-user.json"; 
    
                // Use unlink() function to delete a file 
                /* if (!unlink($file_pointer)) { 
                    echo ("$file_pointer cannot be deleted due to an error<br>"); 
                } 
                else { 
                    echo ("$file_pointer has been deleted<br>"); 
                } */ 
                if (file_exists($file_pointer) == false) {
                    echo ("No<br>");
                }else{
                    echo ("Yes<br>");
                } 


    die;
    exit(); 
    $location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    error_log($location);
    header('Location: ' . $location);
} 
?>
<?php
/* require '../license/license.php';
$licence = new License();
if (!$licence->isValid()) {
    $time = file_get_contents('../license/trial-expire.json');
//$time = str_replace($phpExit, '', $time);
echo "<pre>"; print_r($time);
$time = json_decode($time,true);
echo "<pre>"; print_r($time);
//echo $time."<br>";
$time = $time["time"];
echo $time."<br>";
    echo time()."<br>"; 
    echo 'test';
    die;
    exit();
} */

?>

<?php
/* require 'callAPI.php';
require 'admin_token.php';

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();
echo 'it is working'; */
?>
