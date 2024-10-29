<script type="text/javascript">
	$(document).ready(function() {
		
		$('#import_form').submit(false);
		$("#import_output").hide();

		$('[data-toggle="tooltip"]').tooltip();
		
		$(document).on("change", '#create_only', function() {

			if($(this).is(':checked')){
				$('#source\\[\\]').attr('disabled', 'disabled');
				$('#btn_submit').html('Create');
			}else{
				$('#source\\[\\]').removeAttr('disabled');
				$('#btn_submit').html('Import');
			}
		});
		
		$(document).on("click", "#btn_submit", function() {
			 	var obj = $(this);
			 	var input = $('#import_form').find('input[type="text"], input[type="file"], select');
				var empty = false;
				
				obj.toggle();
				
				input.each(function() {
					if (!$(this).prop('disabled') && $(this).prop('required') && !$(this).val()) {
						$(this).addClass("error");
						empty = true;
					} else {
						$(this).removeClass("error");
					}
				});

				if(empty){
					alert('focus');
					$('#import_form').find(".error").first().focus();
				}else{
						$.ajax({
							type: "POST",
							url: 'action/import.php',
							data: new FormData($('#import_form')[0]),
							processData: false,
							contentType: false,
							dataType: "html",
							success: function(response){
								obj.toggle();
								$("#import_output").show();
								$("#import_output").html(response);
							}
						});
				}
		});
	});
</script>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'datasources.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper" style="background: #fff;">
            
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                      <nav aria-label="breadcrumb"></nav>
                      <h1 class="mb-0 fw-bold">Create PostGIS connection</h1>
                    </div>
                </div>
            </div>
            
      <div class="container-fluid">
				
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link <?php if($tab == 'ds') { ?> active <?php } ?>" href="datasources.php?tab=ds">Datasource</a> </li>
					<li class="nav-item"><a class="nav-link <?php if($tab == 'pg') { ?> active <?php } ?>" href="datasources.php?tab=pg">PostGIS</a> </li>
					<li class="nav-item"><a class="nav-link <?php if($tab == 'gs') { ?> active <?php } ?>" href="datasources.php?tab=gs">GeoServer</a></li>
					<li class="nav-item"><a class="nav-link <?php if($tab == 'import') { ?> active <?php } ?>" href="datasources.php?tab=import">Create</a></li>
				</ul>

				<form id="import_form" class="border shadow p-3 rounded"
						action=""
						method="post"
						enctype="multipart/form-data"
						style="width: 450px;">

						<?php if (isset($_GET['error'])) { ?>
							<div class="alert alert-danger" role="alert"><?=$_GET['error']?></div>
						<?php } else if(isset($_GET['success'])) { ?>
							<div class="alert alert-success" role="alert"><?=$_GET['success']?></div>
						<?php } ?>

				<div class="form-group">
					<label for="dbname" class="form-label">Database Name</label>
					<input type="text" class="form-control" name="dbname" id="dbname" value="" required />
				</div>
				<div class="form-group">
					<label for="source[]" class="form-label">Source file</label>
					<input type="file" class="form-control" name="source[]" id="source[]" value="" accept=".gpkg,.shp,.sql,.zip,.dump" multiple required />
				</div>
				
				<div class="form-group">
					<input type="checkbox" name="create_link" id="create_link" value="1" checked />
					<label for="create_link" class="form-label">Create new link to database</label>
				</div>
				
				<div class="form-group">
					<input type="checkbox" name="create_only" id="create_only" value="1" />
					<label for="create_only" class="form-label">Only create PostGIS database</label>
				</div>

				<button type="submit" id="btn_submit" class="btn btn-primary">Import</button>
			</form>
		
		<div class="row">

							<p>&nbsp;</p>
			<div class="col-6" style="width: 50%!important">
				<p>&nbsp;</p>
				<div id = "repThumbnail" class = "alert alert-success">
					 <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
					 <strong>Note:</strong> You can upload .gpkg, .shp or .sql/.dump files. Zip archives are supported.
				</div>

				<div class="col-8" style="width: 90%!important">
					<p>&nbsp;</p>
					<div class="col-6"></div>
					<script type = "text/javascript">
						 $(function(){
								$(".close").click(function(){
									 $("#repThumbnail").alert();
								});
						 });
					</script>
				</div>

			</div>

			<pre id='import_output'></pre>
	</div>
</div>
