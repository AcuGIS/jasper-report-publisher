<?php
const PERMALINK_DONT_COUNT = true;
include('../../admin/incl/index_prefix.php');

$f = str_replace('..', '', $_GET['f']);
$fpath = DATA_DIR.'/'.MAP_ID.'/'.$f;

if(!empty($_GET['f']) && is_file($fpath)){
	header('Content-Type: '.mime_content_type($fpath));
	header('Content-disposition: attachment; filename="'.$f.'"');
	readfile($fpath);
}else{
	header("HTTP/1.1 400 Bad Request");
}
?>
