<?php
class AppQ3D {
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
		
		return [$ds, $ls, $ss, $use_dt, $is_public, $qgis_layout, APP_TYPE_Q3D];
	}
	
	public static function updateIndex($details, $html_dir, $data_dir){
		$changes = 0;
		$js_varnames = array();
		$lines = file($html_dir.'/index.php');
		list($fds,$lys, $ses, $use_dt, $is_public, $qgis_layout, $map_type) = AppQ3D::parseIndex($html_dir, $lines);	// file data sources
		
		$newId = $details['id'];

		// update the file
		if($changes){
			$fp = fopen($html_dir.'/index.php', 'w');
			foreach($lines as $line){
				fwrite($fp, $line);
			}
			fclose($fp);
		}
		
		// #update env
		$show_dt 	 = 'False';
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
		$scene_ext = 'json';
		
		// move html dir to apps
		App::copy_r($html_dir, $apps_dir.'/'.$newId);
		// work in new html dir
		$html_dir = $apps_dir.'/'.$newId;
		$data_dir = $data_dir.'/'.$newId;
		
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
			
			if(str_contains($line, '<script src="./Qgis2threejs.js"></script>')){
				$str = '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js" integrity="sha384-NaWTHo/8YCBYJ59830LTz/P4aQZK1sS0SneOgAvhsIl3zBu8r9RevNg5lHCHAuQ/" crossorigin="anonymous"></script>';
				$line = $str."\n".$line;
				
			} else if(preg_match('/src="images\//', $line, $matches)){
				$line = str_replace('images/', 'data_filep.php?f=images/', $line);
				copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');

			}else if(preg_match('/app.loadSceneFile\(".\/data\/(.*)",/', $line, $matches)){
				$json_filename = $matches[1];
				$scene_ext = str_ends_with($json_filename, '.js') ? 'js' : 'json';
				$vars = [ 'DATA_FILE' => '../../../data/'.$newId.'/'. $json_filename ];
				App::update_template('../snippets/data_file.php', $html_dir.'/data_file'.$di.'.php', $vars);
				
				$line = str_replace('./data/'.$json_filename, 'data_file'.$di.'.php<?=$permalink?>', $line);
				$di = $di + 1;
				
				# protect image links in scene.json
				$jd = file_get_contents($data_dir.'/'.$json_filename);
				if(preg_match_all('/"url": "\.\/data\/([a-zA-Z0-9_\/\.-]*)"/', $jd, $matches, PREG_SET_ORDER)){
					
					
					copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
					
					foreach($matches as $match){
						$jd = str_replace($match[0], '"url": "data_filep.php?f='.urlencode($match[1]).'"', $jd);
					}
					file_put_contents($data_dir.'/'.$json_filename, $jd);
				}
			
			}else if(str_contains($line, '<div id="infobtn"></div>')){
				$line .= '<div id="printbtn"></div>';
				
			}else if(str_contains($line, '<body>')){
				$line .= "\n".'<?php include("../../admin/incl/index_header.php"); ?>';
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		$ly_arr = 'array();';
		
		// update map env.php
		$show_dt			= 'False';
		$has_sentinel	= 'False';
		$is_public		= isset($details['is_public']) ? 'True' : 'False';
		$qgis_layout	= isset($details['qgis_layout']) ? '"'.$details['qgis_layout'].'"' : '""';
		$vars = [ 'MAP_ID' => $newId, 'SHOW_DATATABLES' => $show_dt, 'IS_PUBLIC' => $is_public, 'LAYER_NAMES' => $ly_arr,
			'QGIS_LAYOUT' => $qgis_layout, 'HAS_SENTINEL' => $has_sentinel
		];
		copy('../snippets/env.php', $html_dir.'/env.php');
		App::update_env($html_dir.'/env.php', $vars);

		//insert our php auth code above <!doctype html> in index.php
		$content = file_get_contents($html_dir.'/index.php');
		file_put_contents($html_dir.'/index.php', '<?php include("../../admin/incl/index_prefix.php"); ?>' . $content);
		
		# create emtpy map specific CSS
		$fp = fopen($html_dir.'/thismap.css', "w");
		fwrite($fp, $details['thismap_css']);
		fclose($fp);
		
		# fix the scene loading
		$lines = file($html_dir.'/Qgis2threejs.js');
		
		$fp = fopen($html_dir.'/Qgis2threejs.js', "w");
		foreach($lines as $line){
			if(str_contains($line, 'if (ext == "json") app.loadJSONFile(url, onload);')){
				$line = str_replace('if (ext == "json") app.loadJSONFile(url, onload);', 'ext = "'.$scene_ext.'"; if (ext == "php") app.loadJSONFile(url, onload);', $line);
			}else if(str_contains($line, 'var saveCanvasImage = saveImageFunc || function (canvas) {')){
				$line = $line.'if(as_pdf){
            var imgData = canvas.toDataURL(\'image/jpeg\');
            var pdf = new jsPDF({ orientation: "l", unit: "pt", format: [canvas.width, canvas.height] });
						pdf.textWithLink("View \'" + document.title + "\' QuartzMap online", 25, 25, { url: app.currentViewUrl() });
            pdf.addImage(imgData, \'JPEG\', 0, 0, canvas.width, canvas.height);
            pdf.save("download.pdf");
            gui.popup.hide();
          }else '."\n";	
			}else if(str_contains($line, 'app.saveCanvasImage = function (width, height, fill_background')){
				$line = str_replace('fill_background,', 'fill_background, as_pdf,', $line);
			
			}else if(str_contains($line, 'CE("span", d6, "Fill Background");')){
				$line .= "\n".'var d61 = CE("div", f),
                        pdf = CE("input", d61);
                pdf.type = "checkbox";
                pdf.checked = false;
                CE("span", d61, "Export to PDF");';
			}else if(str_contains($line, 'app.saveCanvasImage(width.value, height.value, bg.checked);')){
				$line = str_replace('bg.checked', 'bg.checked, pdf.checked', $line);
			}else if(str_contains($line, 'ON_CLICK("infobtn", function ()')){
				$line = 'ON_CLICK("printbtn", function () {
                gui.layerPanel.hide();
                if (gui.popup.isVisible() && gui.popup.content == "pageinfo") gui.popup.hide();
                else gui.showPrintDialog();
      	});'."\n".$line;

			}
			fwrite($fp, $line);
		}
		fclose($fp);

		file_put_contents($html_dir.'/Qgis2threejs.css', file_get_contents('../snippets/q3d_print.css'), FILE_APPEND);
		return [0];
	}
};
?>