<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('class/datasource.php');
    require('incl/jru-lib.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
		
		$datasources = array();

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$ds_obj = new datasource_Class($database->getConn());
		$datasources = $ds_obj->getArr();
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



						// Add row on add button click
						$(document).on("click", ".add", function() {
						  var obj = $(this);
							var tr  = obj.closest("tr");
							var td  = tr.find('td').eq(5);
							var inp = tr.find('input[name="password"]');
							
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], select');
							input.each(function() {
								if (!$(this).val()) {
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
                                    url: 'action/datasource.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
																					td.attr('data-pwd', inp.val());	// update hidden password
																					// update id new row
																					obj.closest('table').find('tr:last-child').attr('data-id', response.id);
																					obj.closest('table').find('tr:last-child td:first-child').text(response.id)
                                        }
                                        alert(response.message);
                                    }
                                });

								$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
								$(this).closest("tr").find('td').eq(5).html('******');
								$(".add-new").removeAttr("disabled");
							}
						});



						// Edit row on edit button click
						$(document).on("click", ".edit", function() {
							var obj = $(this);
								var ai = $(this).siblings('.pwd_vis').find('i');
								
    								$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {

    								    if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
        								    var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
														var id = $(this).closest('tr').attr('data-id');

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
										var tr  = obj.closest("tr");
										var td  = tr.find('td').eq(5);
										var inp = tr.find('input[name="password"]');
										
										// replaces starts with password
										ai.text("visibility_off");
										inp.val(td.attr('data-pwd'));
									}

									$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
									$(".add-new").attr("disabled", "disabled");
								});



							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}

							    $.ajax({
                      type: "POST",
                      url: 'action/datasource.php',
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

							});
							
							// Change on password visibility
							$(document).on("click", ".pwd_vis", function() {
							    var obj = $(this);	// <a> with the icon
									var ai = obj.find('i');
									var td = obj.closest("tr").find('td').eq(5);
									
									if(ai.text() == "visibility"){
										ai.text("visibility_off");
								 		obj.attr("data-original-title", "Hide Password");
										td.text(td.attr('data-pwd'));
										
									}else{
										ai.text("visibility");
										obj.attr("data-original-title", "Show Password");
										td.text('******');
									}

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
  
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'datasources.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
      
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Datasources</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
													<button type="button" class="btn btn-primary text-white add-new">
														<i class="fa fa-plus"></i> Add New </button><br>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">

					<table class="table custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>#</th>
							<th data-name="type" data-type="select">Type</th>
							<th data-name="name">Name</th>
							<th data-name="url">URL</th>
							<th data-name="username">Username</th>
							<th data-name="password">Password</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php foreach($datasources as $ds_name => $ds){ ?> <tr data-id="<?=$ds_name?>" align="left">
						<td><?=$ds_name?></td>
						<td data-type="select" data-value="<?=$ds['type']?>"><?=$ds['type']?></td>
						<td><?=$ds['name']?></td>
						<td><?=$ds['url']?></td>
						<td><?=$ds['username']?></td>
						<td data-pwd="<?=$ds['password']?>">******</td>
							<td>
								<?php if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin'){ ?>
								<a class="add" title="Add" data-toggle="tooltip">			 <i class="material-icons">&#xE03B;</i></a>
								<a class="edit" title="Edit" data-toggle="tooltip">		 <i class="material-icons">&#xE254;</i></a>
								<a class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
							<?php } ?>
								<a class="pwd_vis" title="Show Password" data-toggle="tooltip" style="color: grey;"><i class="material-icons">visibility</i></a>
							</td>
						</tr> <?php } ?>
					</tbody>
				</table>				
				</div>
      </div>
    </div>
	<script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
