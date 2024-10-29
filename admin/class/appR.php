<?php
class AppR {
	public static function r_get_html_files($src_file){
		$maps = array();
		$plots = array();
		$plotly = array();
		$knitr_tbls = array();
		$rmarkdown = array();
		
		$is_leaflet = false;
		$is_plotly = false;
		
		$lines = file($src_file);
		foreach($lines as $line){
			
			if(str_contains($line, 'library(leaflet)')){
				$is_leaflet = true;
			}else if(str_contains($line, 'library(plotly)')){
				$is_plotly = true;
			
			#saveWidget(india_map, file = "india.html")
			}else if(preg_match('/saveWidget\((.*),\s*file\s*=\s*"(.*\.html)"\)/', $line, $matches)){
				if($is_leaflet){
					$maps[] = $matches[2];
				}else if($is_plotly){
					$plotly[] = $matches[2];	
				}

				#html_plot(pl(),	out="index.html")
			}else if(preg_match('/html_plot\((.*),\s*out\s*=\s*"(.*\.html)"\)/', $line, $matches)){
				$plots[] = $matches[2];
			}else if(preg_match('/save_kable\(file = "(.*\.html)", self_contained = T\)/', $line, $matches)){
				$knitr_tbls[] = $matches[1];
			}else if(preg_match('/rmarkdown::render\(input=".*\.Rmd", output_file="(.*\.html)"/', $line, $matches)){
				$rmarkdown[] = $matches[1];
			}
		}
		return [$maps, $plots, $plotly, $knitr_tbls, $rmarkdown];
	}

	public static function rmd_get_output_files($r_file, $html_file){
		$files = [];
		$rmd_file = '';
		
		$lines = file($r_file);
		foreach($lines as $line){
			if(preg_match('/^rmarkdown::render\(input="(.*)", output_file="'.$html_file.'"/', $line, $matches)){
				$rmd_file = $matches[1];
				break;
			}
		}

		foreach($lines as $line){
			if(preg_match('/^rmarkdown::render\(input="'.$rmd_file.'", output_file="(.*\.([a-zA-z]+))"/', $line, $matches)){
				if($matches[2] != 'html'){
					$files[$matches[1]] = $matches[2];
				}
			}
		}

		return $files;
	}
	
	public static function rmd_get_formats($r_file, $rmd_file){
		$files = [];
		
		$lines = file($r_file);
		foreach($lines as $line){
			if(preg_match('/^rmarkdown::render\(input="'.$rmd_file.'", output_file="(.*\.([a-zA-z]+))"/', $line, $matches)){
				if($matches[2] != 'html'){
					$files[$matches[2]] = $matches[1];
				}
			}
		}

		return $files;
	}
	
