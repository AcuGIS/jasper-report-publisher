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
		
		$myuser_result = $obj->getById($_SESSION[SESS_USR_KEY]->id);
		$myuser = pg_fetch_assoc($myuser_result);
		pg_free_result($myuser_result);

		$acc_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
    $acc_grps = $acc_obj->getRowsArr();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
		<script type="text/javascript">
			$(document).ready(function() {
						$('[data-toggle="tooltip"]').tooltip();

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
							    } else {
										if($(this).attr('data-type') == 'select') {
											if($(this).attr('data-name') == 'groups') {
												row += `
														<td data-type="select" data-value="0">
																<select name="`+$(this).attr('data-name')+`" multiple>
																		<?PHP foreach($acc_grps as $k => $v) { ?>
																		<option value="<?=$k?>"><?=$v?></option>
																		<?PHP } ?>
																</select>
														</td>
												`;
										}	else if($(this).attr('data-name') == 'accesslevel') {
											row += `
													<td data-type="select" data-value="0">
															<select name="`+$(this).attr('data-name')+`">
																	<?PHP foreach(ACCESS_LEVELS as $k) { ?>
																	<option value="<?=$k?>"><?=$k?></option>
																	<?PHP } ?>
															</select>
													</td>
											`;
										}
									}	else {
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
							var input = $(this).parents("tr").find('input[type="text"], input[type="password"],select');
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
									let td = $(this).closest('td');
									if(td.attr('data-type') == 'select') {
											var val = $(this).find('option:selected').toArray().map(item => item.text).join(',');
											td.attr('data-value', $(this).val());
											td.html(val);
									}else if($(this).attr('data-name') == 'password') {
									       td.html('********');
									}else {
											td.html($(this).val());
									}

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/user.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
					    obj.closest('tr').attr('data-id', response.id);
                                            obj.closest('tr').find('td:first-child').text(response.id);
                                            obj.closest('tr').find('td').eq(3).text(response.password);
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
														
														var data_type = $(this).closest('table').find('thead tr th').eq(k).attr('data-type');
														if(data_type == 'select') {
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
														
														}else if(name == 'password'){
															$(this).html(' <input type="password" class="form-control" data-name="password" name="'+ name +'" value="' + $(this).text() + '"> ');
															
														}	else {
        											$(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');
													}
    								    }


									});

									$(this).parents("tr").find(".add, .edit").toggle();
									$(".add-new").attr("disabled", "disabled");
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
                        <h1 class="mb-0 fw-bold">Users</h1><p>&nbsp;</p>
<p><strong>Note:</strong> Your FTP username is <b><?=$myuser['ftp_user']?></b>. Your FTP password is your login password.</p>

                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            

<a href="registration.php" class="btn btn-info btn-md active" role="button" aria-pressed="true">Add User</a>

							


                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">

				<table class="table table-bordered custom-table">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">name</th>
							<th data-name="email" data-editable='false'>Email</th>
							<th data-name="password">Password</th>
							<th data-name="ftp_user" data-editable='false'>FTP User</th>
							<th data-name="accesslevel" data-type="select">Access Level</th>
							<th data-name="groups"      data-type="select">Access Groups</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody style="font-size: 16px;font-weight: 300;"> <?php while($user = pg_fetch_object($users)): ?> <tr data-id="<?=$user->id?>" align="left">
							<td><?=$user->id?> </td>
							<td><?= $user->name?></td>
							<td><?= $user->email?></td>
							<td><?= $user->password?></td>
							<td><?= $user->ftp_user?></td>
							<td data-type="select" data-value="<?=$user->accesslevel?>"><?=$user->accesslevel?></td>
								<?php
									$usr_acc_grps = $acc_obj->getByUserId($user->id);
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
					<!--<div class="col-6" style="width: 50%!important">
						<div class = "alert alert-success">
							<a href = "#" class = "close" data-dismiss = "alert">&times;</a>
							<strong>Note:</strong> Your personal FTP login username is <b><?=$myuser['ftp_user']?></b>. For password use your login password.
						</div>
					</div>-->
				</div>
				
      </div>
			  
    </div>      
  </div>

    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.js"></script>
</body>

</html>
