<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('class/user.php');
require('class/database.php');
require('class/datasource.php');

if(file_exists('incl/const.php')){
	require('incl/const.php');
}

function load_sample_data($database){
	
	$dbname = 'beedatabase';
	
	// sample datasource
	$ds = ['name' => 'beedatabase', 'type' => 'jndi', 'url' => 'jdbc:postgresql://'.DB_HOST.':'.DB_PORT.'/'.$dbname, 'username' => 'admin1', 'password' => user_Class::randomPassword()];
	// sample schedules
	$schs = [
		['cron_period' => 'weekly', 'name' => 'lov-parameter', 		'format' => 'pdf','datasource_id' => 1, 'filename' => 'LOVParameter.pdf',		'email' => '', 'email_subj' => '', 'email_body' => '', 'url_opt_params' => '', 'noemail' => true, 'email_tmpl' => ''],
		['cron_period' => 'hourly', 'name' => 'query-parameter', 	'format' => 'pdf','datasource_id' => 1, 'filename' => 'QueryParameter.pdf', 'email' => '', 'email_subj' => '', 'email_body' => '', 'url_opt_params' => '', 'noemail' => true, 'email_tmpl' => ''],
		['cron_period' => 'hourly', 'name' => 'SimpleBees', 			'format' => 'pdf','datasource_id' => 1, 'filename' => 'SimpleBees.pdf',			'email' => '', 'email_subj' => '', 'email_body' => '', 'url_opt_params' => '', 'noemail' => true, 'email_tmpl' => '']
	];
	
	$reps = [
		['repname' => 'SimpleBees', 			'datasource_id' => 1, 'download_only' => 'html', 'outname' => 'SimpleBees', 			'name' => 'Simple Bee Report',	'is_grouped' => 0, 'description' => 'Simple Bee Chart', 		'accgrps[]' => 1],
		['repname' => 'query-parameter',	'datasource_id' => 1, 'download_only' => 'html', 'outname' => 'query-parameter', 	'name' => 'Query Parameter', 		'is_grouped' => 0, 'description' => 'Query Parameter Demo',	'accgrps[]' => 1],
		['repname' => 'lov-parameter',		'datasource_id' => 1, 'download_only' => 'html', 'outname' => 'lov-parameter',		'name' => 'LOV Parameter', 			'is_grouped' => 0, 'description' => 'LOV Parameter Demo',		'accgrps[]' => 1],
	];
	
	$params = [
		['reportid' => 3, 'ptype' => 'dropdown', 'pvalues' => 'Apis Mellifera Carnica,Apis Mellifera,Apis Mellifera Mellifera', 'pname' => 'beespecies'],
		['reportid' => 2, 'ptype' => 'query', 	 'pvalues' => 'beespecies,beekeeper',																						'pname' => 'Select Species']
	];

	// create user and db
	$database->create_user($ds['username'], $ds['password']);
	if(!$database->create_user_db($dbname, $ds['username'], $ds['password'])){
		$err = pg_last_error($database->getConn());
		die();
	}
	
	$exts = ['hstore', 'postgis'];
	$dsdb = new Database(DB_HOST, $dbname, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dsdb->create_extensions($exts);
	pg_close($dsdb->getConn());
	
	
	$proto = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	$post_url = $proto.'://'.$_SERVER['HTTP_HOST'].'/admin/action';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/sample.cookie');

	// login
	$auth = ['email' => $_POST['super_admin_email'], 'pwd' => $_POST['super_admin_pass'], 'submit' => 'Submit'];
	curl_setopt($ch, CURLOPT_URL, $post_url.'/login.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
	$response = curl_exec($ch);
	
	curl_setopt($ch, CURLOPT_COOKIEJAR, null);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/sample.cookie');
	
	// create datasource
	curl_setopt($ch, CURLOPT_URL, $post_url.'/datasource.php');
	$ds['save'] = 1;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ds);
	$response = curl_exec($ch);
	
	// create schedules
	foreach($schs as $sch){
		curl_setopt($ch, CURLOPT_URL, $post_url.'/schedule.php');
		$sch['create'] = 1;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sch);
		$response = curl_exec($ch);
	}
	
	// create reports
	foreach($reps as $rep){
		curl_setopt($ch, CURLOPT_URL, $post_url.'/report.php');
		$rep['save'] = 1;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $rep);
		$response = curl_exec($ch);
	}
	
	// create parameters
	foreach($params as $par){
		curl_setopt($ch, CURLOPT_URL, $post_url.'/parameter.php');
		$par['save'] = 1;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $par);
		$response = curl_exec($ch);
	}

	curl_close($ch);
	
	// connect to datasource db
	$dsdb = new Database(DB_HOST, $dbname, $ds['username'], $ds['password'], DB_PORT, DB_SCMA);
	if(!$dsdb->is_connected()){
		echo pg_last_error($dsdb->getConn());
		die();
	}
	
	// load dump
	$sql = file_get_contents('beedatabase.sql');
	$res = pg_query($dsdb->getConn(), $sql);
	if(!$res){
		echo pg_last_error($dsdb->getConn());
		die();
	}

	return 0;
}

