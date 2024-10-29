<?php
if(!IS_PUBLIC){
	if(!isset($_SESSION[SESS_USR_KEY])) {
		die('Not logged in!');
	}else if($_SESSION[SESS_USR_KEY]->id != USER_ID){
		die('Access denied!');
	}
}
readfile('DATA_FOR');
?>