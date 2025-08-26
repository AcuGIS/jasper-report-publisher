<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('incl/jru-lib.php');
    require('class/database.php');
		require('class/schedule.php');
		require('class/datasource.php');
    require('class/report.php');
    require('class/user.php');
		require('class/access_group.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }

    $yesNo = [0 => 'No', 1 => 'Yes'];
		const REPFORMATS = array('html', 'pdf', 'yes');

		$datasources = array();
		$rows = false;
		$acc_grps = array();
		$repnames = array();


		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

	  $rep_obj = new Report_Class($dbconn);
	  $rows = $rep_obj->getRows();
			
		$acc_obj = new access_group_Class($dbconn);
		$acc_grps = $acc_obj->getAccessGroupsArr();

		$sch_obj = new schedule_Class($dbconn);
		$schedules = $sch_obj->getArr();
			
		foreach ($schedules as $schid => $sched) {
			array_push($repnames, $sched['name']);
		}
			
		$ds_obj = new datasource_Class($dbconn);
		$datasources = $ds_obj->getArr();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/admin.css" rel="stylesheet">
	<link href="dist/css/table.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
	<style>
		.page-wrapper {
			flex: 1;
			padding: var(--space-md) var(--space-lg);
			margin-left: 250px;
			background-color: var(--white);
		}
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
			max-width: 100px !important;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
		.table-container {
			background: white;
			border-radius: 8px;
			box-shadow: var(--shadow-sm);
			overflow: hidden;
			margin-top: 24px;
		}
		.custom-table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			margin-bottom: var(--space-lg);
		}
		.custom-table th {
			background: var(--background-gray);
			font-weight: 600;
			text-align: left;
			padding: 1rem;
			border-bottom: 2px solid var(--border-color);
		}
		.custom-table td {
			padding: 1rem;
			border-bottom: 1px solid var(--border-color);
			vertical-align: middle;
		}
		.custom-table tbody tr:hover {
			background-color: var(--background-gray);
		}
		.action-icon {
			color: var(--text-medium);
			cursor: pointer;
			padding: 0.25rem;
			border-radius: 4px;
			transition: all 0.2s;
		}
		.action-icon:hover {
			color: var(--primary-blue);
			background: var(--background-gray);
		}
		.action-icon.edit {
			color: #3b82f6;
		}
		.action-icon.delete {
			color: #ef4444;
		}
		.action-icon.add {
			color: #10b981;
		}
		.form-control {
			display: block;
			width: 100%;
			padding: 0.5rem 0.75rem;
			font-size: 0.875rem;
			line-height: 1.5;
			color: var(--text-dark);
			background-color: #fff;
			border: 1px solid var(--border-color);
			border-radius: 4px;
			transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
		}
		.form-control:focus {
			border-color: var(--primary-blue);
			outline: 0;
			box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
		}
		.error {
			border-color: #ef4444 !important;
		}
		.alert {
			padding: 12px 16px;
			border-radius: 4px;
			margin-bottom: 20px;
		}
		.alert-success {
			background-color: #d1e7dd;
			border-color: #badbcc;
			color: #0f5132;
		}
		.alert-warning {
			background-color: #fff3cd;
			border-color: #ffecb5;
			color: #664d03;
		}
	</style>
	<script type="text/javascript">
	        var deleted_ids = [];
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
							            if($(this).attr('data-name') == 'accgrps') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`" multiple>
    							                        <?PHP foreach($acc_grps as $k => $v) { ?>
    							                        <option value="<?=$k?>"><?=$v?></option>
    							                        <?PHP } ?>
    							                    </select>
    							                </td>
    							            `;
							            }
													else if($(this).attr('data-name') == 'datasource_id') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach($datasources as $ds_id => $ds) { ?>
    							                        <option value="<?=$ds_id?>"><?=$ds['name']?></option>
    							                        <?PHP } ?>
    							                    </select>
    							                </td>
    							            `;
							            }
													else if($(this).attr('data-name') == 'repname') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach($repnames as $k) { ?>
    							                        <option value="<?=$k?>"><?=$k?></option>
    							                        <?PHP } ?>
    							                    </select>
    							                </td>
    							            `;
							            }
							            else if($(this).attr('data-name') == 'is_grouped') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach($yesNo as $k => $v) { ?>
    							                        `+
    							                            `<option value="<?=$k?>"><?=$v?></option>`
    							                        +`
    							                        <?PHP } ?>
    							                    </select>
    							                </td>
    							            `;
							            }else if($(this).attr('data-name') == 'download_only') {
    							            row += `
    							                <td data-type="select" data-value="0">
    							                    <select name="`+$(this).attr('data-name')+`">
    							                        <?PHP foreach(REPFORMATS as $v) { ?>
    							                        `+
    							                            `<option value="<?=$v?>"><?=$v?></option>`
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
                                    url: 'action/report.php',
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
        									    if(name == 'accgrps') {

            									    $(this).html(`
                									    <select name="`+name+`" multiple>
        							                        <?PHP foreach($acc_grps as $k => $v) { ?>
        							                        <option value="<?=$k?>"><?=$v?></option>
        							                        <?PHP } ?>
        							                    </select>
    							                    `);

        									    }
															else if(name == 'datasource_id') {

            									    $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach($datasources as $ds_id => $ds) { ?>
        							                        <option value="<?=$ds_id?>"><?=$ds['name']?></option>
        							                        <?PHP } ?>
        							                    </select>
    							                    `);

        									    }
															else if(name == 'repname') {

            									    $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach($repnames as $k) { ?>
        							                        <option value="<?=$k?>"><?=$k?></option>
        							                        <?PHP } ?>
        							                    </select>
    							                    `);

        									    }
        									    else if(name == 'is_grouped') {
        									        $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach($yesNo as $k => $v) { ?>
        							                        `+
        							                            `<option value="<?=$k?>"><?=$v?></option>`
        							                        +`
        							                        <?PHP } ?>
        							                    </select>
    							                    `);
        									    }else if(name == 'download_only') {
        									        $(this).html(`
                									    <select name="`+name+`">
        							                        <?PHP foreach(REPFORMATS as $v) { ?>
        							                        `+
        							                            `<option value="<?=$v?>"><?=$v?></option>`
        							                        +`
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
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'delete': true, 'id': id}

							    $.ajax({
                                    type: "POST",
                                    url: 'action/report.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
                                            obj.parents("tr").remove();

                                            deleted_ids.push(id);
                                        }

                                        $(".add-new").removeAttr("disabled");
                                        alert(response.message);
                                    }
                                });

							});
						});
		</script>

</head>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
       
        <?php define('MENU_SEL', 'reports.php');
            include("incl/topbar.php");
            include("incl/sidebar.php");
        ?>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb"></nav>
                        <h1 class="mb-0 fw-bold">Reports</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <button type="button" class="btn btn-primary text-white add-new">
                                <i class="fa fa-plus"></i> Add New
                            </button>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-container">
                                    <div class="table-responsive">
                                        <table class="table table-borderless custom-table" id="sortTable">
                                            <thead>
                                                <tr>
                                                    <th data-editable="false">ID</th>
                                                    <th data-editable="true" data-name="name">Name</th>
                                                    <th data-editable="true" data-name="description">Description</th>
                                                    <th data-editable="true" data-name="datasource_id" data-type="select">Data Source</th>
                                                    <th data-editable="true" data-name="repname" data-type="select">Report Name</th>
                                                    <th data-editable="true" data-name="is_grouped" data-type="select">Grouped</th>
                                                    <th data-editable="true" data-name="download_only" data-type="select">Download Only</th>
                                                    <th data-editable="true" data-name="accgrps" data-type="select">Access Groups</th>
                                                    <th data-editable="false" data-action="true">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($row = pg_fetch_object($rows)): ?>
                                                <tr data-id="<?=$row->id?>">
                                                    <td><?=$row->id?></td>
                                                    <td><?=$row->name?></td>
                                                    <td><?=$row->description?></td>
                                                    <td data-type="select" data-value="<?=$row->datasource_id?>"><?=$datasources[$row->datasource_id]['name']?></td>
                                                    <td data-type="select" data-value="<?=$row->repname?>"><?=$row->repname?></td>
                                                    <td data-type="select" data-value="<?=$row->is_grouped?>"><?=$yesNo[$row->is_grouped]?></td>
                                                    <td data-type="select" data-value="<?=$row->download_only?>"><?=$row->download_only?></td>
                                                    <td data-type="select" data-value="<?=$row->accgrps?>"><?=$row->accgrps?></td>
                                                    <td>
                                                        <a class="action-icon add" title="Add" data-toggle="tooltip">
                                                            <i class="material-icons">&#xE03B;</i>
                                                        </a>
                                                        <a class="action-icon edit" title="Edit" data-toggle="tooltip">
                                                            <i class="material-icons">&#xE254;</i>
                                                        </a>
                                                        <a class="action-icon delete" title="Delete" data-toggle="tooltip">
                                                            <i class="material-icons">&#xE872;</i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success" id="repThumbnail">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <strong>Note:</strong> You can set the thumbnail for a report by adding reportid.png to the assets/maps folder.
                        </div>
                        
                        <div class="alert alert-warning" id="repThumbnail2">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <strong>URL</strong> must contain protocol - http or https.
                        </div>
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
