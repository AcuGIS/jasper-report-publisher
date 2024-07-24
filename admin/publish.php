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
	<link href="dist/css/table.css" rel="stylesheet">
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
					
				<form class="border shadow p-3 rounded"
					action="action/publish.php"
					method="post"
					enctype="multipart/form-data"
					style="width: 450px;">

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
						<?php
							foreach($updirs as $d){?>
								<option <?php if($dsel == $d){?> selected <?php }?> value="<?=$d?>"><?=$d?></option>
						<?php
							}
						?>
					</select>
			</div>

			<div class="form-group">
				<label for="source" class="form-label">Source file</label>
				<input type="file" class="form-control" name="source" id="source"
					value="<?php echo (isset($_SESSION['pub_source']) ? $_SESSION['pub_source'] : ''); ?>" />
			</div>

			<div class="form-group">
				<input type="checkbox" name="publish_extract" id="publish_extract"
					value="<?php echo (isset($_SESSION['pub_extract']) ? 'checked' : ''); ?>" />
				<label for="publish_extract" class="form-label">Extarct if upload is archive (.zip)</label>
			</div>

			<div class="form-group">
				<input type="checkbox" name="publish_overwrite" id="publish_overwrite"
					value="<?php echo (isset($_SESSION['pub_overwrite']) ? 'checked' : ''); ?>" />
				<label for="publish_overwrite" class="form-label">Overwrite existing files</label>
			</div>

			<button type="submit" class="btn btn-primary">Publish</button>
		</form>
				
			
				<div class="row">
          <div class="col-6">
						<p>&nbsp;</p>
						<div id="repThumbnail" class = "alert alert-success">
						   <a href="#" class="close" data-dismiss = "alert">&times;</a>
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
