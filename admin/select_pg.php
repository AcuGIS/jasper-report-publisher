<?php
    session_start(['read_and_close' => true]);
		
		require('incl/const.php');
    require('class/database.php');
		require('class/pglink.php');
		require('class/user.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		
		$step = isset($_POST['step']) ? $_POST['step'] : 1;

		$msg = '';
		$dbs = null;
		$dbs_err = null;

		if($step == 2){
			$rdb = new Database($_POST['host'], 'postgres', $_POST['username'], $_POST['password'], $_POST['port'], 'public');
			if($rdb->is_connected()){
				list($dbs, $dbs_err) = $rdb->getDatabases($_POST['username']);
			}else{
				$dbs_err = 'Error: Failed to connect!';
			}
		}
			
		$_POST['step'] = $step + 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php include("incl/meta.php"); ?>
<script>
function validateForm() {
  var elements = document.getElementById("proj_form").elements;
	for (var i = 0, element; element = elements[i++];) {
    if ((element.tagName.toLowerCase() === 'input') && (element.type !== "hidden") &&
				(!element.disabled) && (element.value === "") && (element.name != 'svc_name')){
	    alert("Error: Field " + element.name + " missing!");
	    return false;
		}
  }
}

function pwd_vis(pwd_field_id) {
	var x = document.getElementById(pwd_field_id);
	var i = document.getElementById(pwd_field_id + '_vis_i');
	if (x.type === "password") {
		x.type = "text";
		i.innerHTML = "visibility_off";
	} else {
		x.type = "password";
		i.innerHTML = "visibility";
	}
}

	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>
	<style>
		table{width:30% !important; text-align:center; margin:auto; margin-top:70px;}
		.success{color:green;}
		.error{color:red;}
		.frm{width:70% !important; margin:auto; margin-top:100px;}

label {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 500 !important;
color:black!important;
}

.label {
	color:#666!important;
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
       
        <div class="page-wrapper" style="background: #22303c; color:#fff">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h1 class="mb-0 fw-bold">
													<span class="label">
													<?php 			 if($step == 1){ ?>PostGIS Data Source
													<?php } else if($step == 2){ ?>Select Database
													<?php } else if($step == 4){ ?>Result
													<?php } ?>
													</span>
												</h1>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
							<p>&nbsp;</p>
							<?php if($step == 3){ /* POST */
									// save details
									
									$_POST['name'] = $_POST['dbname'].'@'.$_POST['host'];
									$pglink_obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
									$newId = $pglink_obj->create($_POST);
									if($newId == 0){ ?>
										<span class="error">Save Failed!</span>
										<?php }else{
											$pglink_obj->pg_service_ctl('add', $_POST);
										?>
										<span class="success">PostGIS Data Source has been registered with ID <?=$newId?>.</span>
										</br></br>
										<a href="datasources.php?tab=pg" style="text-decoration:none; color: #fff">Return to Links</a>
								<?php
									}
									
									
								} else{ 
							?>
							<form id="proj_form" class="frm" method="post" action="select_pg.php" onsubmit="return validateForm()" required>
								
								<div class="form-group">

								<?php
									foreach($_POST as $k => $v){
										?><input type="hidden" class="form-control" name="<?=$k?>" value="<?=$v?>"><?php
									}
									if($step == 1){ /* db type */?>
										<label for="host">Host</label>
										<input type="text" class="form-control"	id="host" 	name="host" 	value="">
										
										<label for="port">Port</label>
										<input type="number" class="form-control"	id="port" 	name="port" 	value="5432">
										
										<label for="username">Username</label>
										<input type="text" class="form-control" id="username" name="username" value="">
										
										<label for="password">Password</label>
										<input type="password" class="form-control" id="password" name="password" value="">
										
										<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('password')">
											<i id="password_vis_i" class="material-icons" style="color:grey">visibility</i>
										</a>
										</br>

										<label for="username">Service Name</label>
										<input type="text" class="form-control" id="svc_name" name="svc_name" value="">

										</br>
										<button type="submit" name="submit" class="btn btn-primary" value="1">Next</button>
								
								<?php } else if($step == 2){ /* select db name */
										if($dbs_err){ ?>
											<span class="error"><?=$dbs_err?></span>
											<button onclick="history.back()">Go Back</button>
										
										<?php } else { ?>
										<label for="dbname">Available Databases</label><br>
										<select class="form-control" name="dbname" id="dbname" aria-label="Select db">
										<?php foreach($dbs as $k){ ?>
											<option value="<?=$k?>"><?=$k?></option>
										<?php	} ?> 
										</select><br>
										<button type="submit" name="submit" class="btn btn-primary" value="1">Save</button>
									<?php } ?>
								<?php } ?>
									</div>
									
							<?php	} //end if( step == 3) ?>
								</div>
							</form>
            </div>
            <footer class="footer text-center" style="background: #fff"></footer>
        </div>
    </div>
</body>
</html>
