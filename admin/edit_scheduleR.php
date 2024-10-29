<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/database.php');
		require('class/scheduleR.php');
		require('class/map.php');
		require('class/app.php');
		require('class/cron.php');
		require('incl/jru-lib.php');

		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$sch_obj = new scheduleR_Class($database->getConn());
		
		$maps_obj = new map_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
		$map_rows = $maps_obj->getRows();
		$rmd_maps = array();
		
		# get all RMarkdown maps
		while($row = pg_fetch_assoc($map_rows)){
			$row['rmd_files'] = App::getFilesByType(APPS_DIR.'/'.$row['id'], 'Rmd');
			if(count($row['rmd_files']) > 0){
				$rmd_maps[] = $row;
			}
		}
		
		$r_files = array();
		$html_rmarkdown = array();
		$output_formats = array();
			
		$eml_tmpls = array();
		$sched = array_fill_keys(SCH_R_KEYS, '');
			
		$eml_tmpls = get_email_templates();
		array_unshift($eml_tmpls, '');
				
		if(empty($_GET['id'])){
			if(count($rmd_maps)){
				$sched['rmap_id'] = $rmd_maps[0]['id'];
			}
			$cron = CRON::get($sched['rmap_id']);
		}else{
			$sched = $sch_obj->getById($_GET['id']);
			if($sched){
				$cron = CRON::get($sched['rmap_id']);
			}else{
				$_GET['error'] = 'Error: No such schedule!';
			}
		}
		
		$data_dir = DATA_DIR.'/'.$sched['rmap_id'];
		$rmd_files = App::getFilesByType(APPS_DIR.'/'.$sched['rmap_id'], 'Rmd');
		
		if(empty($_GET['id'])){
			if(count($rmd_files)){
				$sched['rmd_source'] = $rmd_files[0];
			}
		}
		$output_formats = AppR::rmd_get_formats($data_dir.'/index.R', $sched['rmd_source']);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
		<script type="text/javascript">
			var schid = <?= (isset($_GET['id'])) ? $_GET['id'] : 0?>;
function clear_disable_obj(name){
	var Obj = document.getElementsByName(name)[0];
	Obj.disabled = !Obj.disabled;
}

function update_nomail(){
  var mailObjs = ['email', 'email_subj', 'email_body', 'email_tmpl'];
  mailObjs.forEach(clear_disable_obj);
}

function reload_select(name, arr){
	var obj = $('#' + name);
	obj.empty();
	
	let sel = true;
	$.each(arr, function(x){
		obj.append($('<option>',{text: arr[x], value: arr[x], selected: sel}));
		sel = false;
	});
	obj.change();
}

			$(document).ready(function() {
				
				$(document).on("change", '#rmap_id', function() {
					let obj = $(this);
					let data = {
						'rmap_id' 			: obj.find('option:selected').val(),
						'rmd_sources' : true
					};
					
					$('#rmap_id').attr('disabled', 'disabled');					
					$('#rmd_source').attr('disabled', 'disabled');
					$('#format').attr('disabled', 'disabled');

					$.ajax({
						type: "POST",
						url: 'action/scheduleR.php',
						data: data,
						dataType:"json",
						success: function(response){
							 if(response.success) {
								 $('#rmap_id').removeAttr('disabled');
								 reload_select('rmd_source', response.rmd_sources);
								 $('#cron_period').val(response.cron_period).change();
								 $('#cron_custom').val(response.cron_custom);
							 }else{
								 alert('Error: Failed to list project. ' + response.message);
							 }
						},
						fail: function(){	alert('Error: POST failure');	}
					});
				});
				
				$(document).on("change", '#rmd_source', function() {
					let obj = $(this);
					let data = {
						'rmap_id' 		 	 : $('#rmap_id').find('option:selected').val(),
						'rmd_source' 		 : obj.find('option:selected').val(),
						'output_formats' : true
					};
										
					$('#rmd_source').attr('disabled', 'disabled');
					$('#format').attr('disabled', 'disabled');

					$.ajax({
						type: "POST",
						url: 'action/scheduleR.php',
						data: data,
						dataType:"json",
						success: function(response){
							 if(response.success) {
								 $('#rmd_source').removeAttr('disabled');
								 reload_select('format', response.output_formats);
								 $('#format').removeAttr('disabled');								 
							 }else{
								 alert('Error: Failed to list project. ' + response.message);
							 }
						},
						fail: function(){	alert('Error: POST failure');	}
					});
				});
				
				$(document).on("change", "#cron_period", function() {
					var cron_period = $(this).find('option:selected').text();
					
					if(cron_period == 'custom'){
						$('#cron_custom').show();
					}else{
						$('#cron_custom').hide();
						$('#sched_form').attr('action', '');
						$('#sched_form').submit(false);
						if(schid > 0){
							$('#btn_submit').html('Update');
						}else{
							$('#btn_submit').html('Create');
						}
					}
				});
				
				// Update/Create schedule on submit button click
				$(document).on("click", "#btn_submit", function() {
				    var obj = $(this);
				    var data = $('#sched_form').serialize() + '&save=true';
						
						var input = $('#sched_form').find('input[type="text"], select');
						var empty = false;
						input.each(function() {
							if (!$(this).prop('disabled') && $(this).prop('required') && !$(this).val()) {
								$(this).addClass("error");
								empty = true;
							} else {
								$(this).removeClass("error");
							}
						});

						if(empty){
							$('#sched_form').find(".error").first().focus();
						}else{

							$.ajax({
                type: "POST",
                url: 'action/scheduleR.php',
                data: data,
                dataType:"json",
                success: function(response){
									alert(response.message);
                   if(response.success) {
										window.location.href = 'schedules.php?tab=R';
									 }
								 }
            	});
						}
				});
				
				$('[data-toggle="tooltip"]').tooltip();
				$('#sched_form').submit(false);
				$('#cron_period').change();
			});
		</script>