	private static function export_scripts($src, $data_dir){
		$html_dir = dirname($src);
		$js_name = substr(basename($src), 0, -5);
		
		$doc = new DOMDocument();

		libxml_use_internal_errors(true);	// hide warnings

		// load the HTML string we want to strip
		$doc->loadHTMLFile($src, LIBXML_BIGLINES | LIBXML_PARSEHUGE | LIBXML_SCHEMA_CREATE);

		// get all the script tags
		$script_tags = $doc->getElementsByTagName('script');

		$length = $script_tags->length;

		// for each tag, remove it from the DOM
		for ($i = $length - 1; $i >= 0; $i--) {
			$itm = $script_tags->item($i);
			if(($itm == null) || $itm->hasAttribute('type')){
				
				if($itm->hasAttribute('data-for')){
					if(preg_match('/htmlwidget-([0-9a-f]+)/', $itm->getAttribute('data-for'), $matches)){

							file_put_contents($data_dir.'/'.$js_name.'_'.$i.'.json', $itm->nodeValue);
							# allow group to rewrite data files
							chmod($data_dir.'/'.$js_name.'_'.$i.'.json', 0660);
							
							if($itm->getAttribute('type') == 'application/json'){
								
								$scr = $doc->createElement('script', '');
								$scr->setAttribute('type', 'application/json');
								$scr->setAttribute('data-for', $itm->getAttribute('data-for'));
								$scr->setAttribute('src', 'data_filep.php?f='.$js_name.'_'.$i.'.json');
								$itm->replaceWith($scr);
								
								if(!is_file($html_dir.'/data_filep.php')){
									copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
								}
							}else{
								$vars = [ 'DATA_FOR' => $data_dir.'/'.$js_name.'_'.$i.'.json', 'USER_ID' => $_SESSION[SESS_USR_KEY]->id];
								App::update_template("../snippets/data_for.php", $html_dir.'/'.$js_name.'_js_'.$i.'.php', $vars);
								# we can't put this in a file, because index.0.js (var data = JSON.parse(scriptData.textContent || scriptData.text)) is parsing it								
								$scr = $doc->createElement('script', '<?php include("'.$js_name.'_js_'.$i.'.php'.'"); ?>');
								$scr->setAttribute('type', 		 $itm->getAttribute('type'));
								$scr->setAttribute('data-for', $itm->getAttribute('data-for'));
								$itm->replaceWith($scr);
							}
					}
				}
				
				continue;
			}

			# export map object from Leaflet library
			if(str_contains($itm->nodeValue, 'var _leaflet = require("./global/leaflet");')){
				//NOTE: $itm->nodeValue is read-only, so we can't re-assign!
				$content = str_replace('this._map = map;', 'window.lf_map = map; this._map  =  map;', $itm->nodeValue);
				$itm->removeChild($itm->firstChild);
				$newText = new DOMText($content);
				$itm->appendChild($newText);
			}else if(str_contains($itm->nodeValue, 'var data = JSON.parse(scriptData.textContent || scriptData.text);')){
				
				$replacement = "var data = '';
				if(scriptData.src){
					var Httpreq = new XMLHttpRequest();
					Httpreq.open(\"GET\", scriptData.src, false);
					Httpreq.send(null);
					data = JSON.parse(Httpreq.responseText);
				}else{
					data = JSON.parse(scriptData.textContent || scriptData.text);
				}";
				
				$content = str_replace('var data = JSON.parse(scriptData.textContent || scriptData.text);', $replacement, $itm->nodeValue);
				# replace node value
				$itm->removeChild($itm->firstChild);
				$newText = new DOMText($content);
				$itm->appendChild($newText);
			}

			$scr = $doc->createElement('script');
			$scr->setAttribute('type', 'text/javascript');
			
			if(preg_match('/\*\splotly\.js\sv([0-9\.]+)/', $itm->nodeValue, $matches)){
				
				$ptly_file = '../../assets/dist/js/plotly-'.$matches[1].'.min.js';
				if(!is_file($ptly_file)){
					$plotly_js = file_get_contents('https://cdn.plot.ly/plotly-'.$mathes[1].'.min.js');
					file_put_contents($ptly_file, $plotly_js);
				}
				
				// parsed script in nodeValue has invalid CDATA characters and its broken
				$scr->setAttribute('src', $ptly_file);	
			}else{
				
				$js_sha1 = sha1($itm->nodeValue);
				$js_filename = '../js/'.$js_sha1.'.js';

				if(!is_file($html_dir.'/'.$js_filename)){
					file_put_contents($html_dir.'/'.$js_filename, $itm->nodeValue);
				}

				$scr->setAttribute('src', $js_filename);
			}
			$itm->replaceWith($scr);
		}

		$dst = $html_dir.'/'.$js_name.'.php';
		$doc->saveHTMLFile($dst);
		unlink($src);

		return $dst;
	}

	private static function export_styles($src){
		$html_dir = dirname($src);
		$js_name = substr(basename($src), 0, -4);

		$doc = new DOMDocument();

		libxml_use_internal_errors(true);	// hide warnings

		// load the HTML string we want to strip
		$doc->loadHTMLFile($src, LIBXML_BIGLINES | LIBXML_PARSEHUGE | LIBXML_SCHEMA_CREATE);

		// get all the script tags
		$style_tags = $doc->getElementsByTagName('style');

		$length = $style_tags->length;

		// for each tag, remove it from the DOM
		for ($i = $length - 1; $i >= 0; $i--) {
			$itm = $style_tags->item($i);
			if($itm == null){
				continue;
			}else if($itm->hasAttribute('type') && $itm->getAttribute('type') == 'text/css'){
				
				$js_sha1 = sha1($itm->nodeValue);
				$js_filename = '../css/'.$js_sha1.'.css';

				if(!is_file($html_dir.'/'.$js_filename)){
					file_put_contents($html_dir.'/'.$js_filename, $itm->nodeValue);
				}

				$scr = $doc->createElement('link');
				$scr->setAttribute('href', $js_filename);
				$scr->setAttribute('rel', 'stylesheet');
				$itm->replaceWith($scr);
			}
		}

		$doc->saveHTMLFile($src);
		return $src;
	}
	
	private static function export_images($src, $data_dir){
		$html_dir = dirname($src);
		$js_name = substr(basename($src), 0, -4);

		$doc = new DOMDocument();

		libxml_use_internal_errors(true);	// hide warnings

		// load the HTML string we want to strip
		$doc->loadHTMLFile($src, LIBXML_BIGLINES | LIBXML_PARSEHUGE | LIBXML_SCHEMA_CREATE);

		// get all the script tags
		$img_tags = $doc->getElementsByTagName('img');

		$length = $img_tags->length;

		// for each tag, remove it from the DOM
		for ($i = $length - 1; $i >= 0; $i--) {
			$itm = $img_tags->item($i);
			if($itm == null){
				continue;
			}else if($itm->hasAttribute('src')){
				
				$img_src = $itm->getAttribute('src');
				if(str_starts_with($img_src, 'data:image/')){
					
					// ex. 'data:image/png;base64,AAAFBfj42Pj4...'
					list($data_type, $data) 		= explode(';', $img_src);
					list(, $type) 		= explode(':', $data_type);
					list($temp, $data)    = explode(',', $data);
					list($temp, $img_ext) = explode('/', $type);
					$data = base64_decode($data);

					$img_file = $data_dir.'/data_image'.$i.'.'.$img_ext;
					file_put_contents($img_file, $data);
					
					//
					
					$scr = $doc->createElement('img', '');
					$scr->setAttribute('type', $type);
					$scr->setAttribute('src', 'data_filep.php?f=data_image'.$i.'.'.$img_ext);
					$itm->replaceWith($scr);
					
					if(!is_file($html_dir.'/data_filep.php')){
						copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
					}
				}
			}
		}
		
		$doc->saveHTMLFile($src);
		return $src;
	}
	
	public static function runR($html_dir, $map_file_r){
		$descriptorspec = array(
		   0 => array("pipe", "r"),
		   1 => array("pipe", "w"),
		   2 => array("pipe", "w")
		);
		
		$cmd = 'R CMD BATCH --silent --no-save "'.$map_file_r.'"';
		$cwd = $html_dir;
		$env = array("HOME" => $html_dir, 'PATH' => getenv('PATH'), 'OPENSSL_CONF' => '/dev/null');
		
		
		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
		
		if (is_resource($process)) {
		    // $pipes now looks like this:
		    // 0 => writeable handle connected to child stdin
		    // 1 => readable handle connected to child stdout
		    // Any error output will be appended to /tmp/error-output.txt

		    fclose($pipes[0]);

		    //$out = stream_get_contents($pipes[1]);
		    fclose($pipes[1]);
				
				//$err = stream_get_contents($pipes[2]);
		    fclose($pipes[2]);

		    // It is important that you close any pipes before calling
		    // proc_close in order to avoid a deadlock
		    $return_value = proc_close($process);

		    		$r_out = $html_dir.'/'.basename($map_file_r).'out';
		    		$out = file_get_contents($r_out);
		    		unlink($r_out);
		    		$err = '';
		    return [$return_value, $out, $err];
		}else{
			return [1, '', 'Failed to start "'.$cmd."'"];
		}
	}
	
	public static function parseIndex($html_dir, $data_dir, $lines = null){
		$ds = array();
		$ls = array();
		$ss = array();
		$use_dt = 0;
		$is_public = false;
		$qgis_layout = '';

		$env_content = file_get_contents($html_dir.'/env.php');

		if(preg_match('/const IS_PUBLIC = (.*);/', $env_content, $matches)){
			$is_public = ($matches[1] == 'True');
		}

		if($lines == null){
			$lines = file($html_dir.'/index.php');
		}

		return [$ds, $ls, $ss, $use_dt, $is_public, $qgis_layout, APP_TYPE_R];
	}

	public static function r_needs_rebuild($rf, $html_dir, $map_files, $plot_files, $plotly_files, $knitr_tbls, $rmarkdown){
		$rebuild = false;
		
		$rf_mtime = filemtime($rf);
		$files = array_merge($map_files, $plot_files, $plotly_files, $knitr_tbls, $rmarkdown);

		foreach($files as $f){
			$f = str_replace('.html', '.php', $f);
			if(!is_file($html_dir.'/'.$f) || 
				($rf_mtime > filemtime($html_dir.'/'.$f)) ){
				$rebuild = true;	// rebuild if file is newer or missing
				break;
			}
		}
		
		$rmd_files = App::getFilesByType($html_dir, 'Rmd');
		foreach($files as $f){
			if(!is_file($html_dir.'/'.$f) || 
				($rf_mtime < filemtime($html_dir.'/'.$f)) ){
				$rebuild = true;	// rebuild if Rmd was updated
				break;
			}
		}
		
		return $rebuild;
	}
	
	public static function updateIndex($details, $html_dir, $data_dir){

		$lines = file($html_dir.'/index.php');

		list($fds,$lys, $ses, $use_dt, $is_public, $qgis_layout, $map_type) = AppR::parseIndex($html_dir, $data_dir, $lines);	// file data sources
		$newId = $details['id'];
		
		$cron = CRON::get($newId);
		
		$r_files = App::getRfiles($data_dir);
		foreach($r_files as $rf){

			list($map_html_files, $plot_html_files, $plotly_html_files, $knitr_tbls, $rmarkdown) = AppR::r_get_html_files($data_dir.'/'.$rf);
			
			if(!AppR::r_needs_rebuild($data_dir.'/'.$rf, $html_dir, $map_html_files, $plot_html_files, $plotly_html_files, $knitr_tbls, $rmarkdown)){
				continue;
			}
			
			# compile the index.R file
			list($rv, $out, $err) = AppR::runR($html_dir, $data_dir.'/'.$rf);
			if($rv != 0){
				return [1, 'r_file' => $rf, 'r_out' => $out, 'r_err' => $err];
			}

			foreach($map_html_files as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_map_index($details, $html_dir, $php_f);
			}
			
			if(is_dir($html_dir.'/figures') && !is_dir($data_dir.'/images')){
				mkdir($data_dir.'/images');
			}
			
			foreach($plot_html_files as $f){
				
				$name = substr($f, 0, -5);
				$php_f = $html_dir.'/'.$name.'.php';
				#rename to PHP
				rename($html_dir.'/'.$f, $php_f);

				unlink($html_dir.'/'.$f.'.rawhtml');

				AppR::r_parse_plot_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($plotly_html_files as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_plot_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($knitr_tbls as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_knitr_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($rmarkdown as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::export_images($php_f, $data_dir);
				AppR::r_parse_rmd_index($details, $html_dir, $data_dir, $php_f, $rf);
			}
		}

		// #update env
		$is_public = isset($details['is_public']) ? 'True' : 'False';
		$vars = [  'IS_PUBLIC' => $is_public];
		App::update_env($html_dir.'/env.php', $vars);
		
		if(isset($details['infobox_content'])){
			file_put_contents($data_dir.'/infobox.html', $details['infobox_content']);
		}

		# update map specific CSS
		$fp = fopen($html_dir.'/thismap.css', "w");
		fwrite($fp, $details['thismap_css']);
		fclose($fp);

		// check if there is cron change
		if(isset($details['cron_period'])){
			if(	($cron['cron_period'] != $details['cron_period']) ||
					(($cron['cron_period'] == 'custom') && ($cron['cron_custom'] != $details['cron_custom'])) ){
				
				if($details['cron_period'] == 'never'){	// disable
					CRON::remove($newId);
				}else if($cron['cron_period'] == 'never'){	// enable
					CRON::add($details['cron_period'], $details['cron_custom'], $newId);
				}else{	// change
					CRON::update($details['cron_period'], $details['cron_custom'], $newId);
				}
			}
		}
	}
	
	private static function r_parse_map_index($details, $html_dir, $filename){
		$layer_names = array();
		
		// Replace sources to data files
		$lines = file($filename);
		
		$fp = fopen($filename, "w");

		$di = 0; $li = 0;
		
		//insert our php auth code above <!doctype html> in index.php
		$lines[0] = '<?php include("../../admin/incl/index_prefix.php"); ?>'."\n".$lines[0];
		
		foreach($lines as $line){

			if(preg_match('/src="images\//', $line, $matches)){
				$line = str_replace('images/', 'data_filep.php?f=images/', $line);
				copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
			
			}else if(preg_match('/<script type="application\/json" data-for="htmlwidget-([0-9a-f]+)">(.*)<\/script>"/', $line, $matches)){

				file_put_contents($data_dir.'/index_'.$matches[1].'.js', $matches[2]);
				$vars = [ 'DATA_FOR' => $data_dir.'/index_'.$matches[1].'.js', 'USER_ID' => $_SESSION[SESS_USR_KEY]->id];

				App::update_template("../snippets/data_for.php", $html_dir.'/index_'.$matches[1].'.php', $vars);

				$line = '<script type="application/json" data-for="htmlwidget-'.$matches[1].'"><?php include("index_'.$matches[1].'.php")?></script>'."\n";

			}else if(str_contains($line, '</head>')){
				
				$js_str  = '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">';
				$js_str .= '<script src="../../assets/dist/js/leaflet.browser.print.min.js"></script>'. "\n";
				$js_str .= '<script src="../../assets/dist/js/custom_lf_ctrls.js"></script>'. "\n";
				
				$js_str .= '<script>'."\n";
				$js_str .= '$(document).ready(function() {'."\n";

				$js_str .= '<?php if($loc) {?>window.lf_map.flyTo([<?=$loc[1]?>, <?=$loc[2]?>], <?=$loc[0]?>);<?php } ?>'. "\n";
				$pl_js = str_replace('map.', 'window.lf_map.', file_get_contents('../snippets/permalink_control.js'));
				$js_str .= '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".$pl_js."\n<?php } ?>\n";
				
				$js_str .= "});\n";
				$js_str .= '</script>'."\n";

				$line = ''."\n".$js_str."\n".$line;
						
			}else if(str_contains($line, '<body>') || str_contains($line, '<body ')){
				$line .= "\n".'<?php include("../../admin/incl/index_header.php"); ?>'."\n";
			
			}else if(str_contains($line, 'id="htmlwidget_container"')){
				# make space for the top header
				$line = str_replace('inset: 0px;', 'inset: 50px 0px 0px 0px;', $line);
				
			}else if(str_contains($line, '</body>')){
				
				$line = '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".file_get_contents('../snippets/permalink_modal.html')."\n<?php } ?>\n".$line;
				
				if(isset($details['infobox_content'])){
					$line = file_get_contents('../snippets/infobox_modal.php')."\n".$line;
				}
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		return $layer_names;
	}
	
	private static function r_parse_plot_index($details, $html_dir, $data_dir, $filename){
		$layer_names = array();
		
		// Replace sources to data files
		$lines = file($filename);
		
		$fp = fopen($filename, "w");

		$di = 0; $li = 0;
		
		//insert our php auth code above <!doctype html> in index.php
		$lines[0] = '<?php include("../../admin/incl/index_prefix.php"); ?>'."\n".$lines[0];
		
		foreach($lines as $line){

			# <img src='./figures/index001.png' alt='Oops something went wrong, check your code' class='img-resp'>
			if(preg_match("/<img src='\.\/figures\/([^']*)'/", $line, $matches)){
				
				rename($html_dir.'/figures/'.$matches[1], $data_dir.'/images/'.$matches[1]);
				
				$line = str_replace('./figures/', 'data_filep.php?f=images/', $line);
				if(!is_file($html_dir.'/data_filep.php')){
					copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
				}
						
			}else if(str_contains($line, '<body>') || str_contains($line, '<body ')){
				$line .= "\n".'<?php include("../../admin/incl/index_header_plot.php"); ?>'."\n";
			
			}else if(str_contains($line, 'id="htmlwidget_container"')){
				# make space for the top header
				$line = str_replace('inset: 0px;', 'inset: 50px 0px 0px 0px;', $line);

			}else if(str_contains($line, '</body>')){
				
				$line = '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".file_get_contents('../snippets/permalink_modal.html')."\n<?php } ?>\n".$line;
				
				if(isset($details['infobox_content'])){
					$line = file_get_contents('../snippets/infobox_modal.php')."\n".$line;
				}
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		return $layer_names;
	}
	
	private static function r_parse_knitr_index($details, $html_dir, $data_dir, $filename){
		$layer_names = array();
		
		// Replace sources to data files
		$lines = file($filename);
		
		$fp = fopen($filename, "w");

		$di = 0; $li = 0;
		
		//insert our php auth code above <!doctype html> in index.php
		$lines[0] = '<?php include("../../admin/incl/index_prefix.php"); ?>'."\n".$lines[0];
		
		foreach($lines as $line){

			if(str_contains($line, '<body>') || str_contains($line, '<body ')){
				$line .= "\n".'<?php include("../../admin/incl/index_header_plot.php"); ?>'."\n";
			
			}else if(str_contains($line, '</body>')){
				
				$modal_html = file_get_contents('../snippets/permalink_modal.html');
				# Knitr tables use BS 3.3 and fade prevents window from showing ?
				$modal_html = str_replace('class="modal fade"', 'class="modal"', $modal_html);
				
				$line = '<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".$modal_html."\n<?php } ?>\n".$line;
				
				if(isset($details['infobox_content'])){

					$modal_html = file_get_contents('../snippets/infobox_modal.php');
					# Knitr tables use BS 3.3 and fade prevents window from showing ?
					$modal_html = str_replace('class="modal fade"', 'class="modal"', $modal_html);
					
					$line = $modal_html."\n".$line;
				}
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		return $layer_names;
	}
	
	private static function r_parse_rmd_index($details, $html_dir, $data_dir, $php_file, $rf){
		$layer_names = array();
		
		$html_file = str_replace('.php', '.html', basename($php_file));
		
		$output_files = AppR::rmd_get_output_files($data_dir.'/'.$rf, $html_file);
		$download_footer = '';
		if(count($output_files) > 0){
			$delim = '';
			$download_footer = '<footer><p>Download as:';
			foreach($output_files as $fname => $ftype){
				$download_footer .= $delim.'<a href="data_filep.php?f='.$fname.'">'.strtoupper($ftype).'</a>';
				$delim = ' ,';
				# move file to data directory
				rename($html_dir.'/'.$fname, $data_dir.'/'.$fname);
			}
			$download_footer .= '</p></footer>';
		}
		
		// Replace sources to data files
		$lines = file($php_file);
		
		$fp = fopen($php_file, "w");

		$di = 0; $li = 0;
		
		//insert our php auth code above <!doctype html> in index.php
		$lines[0] = '<?php include("../../admin/incl/index_prefix.php"); ?>'."\n".$lines[0];
		
		foreach($lines as $line){

			if(str_contains($line, '<body>') || str_contains($line, '<body ')){
				$line .= "\n".'<?php include("../../admin/incl/index_header_plot.php"); ?>'."\n";
			
			}else if(str_contains($line, '</body>')){
				
				$modal_html = file_get_contents('../snippets/permalink_modal.html');
				# Knitr tables use BS 3.3 and fade prevents window from showing ?
				$modal_html = str_replace('class="modal fade"', 'class="modal"', $modal_html);
				
				$line = $download_footer.'<?php if(isset($_SESSION[SESS_USR_KEY])) { ?>'."\n".$modal_html."\n<?php } ?>\n".$line;
				
				if(isset($details['infobox_content'])){

					$modal_html = file_get_contents('../snippets/infobox_modal.php');
					# Knitr tables use BS 3.3 and fade prevents window from showing ?
					$modal_html = str_replace('class="modal fade"', 'class="modal"', $modal_html);
					
					$line = $modal_html."\n".$line;
				}
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		return $layer_names;
	}
	
	private static function r_parse_user_index($details, $html_dir, $filename){
		$layer_names = array();
		
		// Replace sources to data files
		$lines = file($filename);
		
		$fp = fopen($filename, "w");

		$di = 0; $li = 0;
		
		//insert our php auth code above <!doctype html> in index.php
		$lines[0] = '<?php include("../../admin/incl/index_prefix.php"); ?>'."\n".$lines[0];
		
		foreach($lines as $line){
			if(preg_match('/src="images\//', $line, $matches)){
				$line = str_replace('images/', 'data_filep.php?f=images/', $line);
				copy('../snippets/data_filep.php', $html_dir.'/data_filep.php');
						
			}else if(str_contains($line, '<body>') || str_contains($line, '<body ')){
				$line .= "\n".'<?php include("../../admin/incl/index_header.php"); ?>'."\n";
			}

			fwrite($fp, $line);
		}
		fclose($fp);
		
		return $layer_names;
	}

	public static function installApp($newId, $details, $src_dir, $data_dir, $apps_dir){

		// move html dir to apps
		App::copy_r($src_dir, $apps_dir.'/'.$newId);
		
		// work in new html dir
		$html_dir = $apps_dir.'/'.$newId;
		$data_dir = $data_dir.'/'.$newId;
		mkdir($data_dir);
		
		$r_files = App::getRfiles($html_dir);
		foreach($r_files as $rf){

			// move all .R files to data dir
			rename($html_dir.'/'.$rf, $data_dir.'/'.$rf);
			
			# compile the index.R file
			list($rv, $out, $err) = AppR::runR($html_dir, $data_dir.'/'.$rf);
			if($rv != 0){
				return [1, 'r_file' => $rf, 'r_out' => $out, 'r_err' => $err];
			}

			list($map_html_files, $plot_html_files, $plotly_html_files, $knitr_tbls, $rmarkdown) = AppR::r_get_html_files($data_dir.'/'.$rf);
			
			foreach($map_html_files as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_map_index($details, $html_dir, $php_f);
			}
			
			if(is_dir($html_dir.'/figures') && !is_dir($data_dir.'/images')){
				mkdir($data_dir.'/images');
			}
			
			foreach($plot_html_files as $f){
				
				$name = substr($f, 0, -5);
				$php_f = $html_dir.'/'.$name.'.php';
				#rename to PHP
				rename($html_dir.'/'.$f, $php_f);

				unlink($html_dir.'/'.$f.'.rawhtml');

				AppR::r_parse_plot_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($plotly_html_files as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_plot_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($knitr_tbls as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::r_parse_knitr_index($details, $html_dir, $data_dir, $php_f);
			}
			
			foreach($rmarkdown as $f){
				$php_f = AppR::export_scripts($html_dir.'/'.$f, $data_dir);
				AppR::export_styles($php_f);
				AppR::export_images($php_f, $data_dir);
				AppR::r_parse_rmd_index($details, $html_dir, $data_dir, $php_f, $rf);
			}
		}

		// link images to source dir
		if(is_dir($html_dir.'/images')){
			App::rrmdir($data_dir.'/images');
			symlink($html_dir.'/images', $data_dir.'/images');
		}

		// index.html -> index.php
		if(is_file($html_dir.'/index.html')){
			rename($html_dir.'/index.html', $html_dir.'/index.php');
			$layer_names = AppR::r_parse_user_index($details, $html_dir, $html_dir.'/index.php');
		}
		
		// update map env.php
		$is_public = isset($details['is_public']) ? 'True' : 'False';

		$vars = [ 'MAP_ID' => $newId, 'IS_PUBLIC' => $is_public	];
		copy('../snippets/env.php', $html_dir.'/env.php');
		App::update_env($html_dir.'/env.php', $vars);

		//insert our php auth code above <!doctype html> in index.php
		$content = file_get_contents($html_dir.'/index.php');
		if(!str_contains($content, 'admin/incl/index_prefix.php')){	//insert can happen in foreach($r_files)
			file_put_contents($html_dir.'/index.php', '<?php include("../../admin/incl/index_prefix.php"); ?>' . $content);
		}
		
		if(isset($details['infobox_content'])){
			file_put_contents($data_dir.'/infobox.html', $details['infobox_content']);
		}

		# create emtpy map specific CSS
		$fp = fopen($html_dir.'/thismap.css', "w");
		fwrite($fp, $details['thismap_css']);
		fclose($fp);
		
		# install crontab
		if(isset($details['cron_period']) && $details['cron_period'] != 'never'){
			CRON::add($details['cron_period'], $details['cron_custom'], $newId);
		}
		
		return [0];
	}
	
	public static function is_rmd_source($str){
		return str_starts_with($str, '---');
	}
};
?>
