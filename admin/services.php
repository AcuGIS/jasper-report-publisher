<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/backend.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }

		$bknd = new backend_Class();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<link href="dist/css/slider.css" rel="stylesheet">
	<style type="text/css">
td {
    max-width: 100px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
	</style>

<style type="text/css">

</style>

	<script type="text/javascript">
	        
			$(document).ready(function() {
						
						$('[data-toggle="tooltip"]').tooltip();
							
							// Call systemd to enable/disable service on switch change
							$('input:checkbox').change( function() {
								var obj = $(this);
								var svc = obj.parents("tr").attr('data-id');	// data-id holds service name
								var op = obj.is(':checked') ? 'enable' : 'disable';
								var data = {'op': op, 'svc': svc}

								$.ajax({
												type: "POST",
												url: 'action/backend.php',
												data: data,
												dataType:"json",
												success: function(response){
														if(!response.success) {
															obj.prop("checked", false);
															alert(response.message);
														}
												}
										});
							});

							// Call systemd through backend, on action click
								$(document).on("click", ".action", function() {
							    var obj = $(this);
									var svc = obj.parents("tr").attr('data-id');	// data-id holds service name
									var op = obj.prop('name');
									var data = {'op': op, 'svc': svc}
									
									$.ajax({
													type: "POST",
													url: 'action/backend.php',
													data: data,
													dataType:"json",
													success: function(response){
															if(response.success) {
																location.reload(true);	//refresh page to show service details
															}else{
																alert('Action ' + op + ' failed! ');
															}
													}
											});

							});
						});
		</script>

</head>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'services.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper" style="background: #fff;color: #22303c;">
            
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                        </nav>
                        <h1 class="mb-0 fw-bold">Service & Apps</h1>
                    </div>
                </div>
            </div>
            
    <div class="container-fluid">
				<div class="table-responsive">
				<table class="table table-borderless">

					<thead>
						<tr>
							<th data-name="service">Service</th>
							<th data-name="enabled">Enabled</th>
							<th data-name="active">Active</th>
							<th data-name="memory">Memory</th>
							<th data-name="cpu">CPU</th>

							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody>
						<tr align="left" data-id='tomcat'>
							<td>Tomcat</td>
							<?php	$status = $bknd->tomcat_status(); ?>
							
							<td><label class="switch">
									  <input type="checkbox" <?php if(strstr($status['enabled'], 'enabled') !== false){ ?> checked <?php } ?>>
									  <span class="slider"></span>
									</label>
							</td>
							
							<td><?=$status['active']?></td>
						<?php if(strstr($status['active'], 'running')){ ?>
									<td><?=$status['memory']?></td>
									<td><?=$status['cpu']?></td>
									<td>
									<a class="action" name='stop' title="Stop" data-toggle="tooltip">
										<i class="material-icons">&#xE047;</i>
									</a>
									<a class="action" name='restart' title="Restart" data-toggle="tooltip" >
										<i class="material-icons">&#xf053;</i>
									</a>
								<?php }else { ?>
									<td></td>
									<td></td>
									<td>
									<a class="action" name='start' title="Start" data-toggle="tooltip" >
										<i class="material-icons">&#xe037;</i>
									</a>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>

		<div class="col-6">
			<p>&nbsp;</p>
			<div id = "repThumbnail" class = "alert alert-success">
					<a href = "#" class = "close" data-dismiss = "alert">&times;</a>
					<strong>Note:</strong> Restart service after making changes to configuration </div>
				<script type = "text/javascript">
				   $(function(){
				      $(".close").click(function(){
				         $("#repThumbnail").alert();
				      });
				   });
				</script>
		</div>
  </div>
</div>
    <footer class="footer text-center"></footer>
</div>

    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>
</html>
