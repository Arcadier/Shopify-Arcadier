<?php
	include 'callAPI.php';
	
	$contentBodyJson = file_get_contents('php://input');
	$content = json_decode($contentBodyJson, true);

	// error_log(json_encode($content), 3, "tanoo_log.php");
	if($content['id'] == ){}
	echo json_encode($content);
	
?>