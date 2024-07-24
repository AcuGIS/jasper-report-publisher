<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/access_groups.php');

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    	$obj = new access_group_Class($database->getConn());
		
        if(isset($_POST['save'])) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $newId = 0;

            if($id) { // update
              $newId = $obj->update($_POST) ? $id : 0;
            } else { // insert
              $newId = $obj->create($_POST);
            }

						if($newId > 0){
							$result = ['success' => true, 'message' => 'Access Group successfully created!', 'id' => $newId];
						}else{
							$result = ['success' => false, 'message' => 'Failed to save Acccess Group!'];
						}
        } else if(isset($_POST['delete'])) {
						$id = intval($_POST['id']);
	
						$ref_ids = array();
						$ref_name = null;
						$acc_tbls = array('user', 'link', 'group', 'report');
						
						foreach($acc_tbls as $k){
							$rows = $database->getAll($k.'_access', 'access_group_id = '.$id);							
							foreach($rows as $row){
								$ref_ids[] = $row[$k.'_id'];
							}
							
							if(count($ref_ids) > 0){
								$ref_name = $k;
								break;
							}
						}						
						
						if(count($ref_ids) > 0){

							$result = ['success' => false, 'message' => 'Error: Can\'t delete because '.$ref_name.'(s) ' . implode(',', $ref_ids) . ' rely on access group!' ];
						
						}else if($obj->delete($id)){

							$result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
						}else{
							$result = ['success' => false, 'message' => 'Error: Data Not Deleted!'];
						}
        }
    }

    echo json_encode($result);
?>