</head>
<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'schedules.php';
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
													<h1 class="mb-0 fw-bold">Update schedule <?=$_GET['id']?></h1>
												<?php } else { ?>
                        	<h1 class="mb-0 fw-bold">Add new schedule</h1>
												<?php } ?>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
							
							<form action="" method="post" id="sched_form" style="width: 50%;">
											<?php if (isset($_GET['error'])) { ?>
											<div class="alert alert-danger" role="alert">
												<?=$_GET['error']?>
										  </div>
										  <?php } ?>

									<?php if(isset($_GET['id'])){ ?>
										<input type="hidden" class="form-control" name="id" id="id" value="<?=$_GET['id']?>" />
									<?php } ?>

									<div class="form-group">
										<label for="rmap_id" class="form-label">R map</label>
										<select class="form-control" name="rmap_id" id="rmap_id" aria-label="Default select example">
											<?php foreach($rmd_maps as $map){ ?>
													<option <?php if($sched['rmap_id'] == $map['id']){?> selected <?php }?> value="<?=$map['id']?>"><?=$map['name']?></option>
											<?php	} ?>
								  	</select>
									</div>
									
									<div class="form-group">
										<label for="rmap_id" class="form-label">R source</label>
										<select class="form-control" name="rmd_source" id="rmd_source" aria-label="Default select example">
											<?php foreach($rmd_files as $rmdf){ ?>
													<option <?php if($sched['rmd_source'] == $rmdf){?> selected <?php }?> value="<?=$rmdf?>"><?=$rmdf?></option>
											<?php	} ?>
								  	</select>
									</div>

									<div class="form-group">
										<label for="filename" class="form-label">Format</label>
										<select class="form-control" name="format" id="format" aria-label="Default select example">
											<?php foreach($output_formats as $fmt => $f){
												$f = strtoupper($f); ?>
													<option <?php if($sched['format'] == $fmt){?> selected <?php }?> value="<?=$fmt?>"><?=$fmt?></option>
											<?php	} ?>
								  	</select>
									</div>
									
									<div class="form-group">
						      	<label for="cron_period" class="form-label">CRON:</label>
										<select class="form-control" name="cron_period" id="cron_period" aria-label="Select cron period" disabled>
												<?php foreach(CRON_PERIOD as $period){ ?>
													<option <?php if($cron['cron_period'] == $period){?> selected <?php }?> value="<?=$period?>"><?=$period?></option>
											<?php	} ?>
								  	</select>
										<input type="text" class="form-control" name="cron_custom" id="cron_custom" <?php if($cron['cron_period'] != 'custom'){?> style="display: none;" <?php }?>
											value="<?php echo (!empty($cron['cron_custom']) ? $cron['cron_custom'] : '*/30 * * * *'); ?>" disabled/>
									</div>
									
									<div class="form-group">
										<label for="email" class="form-label">Email</label>
										<input type="text" class="form-control" name="email" id="email"
											value="<?=$sched['email']?>" required />
									</div>
									
									<div class="form-group">
										<input type="checkbox" name="mail_on_change_only" id="mail_on_change_only" value="f" <?php if($sched['mail_on_change_only'] == 't'){ ?> checked <?php } ?> />
										<label for="mail_on_change_only" class="form-label">Send email, only if output file is changed</label>
									</div>
									
									<fieldset><legend>Optional Email fields:</legend>
										<div class="form-group">
											<label for="email_subj" class="form-label">Email Subject</label>
											<input type="text" class="form-control" name="email_subj" id="email_subj"
												value="<?=$sched['email_subj']?>" required />
										</div>

										<div class="form-group">
											<label for="email_body" class="form-label">Email Body</label>
											<textarea class="form-control" name="email_body" id="email_body" required><?=$sched['email_body']?></textarea>
										</div>

										<div class="form-group">
											<label for="email_tmpl" class="form-label">Email Template</label>
											<select class="form-control" name="email_tmpl" aria-label="Default select example">
												<?php
													foreach($eml_tmpls as $t){?>
														<option <?php if($sched['email_tmpl'] == $t){?> selected <?php }?> value="<?=$t?>"><?=$t?></option>
												<?php	}
												?>
									  	</select>
										</div>
									</fieldset>
									
									<button type="submit" class="btn btn-primary" id="btn_submit"><?php if(isset($_GET['id'])){ ?>Update<?php } else { ?>Create<?php } ?></button>
				      </div>
							</form>
            </div>
            
						<footer class="footer text-center" style="background-color:gainsboro">
            </footer>
        </div>
    </div>
    
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
