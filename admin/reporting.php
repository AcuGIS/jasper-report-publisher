<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('class/schedule.php');
    require('incl/jru-lib.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$sch_obj = new schedule_Class($database->getConn());
		$schedules = $sch_obj->getArr();
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
					
					$('#run_output').hide();
					
					// Run report on run button click
					$(document).on("click", ".run", function() {
						var obj = $(this);
						var schid = obj.parents("tr").attr('data-id');

						$.get('action/reporting.php?out=txt&id=' + schid).done(function( data ) {
							$('#run_output').show();
						  $( "#run_output" ).html( data );
						});
					});			
			});
		</script>

</head>

<body>
  
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'reporting.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
      
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Reporting</h1>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">

				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="schedule" data-editable='false'>Schedule ID</th>
							<th data-name="name" data-editable='false'>Schedule Name</th>
							<th data-name="files" data-editable='false'>Files</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody>
						<?php
							foreach ($schedules as $schid => $sched) {?>
							<tr data-id="<?=$schid?>" align="left">
								<td><?=$schid?></td>
								<td><?=$sched['name']?></td>
								<?php
									$pos = strrpos($sched['name'], '/');
									$report_dname = substr($sched['name'], 0, $pos);

									$files = get_all_reports($report_dname, $sched['filename']);
									$files_len = count($files);
									if($files_len > 2){
										$files = array_slice($files, 0, 2);	# limit to 2 direct links
									}
									$file_links = array();
									foreach($files as $f){
										array_push($file_links, '<a href="action/reporting.php?file='.urlencode($report_dname."/".$f).'">'.$f.'</a>');
									}
									#sort($file_links, SORT_STRING);
									if($files_len > 2){
										array_push($file_links, '<a href="files.php?p='.urlencode('reports/'.$report_dname).'&filter='.urlencode($sched['filename']).'">more</a>');
									}
								?>
								<td><?php echo(implode('<br>', $file_links)); ?></td>
								<td><a class="run" title="Run" data-toggle="tooltip"><i class="material-icons">&#xE1C4;</i></a></td>
						</tr>
					<?php } ?>

					</tbody>
				</table>					
						<pre id='run_output'></pre>
            </div>
        </div>
    </div>
    
		<script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
