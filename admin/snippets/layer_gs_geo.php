<?php if(empty(DB_HOST)){ die("Error: Can\'t execute!"); } ?>

var VARNAME_data_lg     = L.layerGroup(null, {
	color: "STYLE_COLOR",
	fillColor: "STYLE_FILL_COLOR",
	opacity: STYLE_OPACITY,
	fillOpacity: STYLE_FILL_OPACITY,
	weight: 2	
});
var VARNAME_data_lg_ids = [];
const VARNAME_data = <?php

function fn_VARNAME(){
	$CACHE_PERIOD = CACHE_PERIOD_SECONDS;
	$GS_URL = 'FULL_URL'; 
	if($CACHE_PERIOD == 0){
		readfile($GS_URL);
	}else {
		$js_file = CACHE_DIR.'/'.MAP_ID.'/VARNAME_data.js';
		if(!is_file($js_file) || (time() - filemtime($js_file)) > $CACHE_PERIOD){
			
			if(!is_dir(CACHE_DIR.'/'.MAP_ID)){
				mkdir(CACHE_DIR.'/'.MAP_ID);
			}

			$fin  = fopen($GS_URL, 'r');
			$fout = fopen($js_file, 'w');
			
			while(($contents = fread($fin, 4096))){
				fwrite($fout, $contents);
			}
			fclose($fin);
			fclose($fout);
		}
		readfile($js_file);
	}
}
fn_VARNAME();
?>;

var VARNAME = L.geoJson(VARNAME_data, {
	style: {
		color: "STYLE_COLOR",
		fillColor: "STYLE_FILL_COLOR",
		opacity: STYLE_OPACITY,
		fillOpacity: STYLE_FILL_OPACITY,
		weight: 2
	},
	onEachFeature(feature, layer) {
		
		VARNAME_data_lg.addLayer(layer);
		VARNAME_data_lg_ids.push(layer._leaflet_id);
		
		var on_event = 'click';

				layer.on(on_event, function(e) {
						var properties = feature.properties;

						var html = '<table style="text-align: left; font-size: 100%;">';
						for (const key in properties) {
								html += '<tr><th>'+ key +"</th> <td>"+ properties[key] + "</td></tr>";
						}
						html += '</table>';
												
						$('.sidebar .table-container').html(html);
						$('.sidebar').show();
				});
	},

	pointToLayer(feature, latlng) {
		return L.circleMarker(latlng, {
			radius: 6,
			color: "STYLE_COLOR",
			fillColor: "STYLE_FILL_COLOR",
			opacity: STYLE_OPACITY,
			fillOpacity: STYLE_FILL_OPACITY,
			weight: 2
		});
	}
});