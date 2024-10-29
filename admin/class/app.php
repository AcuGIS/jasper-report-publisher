<?php

const TIME_MAP = array('Weeks' => 604800, 'Days' => 86400, 'Hours' => 3600, 'Minutes' => 60, 'Off' => 0);
const APP_TYPE_NONE = 0;
const APP_TYPE_Q2W  = 1;
const APP_TYPE_Q3D  = 2;
const APP_TYPE_R 	  = 3;

class App {

	public static function get_app_type($html_dir, $data_dir){
		if(is_file($html_dir.'/index.R') || count(App::getRfiles($html_dir)) > 0) {	// if install dor has R files
			return APP_TYPE_R;
		}else if(is_dir($data_dir) && count(App::getRfiles($data_dir)) > 0) {	// if data dir has R files
			return APP_TYPE_R;
		}else if(is_file($html_dir.'/Qgis2threejs.js')){
			return APP_TYPE_Q3D;
		}else if (is_file($html_dir.'/index.html') || is_file($html_dir.'/index.php')){
			return APP_TYPE_Q2W;
		}else{
			return APP_TYPE_NONE;
		}
	}
	
	public static function rrmdir($dir) {
	 if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
			 if ($object != "." && $object != "..") {
				 if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
					 App::rrmdir($dir. DIRECTORY_SEPARATOR .$object);
				 else
					 unlink($dir. DIRECTORY_SEPARATOR .$object);
			 }
		 }
		 rmdir($dir);
	 }
	}
	
	public static function find_per($cache_seconds){
		foreach(TIME_MAP as $per => $val){
			if($cache_seconds >= $val){
				return $per;
			}
		}
		return 'off';	
	}
	
	public static function parseQGIS($html_dir){
		
		if(is_file($html_dir.'/proxy_qgis.php')){
			$content = file_get_contents($html_dir.'/proxy_qgis.php');
			if(preg_match('/const QGIS_FILENAME = \'\/.*\/\d+\/(.*)\';/', $content, $matches)){
				return $matches[1];
			}
		}
		return null;
	}
	
	public static function parseQGISLayouts($qgis_filename){
    $xml = simplexml_load_file($qgis_filename);
    list($layouts) = $xml->xpath('/qgis/Layouts//Layout/@name');
		return $layouts;
	}

	public static function update_template($src, $dest, $vars){
		
		$lines  = file($src);
		$fp = fopen($dest, 'w');
		
		foreach($lines as $ln => $line){
			foreach($vars as $k => $v){
				if(str_contains($line, $k)){
					$line = str_replace($k, $v, $line);
				}
			}
			fwrite($fp, $line);
		}
		
		fclose($fp);
	}
	
	public static function update_env($src, $vars){
		
		$lines  = file($src);
		$fp = fopen($src, 'w');
		
		foreach($lines as $ln => $line){
			foreach($vars as $k => $v){
				if(str_starts_with($line, 'const '.$k.' =')){
					$line = 'const '.$k.' = '.$v.';'."\n";
					break;
				}
			}
			fwrite($fp, $line);
		}
		
		fclose($fp);
	}
	
	public static function extract_varname($filename){
		// extract varname from first line of data file
		$js_fp = fopen($filename, 'r');
		$line = fread($js_fp, 1024);
		fclose($js_fp);
		
		$eq_pos = strpos($line, '=');
		$js_varname_decl = substr($line, 0, $eq_pos);			// var json_neighborhoods_2
		$js_varname = explode(' ', $js_varname_decl)[1];	//     json_neighborhoods_2
		return $js_varname;
	}

	public static function upload_dir($username){
		$ftp_home = shell_exec('grep "^'.$username.':" /etc/passwd | cut -f6 -d:');
		$upload_dir = substr($ftp_home, 0, -1);
		return $upload_dir;
	}
	
	public static function copy_r($source, $target){
		if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            $Entry = $source . '/' . $entry; 
            if ( is_dir( $Entry ) ) {
                App::copy_r( $Entry, $target . '/' . $entry );
            } else {
							copy( $Entry, $target . '/' . $entry );
						}
        }

        $d->close();
    }else {
        copy( $source, $target );
    }
	}
	
	public static function parseIndex($html_dir, $data_dir, $lines = null){
		$ds = array();
		$ls = array();
		$ss = array();
		$use_dt = 0;
		$is_public = false;
		$qgis_layout = '';
		
		[$ds, $ls, $ss, $use_dt, $is_public, $qgis_layout, APP_TYPE_NONE];
	}

	public static function updateIndex($details, $html_dir, $data_dir){
		return 0;
	}

	public static function installApp($newId, $details, $html_dir, $data_dir, $apps_dir){
		return [0];
	}

	public static function getApps($apps_dir) {
		$rv = array();
    $entries = scandir($apps_dir);
		foreach($entries as $e){
			if(is_dir($apps_dir.'/'.$e) && !str_starts_with($e, '.')){
				array_push($rv, $e);
			}
		}
		return $rv;
  }
	
	public static function qgis_features_html($map_id, $qgis_file, $qgis_path){
		
		$xml = simplexml_load_file($qgis_file);
		list($DefaultViewExtent) = $xml->xpath('/qgis/ProjectViewSettings/DefaultViewExtent');
		
		$bounding_box = $DefaultViewExtent['xmin'].'.,</br>'.$DefaultViewExtent['ymin'].',</br>'.$DefaultViewExtent['xmax'].',</br>'.$DefaultViewExtent['ymax'];
		list($projection) = $xml->xpath('/qgis/ProjectViewSettings/DefaultViewExtent/spatialrefsys/authid');
		
		$html = <<<END
<table class="table table-bordered" id="sortTable">
<tbody>
</tbody>
<tr>
	<td>Projection</td>
	<td>{$projection}</td>
</tr>
<tr>
	<td>Bounding Box</td>
	<td>{$bounding_box}</td>
</tr>
<tr>
	<td>Web Map Service</td>
	<td>
		<a href="{$qgis_path}proxy_qgis.php?SERVICE=WMS&REQUEST=GetCapabilities" target="_blank">WMS Url</a></br>
		<a href="{$qgis_path}proxy_qgis.php?SERVICE=WFS&REQUEST=GetCapabilities" target="_blank">WFS Url</a></br>
		<a href="{$qgis_path}proxy_qgis.php?SERVICE=WMTS&REQUEST=GetCapabilities" target="_blank">WMTS Url</a>
	</td>
</tr>
</table>
END;
			
		return $html;
	}
	
	public static function getFilesByType($html_dir, $ext){
		$rv = array();
    $entries = scandir($html_dir);
		foreach($entries as $e){
			if(is_file($html_dir.'/'.$e) && str_ends_with($e, '.'.$ext)){
				array_push($rv, $e);
			}
		}
		sort($rv);
		return $rv;
	}
	
	public static function getRfiles($html_dir) {
		return App::getFilesByType($html_dir, 'R');
  }
};

