<?php
session_start(['read_and_close' => true]);
require('../incl/const.php');
require('../incl/jru-lib.php');
require('../class/database.php');
require('../class/schedule.php');
require('../class/datasource.php');

# if we are logged
if(!isset($_SESSION[SESS_USR_KEY])) {
	header('Location: ../../login.php');
	exit;
}

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);

# if we are logged and have file
if(!empty($_GET['file'])) {
	$jasper_home = get_jasper_home();

	$report_name = basename($_GET['file']);
	$report_path = $jasper_home.'/reports'.$_GET['file'];

	header("Content-type: ".mime_content_type($report_path));
	header('Content-Disposition: attachment; filename="'.$report_name.'"');

	readfile($report_path);

}else {	// run through GET or POST

	// build the command to run
	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		
		if(isset($_POST['id'])){
			$obj = new schedule_Class($database->getConn());
			$sched = $obj->getById($_POST['id']);
			if($sched === false){
				die('Error: Invalid schedule id!');
			}
		}else{
			$_POST['id'] = 1000000 + $_SESSION[SESS_USR_KEY]->id;
		}
		
		# check if schedule has to run now
		if($_POST['cron_period'] == 'now'){
			# convert datasource id to name
			$ds_obj = new datasource_Class($database->getConn());
			$ds_row = $ds_obj->getById($_POST['datasource_id']);
			$_POST['datasource'] = $ds_row['name'];
			
			build_sch_env($_POST, true);
		}
		
		$out_fmt = isset($_POST['out']) ? $_POST['out'] : 'txt';
		
		$cmd = build_cmd_line($_POST);
		
	} else {
		
		if(empty($_GET['id'])){
			die('Error: No schedule id!');
		}
		// check if schedule id is valid
		$obj = new schedule_Class($database->getConn());
		$sched = $obj->getById($_GET['id']);
		if($sched === false){
			die('Error: Invalid schedule id!');
		}
		
		$out_fmt = isset($_GET['out']) ? $_GET['out'] : 'txt';
		$cmd = build_cmd_line($sched);
	}
	
	$out = my_exec($cmd);
	$out = implode("\n", $out);
	
	if($out_fmt == 'txt'){
		echo $out;
	}else {
		$out = htmlspecialchars($out);
		$out = nl2br($out);
?>
	<!DOCTYPE html>
	<html>
	<head>
		<title>Run Report</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	</head>
	<body>
			<div>
				<?=$out?>
			</div>
	</body>
	</html>
<?php
	}
}
?>
