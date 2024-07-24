<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/context.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
		
    $obj = new context_Class($dbconn);
    $rows = $obj->getRows();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

	<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.css" />
	<script type="importmap">
	{
	   "imports": {
	   	"ckeditor5": "https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.js",
	    "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/42.0.0/"
		}
	}
	</script>

		<script type="text/javascript">

			$(document).ready(function() {
						$('[data-toggle="tooltip"]').tooltip();

						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], textarea');
							/*input.each(function() {
								if (($(this).attr('data-type') == 'textarea') && !$(this).html()) {
									$(this).addClass("error");
									empty = true;
								}else if(!$(this).val()){
									$(this).addClass("error");
									empty = true;
								} else {
									$(this).removeClass("error");
								}
							});*/

							$(this).parents("tr").find(".error").first().focus();
							if (!empty) {
								var data = {};
								data['save'] = 1;
								data['id'] = $(this).closest('tr').attr('data-id');

								input.each(function() {

									if($(this).closest('td').attr('data-type') == 'editor') {
										var val = $(this).html();
										$(this).parent("td").attr('data-value', val);
										$(this).parent("td").html($(this).val());
										data['input'] = $(this).find('[id="input"]').html();
									}else{
										$(this).parent("td").html($(this).val());
										data[$(this).attr('name')] = $(this).val();
									}
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/context.php',
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

							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var data = {'delete': true, 'id': obj.parents("tr").attr('data-id')}

							    $.ajax({
                                    type: "POST",
                                    url: 'action/context.php',
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
						});
		</script>

</head>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <div class="navbar-header" data-logobg="skin6">
                    
                    <a class="navbar-brand" href="index.php">
                        <b class="logo-icon">
                            <img src="assets/images/cited-logo.png" alt="homepage" class="dark-logo" />
                            <img src="assets/images/cited-logo.png" alt="homepage" class="light-logo" />
                        </b>
                       
                        <span class="logo-text">
                      

                        </span>
                    </a>
                   
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                </div>
                

            </nav>
        </header>
       
        <?php const MENU_SEL = 'contexts.php'; include("incl/sidebar.php"); ?>
       
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Contexts</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
													<a href="edit_context.php" class="btn btn-primary text-white add-new" role="button" aria-pressed="true"><i class="fa fa-plus"></i> Add Context</a>
                        </div>
                    </div>
                </div>
            </div>
           
      <div class="container-fluid">

				<table class="table table-bordered custom-table" id="sortTable">
					<thead>
						<tr>
							<th data-name="id" data-editable='false'>ID</th>
							<th data-name="name">Name</th>
							<th data-name="input" data-type="editor">Input</th>
              <th data-name="report_id">Report ID</th>
							<th data-editable='false' data-action='true'>Actions</th>


						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<td><?=$row->id?> </td>
							<td><?=$row->name?> </td>
							<td data-type="editor" data-value="hohoboho"><?=$row->input?></td>
							<td><?=$row->report_id?> </td>

							<td>
								<a class="add" title="Add" data-toggle="tooltip">
									<i class="material-icons">&#xE03B;</i>
								</a>
								<a class="edit" title="Edit" href="edit_context.php?id=<?=$row->id?>">
									<i class="material-icons">&#xE254;</i>
								</a>
								<a class="delete" title="Delete" data-toggle="tooltip">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr> <?php endwhile; ?> </tr>
					</tbody>
				</table>
      </div>
            <footer class="footer text-center"></footer>
    </div>
</div>
   
    <script>new DataTable('#sortTable', { paging: false });</script>
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>

</body>

</html>
