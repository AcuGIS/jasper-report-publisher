<?php
	session_start(['read_and_close' => true]);
	
	require('admin/incl/const.php');
	if(!isset($_SESSION[SESS_USR_KEY])) {
			header('Location: login.php');
			exit;
	}
	
	$url = 'http://localhost:8080/JasperReportsIntegration/report_image?'.$_SERVER['QUERY_STRING'];
	$cookiefile = '/tmp/qwc2cookie'.session_id();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	#curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
	
	$response = curl_exec($ch);
	if (curl_error($ch)) {
		#unlink($cookiefile);
		echo '';
	}else{
		$responseHeaders = curl_getinfo($ch);
		$content_length  = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		curl_close($ch);

		header('Content-Type: '.$responseHeaders['content_type'], true);
		if($content_length != -1){
			header('Content-Length: '.$content_length, true);
		}
		echo $response;
	}
?>