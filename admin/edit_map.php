<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/database.php');
		require('class/map.php');
		require('class/app.php');
		require('class/pglink.php');
		require('class/gslink.php');
		require('class/access_groups.php');
		require('class/cron.php');

		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin'){
        header('Location: ../login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		
		$map = ['name' => '','description' => ''];
		
		$map_acc_ids		= array();
		$pglinks = array();
		$use_dt = 0;
		$is_public = false;
		$qgis_filename = null;
		$qgis_layouts = array();
		$qgis_layout = null;
		$map_type = APP_TYPE_NONE;
		$r_files = array(1);
		$rmd_files = array();
		$cron = ['cron_period' => 'never', 'cron_custom' => '*/30 * * * *'];
		
		if(!empty($_GET['id'])){
			$map_obj = new map_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
			
			$result = $map_obj->getById($_GET['id']);
			if(!$result){
				$_GET['error'] = 'Error: No such map!';
				exit;
			}
			$map = pg_fetch_assoc($result);
			pg_free_result($result);
			
			$map_acc_grps = $map_obj->getAccessGroups($_GET['id']);
			$map_acc_ids  = array_keys($map_acc_grps);
			
			$cron = CRON::get($_GET['id']);
			list($dss,$lys, $ses, $use_dt,$is_public,$qgis_layout, $map_type) = parseIndex(APPS_DIR.'/'.$map['id'], DATA_DIR.'/'.$map['id']);
			
			if($map_type != APP_TYPE_R){
				$qgis_filename = App::parseQGIS(APPS_DIR.'/'.$map['id']);
				if($qgis_filename){
					$qgis_layouts = App::parseQGISLayouts(DATA_DIR.'/'.$map['id'].'/'.$qgis_filename);
				}
				
				$pgobj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$pglinks = $pgobj->getArr();
				
				$gsobj = new gslink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$gslinks = $gsobj->getArr();
}			
			
		}else{
			$upload_dir = App::upload_dir($_SESSION[SESS_USR_KEY]->ftp_user);
			$app_names = App::getApps($upload_dir);
		}
		
		$acc_obj = new access_group_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
		$acc_grps = $acc_obj->getRowsArr();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<?php if(!empty($_GET['id'])){ ?>
	<link href="dist/css/accordion.css" rel="stylesheet">
	<?php } ?>

		<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.css" />
		<script type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/42.0.0/"
        }
    }
		</script>

		<link rel="stylesheet" href="../assets/dist/codemirror/codemirror.css">
		<link rel="stylesheet" href="../assets/dist/codemirror/show-hint.css">
		<script src="../assets/dist/codemirror/codemirror.js"></script>
		<script src="../assets/dist/codemirror/show-hint.js"></script>
		<script src="../assets/dist/codemirror/css-hint.js"></script>
		<script src="../assets/dist/codemirror/css.js"></script>
		<script src="dist/js/edit_map.js"></script>
		
		<script type="text/javascript">
			$(document).ready(function() {
			
				$('[data-toggle="tooltip"]').tooltip();
				
				<?php if($qgis_filename){ ?>
					$('#qgis_file').hide();
				<?php } else {?>
					$('#qgis_file_current').hide();
					$('#qgis_layout').hide();
					$('#qgis_layout_label').hide();
					
				<?php } ?>
				
				$('#map_form').submit(false);
				<?php
				if(isset($_GET['id'])){
					
					foreach($dss as $dsi => $ds) {
						if($ds['data_type'] == 'gs'){ ?>
						$('#pg_details<?=$dsi?>').hide();
				<?php } else if($ds['data_type'] == 'pg'){ ?>
						$('#gs_details<?=$dsi?>').hide();
				<?php }else { ?>
						$('#pg_details<?=$dsi?>').hide();
						$('#gs_details<?=$dsi?>').hide();
				<?php }
					}
					
					foreach($lys as $lyi => $ly) {
						if($ly['layer_type'] == 'gs_geo'){ ?>
						$('#wms_details<?=$lyi?>').hide();
						<?php }else if($ly['layer_type'] == 'wms'){ ?>
						$('#gs_geo_details<?=$lyi?>').hide();
				<?php }
					}
				} ?>

				//$('#map_source_r').hide();
				$('#app').hide();
				$('#archive').hide();
				
				$('#r_output').hide();
			});

			// Codemirror doesn't refresh on accordion open, so we do it here!
			$(document).on("click", ".accordion-plus-toggle", function() {
				let id = $(this).data('parent').match(/\d+/)[0];
				let edX = window['editor' + id];
				edX.refresh();
			});
		</script>


