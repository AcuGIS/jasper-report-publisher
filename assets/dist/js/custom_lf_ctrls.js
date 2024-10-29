$(document).ready(function() {
	
var CustomControlInfoBox = L.Control.extend({
	options: { position: 'topleft'},
	onAdd: function(map) {
		var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
		container.innerHTML = `<a href="#" id="fg-infobox" class="fa fa-info"></a>`;
		L.DomEvent.disableClickPropagation(container);	// Prevent click events propagation to map
		return container;
	}
});
window.lf_map.addControl(new CustomControlInfoBox());

L.control.browserPrint({
title: 'Just print me!',
documentTitle: 'Map printed using leaflet.browser.print plugin',
printLayer: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
subdomains: 'abcd',
minZoom: 1,
maxZoom: 16,
ext: 'png'
}),
closePopupsOnPrint: false,
printModes: [
            L.BrowserPrint.Mode.Landscape(),
            "Portrait",
            L.BrowserPrint.Mode.Auto("B4",{title: "Auto"}),
            L.BrowserPrint.Mode.Custom("B5",{title:"Select area"})
],
manualMode: false
}).addTo(window.lf_map);

});