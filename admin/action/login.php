<?php
	session_start();
	require('../incl/const.php');
	require('../class/database.php');
	require('../class/user.php');

	if(isset($_SESSION[SESS_USR_KEY])) {
		header("Location: ../../index.php");
	}

	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$user_obj = new user_Class($database->getConn(), 0);

	if(isset($_POST['submit'])&&!empty($_POST['submit'])){
			$row = $user_obj->loginCheck($_POST['pwd'], $_POST['email']);
			if($row){
				$_SESSION[SESS_USR_KEY] = $row;
				header("Location: ../../index.php");
			}else{			
				header("Location: ../../login.php?err=".urlencode('Error: Failed to login!'));
			}
	}

?>