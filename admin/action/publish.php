<?php
session_start(['read_and_close' => true]);
require('../incl/const.php');
require('../class/database.php');
require('../incl/jru-lib.php');

function rrmdir($dir) {
 if (is_dir($dir)) {
	 $objects = scandir($dir);
	 foreach ($objects as $object) {
		 if ($object != "." && $object != "..") {
			 if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
				 rrmdir($dir. DIRECTORY_SEPARATOR .$object);
			 else
				 unlink($dir. DIRECTORY_SEPARATOR .$object);
		 }
	 }
	 rmdir($dir);
 }
}

function unzip_me($zipname){
	$ext_dir = '/tmp/ext/';
	if(!is_dir($ext_dir)){
		mkdir($ext_dir);
	}

	$zip = new ZipArchive;
	$res = $zip->open($zipname);
	if ($res === TRUE) {
		$zip->extractTo($ext_dir);
		$zip->close();
	} else {
		echo 'Error: Failed to open'.$zipname;
	}
	return $ext_dir;
}

$result = ['success' => false, 'message' => 'Error while processing your request!'];

if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {

	if(!isset($_FILES['source'])){
		header("Location: ../publish.php&error=No source");
		return;
	}
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$upload_path = get_jasper_home().'/reports';
	$dest_dir = $upload_path.'/'.$_POST['destination'];

	#check if upload dir has .. in path
	if(preg_match('/\.\./', $dest_dir)){
		header("Location: ../publish.php&error=Invalid destination");
		return;
	}

	$files = array();	#file to be published

	#Check if its a .zip or .jar
	$source_dir = NULL;
	if(isset($_POST['publish_extract'])){
		$source_dir 	= unzip_me($_FILES["source"]["tmp_name"]);

		$banned = array('.', '..');

		$cdir = scandir($source_dir);
   	foreach ($cdir as $key => $value){
			if(!in_array($value, $banned)){
				$files[$value] = $source_dir.'/'.$value;
			}
		}
	}else{
		$files[$_FILES["source"]["name"]] = $_FILES["source"]["tmp_name"];
	}

	foreach($files as $name => $fpath) {
		move_uploaded_file($fpath, $dest_dir.'/'.$name);
		chmod($dest_dir.'/'.$name, 0644);
		exec('sudo /usr/local/bin/chown_ctl.sh '.$dest_dir.'/'.$name);
	}

	if(!is_null($source_dir) && is_dir($source_dir)){
		rrmdir($source_dir);
	}

	header("Location: ../publish.php?success=Upload succeeded!");

}else {
	header("Location: ../index.php");
}