$msg="";

$values = array();

$msg="";
$smtp_keys = ['host' => 'text', 'user' => 'text', 'pass' => 'password', 'port' => 'number'];

if(isset($_POST['submit'])){
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	if(!$database->is_connected()){
		$msg = pg_last_error($databse->getConn());
	}else{

		$sql = file_get_contents('setup.sql');
		
		$con = $database->getConn();
		
		$res = pg_query($con, $sql);
		if(!$res){
			echo pg_last_error($con);
			die();
		}
		
		$def_user = array('name' => $_POST['super_admin_name'], 'email' => $_POST['super_admin_email'], 'password' => $_POST['super_admin_pass'],
											'accesslevel' => 'Admin');
		$def_grp = array('name' => 'Default');
		$def_usr_grps = array('user_id' => 1, 'access_group_id' => 1);

		$def_user['password'] = password_hash($def_user['password'], PASSWORD_DEFAULT);
		
		// insert manually
		if(!pg_insert($con, 'public.user', 					$def_user) ||
			 !pg_insert($con, 'public.access_groups', $def_grp)  ||
			 !pg_insert($con, 'public.user_access',		$def_usr_grps) 	){
			die(pg_last_error($con));
		}

		# add SMTP configuration for mutt		
		$mutt_conf_file = '/var/www/.muttrc';
		$mutt_conf = '';
		if(is_file($mutt_conf_file)){
			$mutt_conf = file_get_contents($mutt_conf_file);
		}
		
		# ex. => set smtp_url = 'smtps://user@gmail.com:password@smtp.gmail.com:465/'
		$mutt_conf .= "\n";
		$mutt_conf .= "set from='Jasper Report Publisher <".$_POST['smtp_user'].">'\n";
		$mutt_conf .= "set smtp_url = 'smtp://".$_POST['smtp_user'].":".$_POST['smtp_pass']."@".$_POST['smtp_host'].":".$_POST['smtp_port']."/'\n";
		file_put_contents($mutt_conf_file, $mutt_conf);

		if(isset($_POST['load_sample_data'])){
			load_sample_data($database);
		}
		
		unlink('setup.sql');
		unlink('setup.php');

		header('Location: index.php');
	}
}

$values['smtp_host'] = defined('SMTP_HOST') ? SMTP_HOST : '';
$values['smtp_port'] = defined('SMTP_PORT') ? SMTP_PORT : '';
$values['smtp_user'] = defined('SMTP_USER') ? SMTP_USER : '';
$values['smtp_pass'] = defined('SMTP_PASS') ? SMTP_PASS : '';
?>

<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>PHP Installer</title>
      <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
			<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
