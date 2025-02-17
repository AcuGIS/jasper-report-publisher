<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/user.php');
		
    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	    $obj = new user_Class($database->getConn());
			$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
			
				if(isset($_POST['save']) && ($id > 0)) {	// only updates
					
					$newId = $obj->update($_POST);
					if($newId > 0){
						if($_POST['accesslevel'] == 'Admin'){
							
							$result = $obj->getById($id);
							$row = pg_fetch_assoc($result);
							pg_free_result($result);
							
							user_Class::update_ftp_user($row['ftp_user'], $row['password']);
						}
						$result = ['success' => true, 'message' => 'User Successfully Updated!', 'id' => $newId];
					}else{
						$result = ['success' => false, 'message' => 'Failed to update user!'];
					}
        
				} else if(isset($_POST['delete']) && ($id != SUPER_ADMIN_ID)) {

					$ref_ids = array();
					$ref_name = null;
					$tbls = array('user', 'map', 'access_groups', 'permalink');
					
					foreach($tbls as $k){
						$rows = $database->getAll('public.'.$k, 'owner_id = '.$id);							
						foreach($rows as $row){
							$ref_ids[] = $row['id'];
						}
						
						if(count($ref_ids) > 0){
							$ref_name = $k;
							break;
						}
					}						
					
					if(count($ref_ids) > 0){
						$result = ['success' => false, 'message' => 'Error: Can\'t delete because user is owner of '.count($ref_ids).' '.$ref_name.'(s) with ID(s) ' . implode(',', $ref_ids) . '!' ];
					}else {
						
						$result = $obj->getById($id);
						$row = pg_fetch_assoc($result);
						pg_free_result($result);
						
	          $ret_val = $obj->delete($id);
						if($ret_val){
							shell_exec('sudo /usr/local/bin/delete_ftp_user.sh '.$row['ftp_user']);
						}
	          $result = ['success' => $ret_val, 'message' => 'Data Successfully Deleted!'];
					}
        }
    }

    echo json_encode($result);
?>
