<?php
	$sent_layer_id = LAYER_ID_VALUE;
	$sent_date_from = (!empty($_GET['from'.$sent_layer_id])) ? $_GET['from'.$sent_layer_id] : 'DATE_FROM_VALUE';
	$sent_date_to	= (!empty($_GET['to'.$sent_layer_id])) 	 ? $_GET['to'.$sent_layer_id] 	: 'DATE_TO_VALUE';
	$sent_layer_name = 'LAYER_NAME_VALUE';
	$sent_layer_url = 'LAYER_URL_VALUE';
?>

L.control.calendar({
	id: <?=$sent_layer_id?>,
	minDate: "1999-01-01",
	value: "<?=$sent_date_from?>",
	maxDate: "<?=date("Y-m-d")?>",
	onSelectDate: (value) => onSelectDate('from<?=$sent_layer_id?>', value),
	position: "topleft",
}).addTo(map);

<?php if(!empty($sent_date_to)){ ?>
L.control.calendar({
	id: <?=$sent_layer_id+1?>,
	minDate: "1999-01-01",
	value: "<?=$sent_date_to?>",
	maxDate: "<?=date("Y-m-d")?>",
	onSelectDate: (value) => onSelectDate('to<?=$sent_layer_id?>', value),
	position: "topleft",
}).addTo(map);
<?php } ?>