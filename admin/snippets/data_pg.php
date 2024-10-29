var VARNAME = 
<?php
	const PERMALINK_DONT_COUNT = true;
	const CACHE_PERIOD = CACHE_PERIOD_SECONDS;
	const PGLINK_ID = PGLINK_ID_VALUE;
	const GEOM_COL = "GEOM_COL_VAL";
	
	include('../../admin/incl/index_prefix.php');
	include('../../admin/class/pglink.php');
	
	function new_proj_db(){
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$pgl_obj = new pglink_Class($database->getConn(), SUPER_ADMIN_ID);
		
		$pg_res = $pgl_obj->getById(PGLINK_ID);
		$pgr = pg_fetch_assoc($pg_res);
		pg_free_result($pg_res);
		
		$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], "PG_SCHEMA");
		return $proj_db;
	}
	
	header('Content-Type: application/javascript');
	
	if(CACHE_PERIOD == 0){
		$proj_db = new_proj_db();
		$proj_db->getGeoJSON("PG_SCHEMA", "PG_TBL", GEOM_COL);
		
	}else {
		$js_file = CACHE_DIR.'/'.MAP_ID.'/VARNAME_data.js';
		if(!is_file($js_file) || (time() - filemtime($js_file)) > CACHE_PERIOD){
			
			if(!is_dir(CACHE_DIR.'/'.MAP_ID)){
				mkdir(CACHE_DIR.'/'.MAP_ID);
			}
			
			$fout = fopen($js_file, 'w');
			ob_start(function($buffer) use($fout){
			    fwrite($fout, $buffer);
			}, 1024); //notice the use of chunk_size == 1
				
			$proj_db = new_proj_db();
			$proj_db->getGeoJSON("PG_SCHEMA", "PG_TBL", GEOM_COL);
			
			ob_end_clean();
			
			fclose($fout);
		}
		readfile($js_file);
	}
?>
;