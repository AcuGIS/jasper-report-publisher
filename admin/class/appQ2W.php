<?php
class AppQ2W {
	public static function parseIndex($html_dir, $lines = null){
		$ds = array();
		$ls = array();
		$ss = array();
		$use_dt = 0;
		$is_public = false;
		$qgis_layout = '';
		
		$env_content = file_get_contents($html_dir.'/env.php');
		if(preg_match('/const SHOW_DATATABLES = (.*);/', $env_content, $matches)){
			$use_dt = ($matches[1] == 'True') ? 1 : 0;
		}
		
		if(preg_match('/const JS_VARNAMES = array\(\);/', $env_content, $matches)){
			$use_dt = -1;
		}
		
		if(preg_match('/const IS_PUBLIC = (.*);/', $env_content, $matches)){
			$is_public = ($matches[1] == 'True');
		}
		
		if(preg_match('/const QGIS_LAYOUT = "(.*)";/', $env_content, $matches)){
			$qgis_layout = $matches[1];
		}
		
		if($lines == null){
			$lines = file($html_dir.'/index.php');
		}
		
		foreach ($lines as $i => $line) {
			if(preg_match('/<script src="data_(file|pg|gs)(\d+)\.php<\?=\$permalink\?>" data\-jfn="(.*)"/', $line, $matches)){
				$dsi = $matches[2];
				$v = array('data_type' => $matches[1], 'line' => $line, 'ln' => $i, 'name' => $matches[3], 'json_filename' => $matches[3],
							'pglink_id' => '0', 'pg_schema' => '0', 'pg_tbl' => '0', 'pg_geom' => 'geom',
							'gslink_id' => '0', 'gs_ws' => '', 'gs_layer' => '',
							'pg_cache_per' => 'Off', 'pg_cache_val' => '', 'gs_cache_per' => 'Off', 'gs_cache_val' => '');
				
				$content = null;
				if($matches[1] == 'pg'){
					$content = file_get_contents($html_dir.'/data_pg'.$matches[2].'.php');
					if(preg_match('/const PGLINK_ID = ([0-9]+);/', $content, $pg_matches)){
						$v['pglink_id'] = $pg_matches[1];
					}
					
					if(preg_match('/const GEOM_COL = "(.*)";/', $content, $pg_matches)){
						$v['pg_geom'] = $pg_matches[1];
					}
					
					if(preg_match('/\$proj_db->getGeoJSON\("(.*)", "(.*)", GEOM_COL\);/', $content, $pg_matches)){
						$v['pg_schema'] = $pg_matches[1];
						$v['pg_tbl'] 		= $pg_matches[2];
					}
					
					
				}else if($matches[1] == 'gs'){
					$content = file_get_contents($html_dir.'/data_gs'.$matches[2].'.php');
					if(preg_match('/const GSLINK_ID = ([0-9]+);/', $content, $gs_matches)){
						$v['gslink_id'] = $gs_matches[1];
					}
					
					if(preg_match('/const GS_WS = \'(.*)\';/', $content, $gs_matches)){
						$v['gs_ws'] = $gs_matches[1];
					}
					
					if(preg_match('/const GS_LAYER = \'(.*)\';/', $content, $gs_matches)){
						$v['gs_layer'] = $gs_matches[1];
					}
				}
				
				if($content && preg_match('/const CACHE_PERIOD = ([0-9]+)/', $content, $matches)) {
					$cache_seconds = $matches[1];
					$per = App::find_per($cache_seconds);
					$v[$v['data_type'].'_cache_val'] = (TIME_MAP[$per] == 0) ? 0 : $cache_seconds / TIME_MAP[$per];
					$v[$v['data_type'].'_cache_per'] = $per;
				}

				$ds[$dsi] = $v;
			
			}else if(preg_match('/<\?php include\("layer_sentinel(\d+)\.php"\); \?>/', $line, $matches)){
				$lyi = $matches[1];
				$v = array('layer_type' => 'sentinel', 'li' => $lyi);
				
				$sent_lines = file($html_dir.'/layer_sentinel'.$lyi.'.php');
				$v['date_from'] = preg_match("/: '(.*)';/", $sent_lines[2], $date_matches) ? $date_matches[1] : '0000-00-00';
				$v['date_to']		= preg_match("/: '(.*)';/", $sent_lines[3], $date_matches) ? $date_matches[1] : '0000-00-00';
				$v['se_proxy'] = is_file($html_dir.'/proxy_sentinel'.$lyi.'.php');
				
				$ss[$lyi] = $v;
				
			}else if(preg_match('/<\?php include\("layer_(wms|gs_geo)(\d+)\.php"\); \?>/', $line, $matches)){
				$lyi = $matches[2];
				$v = array('layer_type' => $matches[1], 'line' => $line, 'ln' => $i, 'name' => '',
									 'ly_ws'	=> '', 'ly_layer' => '', 'ly_user' => '', 'ly_pwd' => '',
								 'wms_user' => '', 'wms_pwd' => '', 'wms_url' => '', 'wms_ws' => '', 'wms_layer' => '',
							 'gs_geo_host' => '', 'gs_geo_user' => '', 'gs_geo_pwd' => '', 'gs_geo_ws' => '', 'gs_geo_layer' => '',
							 'gs_geo_color' => '', 'gs_geo_opacity' => '', 'gs_geo_fill_color' => '', 'gs_geo_fill_opacity' => '',
							 'gs_geo_cache_per' => 'minutes', 'gs_geo_cache_val' => '');
				
				$content = file_get_contents($html_dir.'/layer_wms'.$lyi.'.php');
				
				if(preg_match('/var (.*) = L\.(WMS\.layer|geoJson\()/', $content, $var_matches)){
					$v['layer_varname'] = $var_matches[1];
				}
				
				if(preg_match('/var .* = L\.WMS\.layer\("(.*)", "(.*):(.*)",\s+{/', $content, $matches)){
					if(str_starts_with($matches[1], 'proxy_wms')){	// if secured through proxy
						$content = file_get_contents($html_dir.'/proxy_wms'.$lyi.'.php');
						if(preg_match('/const BASE_URL = \'(http[s]?):\/\/(.*):(.*)@(.*)\';/', $content, $url_matches)){
							$v['wms_user'] = $url_matches[2];
							$v['wms_pwd']  = $url_matches[3];
							$v['wms_url']  = $url_matches[1].'://'.$url_matches[4];
						}
					}else{
						$v['wms_url'] 		= $matches[1];
					}
					$v['wms_ws']	 		= $matches[2];
					$v['wms_layer']	= $matches[3];
				}
				$v['name'] = $v['wms_ws'].':'.$v['wms_layer'];
				
				if(is_file($html_dir.'/layer_gs_geo'.$lyi.'.php')){
					$content = file_get_contents($html_dir.'/layer_gs_geo'.$lyi.'.php');
					if(preg_match('/:\/\/(.*):(.*)@(.*)\/geoserver\/.*&typeName=(.*):([^&]+)/', $content, $matches)){
						$v['gs_geo_host'] 		= $matches[3];
						$v['gs_geo_user'] 	= $matches[1];
						$v['gs_geo_pwd']  	= $matches[2];
						$v['gs_geo_ws']	 		= $matches[4];
						$v['gs_geo_layer']	= $matches[5];
					}
					
					if(preg_match('/color: "(.*)",/', $content, $matches)){	$v['gs_geo_color'] = $matches[1]; }
					if(preg_match('/fillColor: "(.*)",/', $content, $matches)){	$v['gs_geo_fill_color'] = $matches[1]; }
					if(preg_match('/opacity: ([0-9\.]+),/', $content, $matches)){	$v['gs_geo_opacity'] = $matches[1]; }
					if(preg_match('/fillOpacity: "([0-9\.]+)",/', $content, $matches)){	$v['gs_geo_fill_opacity'] = $matches[1]; }
					
					if(preg_match('/const CACHE_PERIOD = ([0-9]+)/', $content, $matches)) {
						$cache_seconds = $matches[1];
						$per = App::find_per($cache_seconds);
						$v['gs_geo_cache_val'] = (TIME_MAP[$per] == 0) ? 0 : $cache_seconds / TIME_MAP[$per];
						$v['gs_geo_cache_per'] = $per;
					}
				}
				
				$ls[$lyi] = $v;
			}
		}
		
		return [$ds, $ls, $ss, $use_dt, $is_public, $qgis_layout, APP_TYPE_Q2W];
	}
	
