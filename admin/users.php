<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/user.php');
		require('class/access_groups.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $obj = new user_Class($dbconn);
    $users = $obj->getRows();

		$acc_obj = new access_group_Class($dbconn);
    $acc_grps = $acc_obj->getAccessGroupsArr();
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

						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], input[type="password"], select');
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
									/*if($(this).closest('td').attr('data-type') == 'select') {
											var val = $(this).find('option:selected').text();
											$(this).parent("td").attr('data-value', $(this).val());
											$(this).parent("td").html(val);
									}else {
										$(this).parent("td").html($(this).val());
									}*/

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/user.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
                                            obj.closest('table').find('tr:last-child').attr('data-id', response.id);
                                            obj.closest('table').find('tr:last-child td:first-child').text(response.id);
                                        }
                                        alert(response.message);
																				window.location.href = 'users.php';
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
															if(name == 'accesslevel') {
																$(this).html(`
																		<select name="`+name+`">
																						<?PHP foreach(ACCESS_LEVELS as $k) { ?>
																						<option value="<?=$k?>"><?=$k?></option>
																						<?PHP } ?>
																				</select>
																		`);

																		var val = $(this).attr('data-value');
																		$(this).find('[name='+name+']').val(val);
														}	else if(name == 'groups') {
																$(this).html(`
																		<select name="`+name+`" multiple>
																						<?PHP foreach($acc_grps as $k => $v) { ?>
																						<option value="<?=$k?>"><?=$v?></option>
																						<?PHP } ?>
																				</select>
																		`);
															}

															var val = $(this).attr('data-value').split(',');
															$(this).find('[name='+name+']').val(val);

														}	else if(name == 'password'){
															$(this).html(' <input type = "password" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
														} else {
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
									
									if(confirm('User will be deleted ?')){
							    	$.ajax({
                                    type: "POST",
                                    url: 'action/user.php',
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

        <?php const MENU_SEL = 'users.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
      
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Users</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            

<a href="registration.php" class="btn btn-info btn-md active" role="button" aria-pressed="true">Add User</a>

							


                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">

				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">name</th>
							<th data-name="email">Email</th>
							<th data-name="password">Password</th>
							<th data-name="accesslevel" data-type="select">Access Level</th>
							<th data-name="groups"      data-type="select">Access Groups</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($user = pg_fetch_object($users)): ?> <tr data-id="<?=$user->id?>" align="left">
							<td><?=$user->id?> </td>
							<td><?= $user->name?></td>
							<td><?= $user->email?></td>
							<td><?= $user->password?></td>
							<td data-type="select" data-value="<?=$user->accesslevel?>"><?=$user->accesslevel?></td>
								<?php
									$usr_acc_grps = $obj->getUserAccessGroups($user->id);
									$grp_ids = implode(',',array_keys($usr_acc_grps));
									$grp_names = implode(',',array_values($usr_acc_grps));
								?>
							<td data-type="select" data-value="<?=$grp_ids?>"><?=$grp_names?></td>

							<td>
								<a class="add" title="Add" data-toggle="tooltip">
									<i class="material-icons">&#xE03B;</i>
								</a>
								<a class="edit" title="Edit" data-toggle="tooltip">
									<i class="material-icons">&#xE254;</i>
								</a>
								<a class="delete" title="Delete" data-toggle="tooltip">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr> <?php endwhile; ?>
					</tbody>
				</table>






                
                <div class="row">
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                    <div class="col-12">

                    </div>
                </div>
               
            </div>
            
        </div>
        
    </div>

				<script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
