var CustomControlPermalink = L.Control.extend({
	options: { position: 'topleft'},
	onAdd: function(map) {
		var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
		container.innerHTML = `<a href="#" id="fg-permalink" class="fa fa-share-alt"></a>`;
		L.DomEvent.disableClickPropagation(container);	// Prevent click events propagation to map
		return container;
	}
});
window.map.addControl(new CustomControlPermalink());
