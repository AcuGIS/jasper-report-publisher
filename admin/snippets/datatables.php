<?php
if(SHOW_DATATABLES){ ?>
<script>
$(document).ready(function() {
	
	let lg_ids_i = 0;
	
	<?php for($i=0; $i < count(JS_VARNAMES); $i++){
		$varname = JS_VARNAMES[$i];	?>
		var columns_<?=$i?> = Object.keys(<?=$varname?>.features[0].properties).map(function(k){
			return {'title' : k};
		});
		
		if (typeof <?=$varname?>_lg_ids !== 'undefined') {
			columns_<?=$i?>.unshift({'title' : '#'});
		}
		
		lg_ids_i = 0;
		const dataSet_<?=$i?> = <?=$varname?>.features.map(function(e) {
			var props = Object.keys(e.properties).map(function(k){
				return e.properties[k];
			});
			
			if (typeof <?=$varname?>_lg_ids !== 'undefined') {
				var t = '<a href="javascript:void(0);" class="fa fa-search" onclick="focusLayer(\'<?=$varname?>_lg\',' + <?=$varname?>_lg_ids[lg_ids_i] + ')"></a>';
				props.unshift(t);
				lg_ids_i = lg_ids_i + 1;
			}
			
			return props;
		});

		$('#dataTable<?=$i?>').DataTable({
			columns: columns_<?=$i?>,
			deferRender: true,
			data: dataSet_<?=$i?>
		});
	<?php } ?>
});
</script>
	<div id='dataTables'>
		<ul class="nav nav-tabs" role="tablist">
			<?php $first = ' active'; $li_first = 'class="nav-item" role="presentation"';
				for($i=0; $i < count(JS_VARNAMES); $i++){
					$varname = JS_VARNAMES[$i];	?>
				<li <?=$li_first?>>
					<button class="nav-link<?=$first?>" href="#tab-table<?=$i?>" data-bs-toggle="tab" data-bs-target="#tab-table<?=$i?>"><?=$varname?></button>
				</li>
			<?php $first = ''; $li_first = ''; } ?>
		</ul>
		
		<div class="tab-content pt-2">
			<?php $first = ' show active';
				for($i=0; $i < count(JS_VARNAMES); $i++){
					$varname = JS_VARNAMES[$i]; ?>
				<div class="tab-pane<?=$first?>" id="tab-table<?=$i?>">
					<table id="dataTable<?=$i?>" class="table table-striped table-bordered" cellspacing="0" width="100%"></table>
					<p class="mb-0"> <a href="javascript:void(0);" onclick="dataToCSV('<?=$varname?>', <?=$varname?>)">Export to CSV</a> </p>
				</div>
			<?php $first = ''; } ?>
		</div>
	</div>
<?php } ?>