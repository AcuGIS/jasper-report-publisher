<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/database.php');
		require('class/schedule.php');
		require('class/datasource.php');
		require('incl/jru-lib.php');

		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$ds_obj = new datasource_Class($database->getConn());
		$sch_obj = new schedule_Class($database->getConn());

		$opt_rep_id = array();
		$datasources = array();
		$eml_tmpls = array();
		$sched = array_fill_keys(SCH_KEYS, '');
		$sched['datasource_id'] = 0;
		
		$datasources = $ds_obj->getArr();
			
		$opt_rep_id = get_all_rep_ids();
			
		$eml_tmpls = get_email_templates();
		array_unshift($eml_tmpls, '');
				
		if(!empty($_GET['id'])){
			$sched = $sch_obj->getById($_GET['id']);
				
			if($sched){
				if(str_starts_with($sched['cron_period'], '@')){
					$sched['cron_period'] = substr($sched['cron_period'], 1);
				}else if($sched['cron_period'] == 'custom'){
					$sched['cron_custom'] = get_jri_cron_custom($_GET['id']);
				}
			}else{
				$_GET['error'] = 'Error: No such schedule!';
			}
		}
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

			$(document).ready(function() {
			
				$('[data-toggle="tooltip"]').tooltip();
				$('#run').hide();
				$('#sched_form').submit(false);
				
				if($('#publish_extract').attr('checked')){
					update_nomail();
				}
				
				$(document).on("change", "#cron_period", function() {
					var cron_period = $(this).find('option:selected').text();
					
					if(cron_period == 'custom'){
						$('#cron_custom').show();
					}else if(cron_period == 'now'){
						$('#cron_custom').hide();
						$('#btn_submit').html('Run');
						//$('#sched_form').attr('action', 'action/reporting.php');
						$('#sched_form').submit(true);
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
				    var data = $('#sched_form').serialize() + '&' + obj.html().toLowerCase() + '=true';
						
						var input = $('#sched_form').find('input[type="text"], select, textarea');
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
							
							if($('#btn_submit').html() == 'Run'){
								data['out'] = 'txt';
								$.post('action/reporting.php', data,
	                 function(data, status){
										alert(data);
	                }
								);
								
							}else{
								$.ajax({
	                type: "POST",
	                url: 'action/schedule.php',
	                data: data,
	                dataType:"json",
	                success: function(response){
										alert(response.message);
	                   if(response.success) { //		
											 // redirect to schedules.php
											window.location.href = 'schedules.php';
										 }
									 }
	            	});
							}
						}
				});
				
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
						      	<label for="cron_period" class="form-label">Execute:</label>
										<select class="form-control" name="cron_period" id="cron_period" aria-label="Select cron period">
												<?php
												foreach(CRON_PERIOD as $period){?>
													<option <?php if($sched['cron_period'] == $period){?> selected <?php }?> value="<?=$period?>"><?=$period?></option>
											<?php	}
											?>
											<option <?php if($sched['cron_period'] == 'now'){?> selected <?php }?> value="now">now</option>
								  	</select>
										<input type="text" class="form-control" name="cron_custom" id="cron_custom" <?php if($sched['cron_period'] != 'custom'){?> style="display: none;" <?php }?>
											value="<?php echo (!empty($sched['cron_custom']) ? $sched['cron_custom'] : '*/30 * * * *'); ?>" />
									</div>

									<div class="form-group">
										<label for="name" class="form-label">Name</label>
										<select class="form-control" name="name" aria-label="Default select example">
											<?php
												foreach($opt_rep_id as $rep_id){?>
													<option <?php if($sched['name'] == $rep_id){?> selected <?php }?> value="<?=$rep_id?>"><?=$rep_id?></option>
											<?php	}
											?>
								  	</select>
									</div>

									<div class="form-group">
										<label for="format" class="form-label">Format</label>
										<select class="form-control" name="format" aria-label="Default select example">
											<?php
												foreach(REP_FORMATS as $fmt){?>
													<option <?php if($sched['format'] == $fmt){?> selected <?php }?> value="<?=$fmt?>"><?=$fmt?></option>
											<?php	}
											?>
								  	</select>
									</div>

									<div class="form-group">
										<label for="datasource_id" class="form-label">Datasource</label>
										<select class="form-control" name="datasource_id" aria-label="Default select example">
											<?php
												foreach($datasources as $ds_id => $ds){?>
													<option <?php if($sched['datasource_id'] == $ds_id){?> selected <?php }?> value="<?=$ds_id?>"><?=$ds['name']?></option>
											<?php	}
											?>
								  	</select>
									</div>

									<div class="form-group">
										<label for="filename" class="form-label">Filename</label>
										<input type="text" class="form-control" name="filename" id="filename"
											value="<?=$sched['filename']?>" required/>
									</div>

									<div class="form-group">
										<label for="email" class="form-label">Email</label>
										<input type="text" class="form-control" name="email" id="email"
											value="<?=$sched['email']?>" required />
									</div>

									<div class="form-group">
										<input type="checkbox" name="noemail" id="publish_extract" onclick="update_nomail()"
											value="t" <?php if($sched['noemail'] == 't'){ ?> checked <?php } ?> />
										<label for="noemail" class="form-label">Don't send email</label>
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

										<div class="form-group">
											<label for="url_opt_params" class="form-label">Additional URL variables ( use comma to separate)</label>
											<input type="textarea" rows="5" cols="30" class="form-control" name="url_opt_params" id="url_opt_params"
												value="<?=$sched['url_opt_params']?>" />
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
