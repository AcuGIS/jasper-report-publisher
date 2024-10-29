var sidebarControl = L.Control.extend({
 options: { position: 'bottomleft'},
  onAdd: function (map) {
    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
    container.innerHTML = `<div class="sidebar">
            <a href="#" class="btn btn-sm mt-1 mx-3 close" id="fg-close-it" onclick="$(this).closest('.sidebar').hide()">X</a>
            <div class="table-container px-3 py-4"></div>
        </div>`;
    return container;
  }
});