<style>

.label {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 500!important;
}


.form-select-lg {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    padding-left: 1rem;
    font-size: 1.55rem;

border-radius:9px;border:1px solid #AAAAAA;


}
</style>
</head>
<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'maps.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        <div class="page-wrapper">
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                        </nav>
												<?php if(isset($_GET['id'])){ ?>
													<h1 class="mb-0 fw-bold">Update Map <?=$_GET['id']?></h1>
												<?php } else { ?>
                        	<h1 class="mb-0 fw-bold">Add QGIS Map or R App</h1>
												<?php } ?>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
							
							<form action="" method="post" enctype="multipart/form-data" id="map_form" style="width: 50%;">
											<?php if (isset($_GET['error'])) { ?>
											<div class="alert alert-danger" role="alert">
												<?=$_GET['error']?>
										  </div>
										  <?php } ?>

									<?php if(isset($_GET['id'])){ ?>
										<input type="hidden" class="form-control" name="id" id="id" value="<?=$_GET['id']?>" />
									<?php } ?>
										<input type="hidden" class="form-control" name="save" id="save" value="1" />
									
									
									<div class="form-group">
										<label for="name" class="form-label">Name</label>
										<input type="text" class="form-control" name="name" id="name" value="<?=$map['name']?>" required/>									
										<label for="description" class="form-label">Description</label>
										<input type="text" class="form-control" name="description" id="description" value="<?=$map['description']?>" required/>
									</div>
									
								<?php if(!isset($_GET['id'])){ ?>
									<fieldset>
										<legend>Source</legend>	
										<div class="form-group">
											
											<input type="radio" id="from_code" name="from_type" value="code" checked>
											<label for="from_code">Code</label>
											<input type="radio" id="from_uploaded" name="from_type" value="uploaded">
											<label for="from_uploaded">Uploads</label>
											<input type="radio" id="from_zip" name="from_type" value="archive">
											<label for="from_zip">Archive</label>
											
											<textarea name="map_source_r0" id="map_source_r0" rows="10" cols="80"><?php if(isset($_GET['id']) && is_file(DATA_DIR.'/'.$_GET['id'].'/index.R')) { readfile(APPS_DIR.'/'.$_GET['id'].'/index.R'); }else{ ?>Enter R code for map.<?php } ?></textarea>
											
											<select class="form-control" name="app" id="app" aria-label="Select app" disabled>
												<?php foreach($app_names as $k){ ?>
													<option <?php if($k == $map['name']){ ?> selected <?php }?> value="<?=$k?>"><?=$k?></option>
											<?php	} ?>
										</select>
											
											<input type="file" class="form-control" name="archive" id="archive" value="" accept=".zip" disabled/>
											<p>NOTE: Archive can contains files, or project directory.</p>
										</div>
									</fieldset>
								<?php }else {
									
									if($map_type == APP_TYPE_R) {
										$r_files = App::getRfiles(DATA_DIR.'/'.$_GET['id']);
										$rmd_files = App::getFilesByType(APPS_DIR.'/'.$_GET['id'], 'Rmd');
									?>
									<input type="hidden" id="from_code" name="from_type" value="code">
									
									<fieldset>
										<legend>Source</legend>	
										
										<?php for($i=0; $i < count($r_files); $i++){ ?>
											
											<div class="panel-group" id="accordion<?=$i?>" role="tablist" aria-multiselectable="false">
											   <div class="panel panel-default">
											      <div class="panel-heading" role="tab" id="heading<?=$i?>">
											         <h5 class="panel-title">
											            <a role="button" data-toggle="collapse" class="accordion-plus-toggle collapsed" data-parent="#accordion<?=$i?>" href="#collapse<?=$i?>" aria-expanded="false" aria-controls="collapse<?=$i?>"><?=$r_files[$i]?></a>
											         </h5>
											      </div>
											      <div id="collapse<?=$i?>" class="panel-collapse collapse" role="tabpanel" style="padding: 12px;" aria-labelledby="heading<?=$i?>">
															<textarea name="map_source_r<?=$i?>" id="map_source_r<?=$i?>" rows="10" cols="80"><?php if(isset($_GET['id']) && is_file(DATA_DIR.'/'.$_GET['id'].'/'.$r_files[$i])) { readfile(DATA_DIR.'/'.$_GET['id'].'/'.$r_files[$i]); }else{ ?>Enter R code for map.<?php } ?></textarea>
														</div>
												</div>
											</div>
										
									<?php } ?>

								<?php for($j=0, $i=count($r_files); $j < count($rmd_files); $j++, $i++){ ?>
									
									<div class="panel-group" id="accordion<?=$i?>" role="tablist" aria-multiselectable="false">
										 <div class="panel panel-default">
												<div class="panel-heading" role="tab" id="heading<?=$i?>">
													 <h5 class="panel-title">
															<a role="button" data-toggle="collapse" class="accordion-plus-toggle collapsed" data-parent="#accordion<?=$i?>" href="#collapse<?=$i?>" aria-expanded="false" aria-controls="collapse<?=$i?>"><?=$rmd_files[$j]?></a>
													 </h5>
												</div>
												<div id="collapse<?=$i?>" class="panel-collapse collapse" role="tabpanel" style="padding: 12px;" aria-labelledby="heading<?=$i?>">
													<textarea name="map_source_rmd<?=$j?>" id="map_source_rmd<?=$j?>" rows="10" cols="80"><?php if(isset($_GET['id']) && is_file(APPS_DIR.'/'.$_GET['id'].'/'.$rmd_files[$j])) { readfile(APPS_DIR.'/'.$_GET['id'].'/'.$rmd_files[$j]); }else{ ?>Enter Rmd code for map.<?php } ?></textarea>
												</div>
										</div>
									</div>
								
							<?php } ?>
									</fieldset>
								<?php }
								
									$aci = 0;
								?>

								<fieldset>
									<legend>Data</legend>									
									
									<?php if($map_type == APP_TYPE_R) { ?>
									<div class="form-group">
										<label for="cron_period" class="form-label">Update:</label>
										<select class="form-control" name="cron_period" id="cron_period" aria-label="Select cron period">
										<?php foreach(CRON_PERIOD as $period) { ?>
											<option <?php if($cron['cron_period'] == $period){?> selected <?php }?> value="<?=$period?>"><?=$period?></option>
										<?php	} ?>
										</select>
										<input type="text" class="form-control" name="cron_custom" id="cron_custom" <?php if($cron['cron_period'] != 'custom'){?> style="display: none;" <?php } ?> value="<?=$cron['cron_custom']?>" />
									</div>
								<?php } ?>
										
									<?php foreach($dss as $dsi => $ds) { ?>
					<div class="panel-group" id="accordion<?=$aci?>" role="tablist" aria-multiselectable="false">
					   <div class="panel panel-default">
					      <div class="panel-heading" role="tab" id="heading<?=$aci?>">
					         <h5 class="panel-title">
					            <a role="button" data-toggle="collapse" class="accordion-plus-toggle collapsed" data-parent="#accordion<?=$aci?>" href="#collapse<?=$aci?>" aria-expanded="false" aria-controls="collapse<?=$aci?>"><?=$ds['name']?></a>
					         </h5>
					      </div>
					      <div id="collapse<?=$aci?>" class="panel-collapse collapse" role="tabpanel" style="padding: 12px;" aria-labelledby="heading<?=$aci?>">
														
										<div class="form-group col-sm">
											<input type="radio" class="data_files" 	data-id="<?=$dsi?>" name="data_type<?=$dsi?>" value="file" <?php if($ds['data_type'] == 'file'){ ?> checked<?php } ?>>	<label for="data_files<?=$dsi?>">Files</label>
											<input type="radio" class="data_pg" 		data-id="<?=$dsi?>" name="data_type<?=$dsi?>" value="pg"		<?php if($ds['data_type'] == 'pg'){ ?> checked<?php } ?>>			<label for="data_pg<?=$dsi?>">Postgres</label>
											<input type="radio" class="data_gs" 		data-id="<?=$dsi?>" name="data_type<?=$dsi?>" value="gs"		<?php if($ds['data_type'] == 'gs'){ ?> checked<?php } ?>>			<label for="data_gs<?=$dsi?>">GeoServer</label>
										</div>
											
										<div class="pg_details" id="pg_details<?=$dsi?>">
											
											<label for="pglink_id<?=$dsi?>" class="form-label">PG</label>
											<select class='pglink_id form-select form-select-lg mb-3' id="pglink_id<?=$dsi?>" name="pglink_id<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($pglinks as $k => $v) { ?>
												<option value="<?=$k?>" <?php if($ds['pglink_id'] == $k) { ?> selected <?php } ?>><?=$v?></option>
											<?php } ?>
											</select>

											<?php	
											$schemas = array(); $tables = array(); $geoms = array();
											if($ds['pglink_id'] != '0') {
												$pgl_obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
												$pg_res = $pgl_obj->getById($ds['pglink_id']);
												if($pg_res && (pg_num_rows($pg_res) > 0)){
													$pg_row = pg_fetch_assoc($pg_res);
													pg_free_result($pg_res);	
													
													$rdb = new Database($pg_row['host'], $pg_row['dbname'], $pg_row['username'], $pg_row['password'], $pg_row['port'], $ds['pg_schema']);
													list($schemas,$sch_err) = $rdb->getSchemas($pg_row['dbname'], $pg_row['username']);
													list($tables, $tbl_err)  = $rdb->getTables($ds['pg_schema']);
													list($geoms,  $tbl_err)  = $rdb->getGeomColumns($ds['pg_schema'], $ds['pg_tbl']);
												}
											} ?>
											
											<label for="pg_schema<?=$dsi?>" class="form-label">Schema</label>
											<select class='pg_schema form-select form-select-lg mb-3' id="pg_schema<?=$dsi?>" name="pg_schema<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($schemas as $k) { ?>
												<option value="<?=$k?>" <?php if($ds['pg_schema'] == $k) { ?> selected <?php } ?>><?=$k?></option>
											<?php } ?>
											</select>
											
											<label for="pg_tbl<?=$dsi?>" class="form-label">Table</label>
											<select class='pg_tbl form-select form-select-lg mb-3' id="pg_tbl<?=$dsi?>" name="pg_tbl<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($tables as $k) { ?>
												<option value="<?=$k?>" <?php if($ds['pg_tbl'] == $k) { ?> selected <?php } ?>><?=$k?></option>
											<?php } ?>
											</select>
											
											<label for="pg_geom<?=$dsi?>" class="form-label">Geometry Column</label>
											<select class='pg_geom form-select form-select-lg mb-3' id="pg_geom<?=$dsi?>" name="pg_geom<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($geoms as $k) { ?>
												<option value="<?=$k?>" <?php if($ds['pg_geom'] == $k) { ?> selected <?php } ?>><?=$k?></option>
											<?php } ?>
											</select>
											
											<div class="form-group"><br>
												<label for="pg_cache<?=$dsi?>" class="form-label">Cache&nbsp;&nbsp;</label><br>
												<input type="number" name="pg_cache_val<?=$dsi?>" id="pg_cache_val<?=$dsi?>" value="<?=$ds['pg_cache_val']?>"/>
												<select name="pg_cache_per<?=$dsi?>" id="pg_cache_per<?=$dsi?>">
													<?php foreach(TIME_MAP as $per => $val) { ?>
														<option value="<?=$per?>" <?php if($ds['pg_cache_per'] == $per) { ?> selected <?php } ?>><?=$per?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										
										<div class="gs_details" id="gs_details<?=$dsi?>">
											<label for="gslink_id<?=$dsi?>" class="form-label">GS</label>
											<select class='gslink_id form-select form-select-lg mb-3' id="gslink_id<?=$dsi?>" name="gslink_id<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($gslinks as $k => $v) { ?>
												<option value="<?=$k?>" <?php if($ds['gslink_id'] == $k) { ?> selected <?php } ?>><?=$v?></option>
											<?php } ?>
											</select>

											<?php	
											$workspaces = array(); $layers = array();
											if($ds['gslink_id'] != '0') {
												$gsl_obj = new gslink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
												$gs_res = $gsl_obj->getById($ds['gslink_id']);
												if($gs_res && (pg_num_rows($gs_res) > 0)){
													$gs_row = pg_fetch_assoc($gs_res);
													pg_free_result($gs_res);
													
													$gs_rv = $gsl_obj->getWorkspaces($gs_row['url'], $gs_row['username'], $gs_row['password']);
													if($gs_rv['success'] == true){
														$workspaces = $gs_rv['workspaces'];
													}
													$gs_rv	= $gsl_obj->getLayers($gs_row['url'], $gs_row['username'], $gs_row['password'], $ds['gs_ws']);
													if($gs_rv['success'] == true){
														$layers = $gs_rv['layers'];
													}
												}
											} ?>
											
											<label for="gs_ws<?=$dsi?>" class="form-label">Workspace</label>
											<select class='gs_ws form-select form-select-lg mb-3' id="gs_ws<?=$dsi?>" name="gs_ws<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($workspaces as $v) { ?>
												<option value="<?=$v?>" <?php if($ds['gs_ws'] == $v) { ?> selected <?php } ?>><?=$v?></option>
											<?php } ?>
											</select>
											
											<label for="gs_layer<?=$dsi?>" class="form-label">Layer</label>
											<select class='gs_layer form-select form-select-lg mb-3' id="gs_layer<?=$dsi?>" name="gs_layer<?=$dsi?>">
												<option value='0'>Select</option>
												<?php foreach($layers as $v) { ?>
												<option value="<?=$v?>" <?php if($ds['gs_layer'] == $v) { ?> selected <?php } ?>><?=$v?></option>
											<?php } ?>
											</select>
											
											<div class="form-group">
												<label for="gs_cache<?=$dsi?>" class="form-label">Cache</label>
												<input type="number" name="gs_cache_val<?=$dsi?>" id="gs_cache_val<?=$dsi?>" value="<?=$ds['gs_cache_val']?>"/>
												<select name="gs_cache_per<?=$dsi?>" id="gs_cache_per<?=$dsi?>">
													<?php foreach(TIME_MAP as $per => $val) { ?>
														<option value="<?=$per?>" <?php if($ds['gs_cache_per'] == $per) { ?> selected <?php } ?>><?=$per?></option>
													<?php } ?>
												</select>
											</div>
										</div>
							</div>
						</div>
					</div>
										<?php $aci = $aci + 1;
									} ?>
								</fieldset>
								
								<fieldset>
									<legend>Layers</legend>
									<?php
										foreach($ses as $lyi => $se) { ?>
					<div class="panel-group" id="accordion<?=$aci?>" role="tablist" aria-multiselectable="false">
					   <div class="panel panel-default">
					      <div class="panel-heading" role="tab" id="heading<?=$aci?>">
					         <h5 class="panel-title">
					            <a role="button" data-toggle="collapse" class="accordion-plus-toggle collapsed" data-parent="#accordion<?=$aci?>" href="#collapse<?=$aci?>" aria-expanded="false" aria-controls="collapse<?=$aci?>">Sentinel <?=$lyi?></a>
					         </h5>
					      </div>
					      <div id="collapse<?=$aci?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?=$aci?>">
									
									<div class="form-group col-sm">
										<input type="date" name="from<?=$lyi?>" id="from<?=$lyi?>" value="<?=$se['date_from']?>" min="1999-01-01" max="<?=date("Y-m-d")?>">
										<label for="from<?=$lyi?>" class="form-label">From</label>
									</div>
									
									<div class="form-group col-sm">
										<input type="date" name="to<?=$lyi?>" id="to<?=$lyi?>" value="<?=$se['date_to']?>" min="1999-01-01" max="<?=date("Y-m-d")?>" <?php if(empty($se['date_to'])) {?> disabled <?php } ?>>
										<label for="to<?=$lyi?>" class="form-label">To</label>
										<input type="checkbox" class="form-checkbox disable_to" name="disable_to<?=$lyi?>" data-id="<?=$lyi?>" value="1" <?php if(empty($se['date_to'])) {?> checked <?php } ?>/>
										<label for="disable_to<?=$lyi?>">(disable)</label>
									</div>
									
									<div class="form-group col-sm">
										<input type="checkbox" class="form-checkbox" name="se_proxy<?=$lyi?>" value="1" <?php if($se['se_proxy']) {?> checked <?php } ?>/>
										<label for="se_proxy<?=$lyi?>">Protect with proxy</label>
									</div>
								</div>
						</div>
					</div>
				<?php 				$aci = $aci + 1;
										}
										
										foreach($lys as $lyi => $ly) { ?>
					<div class="panel-group" id="accordion<?=$aci?>" role="tablist" aria-multiselectable="false">
					   <div class="panel panel-default">
					      <div class="panel-heading" role="tab" id="heading<?=$aci?>">
					         <h5 class="panel-title">
					            <a role="button" data-toggle="collapse" class="accordion-plus-toggle collapsed" data-parent="#accordion<?=$aci?>" href="#collapse<?=$aci?>" aria-expanded="false" aria-controls="collapse<?=$aci?>"><?=$ly['name']?></a>
					         </h5>
					      </div>
					      <div id="collapse<?=$aci?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?=$aci?>">
														
										<div class="form-group col-sm">
											<input type="radio" class="layer_wms" 	 data-id="<?=$lyi?>" name="layer_type<?=$lyi?>" value="wms" 		<?php if($ly['layer_type'] == 'wms'){    ?> checked<?php } ?>>	<label for="layer_wms<?=$lyi?>">WMS</label>
											<input type="radio" class="layer_gs_geo" data-id="<?=$lyi?>" name="layer_type<?=$lyi?>" value="gs_geo"	<?php if($ly['layer_type'] == 'gs_geo'){ ?> checked<?php } ?>>	<label for="layer_gs_geo<?=$lyi?>">GeoJSON(GS)</label>
										</div>
										
										<input type="hidden" name="layer_varname<?=$lyi?>"		id="layer_varname<?=$lyi?>"   value="<?=$ly['layer_varname']?>"/>
										
										<div class="wms_details" id="wms_details<?=$lyi?>">
											<label for="wms_url<?=$lyi?>"	 	class="form-label">URL</label>				<input type="text"		 class="form-control" name="wms_url<?=$lyi?>"		id="wms_url<?=$lyi?>"   value="<?=$ly['wms_url']?>"/>
											<label for="wms_ws<?=$lyi?>"		class="form-label">Workspace</label>	<input type="text" 		 class="form-control" name="wms_ws<?=$lyi?>"		id="wms_ws<?=$lyi?>"    value="<?=$ly['wms_ws']?>"/>
											<label for="wms_layer<?=$lyi?>" class="form-label">Layer</label>			<input type="text" 		 class="form-control" name="wms_layer<?=$lyi?>"	id="wms_layer<?=$lyi?>" value="<?=$ly['wms_layer']?>"/>
											
											<label for="wms_user<?=$lyi?>" class="form-label">User (only for secured connections)</label>			<input type="text" 		 class="form-control" name="wms_user<?=$lyi?>" id="wms_user<?=$lyi?>"  value="<?=$ly['wms_user']?>"/>
											<label for="wms_pwd<?=$lyi?>"	 class="form-label">Password</label>
											<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('wms_pwd<?=$lyi?>')">
												<i id="wms_pwd<?=$lyi?>_vis_i" class="material-icons" style="color:grey">visibility</i>
											</a>
											<input type="password" class="form-control" name="wms_pwd<?=$lyi?>"	 id="wms_pwd<?=$lyi?>"   value="<?=$ly['wms_pwd']?>"/>
										</div>
										
										<div class="gs_geo_details" id="gs_geo_details<?=$lyi?>">
											<label for="gs_geo_host<?=$lyi?>"	 class="form-label">Host</label>			<input type="text"		 class="form-control" name="gs_geo_host<?=$lyi?>"	 id="gs_geo_host<?=$lyi?>"  value="<?=$ly['gs_geo_host']?>"/>
											<label for="gs_geo_user<?=$lyi?>"	 class="form-label">User</label>			<input type="text" 		 class="form-control" name="gs_geo_user<?=$lyi?>"	 id="gs_geo_user<?=$lyi?>"  value="<?=$ly['gs_geo_user']?>"/>
											<label for="gs_geo_pwd<?=$lyi?>"	 class="form-label">Password</label>
											<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('gs_geo_pwd<?=$lyi?>')">
												<i id="gs_geo_pwd<?=$lyi?>_vis_i" class="material-icons" style="color:grey">visibility</i>
											</a>
											<input type="password" class="form-control" name="gs_geo_pwd<?=$lyi?>"	 id="gs_geo_pwd<?=$lyi?>"   value="<?=$ly['gs_geo_pwd']?>"/>
											<label for="gs_geo_ws<?=$lyi?>"		 class="form-label">Workspace</label>	<input type="text" 		 class="form-control" name="gs_geo_ws<?=$lyi?>"		 id="gs_geo_ws<?=$lyi?>"    value="<?=$ly['gs_geo_ws']?>"/>
											<label for="gs_geo_layer<?=$lyi?>" class="form-label">Layer</label>			<input type="text" 		 class="form-control" name="gs_geo_layer<?=$lyi?>" id="gs_geo_layer<?=$lyi?>" value="<?=$ly['gs_geo_layer']?>"/>
											
											<div class="form-group">
												<label for="gs_geo_color<?=$lyi?>" class="form-label">Color</label>
												<input type="color" class="form-control" name="gs_geo_color<?=$lyi?>"   id="gs_geo_color<?=$lyi?>"   value="<?=$ly['gs_geo_color']?>"/>
												<input type="range" class="form-control" name="gs_geo_opacity<?=$lyi?>" id="gs_geo_opacity<?=$lyi?>" value="<?=$ly['gs_geo_opacity']?>" min="0" max="100"/>
											</div>
											
											<div class="form-group">
												<label for="gs_geo_fillcolor<?=$lyi?>" class="form-label">Fill Color</label>
												<input type="color" class="form-control" name="gs_geo_fill_color<?=$lyi?>"   id="gs_geo_fill_color<?=$lyi?>"   value="<?=$ly['gs_geo_fill_color']?>"/>
												<input type="range" class="form-control" name="gs_geo_fill_opacity<?=$lyi?>" id="gs_geo_fill_opacity<?=$lyi?>" value="<?=$ly['gs_geo_fill_opacity']?>" min="0" max="100"/>
											</div>
											
											<div class="form-group">
												<label for="gs_geo_cache<?=$lyi?>" class="form-label">Cache</label>
												<input type="number" name="gs_geo_cache_val<?=$lyi?>" id="gs_geo_cache_val<?=$lyi?>" value="<?=$ly['gs_geo_cache_val']?>"/>
												<select name="gs_geo_cache_per<?=$lyi?>" id="gs_geo_cache_per<?=$lyi?>">
													<?php foreach(TIME_MAP as $per => $val) { ?>
														<option value="<?=$per?>" <?php if($ly['gs_geo_cache_per'] == $per) { ?> selected <?php } ?>><?=$per?></option>
													<?php } ?>
												</select>
											</div>
											
										</div>
							</div>
						</div>
					</div>
										<?php $aci = $aci + 1;
									} ?>
								</fieldset>
								
								
							<?php }
								
							if($map_type != APP_TYPE_R) { ?>
									<fieldset>
										<legend>QGIS</legend>	
										<div class="form-group">
											<label for="qgis_file" class="form-label">Project File</label>
											<?php if($qgis_filename) { ?>
												<input type="hidden" class="form-control" name="qgis_remove" id="qgis_remove" value=""/>
												<a id="qgis_remove_a" class="icon-link" href="#" title="Remove QGIS" data-toggle="tooltip" data-placement="bottom" data-trigger="hover">
													<i id="qgis_remove_i" class="material-icons" style="color:grey">delete_forever</i>
												</a>
												<input type="text" class="form-control" name="qgis_file_current" id="qgis_file_current" value="<?=$qgis_filename?>" disabled/>
												<label id="qgis_layout_label" for="qgis_layout" class="form-label">Print Template</label>
												<select class="form-control" name="qgis_layout" id="qgis_layout" aria-label="Select QGIS template" required>
													<option <?php if(empty($qgis_layout)){?> selected <?php }?> value="">Select</option>
													<?php foreach($qgis_layouts as $v){ ?>
														<option <?php if($v == $qgis_layout){?> selected <?php }?> value="<?=$v?>"><?=$v?></option>
													<?php	} ?>
												</select>
											<?php } else {?>
												<input type="text" class="form-control" name="qgis_file_current" id="qgis_file_current" value="" disabled/>
											<?php } ?>
											<input type="file" class="form-control" name="qgis_file[]" id="qgis_file" value="" accept=".qgs,.gpkg" multiple/>
										</div>
									</fieldset>
								<?php } ?>
								
									<fieldset>
										<legend>View</legend>
										
										<div class="form-group">
											<label for="thismap_css" class="form-label">Map CSS</label>
											<textarea name="thismap_css" id="thismap_css" rows="10" cols="80"><?php if(!empty($_GET['id'])){ readfile(APPS_DIR.'/'.$_GET['id'].'/thismap.css'); }else { ?>/* map specific CSS */<?php } ?></textarea>
										</div>
										
									<?php if($map_type != APP_TYPE_R) { ?>
										<div class="form-group">
											<input type="checkbox" class="form-checkbox" name="use_datatable" id="use_datatable" value="1" <?php if($use_dt == 1) {?> checked <?php } ?> <?php if(($use_dt == -1) || ($map_type == APP_TYPE_Q3D)) {?> disabled <?php } ?>/>
											<label for="use_datatable" class="form-label">Show DataTable below map</label>
										</div>
									<?php } ?>
										
										<div class="form-group">
											<label for="image" class="form-label">Thumbnail Image (.png, .jpeg formats)</label>
											 <?php if(isset($_GET['id']) && is_file("../assets/maps/".$_GET['id'].".png")){ ?>
											 	<img src="../assets/maps/<?=$_GET['id']?>.png" alt="Map Preview" width="200" height="150">
											<?php } else { ?>
												<img src="../assets/maps/default.png" alt="Map Preview" width="200" height="150">
											<?php } ?>
											<input type="file" class="form-control" name="image" id="image" value="" accept=".png,.jpg,.jpeg"/>
										</div>

										
										<div class="form-group">
											<label for="infobox_content" class="form-label">Info box</label>
											<textarea name="infobox_content" id="infobox_content" rows="10" cols="80" <?php if($map_type == APP_TYPE_Q3D){ ?>disabled<?php } ?>><?php if(isset($_GET['id']) && is_file(DATA_DIR.'/'.$_GET['id'].'/infobox.html')) { readfile(DATA_DIR.'/'.$_GET['id'].'/infobox.html'); }else{ ?>Enter information to be displayed, when your map Info button is clicked.<?php } ?></textarea>
										</div>
								</fieldset>
										
								<fieldset>
									<legend>Security</legend>
									<div class="form-group">
										
										<input type="checkbox" class="form-checkbox" name="is_public" id="is_public" value="true" <?php if($is_public) {?> checked <?php } ?>/>
										<label for="is_public" class="form-label">Public</label>
										</br>
										
										<label for="accgrps" class="form-label">Access Groups</label>
										<select class="form-control" name="accgrps[]" id="accgrps" aria-label="Select access groups" multiple required>
												<?php foreach($acc_grps as $k => $v){?>
													<option <?php if(in_array($k, $map_acc_ids)){?> selected <?php }?> value="<?=$k?>"><?=$v?></option>
											<?php	} ?>
										</select>
									</div>
								</fieldset>
									
									<button type="submit" class="btn btn-primary" id="btn_submit"><?php if(isset($_GET['id'])){ ?>Update<?php } else { ?>Create<?php } ?></button>
						</form>
						
						<pre id='r_output'></pre>
						
					</div>
          </div>
            
						<footer class="footer text-center"  style="background-color:gainsboro">
            </footer>
        </div>
    
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
		<script type="module" src="dist/js/ckeditor.js"></script>

		<script>
			var editor_css = CodeMirror.fromTextArea(document.getElementById("thismap_css"), {
				extraKeys: {"Ctrl-Space": "autocomplete"}
			});
			
			<?php for($i=0; $i < count($r_files); $i++){ ?>
				var editor<?=$i?> = CodeMirror.fromTextArea(document.getElementById("map_source_r<?=$i?>"), {
					extraKeys: {"Ctrl-Space": "autocomplete"}
				});
			<?php } ?>
			
			<?php for($j=0, $i=count($r_files); $j < count($rmd_files); $j++, $i++){ ?>
				var editor<?=$i?> = CodeMirror.fromTextArea(document.getElementById("map_source_rmd<?=$j?>"), {
					extraKeys: {"Ctrl-Space": "autocomplete"}
				});
			<?php } ?>
		</script>
</body>

</html>
