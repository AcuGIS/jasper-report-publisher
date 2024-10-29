<?php
session_start(['read_and_close' => true]);
require('incl/const.php');
require('class/database.php');
require('class/map.php');
require('class/permalink.php');
require('class/access_groups.php');

if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
		header('Location: ../login.php');
		exit;
}

	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dbconn = $database->getConn();

	$tab = 'permalink'; $action = 'permalink';
	$conn_obj = new permalink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$conn_rows = $conn_obj->getRows();		
	
	$maps_obj = new map_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
	$map_rows = $maps_obj->getRows();
	
	$maps = array();
	while($row = pg_fetch_assoc($map_rows)){
		$maps[$row['id']] = $row;
	}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

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
</style>
<script type="text/javascript">

function reload_select(name, arr, val_sel){
	var obj = $('#' + name);
	obj.empty();
	
	let sel = val_sel == '0';
	//obj.append($('<option>',{text: 'Select', value: '0', selected: sel}));
	$.each(arr, function(x){
		sel = val_sel == arr[x];
		obj.append($('<option>',{text: arr[x], value: arr[x], selected: sel}));
	});
	obj.change();
}

	$(document).ready(function() {
				$('[data-toggle="tooltip"]').tooltip();
				
					var actions = `
					<a class="add" title="Add" data-toggle="tooltip">
						<i class="material-icons">&#xE03B;</i>
					</a>
					<a class="edit" title="Edit" data-toggle="tooltip">
						<i class="material-icons">&#xE254;</i>
					</a>
					<a class="delete" title="Delete" data-toggle="tooltip">
						<i class="material-icons">&#xE872;</i>
					</a>
				`;
				//$("table td:last-child").html();
				// Append table with add row form on add new button click
				$(".add-new").click(function() {
						//var actions = $("table td:last-child").html();
					$(this).attr("disabled", "disabled");
					var index = $("table tbody tr:last-child").index();

					var row = '<tr>';

					$("table thead tr th").each(function(k, v) {
							if($(this).attr('data-editable') == 'false') {

									if($(this).attr('data-action') == 'true') { // last child or actions cell
											row += '<td>'+actions+'</td>';
									}	else {
											row += '<td></td>';
									}
							}
							else {
									if($(this).attr('data-type') == 'select') {
										if($(this).attr('data-name') == 'map_id') {
											row += `
													<td data-type="select" data-value="0">
															<select id="`+$(this).attr('data-name')+`" name="`+$(this).attr('data-name')+`">
																	<?PHP foreach($maps as $k => $v) { ?>
																	<option value="<?=$k?>"><?=$v['name']?></option>
																	<?PHP } ?>
															</select>
													</td>
											`;
									}else if($(this).attr('data-name') == 'page') {
											row += `
													<td data-type="select" data-value="0">
															<select id="`+$(this).attr('data-name')+`" name="`+$(this).attr('data-name')+`">
															</select>
													</td>
											`;
									}
								}else{
									row += ' <td> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
								}
							}
					});

					row += '</tr>';

					$("table").append(row);
					$("table tbody tr").eq(index + 1).find(".add, .edit").toggle();
					$('[data-toggle="tooltip"]').tooltip();
					
					$('#map_id').change();	// force reload of pages select
				});



				// Add row on add button click
				$(document).on("click", ".add", function() {
						var obj = $(this);
					var empty = false;
					var input = $(this).parents("tr").find('input[type="text"], select');
					input.each(function() {
						if (!$(this).val() && ($(this).attr('name') != 'query')) {
							$(this).addClass("error");
							empty = true;
						} else {
							$(this).removeClass("error");
						}
					});

					$(this).parents("tr").find(".error").first().focus();
					if (!empty) {
						var data = {};
						data['save'] = 1;
						data['id'] = $(this).closest('tr').attr('data-id');

						input.each(function() {
							if($(this).closest('td').attr('data-type') == 'select') {
									var val = $(this).find('option:selected').text();
									$(this).parent("td").attr('data-value', $(this).val());
									$(this).parent("td").html(val);
							}else {
									$(this).parent("td").html($(this).val());
							}

							data[$(this).attr('name')] = $(this).val();
						});

						$.ajax({
																type: "POST",
																url: 'action/permalink.php',
																data: data,
																dataType:"json",
																success: function(response){
																		if(response.id) { // means, new record is added
																				obj.closest('table').find('tr:last-child').attr('data-id', response.id);
																				obj.closest('table').find('tr:last-child td:first-child').text(response.id)
																				
																				obj.closest('table').find('tr:last-child td').eq(3).text(response.created);
																				obj.closest('table').find('tr:last-child td').eq(4).text(response.expires);
																				obj.closest('table').find('tr:last-child td').eq(5).text(0);
																				var perma_link = '<a href="../' + response.url + '">' + response.hash + '</a>';
																				obj.closest('table').find('tr:last-child td').eq(7).html(perma_link);
																		}
																		alert(response.message)
																}
														});

						$(this).parents("tr").find(".add, .edit").toggle();
						$(".add-new").removeAttr("disabled");
					}
				});

				$(document).on("change", '#map_id', function() {
					let obj = $(this);
					let data = {
						'id' 			: obj.find('option:selected').val(),
						'pages' : true
					};
					
					let page = $('#page');

					page.val("0");	page.attr('disabled', 'disabled');
					
					if(data.id != "0"){
						$.ajax({
							type: "POST",
							url: 'action/map.php',
							async: false,	// we need false, to set selected in .edit event
							data: data,
							dataType:"json",
							success: function(response){
								 if(response.success) {
									 page.removeAttr('disabled');
									 reload_select('page', response.pages, "0");
								 }else{
									 alert('Error: Failed to list pages. ' + response.message);
								 }
							},
							fail: function(){	alert('Error: POST failure');	}
						});
					}
				});

				// Edit row on edit button click
				$(document).on("click", ".edit", function() {
						$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {

								if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
										var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
										var id = $(this).closest('tr').attr('data-id');
										
										
										if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'select') {
											if(name == 'map_id') {
												$(this).html(`
														<select id="`+name+`" name="`+name+`">
																		<?PHP foreach($maps as $k => $v) { ?>
																		<option value="<?=$k?>"><?=$v['name']?></option>
																		<?PHP } ?>
																</select>
														`);
											}else if(name == 'page') {
												$(this).html(`<select id="`+name+`" name="`+name+`"></select>`);
												$('#map_id').change();	// force reload of pages select
											}

											var val = $(this).attr('data-value').split(',');
											$(this).find('[name='+name+']').val(val);

										}	else {
											var val = $(this).html().replace('<br>', '&');
											$(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + val + '" > ');
										}
								}
					});

					$(this).parents("tr").find(".add, .edit").toggle(); $(".add-new").attr("disabled", "disabled");
				});
			
				
					// Delete row on delete button click
					$(document).on("click", ".delete", function() {
							var obj = $(this);
							var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}
							if(confirm('Permalink will be deleted ?')){
								$.ajax({
																type: "POST",
																url: 'action/permalink.php',
																data: data,
																dataType:"json",
																success: function(response){
																		if(response.success) { // means, new record is added
																				obj.parents("tr").remove();
																		}

																		alert(response.message);
																}
														});
							}

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

</style>

</head>

<body style="background-color:#efefef">
<div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
		data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

		<?php define('MENU_SEL', 'permalinks.php');
			include("incl/topbar.php");
			include("incl/sidebar.php");
		?>
		<div class="page-wrapper">
				  <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Permalinks</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
									<div class="text-end upgrade-btn">
										<a class="btn btn-primary text-white add-new" role="button" aria-pressed="true"><i class="fa fa-plus"></i> Add New</a>											
									</div>
							</div>
						</div>
				</div>
	<div class="container-fluid"><p>&nbsp;</p>
		<div class="table-responsive">

		<table class="table table-bordered custom-table" id="sortTable">
			<thead>
				<tr>
					<th data-name="id" data-editable='false'>ID</th>
					<th data-name="map_id" data-type="select">Map</th>
					<th data-name="page" data-type="select">Page</th>
					<th data-name="description">Description</th>
					<th data-name="query">Query</th>
					
					<th data-name="created" data-editable='false'>Created</th>
					<th data-name="expires">Expires</th>
					
					<th data-name="visits" data-editable='false'>Visits</th>
					<th data-name="visits_limit" >Limit</th>
					
					<th data-name="hash" data-editable='false'>Hash</th>
					<th data-editable='false' data-action='true'>Actions</th>
				</tr>
			</thead>

			<tbody> <?php while($row = pg_fetch_object($conn_rows)): ?> <tr data-id="<?=$row->id?>" align="left">
					<td><?=$row->id?></td>
					<td data-type="select" data-value="<?=$row->map_id?>"><?=$maps[$row->map_id]['name']?></td>
					<td data-type="select" data-value="<?=$row->page?>"><?=$row->page?></td>
					<td><?=$row->description?></td>
					<td><?=str_replace('&', '</br>', urldecode($row->query))?></td>
					<td><?=$row->created?></td>	
					<td><?=$row->expires?></td>
					<td><?=$row->visits?></td>
					<td><?=$row->visits_limit?></td>
					<td>
							<a href="../apps/<?=$row->map_id?>/<?=$row->page?>?permalink=<?=$row->hash?>"><?=$row->hash?></a>
					</td>
					
					<td>
						<a class="add"		title="Add"		 data-toggle="tooltip"><i class="material-icons">&#xE03B;</i></a>
						<a class="edit" 	title="Edit"	 data-toggle="tooltip"><i class="material-icons">&#xE254;</i></a>
						<a class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
					</td>
				</tr> <?php endwhile; ?>
			</tbody>
		</table>
	</div>


						
		<div class="row">

			<p>&nbsp;</p>
			<div class="col-6" style="width: 50%!important">
				<p>&nbsp;</p>
				<div class = "alert alert-success">
					<a href = "#" class = "close" data-dismiss = "alert">&times;</a>
					<strong>Note:</strong> Permalinks will be auto-deleted on when they expire by time or run out of visits. Limit of 0, disables visits limitation.
				</div>
				
				<div class = "alert alert-warning">
					<a href = "#" class = "close" data-dismiss="alert">&times;</a>
					<strong>Note:</strong> In expires you can write PostgreSQL TIMESTAMP values, like '1 hour', '1 day'.
					For more <a href="https://www.postgresql.org/docs/current/functions-datetime.html" target="_blank">see here.</a>
				</div>

				</div>

		</div>
	</div>
	<script>new DataTable('#sortTable', { paging: false });</script>
<footer class="footer text-center">
				</footer>
		</div>
</div>

<script src="dist/js/sidebarmenu.js"></script>
<script src="dist/js/custom.js"></script>
</body>

</html>