require('appQ2W.php');
require('appQ3D.php');
require('appR.php');

function installApp($newId, $details, $html_dir, $data_dir, $apps_dir){
	switch(App::get_app_type($html_dir, $data_dir)){
		case APP_TYPE_Q3D:	return AppQ3D::installApp($newId, $details, $html_dir, $data_dir, $apps_dir);
		case APP_TYPE_Q2W:	return AppQ2W::installApp($newId, $details, $html_dir, $data_dir, $apps_dir);
		case APP_TYPE_R:		return AppR::installApp($newId, $details, $html_dir, $data_dir, $apps_dir);
		default:						return App::installApp($newId, $details, $html_dir, $data_dir, $apps_dir);
	}
}

function uninstallApp($id, $data_dir, $apps_dir){
	App::rrmdir($data_dir.'/'.$id);
	App::rrmdir($apps_dir.'/'.$id);
	CRON::remove($id);
}

function parseIndex($html_dir, $data_dir, $lines = null){
	switch(App::get_app_type($html_dir, $data_dir)){
		case APP_TYPE_Q3D:	return AppQ3D::parseIndex($html_dir, $lines);
		case APP_TYPE_Q2W:	return AppQ2W::parseIndex($html_dir, $lines);
		case APP_TYPE_R:		return AppR::parseIndex($html_dir, $data_dir, $lines);
		default:						return App::parseIndex($html_dir, $data_dir, $lines);
	}
}

function updateIndex($details, $html_dir, $data_dir){
	switch(App::get_app_type($html_dir, $data_dir)){
		case APP_TYPE_Q3D:	return AppQ3D::updateIndex($details, $html_dir, $data_dir);
		case APP_TYPE_Q2W:	return AppQ2W::updateIndex($details, $html_dir, $data_dir);
		case APP_TYPE_R:		return AppR::updateIndex($details, $html_dir, $data_dir);
		default:						return App::updateIndex($details, $html_dir, $data_dir);
	}
}

function getPageFiles($data_dir){
	$pages = array();
	
	$r_files = App::getRfiles($data_dir);
	foreach($r_files as $rf){
		list($map_html_files, $plots, $plotly, $knitr_tbls) = AppR::r_get_html_files($data_dir.'/'.$rf);
		foreach($map_html_files as $f){
			$pages[] = str_replace('.html', '.php', $f);
		}
	}
	
	if(empty($r_files)){
		$pages[] = 'index.php';
	}
	
	return $pages;
}
?>
