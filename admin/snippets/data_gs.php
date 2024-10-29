var VARNAME = 
<?php
	const PERMALINK_DONT_COUNT = true;
	const CACHE_PERIOD = CACHE_PERIOD_SECONDS;
	const GSLINK_ID = GSLINK_ID_VALUE;
	const GS_WS = 'GS_WS_VALUE';
	const GS_LAYER = 'GS_LAYER_VALUE';
	
	include('../../admin/incl/index_prefix.php');
	include('../../admin/class/gslink.php');
	
	function full_url($gsl_id, $ws, $layer){
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$gsl_obj = new gslink_Class($database->getConn(), SUPER_ADMIN_ID);
		
		$gs_res = $gsl_obj->getById($gsl_id);
		$gsr = pg_fetch_assoc($gs_res);
		pg_free_result($gs_res);
		
		if(strlen($gsr['username']) > 0){
			$proto = 'https://';
			$url = '';
			if(0 === strpos($gsr['url'], 'https://')){
				$url = substr($gsr['url'], 8);
			}else if(0 === strpos($gsr['url'], 'http://')){
				$proto = 'http://';
				$url = substr($gsr['url'], 7);
			}else{
				$url = $gsr['url'];	//only hostname, no proto
			}
			
			return $proto.$gsr['username'].":".$gsr['password'].'@'.$url. "/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=".$ws.":".$layer."&maxFeatures=3000&outputFormat=application/json";
		}else{
			return 																					 $gsr['url']. "/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=".$ws.":".$layer."&maxFeatures=3000&outputFormat=application/json";
		}
	}
	
	header('Content-Type: application/javascript');

	$gs_url = full_url(GSLINK_ID, GS_WS, GS_LAYER);

	if(CACHE_PERIOD == 0){
		readfile($gs_url);
	}else {
		$js_file = CACHE_DIR.'/'.MAP_ID.'/VARNAME_data.js';
		if(!is_file($js_file) || (time() - filemtime($js_file)) > CACHE_PERIOD){
			
			if(!is_dir(CACHE_DIR.'/'.MAP_ID)){
				mkdir(CACHE_DIR.'/'.MAP_ID);
			}
			
			$fin  = fopen($gs_url, 'r');
			$fout = fopen($js_file, 'w');
			
			while(($contents = fread($fin, 4096))){
				fwrite($fout, $contents);
			}
			fclose($fin);
			fclose($fout);
		}
		readfile($js_file);
	}
?>
;

{
	let qc_id = 0;
	VARNAME.features.forEach(function (feat) { feat.properties["qc_id"] = qc_id; qc_id = qc_id + 1;});

	if(VARNAME.features[0].properties["gid"] === undefined){
		let gid = 0;
		VARNAME.features.forEach(function (feat) { feat.properties["gid"] = gid; gid = gid + 1;});
	}
}