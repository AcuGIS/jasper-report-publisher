<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/map.php');
				
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
				header('Location: ../login.php');
				exit;
		}
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

		$maps_obj = new map_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
		$rows = $maps_obj->getRows();
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
							    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}
									if(confirm('Map will be deleted ?')){
							    	$.ajax({
                                    type: "POST",
                                    url: 'action/map.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
                                            obj.parents("tr").remove();
                                        }

                                        $(".add-new").removeAttr("disabled");
                                        alert(response.message);
                                    }
                                });
									}

							});
							
							$(document).on("click", ".clear", function() {
							    var obj = $(this);
							    var data = {'clear': true, 'id': obj.parents("tr").attr('data-id')}
									if(confirm('Map cache will be deleted ?')){
							    	$.ajax({
                        type: "POST",
                        url: 'action/map.php',
                        data: data,
                        dataType:"json",
                        success: function(response){
													if(response.success){
														obj.hide();
													}
													alert(response.message);
                        }
                    });
									}
							});
						
						$(document).on("click", ".features", function() {
							var obj = $(this);
							var data = {'features': true, 'id': obj.parents("tr").attr('data-id')}
							$.ajax({
									type: "POST",
									url: 'action/features.php',
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
    max-width: 175px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}



/* Table CSS */
.custom-table thead tr, .custom-table thead th {
    border-top: none;
    border-bottom: none !important;
}

.custom-table.table>thead {
	background-color: transparent !important;
    color: inherit;
    border-style: hidden !important;
}

.custom-table.table thead th {
	color: #000 !important;
	border-color: transparent !important;
}

.custom-table {
  border-collapse: separate !important;
  border-spacing: 0 1em !important;
  /*min-width: 900px;*/ }
  .custom-table thead tr, .custom-table thead th {
    border-top: none;
    border-bottom: none !important; }
  .custom-table tbody th, .custom-table tbody td {
    color: #777;
    font-weight: 400;
    padding-bottom: 20px !important;
    padding-top: 20px !important;
    font-weight: 300;}
    .custom-table tbody th small, .custom-table tbody td small {
      color: #b3b3b3;
      font-weight: 300; }
  .custom-table tbody tr:not(.spacer) {
    border-radius: 7px;
    overflow: hidden;
    -webkit-transition: .3s all ease;
    -o-transition: .3s all ease;
    transition: .3s all ease; }
    .custom-table tbody tr:not(.spacer):hover {
      -webkit-box-shadow: 0 2px 10px -5px rgba(0, 0, 0, 0.1);
      box-shadow: 0 2px 10px -5px rgba(0, 0, 0, 0.1); }
  .custom-table tbody tr th, .custom-table tbody tr td {
    background: #fff;
    border: none; }
    .custom-table tbody tr th:first-child, .custom-table tbody tr td:first-child {
      border-top-left-radius: 7px;
      border-bottom-left-radius: 7px; }
    .custom-table tbody tr th:last-child, .custom-table tbody tr td:last-child {
      border-top-right-radius: 7px;
      border-bottom-right-radius: 7px; }
  .custom-table tbody tr.spacer td {
    padding: 0 !important;
    height: 10px;
    border-radius: 0 !important;
    background: transparent !important; }

table td:nth-child(1)
{
  color:#007bff;
}
</style>

</head>

<body>
   
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'maps.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
       
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">QGIS and R</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
													<a href="edit_map.php" class="btn btn-primary text-white add-new" role="button" aria-pressed="true">
														<i class="fa fa-plus"></i> Add New
													</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">

				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">Name</th>
							<th data-name="description">Description</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody style="font-size: 15px;font-weight: 300;"> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->description?></td>
							<td>
								<a href="edit_map.php?id=<?=$row->id?>" class="edit" 	title="Edit"	 data-toggle="tooltip">
									<i class="material-icons">&#xE254;</i>
								</a>
								<?php if(is_dir(CACHE_DIR.'/'.$row->id)) { ?>
								<a class="clear" title="Clear cache" data-toggle="tooltip">
									<i class="material-icons">&#xf0ff;</i>
								</a>
								<?php }
									if(is_file(APPS_DIR.'/'.$row->id.'/proxy_qgis.php')) { ?>
								<a class="features" 	title="GetFeatures"	 data-toggle="tooltip">
									<i class="material-icons">link</i>
								</a>
								<?php } ?>
								<a class="delete" title="Delete" data-toggle="tooltip">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr> <?php endwhile; ?>
					</tbody>
				</table>

                </div>
            </div>
            <footer class="footer text-center"  style="background-color:gainsboro">
            </footer>
        </div>

    </div>
		
		<div id="qgis_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<p>QGIS Metadata</p>
					</div>
					
					<div class="modal-body" id="qgis-modal-body"><p>QGIS Metadata</p></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
		<script>new DataTable('#sortTable', { paging: false });</script>
</body>

</html>
