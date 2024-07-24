<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('class/user.php');
    require('class/access_groups.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn(); 

    $acc_obj = new access_group_Class($dbconn);
    $rows = $acc_obj->getAccessGroups();

		$obj = new user_Class($dbconn);
    $users = $obj->getRowsArr();

    if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
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
												if($(this).attr('data-name') == 'userids') {
														row += `
																<td data-type="select" data-value="0">
																		<select name="`+$(this).attr('data-name')+`" multiple>
																				<?PHP foreach($users as $k => $v) { ?>
																				`+
																						`<option value="<?=$k?>"><?='('.$k.')'.$v?></option>`
																				+`
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
								    }
								    else {
								        $(this).parent("td").html($(this).val());
								    }

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/access_groups.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
                                            obj.closest('table').find('tr:last-child').attr('data-id', response.id);
                                            obj.closest('table').find('tr:last-child td:first-child').text(response.id)
                                        }
                                        alert(response.message)
                                    }
                                });

								$(this).parents("tr").find(".add, .edit").toggle();
								$(".add-new").removeAttr("disabled");
							}
						});



						// Edit row on edit button click
						$(document).on("click", ".edit", function() {
    								$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {

    								    if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
    								        var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
														var id = $(this).closest('tr').attr('data-id');

 												 if($(this).closest('table').find('thead tr th').eq(k).attr('data-type') == 'select') {
 														 if(name == 'userids') {
 																 $(this).html(`
 																		 <select name="`+name+`" multiple>
 																						 <?PHP foreach($users as $k => $v) { ?>
 																						 <option value="<?=$k?>"><?='('.$k.')'.$v?></option>
 																						 <?PHP } ?>
 																				 </select>
 																		 `);
 														 }
 														 var val = $(this).attr('data-value').split(',');
 														 $(this).find('[name='+name+']').val(val);
 												 }
 												 else {
 														 $(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
 												 }
    								    }


									});

									$(this).parents("tr").find(".add, .edit").toggle(); $(".add-new").attr("disabled", "disabled");
								});



							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}

									if(confirm('Access group will be deleted ?')){
							    	$.ajax({
                                    type: "POST",
                                    url: 'action/access_groups.php',
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
						});
		</script>
</head>

<body>

   
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'access_groups.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
       
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Access Groups</h1>
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
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">Name</th>
							<th data-name="userids" data-type="select">Users</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?></td>
							<td><?= $row->name?></td>

								<?php
									$grp_usrs = $acc_obj->getGroupUsers(array($row->id));
									$grp_id_usrs = array();
									foreach($grp_usrs as $id => $name){
										array_push($grp_id_usrs, '('.$id.')'.$name);
									}
								?>
								<td data-type="select" data-value="<?=implode(',',array_keys($grp_usrs))?>"><?=implode(', ',$grp_id_usrs)?></td>

							<td>
								<a class="add" 		title="Add" data-toggle="tooltip">		<i class="material-icons">&#xE03B;</i></a>
								<a class="edit" 	title="Edit" data-toggle="tooltip">		<i class="material-icons">&#xE254;</i></a>
								<a class="delete" title="Delete" data-toggle="tooltip">	<i class="material-icons">&#xE872;</i></a>
							</td>
						</tr> <?php endwhile; ?> </tr>
					</tbody>
				</table>





                
                <div class="row">


                    <div class="col-12">

						<div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-warning">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> Username is prefixed with its ID.
</div>



<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repThumbnail").alert();
      });
   });
</script>
</div>


<div class="row">
            
                  </div>

                    </div>
                </div>
                
            </div>
           
            <footer class="footer text-center">

            </footer>
            
        </div>
       
    </div>
   
		<script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
