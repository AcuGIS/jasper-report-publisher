<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/link.php');
		require('class/access_group.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $obj = new link_Class($dbconn);
    $links = $obj->getRows();

		$acc_obj = new access_group_Class($dbconn);
    $acc_grps = $acc_obj->getAccessGroupsArr();
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
		.add, .edit, .delete {
			color: var(--text-medium);
			cursor: pointer;
			padding: 0.25rem;
			border-radius: 4px;
			transition: all 0.2s;
			text-decoration: none;
		}
		.add:hover {
			color: #10b981;
			background: var(--background-gray);
		}
		.edit:hover {
			color: #3b82f6;
			background: var(--background-gray);
		}
		.delete:hover {
			color: #ef4444;
			background: var(--background-gray);
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

			// Append table with add row form on add new button click
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
						} else {
							$(this).parent("td").html($(this).val());
						}
						data[$(this).attr('name')] = $(this).val();
					});

					$.ajax({
						type: "POST",
						url: 'action/link.php',
						data: data,
						dataType:"json",
						success: function(response){
							if(response.id) {
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
							if(name == 'groups') {
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
						} else {
							$(this).html('<input type="text" name="'+ name +'" class="form-control" value="' + $(this).text() + '">');
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

				$.ajax({
					type: "POST",
					url: 'action/link.php',
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
			});
		});
	</script>
</head>

<body>
	<div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
		data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
		
		<?php define('MENU_SEL', 'links.php');
			include("incl/topbar.php");
			include("incl/sidebar.php");
		?>
		
		<div class="page-wrapper">
			<div class="page-breadcrumb">
				<div class="row align-items-center">
					<div class="col-6">
						<nav aria-label="breadcrumb"></nav>
						<h1 class="mb-0 fw-bold">Links</h1>
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
									<table class="table table-borderless custom-table" id="sortTable">
										<thead>
											<tr>
												<th data-editable="false">ID</th>
												<th data-editable="true" data-name="name">Name</th>
												<th data-editable="true" data-name="url">URL</th>
												<th data-editable="true" data-name="groups" data-type="select">Groups</th>
												<th data-editable="false" data-action="true">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($links as $link): ?>
											<tr data-id="<?=$link['id']?>">
												<td><?=$link['id']?></td>
												<td><?=$link['name']?></td>
												<td><?=$link['url']?></td>
												<td data-type="select" data-value="<?=$link['groups']?>"><?=$link['groups']?></td>
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
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
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
