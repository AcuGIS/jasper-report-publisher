<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/link.php');

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	    $obj = new link_Class($database->getConn());
			$users = $obj->getRows();
		
        if(isset($_POST['save'])) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $newId = 0;

            if($id) { // update
              $newId = $obj->update($_POST) ? $id : 0;
            } else { // insert
              $newId = $obj->create($_POST);
            }

						if($newId > 0){
							$result = ['success' => true, 'message' => 'Link successfully created!', 'id' => $newId];
						}else{
							$result = ['success' => false, 'message' => 'Failed to save Link!'];
						}
        } else if(isset($_POST['delete'])) {
					if($obj->delete(intval($_POST['id']))){
						$result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
					}else{
						$result = ['success' => false, 'message' => 'Error: Data Not Deleted!'];
					}
        }
    }

    echo json_encode($result);
?>
