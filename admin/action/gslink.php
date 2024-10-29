<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
		require('../class/gslink.php');

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
				$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
				$obj = new gslink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
			
				if(($id > 0) && !$obj->isOwnedByUs($id)){
					$result = ['success' => false, 'message' => 'Action not allowed!'];
				
        }else if(isset($_POST['save'])) {
            $newId = 0;

            if($id) {
              if($obj->update($_POST)){
								$newId = $id;
							}
            } else {
              $newId = $obj->create($_POST);
            }
						
						if($newId == 0){
							$result = ['success' => false, 'message' => 'GS Link create/update failed!'];
						}else{
							$result = ['success' => true, 'message' => 'GS Link successfully created/updated!', 'id' => $newId];
						}
        
				} else if(isset($_POST['delete'])) {
						
						$ref_ids = array();
						$tbls = array('map_gslink');
						
						foreach($tbls as $k){
							$rows = $database->getAll('public.'.$k, 'gslink_id = '.$id);
							foreach($rows as $row){
								$ref_ids[] = $row['map_id'];
							}
							
							if(count($ref_ids) > 0){
								break;
							}
						}						
						
						if(count($ref_ids) > 0){
							$result = ['success' => false, 'message' => 'Error: Can\'t delete because gslink is used in '.count($ref_ids).' maps(s) with ID(s) ' . implode(',', $ref_ids) . '!' ];
					
						}else if(!$obj->delete($id)){
							$result = ['success' => false, 'message' => 'GS Link Not Deleted!'];
						}else{
							$result = ['success' => true, 'message' => 'GS Link Successfully Deleted!'];
						}
        } else if(isset($_POST['pwd_vis'])) {
					
					$proj_pass = $obj->getPassword($id);
					if($proj_pass === FALSE){
						$result = ['success' => false, 'message' => 'Failed to get password!'];
					}else{
						$result = ['success' => true, 'message' => $proj_pass];
					}
				
				} else if(isset($_POST['workspaces'])) {
					$gs_res = $obj->getById($id);
					$gsr = pg_fetch_assoc($gs_res);
					pg_free_result($gs_res);
					
					$result = $obj->getWorkspaces($gsr['url'], $gsr['username'], $gsr['password']);
				
				} else if(isset($_POST['layers'])) {
					$gs_res = $obj->getById($id);
					$gsr = pg_fetch_assoc($gs_res);
					pg_free_result($gs_res);
					
					$result = $obj->getLayers($gsr['url'], $gsr['username'], $gsr['password'], $_POST['workspace']);
				}
    }

    echo json_encode($result);
?>
