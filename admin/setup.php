<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('class/user.php');
require('class/database.php');
require('class/datasource.php');
require('class/app.php');

function post_example($auth, $map){
	$proto = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	$post_url = $proto.'://'.$_SERVER['HTTP_HOST'].'/admin/action';
	$cookie = '/tmp/sample.cookie';
	
	// login
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	curl_setopt($ch, CURLOPT_URL, $post_url.'/login.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
	$response = curl_exec($ch);
	
	// post map
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, null);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $post_url.'/map.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $map);
	$response = curl_exec($ch);
	$response_js = json_decode($response);
	if($response_js == null){
		$fp = fopen('/tmp/response.txt', 'a');
		fwrite($fp, '[post_example] '.$response."\n");
		fclose($fp);
	}
	
	curl_close($ch);
	return $response_js->id;
}

function load_r_examples($database, $pgl){

	$auth = ['email' => $_POST['super_admin_email'], 'pwd' => $_POST['super_admin_pass'], 'submit' => 'Submit'];
	
	# create 3js map	
	$zip_file = curl_file_create('../examples/threejs.zip','application/zip', 'threejs.zip');
	$map = ['save' => 1, 'name' => 'Qgis2threejs Map', 'description' => 'Simple Bee qgis2threejs Map',
			'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
			'infobox_content' => '<p>ThreeJS map with WebGL</p>',
			'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create plotly_pg
	$map_source_r = file_get_contents('../examples/plotly_pg/index.R');
	$map_source_r = str_replace(['$DB_NAME', '$DB_USER', '$DB_PASS'], [$pgl['name'], $pgl['username'], $pgl['password']], $map_source_r);
	
	$map = ['save' => 1, 'name' => 'Area harvest', 'description' => 'Average harvest per area',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Apiary average harvest per area ID.</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create multiple charts
	$map_source_r = file_get_contents('../examples/multiple_charts/index.R');
	$map = ['save' => 1, 'name' => 'Multiple Charts', 'description' => 'Multiple Charts with Plotlyjs',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Plot of multiple charts.</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create choropleth
	$map_source_r = file_get_contents('../examples/choropleth/index.R');
	$map = ['save' => 1, 'name' => 'R Choropleth', 'description' => 'Density of USA',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Choropleth map with R/Leaflet and data from GeoJSON</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create covid.R
	$zip_file = curl_file_create('../examples/covid1.zip','application/zip', 'covid1.zip');
	$map = ['save' => 1, 'name' => 'R Animated', 'description' => 'R Animated',
		'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Choropleth map with R/Leaflet and data from CSV</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create tables
	$zip_file = curl_file_create('../examples/tables.zip','application/zip', 'tables.zip');
	$map = ['save' => 1, 'name' => 'R Tables', 'description' => 'R Tables with KableExtra',
		'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Page with R/KabelExtra tables</p>',
		'thismap_css' => '/* map specific CSS */', 'cron_period' => 'never', 'cron_custom' => '*/30 * * * *'
	];
	post_example($auth, $map);
	
	# create report1
	$map_source_r = file_get_contents('../examples/report1/skimr0.Rmd');
	$map = ['save' => 1, 'name' => 'RMarkdow Report', 'description' => 'My Super Fancy Report',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>RMarkdown report example</p>',
		'thismap_css' => '/* map specific CSS */', 'cron_period' => 'never', 'cron_custom' => '*/30 * * * *'
	];
	post_example($auth, $map);
}

function load_simple_bee($database, $pgl){
	
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
	
	// create pg link
	curl_setopt($ch, CURLOPT_URL, $post_url.'/pglink.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$pgl['save'] = 1;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pgl);
	$response = curl_exec($ch);
	$response_js = json_decode($response);
	if($response_js == null){
		$fp = fopen('/tmp/response.txt', 'a');
		fwrite($fp, '[create pg link] '.$response."\n");
		fclose($fp);
	}
	
	$pglink_id = $response_js->id;

	// install map
	$zip_file = curl_file_create('../examples/simple_bee.zip','application/zip', 'simple_bee.zip');
	$qgs_file = curl_file_create('../examples/simple_bee_farming.qgs','application/octet-stream', 'simple_bee_farming.qgs');
	$map = ['save' => 1, 'name' => 'Simple Bee Map', 'description' => 'Simple Bee qgis2web Map', 'from_type' => 'archive',
		'qgis_file[]' => $qgs_file, 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Enter information to be displayed, when your map Info button is clicked.</p>',
		'thismap_css' => '.leaflet-popup-content > table img {width: 300px;}'."\n".
										 '.leaflet-popup-content > img { width: 300px;}'
	];
	
	$map_id = post_example($auth, $map);
	
	// update map
	unset($map['from_type']);
	unset($map['app']);
	unset($map['archive']);
	
	
	$map['save'] = 1;
	$map['id'] = $map_id;	// set ID, so we update
	
	$map['data_type0'] = 'pg';
	$map['pglink_id0'] = $pglink_id;
	$map['pg_schema0'] = 'public';
	$map['pg_tbl0'] 	 = 'fields';
	$map['pg_geom0'] 	 = 'geom';
	$map['pg_cache_val0'] = 0;
	$map['pg_cache_per0'] = 'Off';
	
	$map['data_type1'] = 'pg';
	$map['pglink_id1'] = $pglink_id;
	$map['pg_schema1'] = 'public';
	$map['pg_tbl1'] 	 = 'apiary';
	$map['pg_geom1'] 	 = 'geom';
	$map['pg_cache_val1'] = 0;
	$map['pg_cache_per1'] = 'Off';
	
	$map['qgis_layout'] = 'Bees in Laax';
	
	curl_setopt($ch, CURLOPT_URL, $post_url.'/map.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $map);
	$response = curl_exec($ch);

	curl_close($ch);

	return 0;
}

function load_sample_data($database, $def_user, $dbname){
	
	// sample datasource
	$ds = ['name' => 'beedatabase', 'type' => 'jndi', 'url' => 'jdbc:postgresql://'.DB_HOST.':'.DB_PORT.'/'.$dbname, 'username' => $def_user['ftp_user'], 'password' => $def_user['pg_password']];
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
	
	$contexts = [
		['input' => '<h3>Bee Query</h3><p>Enter one of the bee Species and Beekeeper Names listed below:</p><p><strong><u>Species:</u></strong></p><p>Apis Mellifera</p><p>Apis Mellifera Carnica</p><p>Apis Mellifera Mellifera</p><p>&nbsp;</p><p><strong><u>Beekeeper Names:</u></strong></p><p>Erasmus of Rotterdam</p><p>Galileo Galilei</p><p>Isaac Newton</p><p>This is an example of a <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/context/index.html"><strong>Report Context</strong></a>. &nbsp;</p><p>You can add a Context to any Report.</p><p>&nbsp;</p>', 'name' => 'Selection', 'report_id' => 2]
	];

	// create user and db
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
	
	// create contexts
	foreach($contexts as $ctx){
		curl_setopt($ch, CURLOPT_URL, $post_url.'/context.php');
		$ctx['save'] = 1;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $ctx);
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
	$sql = file_get_contents('../examples/beedatabase.sql');
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

if(file_exists('incl/const.php')){
	require('incl/const.php');
}

if(isset($_POST['submit'])){
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	if(!$database->is_connected()){
		$msg = pg_last_error($database->getConn());
	}else{

		$sql = file_get_contents('setup.sql');
		
		$con = $database->getConn();
		
		$res = pg_query($con, $sql);
		if(!$res){
			echo pg_last_error($con);
			die();
		}
		
		$def_user = array('name' => $_POST['super_admin_name'], 'email' => $_POST['super_admin_email'], 'password' => $_POST['super_admin_pass'],
											'ftp_user' => 'admin1', 'pg_password' => user_Class::randomPassword(), 'accesslevel' => 'Admin');
		$def_grp = array('name' => 'Default');
		$def_usr_grps = array('user_id' => 1, 'access_group_id' => 1);
		
		$def_user['password'] = password_hash($def_user['password'], PASSWORD_DEFAULT);
		
		// insert manually
		if(!pg_insert($con, 'public.user', 					$def_user) ||
			 !pg_insert($con, 'public.access_group', $def_grp)  ||
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

		user_Class::create_ftp_user($def_user['ftp_user'], $def_user['email'], $def_user['password']);
		$database->create_user($def_user['ftp_user'], $def_user['pg_password']);

		if(isset($_POST['load_sample_data'])){
			$dbname = 'beedatabase';
			
			// sample pg link
			$pgl = ['name' => $dbname, 'host' => DB_HOST, 'port' => DB_PORT, 'dbname' => $dbname, 'svc_name' => 'beedatabase',
				'username' => $def_user['ftp_user'], 'password' => $def_user['pg_password']];
				
			load_sample_data($database, $def_user, $dbname);
			load_simple_bee($database, $pgl);
			load_r_examples($database, $pgl);
		}
		
		unlink('setup.sql');
		unlink('setup.php');
		App::rrmdir('../examples');

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
table{width:30% !important; text-align:center; margin:auto; margin-top:70px;}
.success{color:green;}
.error{color:red;}
.frm{width:70% !important; margin:auto; margin-top:50px;}

legend {
    float: left;
    width: 100%;
    padding: 0;
    margin-bottom: .5rem;
    font-size: 15px;
    line-height: inherit;
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
							<legend>Administrator</legend>
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
							<legend>SMTP</legend>
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
								<label for="load_sample_data">Load Sample Data (Recommended)</label>
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
