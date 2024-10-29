<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('class/datasource.php');
    require('incl/jru-lib.php');
		require('class/pglink.php');
		require('class/gslink.php');

		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
		
		$datasources = array();

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
		
		$ds_obj = new datasource_Class($database->getConn());

		if(empty($_GET['tab']) || ($_GET['tab'] == 'ds')){
			$tab = 'ds'; $action = 'datasource';
			$datasources = $ds_obj->getArr();		
			
		}else if($_GET['tab'] == 'pg'){	
			$tab = 'pg';	$action = 'pglink';			// default tab is PostGIS
			$conn_obj = new pglink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$rows = $conn_obj->getRows();
			
		}else if($_GET['tab'] == 'gs'){
			$tab = 'gs'; $action = 'gslink';
			$conn_obj = new gslink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$rows = $conn_obj->getRows();
			
		}else if($_GET['tab'] == 'import'){
			$tab = 'import'; $action = 'import';
			
		}else{
			die('Error: Invalid tab');
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
	<script type="text/javascript">
	        
			$(document).ready(function() {
						
						$('[data-toggle="tooltip"]').tooltip();
						<?php if($tab == 'datasource'){ ?>
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
							        }
							        else {
							            row += '<td></td>';
							        }
							    }
							    else {
										if($(this).attr('data-type') == 'select') {
											if($(this).attr('data-name') == 'type') {
												row += `
														<td data-type="select" data-value="0">
																<select name="`+$(this).attr('data-name')+`">
																		<?PHP foreach(DS_TYPES as $k) { ?>
																		<option value="<?=$k?>"><?=$k?></option>
																		<?PHP } ?>
																</select>
														</td>
												`;
										}
									}
										else {
							        row += ' <td> <input type = "text" class = "form-control" name="'+$(this).attr('data-name')+'"> </td>';
										}
							    }
							});

							row += '</tr>';

							$("table").append(row);
							$("table tbody tr").eq(index + 1).find(".add, .edit").toggle();
							$('[data-toggle="tooltip"]').tooltip();
						});
						<?php } ?>
						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], select');
							input.each(function() {
								if (($(this).attr('name') != 'svc_name') && !$(this).val()) {
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
								    }
								    else {
								        $(this).parent("td").html($(this).val());
								    }

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/<?=$action?>.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
                                            obj.closest('table').find('tr:last-child').attr('data-id', response.id);
                                            //obj.closest('table').find('tr:last-child td:first-child').text(response.id)
                                        }
                                        alert(response.message)
                                    }
                                });

								$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
								$(this).closest("td").prev().html('******');
								$(".add-new").removeAttr("disabled");
							}
						});



						// Edit row on edit button click
						$(document).on("click", ".edit", function() {
									var obj = $(this);
									var id = $(this).closest('tr').attr('data-id');
									var data = {'pwd_vis': true, 'id': id}
									var ai = $(this).siblings('.pwd_vis').find('i');
								
    							$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {
										if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
											var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
											
											if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'select') {
												if(name == 'type') {
													$(this).html(`
															<select name="`+name+`">
																			<?PHP foreach(DS_TYPES as $k) { ?>
																			<option value="<?=$k?>"><?=$k?></option>
																			<?PHP } ?>
																	</select>
															`);

															var val = $(this).attr('data-value');
															$(this).find('[name='+name+']').val(val);
											}

												var val = $(this).attr('data-value').split(',');
												$(this).find('[name='+name+']').val(val);

											}	else {
												$(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
											}
    								}
									});
									
									
									if(ai.text() == "visibility"){
										// replaces starts with password
										$.ajax({
															 type: "POST",
															 url: 'action/<?=$action?>.php',
															 data: data,
															 dataType:"json",
															 success: function(response){
																	 if(response.success) {
																		obj.closest("td").prev().find('input[name="password"]').val(response.message);
																	}
															 }
													 });
									}
								
									$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
									$(".add-new").attr("disabled", "disabled");
								});

							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'delete': true, 'id': id}
									
									if(confirm('Data source will be deleted ?')){
										
										if('<?=$action?>' == 'pglink'){
											let host = obj.closest("tr").children("td").eq(2).text();
											if((host == 'localhost') && confirm('Delete local database too ?')){
												data['drop'] = true;
											}
										}
										
							    	$.ajax({
                                    type: "POST",
                                    url: 'action/<?=$action?>.php',
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
							
							// Change on password visibility
							$(document).on("click", ".pwd_vis", function() {
							    var obj = $(this);	// <a> with the icon
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'pwd_vis': true, 'id': id}
									
									var ai = obj.find('i');
									
									if(ai.text() == "visibility"){
										$.ajax({
	                             type: "POST",
	                             url: 'action/<?=$action?>.php',
	                             data: data,
	                             dataType:"json",
	                             success: function(response){
	                                 if(response.success) {
																		ai.text("visibility_off");
								 										obj.attr("data-original-title", "Hide Password");
	 																	obj.closest("td").prev().html(response.message);
																	}
	                             }
	                         });
													 
									}else{
										ai.text("visibility");
										obj.attr("data-original-title", "Show Password");
										obj.closest("td").prev().html('******');
									}
							});
							
							// Show PG connection info
							$(document).on("click", ".conn_info", function() {
									var obj = $(this);	// <a> with the icon
									var id = obj.parents("tr").attr('data-id');
									var data = {'conn_info': true, 'id': id}
																	
									$.ajax({
													 type: "POST",
													 url: 'action/<?=$action?>.php',
													 data: data,
													 dataType:"json",
													 success: function(response){
															 if(response.success) {
																//alert(response.message);
																$('.modal-body').html(response.message);
																$('#conn_modal').modal('show');
															}
													 }
										 });
							});
						
						});
		</script>

<style>
table.table th:last-child {
	width: 140px;
}
</style>
</head>

<body>
  
	<?php
						if($tab == 'pg'){					require('incl/links_pg.php');
			}else if($tab == 'gs'){					require('incl/links_gs.php');
			}else if($tab == 'import'){			require('incl/links_import.php');
			}else if($tab == 'ds'){					require('incl/links_ds.php');	
			}else{													die('Error: Invalid tab!');
			}
			?>
				
				<div id="conn_modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<!-- <button type="button" class="close" data-dismiss="modal">&times;</button>-->
							<h4 class="modal-title">Connection Information</h4>
						</div>
						<div class="modal-body" id="modal-body"><p>Connection string.</p></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary copy">Copy</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
    
	<script>new DataTable('#sortTable', { paging: false });</script>
  <script src="dist/js/sidebarmenu.js"></script>
  <script src="dist/js/custom.js"></script>
</body>

</html>
