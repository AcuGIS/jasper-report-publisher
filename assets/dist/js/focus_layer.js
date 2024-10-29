var table_visible = false;
var saved_lg_name = null;
var saved_layer_id = null;

const on_focus_style = {
			color: "#efefef",
			fillColor: "#efefef",
			opacity: 1.0,
			fillOpacity: 1.0,
			weight: 1
		};

function unfocusLayer(){
if(saved_lg_name != null){
	var lg_name = saved_lg_name;
	var l = window[lg_name].getLayer(saved_layer_id);
	
	var lg_style = {
		color: window[lg_name].options.color,
		fillColor: window[lg_name].options.fillColor,
		opacity: window[lg_name].options.opacity,
		fillOpacity: window[lg_name].options.fillOpacity,
		weight: window[lg_name].options.weight
	};
	
	l.setStyle(lg_style);
	
	saved_lg_name = null;
	saved_layer_id = null;
}
}

function focusLayer(lg_name, layer_id){
var l = window[lg_name].getLayer(layer_id);

unfocusLayer();
saved_lg_name	 = lg_name;
saved_layer_id = layer_id;

if(l.getLatLng){
	map.flyTo(l.getLatLng());	
}else{
	var bounds = l.getBounds();
	map.fitBounds(bounds);					// Fit the map to the polygon bounds
	map.panTo(bounds.getCenter());	// Or center on the polygon
}
l.setStyle(on_focus_style);
}

$(document).ready(function() {
	$(document).on("click", "#dt-link", function() {
		const d = (table_visible) ? 1 : 2;
		
		$("#map").height($(window).height() / d);
		map.invalidateSize();
		table_visible = !table_visible;
	});	
});
