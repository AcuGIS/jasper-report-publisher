var CustomControlInfoBox = L.Control.extend({
	options: { position: 'topleft'},
	onAdd: function(map) {
		var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
		container.innerHTML = `<a href="#" id="fg-infobox" class="fa fa-info"></a>`;
		L.DomEvent.disableClickPropagation(container);	// Prevent click events propagation to map
		return container;
	}
});
map.addControl(new CustomControlInfoBox());