table{
	width:30% !important; text-align:center; margin:auto; margin-top:70px;
}
.success{color:green;}
.error{color:red;}
.frm{width:70% !important; margin:auto; margin-top:100px;
}
</style>

<script>
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
</script>

   </head>
   <body>

      <main role="main" class="container">
         <?php
			if((isset($_GET['step'])) && $_GET['step']==2){
				?>
				<div align="center"><p>&nbsp;</p><img src="../assets/images/login_box.png" style="width:10%"></div>
				<div align="center"><p>&nbsp;</p>Jasper Publisher Installer</div>


				<form class="frm" method="post">
					
					<div>
						<fieldset>
							<legend>App</legend>
							<div class="form-group">
								<input type="text" class="form-control" placeholder="super admin name" id="super_admin_name" name="super_admin_name" value="John Doe">
								<input type="text" class="form-control" placeholder="super admin email" id="super_admin_email" name="super_admin_email" value="admin@admin.com">
								<input type="password" class="form-control" placeholder="super admin pass" id="super_admin_pass" name="super_admin_pass" value="1234">
								<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('super_admin_pass')">
									<i id="super_admin_pass_vis_i" class="material-icons" style="color:grey">visibility</i>
								</a>
							</div>
						</fieldset>
					</div>
				
					<div>
						<fieldset>
							<legend>SMTP details:</legend>
							<div class="form-group">
							<?php foreach($smtp_keys as $k => $t){ ?>
								<input type="<?=$t?>" class="form-control" placeholder="<?=$k?>" id="smtp_<?=$k?>" name="smtp_<?=$k?>" value="<?=$values['smtp_'.$k]?>" required>
								<?php if($k == 'pass'){ ?>
									<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('smtp_pass')">
										<i id="smtp_pass_vis_i" class="material-icons" style="color:grey">visibility</i>
									</a>
								<?php } ?>
							<?php } ?>
							</div>
						</fieldset>
					</div>
					
					<div>
						<fieldset>
						<legend>Options</legend>
							<div class="form-group">
								<input type="checkbox" class="form-checkbox" placeholder="sample data" name="load_sample_data" value="1"/>
								<label for="load_sample_data">Preload Sample Data</label>
							</div>
						</fieldset>
					</div>
				
				  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
				  <span class="error"><?=$msg?></span>
				</form>

			<?php
		}else{
		?>

	  <div align="center"><p>&nbsp;</p>Jasper Publisher Installer</div>

         <table class="table">
		  <thead>
			<tr>
			  <th scope="col">Requirement</th>
			  <th scope="col">Status</th>
			</tr>
		  </thead>
		  <tbody>
			<tr>
			  <th scope="row">PHP Version</th>
			  <td>
				<?php
					$is_error="";
					$php_version=phpversion();
					if($php_version>5){
						echo "<span class='success'>".$php_version."</span>";
					}else{
						echo "<span class='error'>".$php_version."</span>";
						$is_error='yes';
					}
				?>
			  </td>
			</tr>
			<tr>
			  <th scope="row">Session Working</th>
			  <td>
				<?php
				$_SESSION['IS_WORKING']=1;
				if(!empty($_SESSION['IS_WORKING'])){
					echo "<span class='success'>Yes</span>";
				}else{
					echo "<span class='error'>No</span>";
					$is_error='yes';
				}
				?>
			  </td>
			</tr>

			<tr>
			  <td colspan="2">
				<?php
				if($is_error==''){
					?>
					<a href="?step=2"><button type="button" class="btn btn-success">Next</button></a>
					<?php
				}else{
					?><button type="button" class="btn btn-danger">Errors</button><br><br>Please fix above error(s) and try again<?php
				}
				?>
			  </td>
			</tr>
		  </tbody>

		</table>
		<?php }?>

      </main>

      <script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/popper.min.js"></script>
      <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>
   </body>
</html>
