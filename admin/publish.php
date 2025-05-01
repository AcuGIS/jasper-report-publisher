<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('incl/jru-lib.php');
    require('class/database.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

		$updirs = get_upload_dirs();

		$dsel = (isset($_SESSION['pub_destination'])) ? $_SESSION['pub_destination'] : $updirs[0];
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/admin.css" rel="stylesheet">
	<link href="dist/css/table.css" rel="stylesheet">
	<style>
		.card {
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			margin-bottom: 20px;
		}
		.form-container {
			background: #fff;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			max-width: 450px;
			margin: 0 auto;
		}
		.form-group {
			margin-bottom: 20px;
		}
		.form-label {
			display: block;
			margin-bottom: 8px;
			font-weight: 500;
			color: #495057;
		}
		.form-control {
			width: 100%;
			padding: 8px 12px;
			border: 1px solid #ced4da;
			border-radius: 4px;
			transition: border-color 0.2s, box-shadow 0.2s;
		}
		.form-control:focus {
			border-color: #86b7fe;
			box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
			outline: none;
		}
		.btn-primary {
			background: #0d6efd;
			border: none;
			padding: 10px 20px;
			border-radius: 4px;
			color: #fff;
			font-weight: 500;
			transition: background 0.2s;
			width: 100%;
		}
		.btn-primary:hover {
			background: #0b5ed7;
		}
		.alert {
			padding: 12px 16px;
			border-radius: 4px;
			margin-bottom: 20px;
		}
		.alert-success {
			background-color: #d1e7dd;
			border-color: #badbcc;
			color: #0f5132;
		}
		.alert-danger {
			background-color: #f8d7da;
			border-color: #f5c2c7;
			color: #842029;
		}
		.checkbox-group {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.checkbox-group input[type="checkbox"] {
			width: 16px;
			height: 16px;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip();			
		});
	</script>
</head>

<body>
  
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'publish.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
      
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Publish</h1>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="form-container">
									<form action="action/publish.php" method="post" enctype="multipart/form-data">
										<?php if (isset($_GET['error'])) { ?>
											<div class="alert alert-danger" role="alert">
												<?=$_GET['error']?>
											</div>
										<?php } else if(isset($_GET['success'])) { ?>
											<div class="alert alert-success" role="alert">
												<?=$_GET['success']?>
											</div>
										<?php } ?>

										<div class="form-group">
											<label for="destination" class="form-label">Destination directory</label>
											<select class="form-control" name="destination" aria-label="Default select example">
												<?php foreach($updirs as $d): ?>
													<option <?php if($dsel == $d) echo 'selected'; ?> value="<?=$d?>"><?=$d?></option>
												<?php endforeach; ?>
											</select>
										</div>

										<div class="form-group">
											<label for="source" class="form-label">Source file</label>
											<input type="file" class="form-control" name="source" id="source"
												value="<?php echo (isset($_SESSION['pub_source']) ? $_SESSION['pub_source'] : ''); ?>" />
										</div>

										<div class="form-group">
											<div class="checkbox-group">
												<input type="checkbox" name="publish_extract" id="publish_extract"
													<?php echo (isset($_SESSION['pub_extract']) ? 'checked' : ''); ?> />
												<label for="publish_extract" class="form-label">Extract if upload is archive (.zip)</label>
											</div>
										</div>

										<div class="form-group">
											<div class="checkbox-group">
												<input type="checkbox" name="publish_overwrite" id="publish_overwrite"
													<?php echo (isset($_SESSION['pub_overwrite']) ? 'checked' : ''); ?> />
												<label for="publish_overwrite" class="form-label">Overwrite existing files</label>
											</div>
										</div>

										<button type="submit" class="btn btn-primary">Publish</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="alert alert-success" id="repThumbnail">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<strong>Note:</strong> Uploads are copied to report home directory.
						</div>
					</div>
				</div>
										
            </div>
        </div>
    </div>
    
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
