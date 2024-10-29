<?php
session_start(['read_and_close' => true]);
require('../../admin/incl/const.php');
require('../../admin/class/database.php');
require('../../admin/class/permalink.php');
require('env.php');

$loc = null;
$permalink = '';

if(!IS_PUBLIC){
	if(!isset($_SESSION[SESS_USR_KEY])) {
	  header('Location: ../../login.php');
	  exit;
	}
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	if(!$database->check_user_map_access(MAP_ID, $_SESSION[SESS_USR_KEY]->id)){
		die('Sorry, access not allowed!');
	}
}

if(isset($_GET['permalink'])){
	
	$permalink = '?permalink='.$_GET['permalink'];
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$obj = new permalink_Class($database->getConn(), 0);
	
	if(defined('PERMALINK_DONT_COUNT')){	// called from data_{file,gs,pg}
		$row = $obj->getMap($_GET['permalink'], 0);
	}else{
		$row = $obj->getMap($_GET['permalink'], 1);
	}
	
	if($row == null){
		die('Sorry permalink is invalid or expired!');
	}
	
	if($row['map_id'] != MAP_ID){
		die('Sorry permalink is not for this map!');
	}
	
	$loc = explode('/', $row['query']);	// 11/41.8036/-87.6407
}
?>