	public static function updateIndex($details, $html_dir, $data_dir){
		$changes = 0;
		$js_varnames = array();
		$lines = file($html_dir.'/index.php');
		list($fds,$lys, $ses, $use_dt, $is_public, $qgis_layout, $map_type) = AppQ2W::parseIndex($html_dir, $lines);	// file data sources
		
		$newId = $details['id'];
				
		foreach($fds as $dsi => $ds) {
			
			if(	empty($details['data_type'.$dsi])){		// if we have datasource from form					
				continue;
			}

			$json_filename = $ds['json_filename'];
			
			$js_varname = App::extract_varname($data_dir.'/'.$json_filename);
			if(isset($details['use_datatable'])){
				array_push($js_varnames, $js_varname);
			}
			
			// update fds lines	
			if($details['data_type'.$dsi] == 'file'){
				
				$vars = ['DATA_FILE' => '../../../data/'.$newId.'/'. $json_filename];
				App::update_template('../snippets/data_file.php', $html_dir.'/data_file'.$dsi.'.php', $vars);

				$lines[$ds['ln']] = '<script src="data_file'.$dsi.'.php<?=$permalink?>" data-jfn="'.$json_filename.'"></script>'."\n";
				$changes = $changes + 1;
			}else{
					
				if($details['data_type'.$dsi] == 'pg'){
					
					$cache_seconds = TIME_MAP[$details['pg_cache_per'.$dsi]] * intval($details['pg_cache_val'.$dsi]);
					
					$vars = [ 'VARNAME' => $js_varname, 'CACHE_PERIOD_SECONDS' => $cache_seconds,
						'PGLINK_ID_VALUE' => $details['pglink_id'.$dsi], 'PG_SCHEMA'  => $details['pg_schema'.$dsi], 'PG_TBL' => $details['pg_tbl'.$dsi],
						'GEOM_COL_VAL' => $details['pg_geom'.$dsi]
					];
					App::update_template('../snippets/data_pg.php', $html_dir.'/data_pg'.$dsi.'.php', $vars);
					$lines[$ds['ln']] = '<script src="data_pg'.$dsi.'.php<?=$permalink?>" data-jfn="'.$json_filename.'"></script>'."\n";
					$changes = $changes + 1;
					
				}else if($details['data_type'.$dsi] == 'gs'){
					
					$cache_seconds = TIME_MAP[$details['gs_cache_per'.$dsi]] * intval($details['gs_cache_val'.$dsi]);					
					$vars = ['VARNAME' => $js_varname, 'CACHE_PERIOD_SECONDS' => $cache_seconds,
						'GSLINK_ID_VALUE' => $details['gslink_id'.$dsi], 'GS_WS_VALUE' => $details['gs_ws'.$dsi], 'GS_LAYER_VALUE' => $details['gs_layer'.$dsi]
					];
					App::update_template('../snippets/data_gs.php', $html_dir.'/data_gs'.$dsi.'.php', $vars);
					$lines[$ds['ln']] = '<script src="data_gs'.$dsi.'.php<?=$permalink?>" data-jfn="'.$json_filename.'"></script>'."\n";
					$changes = $changes + 1;
				}
			}
		}
		
		$sidebar_included = false;
		foreach($lys as $lyi => $ly) {
			
			if(	empty($details['layer_type'.$lyi]) ){	// if we have layer from form
				continue;
			}

			// update fds lines	
			if($details['layer_type'.$lyi] == 'wms'){
				
				// update url,ws,layer in WMS file
				$content = file_get_contents($html_dir.'/layer_wms'.$lyi.'.php');
				
				if(!empty($details['wms_user'.$lyi])){	// if WMS is secured
					$wms_url = $details['wms_url'.$lyi];
					$pos = strpos($wms_url, '://');
					$auth_url = substr($wms_url, 0, $pos).'://'.$details['wms_user'.$lyi].':'.$details['wms_pwd'.$lyi].'@'.substr($wms_url, $pos + 3);
					
					$vars = ['BASE_URL_VALUE' => $auth_url];
					App::update_template('../snippets/proxy_wms.php', $html_dir.'/proxy_wms'.$lyi.'.php', $vars);
					
					$details['wms_url'.$lyi] = 'proxy_wms'.$lyi.'.php<?=$permalink?>';
				}
				
				$replacement = ' = L.WMS.layer("'.$details['wms_url'.$lyi].'", "'.$details['wms_ws'.$lyi].':'.$details['wms_layer'.$lyi].'", {';
				$content = preg_replace('/ = L\.WMS\.layer\("(.*)", "(.*):(.*)",\s+{/', $replacement, $content);
				file_put_contents($html_dir.'/layer_wms'.$lyi.'.php', $content);

				$lines[$ly['ln']] = '<?php include("layer_wms'.$lyi.'.php"); ?>'."\n";
				$changes = $changes + 1;
			
			}else if($details['layer_type'.$lyi] == 'gs_geo'){
				
				$cache_seconds = TIME_MAP[$details['gs_geo_cache_per'.$lyi]] * intval($details['gs_geo_cache_val'.$lyi]);
				
				// add layer varname to show up in datatables
				array_push($js_varnames, $details['layer_varname'.$lyi].'_data');
				
				$proto = 'https://';
				$url = '';
				if(0 === strpos($details['gs_geo_host'.$lyi], 'https://')){
					$url = substr($details['gs_geo_host'.$lyi], 8);
				}else if(0 === strpos($details['gs_geo_host'.$lyi], 'http://')){
					$proto = 'http://';
					$url = substr($details['gs_geo_host'.$lyi], 7);
				}else{
					$url = $details['gs_geo_host'.$lyi];	//only hostname, no proto
				}
				
				$full_url = $proto.$details['gs_geo_user'.$lyi].":".$details['gs_geo_pwd'.$lyi].'@'.$url. "/geoserver/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=" . $details['gs_geo_ws'.$lyi] . ":" . $details['gs_geo_layer'.$lyi] . "&maxFeatures=3000&outputFormat=application/json";
				
				$vars = ['VARNAME' => $details['layer_varname'.$lyi], 'FULL_URL' => $full_url, 'CACHE_PERIOD_SECONDS' => $cache_seconds,
					'STYLE_COLOR' 		 => $details['gs_geo_color'.$lyi],  		'STYLE_OPACITY' 		 => $details['gs_geo_opacity'.$lyi],
					'STYLE_FILL_COLOR' => $details['gs_geo_fill_color'.$lyi], 'STYLE_FILL_OPACITY' => $details['gs_geo_fill_opacity'.$lyi],
				];
				
				App::update_template('../snippets/layer_gs_geo.php', $html_dir.'/layer_gs_geo'.$lyi.'.php', $vars);
				
				$line = '';
				if(!$sidebar_included){
					$line = 'map.addControl(new sidebarControl());';
					$sidebar_included = true;	// include sidebar once
				}
				$line .= '<?php include("layer_gs_geo'.$lyi.'.php"); ?>'."\n";
				
				$lines[$ly['ln']] = $line;
				$changes = $changes + 1;
			}
		}
		
		foreach($ses as $li => $se) {
			if($se['layer_type'] == 'sentinel'){
				$sent_lines = file($html_dir.'/layer_sentinel'.$li.'.php');
				$sent_lines[2] = str_replace($se['date_from'], $details['from'.$li], $sent_lines[2]);
				if(isset($details['disable_to'.$li])){
					$details['to'.$li] = '';
				}
				$sent_lines[3] = str_replace($se['date_to'], $details['to'.$li], $sent_lines[3]);
				
				$sent_url = (preg_match('/\$sent_layer_url = \'(.*)\';/', $sent_lines[5], $matches)) ? $matches[1] : '';

				if(isset($details['se_proxy'.$li])) {
					
					// replace url in $sent_lines
					$sent_lines[5] = str_replace($sent_url, 'proxy_sentinel'.$li.'.php', $sent_lines[5]);
					
					// create proxy file
					$vars = ['BASE_URL_VALUE' => $sent_url];
					App::update_template('../snippets/proxy_wms.php', $html_dir.'/proxy_sentinel'.$li.'.php', $vars);
				
				}else if($sent_url == 'proxy_sentinel'.$li.'.php'){
					$content = file_get_contents($html_dir.'/proxy_sentinel'.$li.'.php');
					if(preg_match("/const BASE_URL = '(.*)';/", $content, $matches)){
						$real_sent_url = $matches[1];
						$sent_lines[5] = str_replace($sent_url, $real_sent_url, $sent_lines[5]);
						unlink($html_dir.'/proxy_sentinel'.$li.'.php');
					}
				}
				
				file_put_contents($html_dir.'/layer_sentinel'.$li.'.php', implode($sent_lines));
			}
		}
		// update the file
		if($changes){
			$fp = fopen($html_dir.'/index.php', 'w');
			foreach($lines as $line){
				fwrite($fp, $line);
			}
			fclose($fp);
		}
		
		// #update env
		$show_dt 	 = isset($details['use_datatable']) ? 'True' : 'False';
		$is_public = isset($details['is_public']) ? 'True' : 'False';
		$qgis_layout = isset($details['qgis_layout']) ? '"'.$details['qgis_layout'].'"' : '""';
		$js_varnames_str = (empty($js_varname)) ? '' : '"'.implode('","', $js_varnames).'"';
		$vars = [  'SHOW_DATATABLES' => $show_dt,
							 'IS_PUBLIC' => $is_public,
	 						 'JS_VARNAMES' => 'array('.$js_varnames_str.')',
							 'QGIS_LAYOUT' => $qgis_layout
						];
		App::update_env($html_dir.'/env.php', $vars);
		
		if(isset($details['infobox_content'])){
			file_put_contents($data_dir.'/infobox.html', $details['infobox_content']);
		}
	}
	
