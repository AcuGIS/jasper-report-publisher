<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/backend.php');

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(	isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin'){
			$obj = new backend_Class();
			$rv = $obj->svc_ctl($_POST['svc'], $_POST['op']);
		
			if($rv != 0){
				$result = ['success' => false, 'message' => 'Error: '.$_POST['op'].' '.$_POST['svc'].'!'];
			}else{
				$result = ['success' => true, 'message' => 'Success: '.$_POST['op'].' '.$_POST['svc'].'!'];
			}
    }

    echo json_encode($result);
?>
