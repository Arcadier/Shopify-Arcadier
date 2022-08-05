<?php
/* require '../license/license.php';
// require 'callAPI.php';
// require 'admin_token.php';

// $baseUrl = getMarketplaceBaseUrl();
// $packageId = getPackageID();

$licence = new License();
if ($licence->isValid()) {
    //$location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    //header('Location: ' . $location); */
    ?>
<!-- Your plug-in's index.html
<p> Payment done. You can now use the Plug-In</p>
<script>
	toastr.success("Welcome", "Success");
</script> -->
<?php
/* } else {
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
    $location = $baseUrl . '/admin/plugins/' . $packageId . '/subscribe.php';
    error_log($location);
    header('Location: ' . $location);
} */
?>
<?php
require '../license/license.php';
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
}

?>

<?php
require 'callAPI.php';
require 'admin_token.php';

$baseUrl = getMarketplaceBaseUrl();
$packageId = getPackageID();
echo 'it is working';
?>
