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
	<link href="dist/css/admin.css" rel="stylesheet">
	<link href="dist/css/table.css" rel="stylesheet">
	<link href="dist/css/slider.css" rel="stylesheet">
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip();
			
			// Call systemd to enable/disable service on switch change
			$('input:checkbox').change(function() {
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
			$(document).on("click", ".action-icon", function() {
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
						} else {
							alert('Action ' + op + ' failed! ');
						}
					}
				});
			});
		});
	</script>

<style>
.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    border: none!important;
    border-radius: inherit;
    text-decoration: none!important;
}

.bg-warning {
    background-color: #50667f!important;
}

td {
    max-width: 100px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    margin-top: 24px;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: var(--space-lg);
}

.custom-table th {
    background: var(--background-gray);
    font-weight: 600;
    text-align: left;
    padding: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.custom-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.custom-table tbody tr:hover {
    background-color: var(--background-gray);
}

.action-icon {
    color: var(--text-medium);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.action-icon:hover {
    color: var(--primary-blue);
    background: var(--background-gray);
}

.action-icon.start {
    color: #10b981;
}

.action-icon.stop {
    color: #ef4444;
}

.action-icon.restart {
    color: #f59e0b;
}

/* Switch styling */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary-blue);
}

input:focus + .slider {
    box-shadow: 0 0 1px var(--primary-blue);
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'services.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper">
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
                <div class="table-container">
                    <table class="table table-borderless custom-table">
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
                                <?php $status = $bknd->tomcat_status(); ?>
                                
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" <?php if(strstr($status['enabled'], 'enabled') !== false){ ?> checked <?php } ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                
                                <td><?=$status['active']?></td>
                                <?php if(strstr($status['active'], 'running')){ ?>
                                    <td><?=$status['memory']?></td>
                                    <td><?=$status['cpu']?></td>
                                    <td>
                                        <a class="action-icon stop" name='stop' title="Stop" data-toggle="tooltip">
                                            <i class="material-icons">&#xE047;</i>
                                        </a>
                                        <a class="action-icon restart" name='restart' title="Restart" data-toggle="tooltip">
                                            <i class="material-icons">&#xf053;</i>
                                        </a>
                                    </td>
                                <?php } else { ?>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <a class="action-icon start" name='start' title="Start" data-toggle="tooltip">
                                            <i class="material-icons">&#xe037;</i>
                                        </a>
                                    </td>
                                <?php } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-6">
                    <p>&nbsp;</p>
                    <div id="repThumbnail" class="alert alert-success">
                        <a href="#" class="close" data-dismiss="alert">&times;</a>
                        <strong>Note:</strong> Restart service after making changes to configuration
                    </div>
                    <script type="text/javascript">
                        $(function(){
                            $(".close").click(function(){
                                $("#repThumbnail").alert();
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>
</html>
