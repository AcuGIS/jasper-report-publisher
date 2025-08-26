<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/user.php');
		require('class/access_group.php');
		require('class/database.php');

    if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
				return;
    }

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $dbconn = $database->getConn();

		$acc_ojb = new access_group_Class($dbconn);
    $acc_grp = $acc_ojb->getAccessGroupsArr();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function() {
			
			$('#register_form').submit(false);
			
			$(document).on("click", "#btn_submit", function() {
				 	var obj = $(this);
				 	var input = $('#register_form').find('input[type="text"], input[type="checkbox"], select');
					var empty = false;
					
					obj.toggle();
					
					input.each(function() {
						if (!$(this).prop('disabled') && $(this).prop('required') && !$(this).val()) {
							$(this).addClass("error");
							empty = true;
						} else {
							$(this).removeClass("error");
						}
					});

					if(empty){
						alert('focus');
						$('#register_form').find(".error").first().focus();
					}else{
						let form_data = new FormData($('#register_form')[0]);
						$.ajax({
							type: "POST",
							url: 'action/user.php',
							data: form_data,
							processData: false,
							contentType: false,
							dataType:"json",
							success: function(response){
								alert(response.message);
								 if(response.success) {
									 window.location.href = 'users.php';
								 }
								 obj.toggle();
							 }
						});
					}
			});
			
			$(document).on("change", "#accesslevel", function() {
				var obj = $(this);
				const acc_level = obj.find('option:selected').text();
				
				if(acc_level == 'User'){
					$('#acc_grp_div').show(); $('#acc_grp_div').attr('required', true); 
				}else{
					$('#acc_grp_div').hide(); $('#acc_grp_div').attr('required', false); 
				}
			});
			
		});
	</script>
</head>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'registration.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Register New User</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">





                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container-fluid">

				<table class="table table-bordered">


					<tbody>

<form action="" id="register_form">
	
		<input type="hidden" name="save" value="1">
		
    <div class="form-group">
      <label for="name">Name:</label>
      <input type="text" class="form-control" id="name" placeholder="Enter name" name="name" required>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" class="form-control" id="email" placeholder="Enter email" name="email">
    </div>

    <div class="form-group">
      <label for="accesslevel">Access Level:</label>
      <select name="accesslevel" id="accesslevel">
				<?php foreach(ACCESS_LEVELS as $k) { ?>
					<option value="<?=$k?>"><?=$k?></option>
				<?php } ?>
</select>
    </div>


		<div class="form-group" id="acc_grp_div">
			<fieldset>
			<legend>Access Groups:</legend>
			<?php
				foreach($acc_grp as $group_id => $name){ ?>
				<p>
					<input type="checkbox" name="groups[]" id="group_<?=$group_id?>" value="<?=$group_id?>"/>
					<label for="group_<?=$group_id?>" class="form-label"><?=$name?></label>
				</p>
			<?php } ?>
			</fieldset>
    </div>

    <div class="form-group">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
    </div>

		<button type="submit" name="submit" id="btn_submit" class="btn btn-primary" value="create">Register</button>
  </form>



					</tbody>
				</table>







                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">


                    <div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-danger">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> Be sure to set the Access Level for the user.
</div>



<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repThumbnail").alert();
      });
   });
</script>
</div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center">

            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.js"></script>
</body>

</html>
