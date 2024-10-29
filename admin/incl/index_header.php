<header>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link  href="../../assets/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../../assets/dist/js/bootstrap.bundle.min.js"></script>

				
  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;RMaps</strong>
      </a>
			<?php 
			if(isset($_SESSION[SESS_USR_KEY])){
						if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') { ?>
			<a href="../../admin/index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>
			<?php if(!empty(QGIS_LAYOUT)) { ?>
			<a href="javascript:void(0);" target="_self" id="view_features"	data-id="<?=MAP_ID?>"	style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">View Metadata</a>
			<?php } ?>
      <a href="../../logout.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>
			<?php } else { ?>
			<?php if(!empty(QGIS_LAYOUT)) { ?>
			<a href="javascript:void(0);" target="_self" id="view_features"	data-id="<?=MAP_ID?>"	style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">View Metadata</a>
			<?php } ?>
			<a href="../../index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Back to Dashboard</a>
			<?php }
			} ?>
    </div>
  </div>
</header>

<link  href="../../assets/dist/css/loading_modal.css" rel="stylesheet">
<script src="../../assets/dist/js/loading_modal.js"></script>
<link rel="stylesheet" href="../../assets/dist/css/maps.css?<?=filemtime('../../assets/dist/css/maps.css')?>">
<link rel="stylesheet" href="thismap.css?<?=filemtime('thismap.css')?>">

<script>
<?php if(HAS_SENTINEL) { ?>
var leftSentinels = [];
var rightSentinels = [];
<?php } ?>
</script>

<?php if(SHOW_DATATABLES) { ?>
	<script src="../../assets/dist/js/focus_layer.js"></script>
	<script src="../../assets/dist/js/tbl2CSV.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<?php }

if(!empty(QGIS_LAYOUT)) { ?>
	<link rel="stylesheet" href="../../assets/dist/locationfilter/locationfilter.css">
	
	<script>
$(document).ready(function() {
	$(document).on("click", "#view_features", function() {
		var obj = $(this);
		var data = {'features': true, 'id': obj.attr('data-id'), 'from_map': true}
		$.ajax({
				type: "POST",
				url: '../../admin/action/features.php<?=$permalink?>',
				data: data,
				dataType:"json",
				success: function(response){
					if(response.success){
						$('#qgis-modal-body').html(response.html);
						$('#qgis_modal').modal('show');
					}
				}
		});
	});
});
	</script>
	
	<div id="qgis_modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<p>QGIS Metadata</p>
				</div>
				
				<div class="modal-body" id="qgis-modal-body"><p>QGIS Metadata</p></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<div id="loading">
	<img id="loading-image" src="../../assets/images/ajax-loader.gif" alt="Loading..." />
</div>
