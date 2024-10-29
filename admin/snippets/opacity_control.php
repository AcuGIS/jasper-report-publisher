const Map_AddLayer = {
<?php $fs =''; foreach(LAYER_NAMES as $k => $v){ ?>
    <?=$fs?>'<?=$k?>': <?=$v?>
<?php $fs = ',';} ?>
};
var lay_opa = L.control.opacity(Map_AddLayer, {	label: 'Layers Opacity', }).addTo(map);

<?php if(count(LAYER_NAMES) > 5 ) { ?>
$('.leaflet-control-layers').on('mouseleave', () => {
	if($(this).find('p:contains("Layers Opacity")')){
		lay_opa.collapse();
	}
});

$('.leaflet-control-layers-toggle').on('mouseenter', () => {
		if($(this).find('p:contains("Layers Opacity")')){
			lay_opa.expand();
		}
});
<?php } ?>