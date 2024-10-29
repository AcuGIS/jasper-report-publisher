<?php
session_start(['read_and_close' => true]);
require('../incl/const.php');
require('../class/database.php');
require('../class/scheduleR.php');
require('../class/app.php');
require('../class/cron.php');

$result = ['success' => false, 'message' => 'Error while processing your request!'];
$optional_keys = array('email', 'email_subj', 'email_body', 'url_opt_params', /*'noemail',*/ 'email_tmpl');


if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
	echo json_encode($result);
	return;
}

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
$obj = new scheduleR_Class($database->getConn());
$id = empty($_POST['id']) ? 0 : intval($_POST['id']);

$_POST['mail_on_change_only'] = (empty($_POST['mail_on_change_only'])) ? 'f' : 't';

if(isset($_POST['rmd_sources'])){
	$map_id = intval($_POST['rmap_id']);
	$rmd_sources = App::getFilesByType(APPS_DIR.'/'.$rmap_id, 'Rmd');
	$cron = CRON::get($rmap_id);
	$result = ['success' => true, 'rmd_sources' => $rmd_sources, 'cron_period' => $cron['cron_period'], 'cron_custom' => $cron['cron_custom']];
	
}else if(isset($_POST['output_formats'])){
	$rmap_id = intval($_POST['rmap_id']);
	$data_dir = DATA_DIR.'/'.$rmap_id;
	$output_formats = AppR::rmd_get_formats($data_dir.'/index.R', $_POST['rmd_source']);
	$result = ['success' => true, 'output_formats' => array_values($output_formats)];

}else if(isset($_POST['save'])) {
		$newId = 0;

		if ($id > 0) {
			$newId = $obj->update($_POST) ? $id : 0;
		} else {
			$newId = $obj->create($_POST);
		}

		if($newId > 0){
			$result = ['success' => true, 'message' => 'Schedule successfully created!', 'id' => $newId];
		}else{
			$result = ['success' => false, 'message' => 'Failed to save Schedule!'];
		}
} else if(isset($_POST['delete'])) {
	if($obj->delete($id)){
		$result = ['success' => true, 'message' => 'Schedule Successfully Deleted!'];
	}else{
		$result = ['success' => false, 'message' => 'Error: Schedule Not Deleted!'];
	}
}

echo json_encode($result);