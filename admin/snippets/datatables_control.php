<?php if(SHOW_DATATABLES) { ?>
var DtControl = L.Control.extend({
	options: {	position: 'topleft' },

	onAdd: function(map) {
		var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
		container.title = 'Hide/Show DataTables';
		container.innerHTML = `<a href="#" id="dt-link" class="fa fa-table"></a>`;
		L.DomEvent.disableClickPropagation(container);	/* Prevent click events propagation to map */
		return container;
	}
});
map.addControl(new DtControl());
map.on('click', unfocusLayer);
<?php } ?>