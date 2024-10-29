<?php
    session_start(['read_and_close' => true]);
		
		require('incl/const.php');
    require('class/database.php');
		require('class/gslink.php');
		require('class/user.php');

		function rest_get($url, $username, $password){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
			$json = curl_exec ($ch);
			$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
			curl_close ($ch);
			return [$status_code, $json];
		}
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }
		
		$step = isset($_POST['step']) ? $_POST['step'] : 1;

		$msg = '';
		$dbs = null;
		$dbs_err = null;

		if($step == 2){			
			list($status_code, $js) = rest_get($_POST['url'].'/rest/workspaces', $_POST['username'], $_POST['password']);
			if(($status_code != 200) || empty($js)){
				var_dump($js);
				$dbs_err = 'cURL failed with status code '. $status_code;
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
    if ((element.tagName.toLowerCase() === 'input') && (element.type !== "hidden") && (!element.disabled) && (element.value === "")){
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
													<?php 			 if($step == 1){ ?>Set GeoServer Details
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
									$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
									$gslink_obj = new gslink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
									$newId = $gslink_obj->create($_POST);
									if($newId == 0){ ?>
										<span class="error">Save Failed!</span>
										<?php }else{ ?>
										<span class="success">Geoserver link has been created with ID <?=$newId?>.</span>
										</br></br>
										<a href="datasources.php?tab=gs" style="text-decoration:none; color: #fff">Return to Links</a>
								<?php
									}
									
									
								} else{ 
							?>
							<form id="proj_form" class="frm" method="post" action="select_gs.php" onsubmit="return validateForm()" required>
								
								<div class="form-group">

								<?php
									foreach($_POST as $k => $v){
										?><input type="hidden" class="form-control" name="<?=$k?>" value="<?=$v?>"><?php
									}
									if($step == 1){ /* db type */?>
										
										<label for="host">Name</label>
										<input type="text" class="form-control"	id="name" 	name="name" value="">
										
										<label for="host">URL</label>
										<input type="text" class="form-control"	id="url" 	name="url" 	value="">
										
										<label for="username">Username</label>
										<input type="text" class="form-control" id="username" name="username" value="">
										
										<label for="password">Password</label>
										<input type="password" class="form-control" id="password" name="password" value="">
										
										<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('password')">
											<i id="password_vis_i" class="material-icons" style="color:grey">visibility</i>
										</a>
									</br>
										<button type="submit" name="submit" class="btn btn-primary" value="1">Next</button>
								
								<?php } else if($step == 2){ /* select db name */
										if($dbs_err){ ?>
											<span class="error"><?=$dbs_err?></span>
											<button onclick="history.back()">Go Back</button>
										<?php
													} else { ?>										
											<span class="success">Successfully logged in to Geoserver</span>
											</br>
											<button type="submit" name="submit" class="btn btn-primary" value="1">Save</button>
										<?php }
								} ?>
						</div>			
							<?php	} //end if( step == 3) ?>
								</div>
							</form>
            </div>
            <footer class="footer text-center" style="background: #22303c"></footer>
        </div>
    </div>
</body>
</html>