	public static function installApp($newId, $details, $html_dir, $data_dir, $apps_dir){
		$layer_names = array();
		$sentinel_ids = array();
		
		// move html dir to apps
		App::copy_r($html_dir, $apps_dir.'/'.$newId);
		// work in new html dir
		$html_dir = $apps_dir.'/'.$newId;
		$data_dir = $data_dir .'/'.$newId;
		
		// index.html -> index.php
		rename($html_dir.'/index.html', $html_dir.'/index.php');
		
		// data directory outside of /var/www/html to /var/www/data
		rename($html_dir.'/data', $data_dir);
		
		// link images to source dir
		if(is_dir($html_dir.'/images')){
			App::rrmdir($data_dir.'/images');
			symlink($html_dir.'/images', $data_dir.'/images');
		}
		
		// Replace sources to data files
		$lines = file($html_dir.'/index.php');
		
		$fp = fopen($html_dir.'/index.php', "w");

		$no_exec = '<?php if(empty(DB_HOST)){ die("Error: Can\'t execute!"); } ?>';
		$di = 0; $li = 0;
		
		for($i=0; $i < count($lines); $i++){
			$line = $lines[$i];
			
			if(preg_match('/var (.*) = new L\.geoJson\(/', $line, $matches)){
				array_push($layer_names, $matches[1]);
			
			}else if(preg_match('/var (.*) = new L\.geoJson\.multiStyle\(/', $line, $matches)){
				array_push($layer_names, $matches[1]);
			
			}else if(preg_match('/src="images\//', $line, $matches)){
				$line = str_replace('images/', 'data_filep.php?f=images/', $line);
				copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
				
			}else if(preg_match('/src="data\/(.*)"/', $line, $matches)){
				$json_filename = $matches[1];
				
				$vars = [ 'DATA_FILE' => '../../../data/'.$newId.'/'. $json_filename ];
				App::update_template('../snippets/data_file.php', $html_dir.'/data_file'.$di.'.php', $vars);
				
				$line = '<script src="data_file'.$di.'.php<?=$permalink?>" data-jfn="'.$json_filename.'"></script>'."\n";
				$di = $di + 1;
				
			}else if(preg_match('/var (.*) = L\.WMS\.layer\("(.*)", "(.*)",\s+{/', $line, $matches)){

				$wms_content = $line;
				
				$lb = substr_count($line, '{');	//left brackets
				$rb = substr_count($line, '}');	//right brackets
				
				while($lb > $rb){
					$i++;
					$line = $lines[$i];
					$wms_content .= $line;
					$lb += substr_count($line, '{');	//left brackets
					$rb += substr_count($line, '}');	//right brackets
				}
				
				if(str_contains($matches[2], 'services.sentinel-hub.com')){
					if(!SENTINEL_LAYERS_ENABLED){
						fwrite($fp, $wms_content);	// copy sentinel layer as it is
						$line = "\n";
					}else{
					
					array_push($layer_names, $matches[1]);
					
					$date_matches = array();
					
					if(!preg_match('/time=([0-9\-]+)%2F([0-9\-]+)[^&]*/', $matches[2], $date_matches)){
						// if time is missing from URL
						$d = date("Y-m-d");
						$date_matches = array('time='.$d, $d, $d);	// use today dates
						// append time=$TODAY to WMS URL
						$delim = str_contains($matches[2], '?') ? '&' : '?';
						$wms_content = str_replace($matches[2], $matches[2].$delim.$date_matches[0], $wms_content);
					}
					
					list($sent_url, $sent_url_params) = explode('?', $matches[2]);
					
					$vars = [ 'LAYER_ID_VALUE' => $li, 'LAYER_NAME_VALUE' => $matches[1], 'DATE_FROM_VALUE' => $date_matches[1], 'DATE_TO_VALUE' => $date_matches[2],
										'LAYER_URL_VALUE' => $sent_url ];
					App::update_template('../snippets/layer_sentinel.php', $html_dir.'/layer_sentinel'.$li.'.php', $vars);
					
					$wms_content = str_replace($matches[2], '<?=$sent_layer_url?>?'.$sent_url_params, $wms_content);
					
					$from_content = str_replace($date_matches[0], 'time=<?=$sent_date_from?>', $wms_content);
					
					$to_content = str_replace($date_matches[0], 'time=<?=$sent_date_to?>', $wms_content);
					$to_content = str_replace($matches[1], $matches[1]."_to", $to_content);	// append '_to' to layer name
					
					$map_adds = '';
					
					if(preg_match('/pane: \'(.*)\',/', $to_content, $pane_matches)){
						$to_content = str_replace($pane_matches[0], 'pane: \''.$pane_matches[1].'_to\',', $to_content);
						$map_adds .= 'map.createPane(\''.$pane_matches[1].'_to\');'."\n";
						$map_adds .= 'map.getPane(\''.$pane_matches[1].'_to\').style.zIndex = 401;'."\n";
						$map_adds .= 'leftSentinels.push('.$matches[1].');'."\n";
						$map_adds .= 'rightSentinels.push('.$matches[1].'_to);'."\n";
					}
					$map_adds .= 'map.addLayer('.$matches[1]."_to".');'."\n";
					$to_content = '<?php if(!empty($sent_date_to)){ ?>'."\n".$to_content."\n".$map_adds.'<?php } ?>';
					
					$wms_content = $from_content."\n".$to_content;
					
					$sentinel_ids[$li] = $matches[1];

					
					$content = file_get_contents($html_dir.'/layer_sentinel'.$li.'.php');
					file_put_contents($html_dir.'/layer_sentinel'.$li.'.php', $content."\n".$wms_content);
					$line = '<?php include("layer_sentinel'.$li.'.php"); ?>'."\n";	
					$li = $li + 1;
					}	
				}else{
					array_push($layer_names, $matches[1]);
					
					file_put_contents($html_dir.'/layer_wms'.$li.'.php', $no_exec."\n".$wms_content);
					$line = '<?php include("layer_wms'.$li.'.php"); ?>'."\n";	
					$li = $li + 1;
				}
				
			}else if(str_contains($line, 'src="js/leaflet.pattern.js"')){
				$line .= '<script src="../../assets/dist/js/sidebar_control.js"></script>'."\n";
				$line .= '<script src="../../assets/dist/js/proj.js"></script>'."\n";
				$line .= '<script src="../../assets/dist/locationfilter/locationfilter.js"></script>'."\n";
				$line .= '<script src="../../assets/dist/js/leaflet.browser.print.min.js"></script>'. "\n";
				$line .= '<script src="../../assets/dist/js/Lg.Control.Opacity.js"></script>'."\n";
				$line .= '<link href="../../assets/dist/css/L.Control.Opacity.css" rel="stylesheet" />'."\n";
				
				$line .= '<?php if(HAS_SENTINEL) { ?>'."\n";
				$line .= '<script src="../../assets/dist/leaflet-calendar/calendar-hooks.js"></script>'."\n";
				$line .= '<script src="../../assets/dist/leaflet-calendar/leaflet-calendar.js"></script>'."\n";
				$line .= '<link href="../../assets/dist/leaflet-calendar/leaflet-calendar.css" rel="stylesheet">'."\n";
				$line .= '<script src="../../assets/dist/side-by-side/leaflet-side-by-side.js"></script>'."\n";
				$line .= '<?php } ?>'."\n";
			
			}else if(str_contains($line, 'map.attributionControl.setPrefix')){
				
				$line = '<?php if($loc) {?>map.flyTo([<?=$loc[1]?>, <?=$loc[2]?>], <?=$loc[0]?>);<?php } ?>'. "\n". $line;
				$line = file_get_contents('../snippets/print_control.js') . "\n" . $line;
				
				$line = file_get_contents('../snippets/datatables_control.php'). "\n".$line;
				$line = '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".file_get_contents('../snippets/permalink_control.js'). "\n<?php } ?>\n".$line;
				$line = '<?php if(!empty(QGIS_LAYOUT)) { ?> var locationFilter = new L.LocationFilter({qgisTemplate: "<?=QGIS_LAYOUT?>"}).addTo(map); <?php } ?>'."\n".$line;
				$line = file_get_contents('../snippets/infobox_control.js'). "\n".$line;
							
			}else if(str_contains($line, '<body>')){
				$line .= "\n".'<?php include("../../admin/incl/index_header.php"); ?>';
			
			}else if(str_contains($line, '</body>')){
				
				$line = file_get_contents('../snippets/datatables.php')."\n".$line;
				$line = '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".file_get_contents('../snippets/permalink_modal.html')."\n<?php } ?>\n".$line;
				
				if(isset($details['infobox_content'])){
					$line = file_get_contents('../snippets/infobox_modal.php')."\n".$line;
				}
				
			}else if(str_contains($line, 'width:')){
				
				if(str_contains($lines[$i-1], '#map {')){
					$line = 'width: 100%;'."\n";
				}
			
			}else if(str_contains($line, 'L.control.layers(') || str_contains($line, 'L.control.layers.tree(')){
				
				$str  = '<?php if(HAS_SENTINEL) { ?>'."\n";
				$str .= 'if(leftSentinels.length > 0) {'."\n";
				$str .= 'L.control.sideBySide(leftSentinels, rightSentinels).addTo(map);'."\n";
				$str .= '}<?php } ?>'."\n";
				
				$str .= file_get_contents('../snippets/opacity_control.php'). "\n";
				$line = $str."\n".$line;
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		
		# build layer names array
		$ly_arr = 'array(';
		$fs = '';
		foreach($layer_names as $v){
			$ly_arr .= $fs."'$v' => '$v'";
			$fs = ',';
		}
		$ly_arr .= ')';
		
		// update map env.php
		$show_dt 	 = isset($details['use_datatable']) ? 'True' : 'False';
		$is_public = isset($details['is_public']) ? 'True' : 'False';
		$has_sentinel = (count($sentinel_ids) > 0) ? 'True' : 'False';
		$qgis_layout = isset($details['qgis_layout']) ? '"'.$details['qgis_layout'].'"' : '""';
		$vars = [ 'MAP_ID' => $newId, 'SHOW_DATATABLES' => $show_dt, 'IS_PUBLIC' => $is_public, 'LAYER_NAMES' => $ly_arr,
			'QGIS_LAYOUT' => $qgis_layout, 'HAS_SENTINEL' => $has_sentinel
		];
		copy('../snippets/env.php', $html_dir.'/env.php');
		App::update_env($html_dir.'/env.php', $vars);

		//insert our php auth code above <!doctype html> in index.php
		$content = file_get_contents($html_dir.'/index.php');
		file_put_contents($html_dir.'/index.php', '<?php include("../../admin/incl/index_prefix.php"); ?>' . $content);
		
		if(isset($details['infobox_content'])){
			file_put_contents($data_dir.'/infobox.html', $details['infobox_content']);
		}
		
		# create emtpy map specific CSS
		$fp = fopen($html_dir.'/thismap.css', "w");
		fwrite($fp, $details['thismap_css']);
		fclose($fp);
		
		# fix control background
		$css_content = file_get_contents($html_dir.'/css/qgis2web.css');
		$matchline = '.leaflet-control-zoom-in, .leaflet-control-zoom-out,';
		$css_content = str_replace($matchline, $matchline."\n".'.leaflet-touch .leaflet-bar a:first-child,'."\n".'.leaflet-touch .leaflet-bar a:last-child,', $css_content);
		file_put_contents($html_dir.'/css/qgis2web.css', $css_content);
		return [0];
	}
};
?>