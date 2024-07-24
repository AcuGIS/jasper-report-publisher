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

		$schedules = array();
		$opt_rep_id = array();
		$datasources = array();
		$eml_tmpls = array();
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);

		$sch_obj = new schedule_Class($database->getConn());
		$schedules = $sch_obj->getArr();
			
		$ds_obj = new datasource_Class($database->getConn());
		$datasources = $ds_obj->getArr();
			
		$opt_rep_id = get_all_rep_ids();
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
						    var data = {'delete': true, 'schid': obj.parents("tr").attr('data-id')};

						    $.ajax({
                    type: "POST",
                    url: 'action/schedule.php',
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
													<a href="edit_schedule.php" class="btn btn-primary text-white add-new" role="button" aria-pressed="true"><i class="fa fa-plus"></i> Add Schedule</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">

				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<!-- <th data-name="id" data-editable='false'>#</th> -->
							<th data-name="cron_period" data-type='select'>Cron</th>
							<th data-name="name"		data-type='select'>Name</th>
							<th data-name="format"	data-type='select'>Format</th>
							<th data-name="datasource" data-type='select'>Datasource</th>
							<th data-name="filename">Output</th>
							<th data-name="email">Email</th>
							<th data-name="email_subj">Email Subj.</th>
							<th data-name="email_body" data-type='textarea'>Email Body</th>
							<th data-name="email_tmpl" data-type='select'>Email Templ.</th>
							<th data-name="url_opt_params">Optional</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php foreach ($schedules as $schid => $sched) {?> <tr data-id="<?=$schid?>" align="left">
						<!-- <td><?=$schid?></td> -->
						<td data-type="select" data-value="<?=$sched['cron_period']?>"><?=$sched['cron_period']?></td>
						<td data-type="select" data-value="<?=$sched['name']?>"><?=$sched['name']?></td>
						<td data-type="select" data-value="<?=$sched['format']?>"><?=$sched['format']?></td>
						<td data-type="select" data-value="<?=$sched['datasource_id']?>"><?=$datasources[$sched['datasource_id']]['name']?></td>
						<td><?=$sched['filename']?></td>
						<td><?=$sched['email']?></td>
						<td><?=$sched['email_subj']?></td>
						<td data-type="textarea" data-value="<?=$sched['email_body']?>"><?=$sched['email_body']?></td>
						<td data-type="select" 	 data-value="<?=$sched['email_tmpl']?>"><?=$sched['email_tmpl']?></td>
						<td><?=htmlspecialchars($sched['url_opt_params'])?></td>
						<td>
							<a href="edit_schedule.php?schid=<?=$schid?>" class="edit" 	title="Edit"	 data-toggle="tooltip"><i class="material-icons">&#xE254;</i></a>
							<a class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
						</td>
					</tr>
				<?php }?>
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
