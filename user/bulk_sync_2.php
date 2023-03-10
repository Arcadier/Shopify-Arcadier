<?php

ini_set('max_execution_time', 0); // 0 = Unlimited
ini_set('memory_limit','10240M');

ignore_user_abort(True);
set_time_limit(0);
//error_reporting(E_ALL);
//ini_set('display_errors','On');
require 'callAPI.php';
require 'api.php';
require_once("shopify_functions.php");
$arc = new ApiSdk();

require 'vendor/autoload.php';

use AsyncPHP\Doorman\Manager\ProcessManager;
use AsyncPHP\Doorman\Task\ProcessCallbackTask;



$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i");

$baseUrl = getMarketplaceBaseUrl();
$admin_token = $arc->AdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];


$userEmail = $result['Email'];
$userDisplayName = $result['DisplayName'];

$packageId = getPackageID();

// Query user authentication 

$auth = array(array('Name' => 'merchant_guid', "Operator" => "in",'Value' => $userId));
$url =  $baseUrl . '/api/v2/plugins/'. $packageId .'/custom-tables/auth';
$authDetails =  callAPI("POST", $admin_token, $url, $auth);

$shop_secret_key = $authDetails['Records'][0]['secret_key'];
$shop_api_key = $authDetails['Records'][0]['api_key'];
$shop = $authDetails['Records'][0]['shop'];
$auth_id = $authDetails['Records'][0]['Id'];
$access_token= $authDetails['Records'][0]['access_token'];

$total_created = 0;
$total_unchanged = 0;
$total_changed = 0;

//$userToken = $_COOKIE["webapitoken"];
//$url = $baseUrl . '/api/v2/users/'; 
//$result = callAPI("GET", $userToken, $url, false);

if ($result['CustomFields'] != null)  {

    foreach ($result['CustomFields'] as $cf) {
        if ($cf['Name'] == 'auto_sync_list' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                    $sync_items_list = $cf['Values'][0];
                    $sync_items_list = json_decode($sync_items_list,true);
                   // echo (json_encode($sync_items_list));
                    break;
                   
        }
    
    }

}

// error_log(json_encode($sync_items_list));


// get the custom field id to tag that the items are from shopify

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . $packageId;
$packageCustomFields = callAPI("GET", null, $url, false);

$is_shopify_code = '';

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'is_shopify_item' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $is_shopify_code = $cf['Code'];
    }
}

$time_start = microtime(true);
$arcadier_categories = $arc->getCategories(1000, 1);
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
//execution time of the script
// error_log('<b>Total Execution Time of getting arc categories:</b> '.$execution_time.' seconds');
$arcadier_categories = $arcadier_categories['Records'];

//category mapping query

$time_start = microtime(true);
$data = array(array('Name' => 'merchant_guid', "Operator" => "equal",'Value' => $userId));
//error_log(json_encode($data));

$url =  $baseUrl . '/api/v2/plugins/'. $packageId.'/custom-tables/map';
$category_map  =  callAPI("POST", $admin_token, $url, $data);    
//error_log(json_encode($category_map));



$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
//execution time of the script
//error_log('<b>Total Execution Time of getting category mapping:</b> '.$execution_time.' seconds');

$product_count = shopify_product_count($access_token, $shop);
$total = $product_count['count'];
//step 1. Get all shopify products

$count_chosen =  count($sync_items_list);

//$shopify_products = shopify_get_all_products($access_token, $shop);

//send initial edm to let users know the import has started
$html = "<html><body> <h2>Import Started</h2> <p style=\"background-color: white\"> Hi $userDisplayName !</p> <br> 
Your Shopify products import has started at " . date("h:i:sa") . "<br>
<b> </b> $count_chosen products were found. <br>
<b> You will receive another notification once the import is completed. <br>
</body> </html>";

$subject= 'Product import started for ' . $shop;

$arc->sendEmail($userEmail, $html, $subject);



$url =  $baseUrl . '/api/v2/plugins/'. getPackageID() .'/custom-tables/batch_data?pageSize=1&sort=-CreatedDateTime';
$batchDetails =  callAPI("GET",$admin_token, $url, false);

$current_batch_no = $batchDetails ['Records'][0]['batch_no'];

$current_batch_no = $current_batch_no == null ? 0 : $current_batch_no;

$batch_no = ++$current_batch_no;

    $batch_data = [

                'batch_no' => $batch_no,
                'merchant_guid' => $userId
            
            ];
            
    $create_batch_no = $arc->createRowEntry($packageId, 'batch_data', $batch_data);

    
$time_start = microtime(true);
$chunked = array_chunk($sync_items_list, 4000);


    foreach($chunked as $index => $chunk) {

          $batch_details = [

                'batch_sequence' => $index,
                'batch_data' => $chunk,
                'merchant_guid' => $userId,
                'status' => 'pending',
                'batch_no' => $batch_no
               
            ];

            $create_batch = $arc->createRowEntry($packageId, 'batch_sync_data', $batch_details);

            if ($create_batch['Id']) {
              
                
            }

            
        //save in the custom table 


    // bulk_sync_items($chunk, $access_token, $shop,$baseUrl,$userId,$admin_token, $packageId,$arc, $is_shopify_code,$arcadier_categories, $category_map,$userEmail);
    // error_log('sleeping for 5seconds') ;
    // error_log(count($chunk));
    // sleep(5);
   
    }
include 'batch_sync.php';
include 'batch_sync_2.php';



// $manager = new ProcessManager();

// $task = new ProcessCallbackTask(function () {
//     for ($i = 0; $i < 5; $i++) {
//        error_log("child tick {$i}") ;
//         sleep(1);
//         // include 'batch_sync.php';
//     }
// });

// $manager->addTask($task);

// $loop = React\EventLoop\Factory::create();

// $loop->addPeriodicTimer(0.1, function () use ($manager, $loop) {
//     if (!$manager->tick()) {
//         $loop->stop();
//     }
// });

// $loop->run();



    // $task1 = new ProcessCallbackTask(function () {
   
    // });
 
    // $task2 = new ProcessCallbackTask(function () {
      
    // });
 
    // $manager = new ProcessManager();
 
    // $manager->addTask($task1);
    // $manager->addTask($task2);
    
    // while ($manager->tick()) {
    //     usleep(250);
    // }
  
   
   
    $time_end = microtime(true);
    //dividing with 60 will give the execution time in minutes other wise seconds
    $execution_time = ($time_end - $time_start);

    //execution time of the script
    error_log('<b>Total Execution Time of getting bulk sync products:</b> '.$execution_time.' seconds');
//}