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
            max-width: 175px !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
        .add, .edit, .delete, .clear, .features {
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
        .clear:hover {
            color: #f59e0b;
            background: var(--background-gray);
        }
        .features:hover {
            color: #6366f1;
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
                            if(response.success) {
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
</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <?php define('MENU_SEL', 'maps.php');
            include("incl/topbar.php");
            include("incl/sidebar.php");
        ?>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb"></nav>
                        <h1 class="mb-0 fw-bold">Maps</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                            <a href="edit_map.php" class="btn btn-primary text-white add-new" role="button" aria-pressed="true">
                                <i class="fa fa-plus"></i> Add Map
                            </a>
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
                                                <th data-name="id" data-editable='false'>ID</th>
                                                <th data-name="name">Name</th>
                                                <th data-name="description">Description</th>
                                                <th data-name="url">URL</th>
                                                <th data-name="type">Type</th>
                                                <th data-name="cache">Cache</th>
                                                <th data-editable='false' data-action='true'>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = pg_fetch_object($rows)): ?>
                                            <tr data-id="<?=$row->id?>" align="left">
                                                <td><?=$row->id?></td>
                                                <td><?=$row->name?></td>
                                                <td><?=$row->description?></td>
                                                <td><?=$row->url?></td>
                                                <td><?=$row->type?></td>
                                                <td><?=$row->cache?></td>
                                                <td>
                                                    <a class="features" title="Features" data-toggle="tooltip">
                                                        <i class="material-icons">&#xE8F4;</i>
                                                    </a>
                                                    <a class="edit" title="Edit" href="edit_map.php?id=<?=$row->id?>">
                                                        <i class="material-icons">&#xE254;</i>
                                                    </a>
                                                    <a class="delete" title="Delete" data-toggle="tooltip">
                                                        <i class="material-icons">&#xE872;</i>
                                                    </a>
                                                    <?php if($row->cache == 'Yes'): ?>
                                                    <a class="clear" title="Clear Cache" data-toggle="tooltip">
                                                        <i class="material-icons">&#xE14C;</i>
                                                    </a>
                                                    <?php endif; ?>
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
        </div>
    </div>

    <script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>
</html>
