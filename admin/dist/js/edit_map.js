var infoEditor;

function pwd_vis(pwd_field_id) {
	var x = document.getElementById(pwd_field_id);
	var i = document.getElementById(pwd_field_id + '_vis_i');
	if (x.type === "password") {
		x.type = "text";
		i.innerHTML = "visibility_off";
	} else {
		x.type = "password";
		i.innerHTML = "visibility";
	}
}

function reload_select(name, arr, val_sel){
	var obj = $('#' + name);
	obj.empty();
	
	let sel = val_sel == '0';
	obj.append($('<option>',{text: 'Select', value: '0', selected: sel}));
	$.each(arr, function(x){
		sel = val_sel == arr[x];
		obj.append($('<option>',{text: arr[x], value: arr[x], selected: sel}));
	});
	obj.change();
}
	
$(document).ready(function() {
	
	$(document).on("click", '#qgis_remove_a', function() {
		
		$('#qgis_remove_a').hide();
		$('#qgis_remove_i').hide();
		$('#qgis_file_current').hide();
		$('#qgis_layout').hide();
		$('#qgis_layout_label').hide();
		
		$('#qgis_remove').val($('#qgis_file_current').val());
		
		$('#qgis_file').show();
	});

	$(document).on("click", 'input[name="from_type"]', function() {
		let cm_r = editor0.getWrapperElement();
		
		if($(this).attr('id') == 'from_code'){
			$(cm_r).show();    						$(cm_r).prop('disabled', false);				$(cm_r).prop('required',true);
			$('#app').hide();    					$('#app').prop('disabled', true);				$('#app').prop('required',false);
			$('#archive').hide();					$('#archive').prop('disabled', true);		$('#archive').prop('required',false);
		}else if($(this).attr('id') == 'from_uploaded'){
			$('#app').show();    					$('#app').prop('disabled', false);			$('#app').prop('required',true);
			$(cm_r).hide();    						$(cm_r).prop('disabled', true);					$(cm_r).prop('required',false);
			$('#archive').hide();					$('#archive').prop('disabled', true);		$('#archive').prop('required',false);
		}else{
			$(cm_r).hide();    						$(cm_r).prop('disabled', true);					$(cm_r).prop('required',false);
			$('#app').hide();							$('#app').prop('disabled', true);				$('#app').prop('required',false);
			$('#archive').show(); 				$('#archive').prop('disabled', false);	$('#archive').prop('required',true);
		}
	});

	//// data
	$(document).on("click", 'input[class="data_files"]', function() {
		const id = $(this).attr('data-id');
		let pg = $('#pg_details' + id);
		let gs = $('#gs_details' + id);
		
		pg.hide();	pg.children('input').prop('required',false);
		gs.hide();	gs.children('input').prop('required',false);
	});

	$(document).on("click", 'input[class="data_pg"]', function() {
		const id = $(this).attr('data-id');
		let pg = $('#pg_details' + id);
		let gs = $('#gs_details' + id);
		
		pg.show();	pg.children('input').prop('required',true);
		gs.hide();	gs.children('input').prop('required',false);
	});

	$(document).on("click", 'input[class="data_gs"]', function() {
		const id = $(this).attr('data-id');
		let pg = $('#pg_details' + id);
		let gs = $('#gs_details' + id);
		
		pg.hide();	pg.children('input').prop('required',false);
		gs.show();	gs.children('input').prop('required',true);
	});

	//// layers
	$(document).on("click", 'input[class="layer_wms"]', function() {
		const id = $(this).attr('data-id');
		let wms = $('#wms_details' + id);
		let gs = $('#gs_geo_details' + id);
		
		wms.show();	wms.children('input').prop('required',true);
								wms.children('input[name^="wms_user"], input[name^="wms_pwd"]').prop('required',false);
		gs.hide();	gs.children('input').prop('required',false);
	});

	$(document).on("click", 'input[class="layer_gs_geo"]', function() {
		const id = $(this).attr('data-id');
		let wms = $('#wms_details' + id);
		let gs = $('#gs_geo_details' + id);
		
		wms.hide();	wms.children('input').prop('required',false);
		gs.show();	gs.children('input').prop('required',true);
	});
	
	$(document).on("change", 'input[type="checkbox"].disable_to', function() {
		const id = $(this).attr('data-id');
		let to = $('#to' + id);

		if($(this).is(':checked')){
			to.attr('disabled', 'disabled');
		}else{
			to.removeAttr('disabled');
		}
	});
	
	$(document).on("change", '.pglink_id', function() {
		let obj = $(this);
		let dsi = obj.attr('id').match(/\d+/)[0];
		let data = {
			'id' 			: obj.find('option:selected').val(),
			'schemas' : true
		};
		
		let sch = $('#pg_schema' + dsi);
		let tbl = $('#pg_tbl' + dsi);
		
		sch.val("0");	sch.attr('disabled', 'disabled');
		tbl.val("0");	tbl.attr('disabled', 'disabled');
		
		if(data.id != "0"){
			$.ajax({
				type: "POST",
				url: 'action/pglink.php',
				data: data,
				dataType:"json",
				success: function(response){
					 if(response.success) {
						 sch.removeAttr('disabled');
						 tbl.attr('disabled', 'disabled');
						 reload_select('pg_schema' + dsi, response.schemas, "0");
					 }else{
						 alert('Error: Failed to list project. ' + response.message);
					 }
				},
				fail: function(){	alert('Error: POST failure');	}
			});
		}
	});

	$(document).on("change", '.pg_schema', function() {
		let obj = $(this);
		let dsi = obj.attr('id').match(/\d+/)[0];
		let data = {
			'id' 		 : $('#pglink_id' + dsi).find('option:selected').val(),
			'schema' : obj.find('option:selected').val(),
			'tables' : true
		};

		let tbl = $('#pg_tbl' + dsi);
		let geom = $('#pg_geom' + dsi);
		
		tbl.val("0");	tbl.attr('disabled', 'disabled');
		geom.val("0");	geom.attr('disabled', 'disabled');

		if(data.id != "0"){
			$.ajax({
				type: "POST",
				url: 'action/pglink.php',
				data: data,
				dataType:"json",
				success: function(response){
					 if(response.success) {
						 tbl.removeAttr('disabled');
						 geom.attr('disabled', 'disabled');
						 reload_select('pg_tbl' + dsi, response.tables, "0");
					 }else{
						 alert('Error: Failed to list project. ' + response.message);
					 }
				},
				fail: function(){	alert('Error: POST failure');	}
			});
		}
	});

	$(document).on("change", '.pg_tbl', function() {
		let obj = $(this);
		let dsi = obj.attr('id').match(/\d+/)[0];
		let data = {
			'id' 		 : $('#pglink_id' + dsi).find('option:selected').val(),
			'schema' : $('#pg_schema' + dsi).find('option:selected').val(),
			'tbl'		 : obj.find('option:selected').val(),
			'geoms'  : true
		};

		let geom = $('#pg_geom' + dsi);
		
		geom.val("0");	geom.attr('disabled', 'disabled');

		if(data.id != "0"){
			$.ajax({
				type: "POST",
				url: 'action/pglink.php',
				data: data,
				dataType:"json",
				success: function(response){
					 if(response.success) {
						 geom.removeAttr('disabled');
						 reload_select('pg_geom' + dsi, response.geoms, "0");
					 }else{
						 alert('Error: Failed to list project. ' + response.message);
					 }
				},
				fail: function(){	alert('Error: POST failure');	}
			});
		}
	});

	$(document).on("change", "#cron_period", function() {
		var cron_period = $(this).find('option:selected').text();
		
		if(cron_period == 'custom'){
			$('#cron_custom').show();
		}else{
			$('#cron_custom').hide();
		}
	});

	$(document).on("change", '.gslink_id', function() {
		let obj = $(this);
		let dsi = obj.attr('id').match(/\d+/)[0];
		let data = {
			'id' 			: obj.find('option:selected').val(),
			'workspaces' : true
		};
		
		let ws = $('#gs_ws' + dsi);
		let ly = $('#gs_layer' + dsi);
		
		ws.val("0");	ws.attr('disabled', 'disabled');
		ly.val("0");	ly.attr('disabled', 'disabled');
		
		if(data.id != "0"){
			$.ajax({
				type: "POST",
				url: 'action/gslink.php',
				data: data,
				dataType:"json",
				success: function(response){
					 if(response.success) {
						 ws.removeAttr('disabled');
						 ly.attr('disabled', 'disabled');
						 reload_select('gs_ws' + dsi, response.workspaces, "0");
					 }else{
						 alert('Error: Failed to list project. ' + response.message);
					 }
				},
				fail: function(){	alert('Error: POST failure');	}
			});
		}
	});

	$(document).on("change", '.gs_ws', function() {
		let obj = $(this);
		let dsi = obj.attr('id').match(/\d+/)[0];
		let data = {
			'id' 		 : $('#gslink_id' + dsi).find('option:selected').val(),
			'workspace' : obj.find('option:selected').val(),
			'layers' : true
		};

		let ly = $('#gs_layer' + dsi);
		
		ly.val("0");	ly.attr('disabled', 'disabled');

		if(data.id != "0"){
			$.ajax({
				type: "POST",
				url: 'action/gslink.php',
				data: data,
				dataType:"json",
				success: function(response){
					 if(response.success) {
						 ly.removeAttr('disabled');
						 reload_select('gs_layer' + dsi, response.layers, "0");
					 }else{
						 alert('Error: Failed to list project. ' + response.message);
					 }
				},
				fail: function(){	alert('Error: POST failure');	}
			});
		}
	});

	// Update/Create connection on submit button click
	$(document).on("click", "#btn_submit", function() {
			let obj = $(this);
			let input = $('#map_form').find('input[type="text"], input[type="password"], select');
			let empty = false;
			
			input.each(function() {
				if (!$(this).prop('disabled') && !$(this).is(":hidden") && $(this).prop('required') && !$(this).val()) {
					$(this).addClass("error");
					empty = true;
				} else {
					$(this).removeClass("error");
				}
			});

			if(empty){
				$('#map_form').find(".error").first().focus();
			}else{
				let form_data = new FormData($('#map_form')[0]);
				
				form_data.delete('infobox_content');
				form_data.append('infobox_content', infoEditor.getData());
				
				form_data.delete('thismap_css');
				form_data.append('thismap_css', editor_css.getValue());
				
				const rmd_count = $('[id^=map_source_rmd]').length;
				const r_count = $('[id^=map_source_r]').length - rmd_count;
				
				for(i=0; i < r_count; i++){
					form_data.delete('map_source_r' + i);
					if(form_data.get('from_type') == 'code'){
						let editor_name = 'editor' + i;
						let editorX = window[editor_name];
						form_data.append('map_source_r' + i, editorX.getValue());
					}
				}
				
				
				for(i=0; i < rmd_count; i++){
					form_data.delete('map_source_rmd' + i);
					if(form_data.get('from_type') == 'code'){
						let editor_name = 'editor' + (r_count + i);
						let editorX = window[editor_name];
						form_data.append('map_source_rmd' + i, editorX.getValue());
					}
				}

				$('#btn_submit').prop('disabled', true);

					$.ajax({
						type: "POST",
						url: 'action/map.php',
						data: form_data,
						processData: false,
						contentType: false,
						dataType:"json",
						success: function(response){
							alert(response.message);
							 if(response.success) {
								 if(form_data.has('id')){
									 window.location.href = 'maps.php';
								 }else{
									 window.location.href = 'edit_map.php?id=' + response.id;
								 }
							 }else{
								$('#btn_submit').prop('disabled', false);
								if(r_count > 0){
									$('#r_output').show();
		 						  $('#r_output').html(response.r_out + response.r_err);
								}
							 }
						 }
					});
			}
	});
	
});
