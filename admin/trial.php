<?php
require 'callAPI.php';
require 'admin_token.php';
require 'defines.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentBodyJson = file_get_contents('php://input');
    $content = json_decode($contentBodyJson, true);
    $phpExit = '<?php exit(); ?>';
    $adminID = $content['adminID'];
    $baseUrl = $content['baseURL'];
    $packageId = $content['packageID'];

    $baseUrl1 = getMarketplaceDomain();
    $packageId1 = getPackageID();
    $plugin_license_config_url = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/plugin_license_config/';
    $plugin_license_config1 = callAPI("GET",null,$plugin_license_config_url,false);
    if(!empty($plugin_license_config1['Records'])){
        $plugin_license_config = $plugin_license_config1['Records'][0];
        $total_trial_days = (float) $plugin_license_config['total_trial_days'];
    }else{
        $total_trial_days = TOTAL_TRIAL_DAYS;
    } 



    $GETurl = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/';
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
    
    if (file_exists('../license/trial-expire.json') == false) {
        if (!$adminrowID)
        {
            //$time = time() + 30; //seconds
            //$time = time() + 10 * 60; //seconds
            //$time = time() + 15 * 24 * 60 * 60; //(15days * 24 hours * 60 minutes * 60 seconds).
            $time = time() + $total_trial_days * 24 * 60 * 60; //(15days * 24 hours * 60 minutes * 60 seconds).
            file_put_contents('../license/trial-expire.json', json_encode(array("time"=>$time)));
            $url = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/rows';
            $data = [ 
                'BaseURL' => $baseUrl, 
                'HasUsedTrial' => 'Yes' 
            ];
            
            $createnewrow = callAPI("POST", null, $url, $data);
            echo $time;
        }
        else{
            echo "Trial Used up on this marketplace";
        }
    }
    else {
        $time = file_get_contents('../license/trial-expire.json');
        //$time = str_replace($phpExit, '', $time);
        $time = json_decode($time,true);
        //echo $time."<br>";
        $time = $time["time"];
        if($time > time()){
            echo $time;
        }
        else{
            //echo $time."<br>";
            //echo time()."<br>";
            /* $file_pointer = "../license/trial-expire.json"; 
   
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

            $url = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/rows/'.$adminrowID;
            //$response = $this->callAPI("DELETE", null, $url, null);
            $delete = callAPI("DELETE", null, $url, null); */


            //echo "Time up";
            //$url = 'https://'.$baseUrl.'/api/v2/plugins/'.$packageId.'/custom-tables/Tanoo/rows/'.$adminrowID;
            //$data = [
            //   'HasUsedTrial' => 'Yes'
           // ];
            //$update = callAPI("PUT", null, $url, $data);
            echo "Time up";
            echo 0;
        }
    }
}

?>
