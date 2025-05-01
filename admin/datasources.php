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
	<link href="dist/css/admin.css" rel="stylesheet">
	<link href="dist/css/table.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip();
			<?php if($tab == 'ds'){ ?>
				var actions = `
					<a class="action-icon add" title="Add" data-toggle="tooltip">
						<i class="material-icons">&#xE03B;</i>
					</a>
					<a class="action-icon edit" title="Edit" data-toggle="tooltip">
						<i class="material-icons">&#xE254;</i>
					</a>
					<a class="action-icon delete" title="Delete" data-toggle="tooltip">
						<i class="material-icons">&#xE872;</i>
					</a>
				`;
				
				$(".add-new").click(function() {
					$(this).attr("disabled", "disabled");
					var index = $("table tbody tr:last-child").index();

					var row = '<tr>';

					$("table thead tr th").each(function(k, v) {
						if($(this).attr('data-editable') == 'false') {
							if($(this).attr('data-action') == 'true') {
								row += '<td>'+actions+'</td>';
							} else {
								row += '<td></td>';
							}
						} else {
							if($(this).attr('data-type') == 'select') {
								if($(this).attr('data-name') == 'type') {
									row += `
										<td data-type="select" data-value="0">
											<select name="`+$(this).attr('data-name')+`" class="form-control">
												<?PHP foreach(DS_TYPES as $k) { ?>
												<option value="<?=$k?>"><?=$k?></option>
												<?PHP } ?>
											</select>
										</td>
									`;
								}
							} else {
								row += '<td><input type="text" class="form-control" name="'+$(this).attr('data-name')+'"></td>';
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
						} else {
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
							if(response.id) {
								obj.closest('table').find('tr:last-child').attr('data-id', response.id);
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
									<select name="`+name+`" class="form-control">
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

						} else {
							$(this).html('<input type="text" name="'+ name +'" class="form-control" value="' + $(this).text() + '">');
						}
					}
				});
				
				if(ai.text() == "visibility"){
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
							if(response.success) {
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
</style>

</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'datasources.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>

        <div class="page-wrapper">
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">
                        </nav>
                        <h1 class="mb-0 fw-bold">Data Sources</h1>
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
                <div class="table-container">
                    <table class="table table-borderless custom-table" id="sortTable">
                        <thead>
                            <tr>
                                <th data-name="id" data-editable='false'>ID</th>
                                <th data-name="name">Name</th>
                                <th data-name="type" data-type="select">Type</th>
                                <th data-name="url">URL</th>
                                <th data-name="username">Username</th>
                                <th data-name="password">Password</th>
                                <th data-editable='false' data-action='true'>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($datasources as $ds): ?>
                            <tr data-id="<?=$ds['id']?>">
                                <td><?=$ds['id']?></td>
                                <td><?=$ds['name']?></td>
                                <td data-type="select" data-value="<?=$ds['type']?>"><?=$ds['type']?></td>
                                <td><?=$ds['url']?></td>
                                <td><?=$ds['username']?></td>
                                <td>******</td>
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>
</html>
