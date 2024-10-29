<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('class/schedule.php');
		require('class/datasource.php');
		require('class/scheduleR.php');
		require('class/map.php');
		require('class/cron.php');
    require('incl/jru-lib.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

		$schedules = array();
		$datasources = array();
		$eml_tmpls = array();
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
		
		if(empty($_GET['tab']) || ($_GET['tab'] == 'jri')){
			$_GET['tab'] = 'jri';
			$sch_obj = new schedule_Class($dbconn);
			$schedules = $sch_obj->getArr();
				
			$ds_obj = new datasource_Class($dbconn);
			$datasources = $ds_obj->getArr();
			
			$action = 'schedule';

		}else if($_GET['tab'] == 'R'){
			$sch_obj = new scheduleR_Class($dbconn);
			$schedules = $sch_obj->getArr();
			
			$maps_obj = new map_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$maps = $maps_obj->getArr();
			
			$action = 'scheduleR';
		}
			
		$eml_tmpls = get_email_templates();
		array_unshift($eml_tmpls, '');
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
						$('[data-toggle="tooltip"]').tooltip();

						// Delete row on delete button click
						$(document).on("click", ".delete", function() {
						    var obj = $(this);
						    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')};

						    $.ajax({
                    type: "POST",
                    url: 'action/<?=$action?>.php',
                    data: data,
                    dataType:"json",
                    success: function(response){
                        if(response.success) { // means, new record is added
                            obj.parents("tr").remove();
                        }

                        alert(response.message);
                    }
                });
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
                        <h1 class="mb-0 fw-bold">Schedules</h1>
                    </div>
										<div class="col-6">
                        <div class="text-end upgrade-btn">
													<a href="edit_<?=$action?>.php" class="btn btn-primary text-white add-new" role="button" aria-pressed="true"><i class="fa fa-plus"></i> Add Schedule</a>
                        </div>
                    </div>
                </div>
            </div>

      <div class="container-fluid">
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link <?php if($_GET['tab'] == 'jri') { ?> active <?php } ?>" href="schedules.php?tab=jri">JRI</a> </li>
					<li class="nav-item"><a class="nav-link <?php if($_GET['tab'] == 'R') { ?> active <?php } ?>" href="schedules.php?tab=R">R</a> </li>
				</ul>
				
				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="cron_period" data-type='select'>Cron</th>

							
						<?php if($_GET['tab'] == 'jri') { ?>
							<!-- <th data-name="id" data-editable='false'>#</th> -->
							<th data-name="name"		data-type='select'>Name</th>
							<th data-name="datasource" data-type='select'>Datasource</th>
							<th data-name="filename">Output</th>
						<?php } else { ?>
							<th data-name="rmap_id" data-type='select'>R map</th>
							<th data-name="rmd_source" data-type='select'>Rmd Source</th>
						<?php } ?>
						<th data-name="format"	data-type='select'>Format</th>

						<th data-name="email">Email</th>
						<th data-name="email_subj">Email Subj.</th>
						<th data-name="email_body" data-type='textarea'>Email Body</th>
						<th data-name="email_tmpl" data-type='select'>Email Templ.</th>
						<?php if($_GET['tab'] == 'jri') { ?>
						<th data-name="url_opt_params">Optional</th>
						<?php } ?>
						<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php
							foreach ($schedules as $schid => $sched) { ?> <tr data-id="<?=$schid?>" align="left">
						<!-- <td><?=$schid?></td> -->
						
						<?php if($_GET['tab'] == 'R') {
							$cron = CRON::get($sched['rmap_id']);
							$sched['cron_period'] =  $cron['cron_period'];
						} ?>	
						
						<td data-type="select" data-value="<?=$sched['cron_period']?>"><?=$sched['cron_period']?></td>
						<?php if($_GET['tab'] == 'jri') { ?>
							<td data-type="select" data-value="<?=$sched['name']?>"><?=$sched['name']?></td>
						<td data-type="select" data-value="<?=$sched['datasource_id']?>"><?=$datasources[$sched['datasource_id']]['name']?></td>
						<td><?=$sched['filename']?></td>
						<?php } else { ?>
						<td data-type="select" data-value="<?=$sched['rmap_id']?>"><?=$maps[$sched['rmap_id']]['name']?></td>
						<td data-type="select" data-value="<?=$sched['rmd_source']?>"><?=$sched['rmd_source']?></td>
						<?php } ?>
						<td data-type="select" data-value="<?=$sched['format']?>"><?=$sched['format']?></td>
						<td><?=$sched['email']?></td>
						<td><?=$sched['email_subj']?></td>
						<td data-type="textarea" data-value="<?=$sched['email_body']?>"><?=$sched['email_body']?></td>
						<td data-type="select" 	 data-value="<?=$sched['email_tmpl']?>"><?=$sched['email_tmpl']?></td>
						<?php if($_GET['tab'] == 'jri') { ?>
						<td><?=htmlspecialchars($sched['url_opt_params'])?></td>
						<?php } ?>
						<td>
							<a href="edit_<?=$action?>.php?id=<?=$schid?>" class="edit" 	title="Edit"	 data-toggle="tooltip"><i class="material-icons">&#xE254;</i></a>
							<a class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
						</td>
					</tr>
				<?php } ?>
					</tbody>
				</table>
            </div>
        </div>
    </div>
    	
		<script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
