<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
		require('../class/pglink.php');

    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && $_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {
				$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
				$obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
				$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
			
				if(($id > 0) && !$obj->isOwnedByUs($id)){
					$result = ['success' => false, 'message' => 'Action not allowed!'];
				
        }else if(isset($_POST['save'])) {
            $newId = 0;
						$svc_created = true;
						
            if($id) {
							
							$pg_res = $obj->getById($id);
							$pgr = pg_fetch_assoc($pg_res);
							pg_free_result($pg_res);
							
							$cmd = null;
							if(!empty($_POST['svc_name'])){
								$cmd = empty($pgr['svc_name']) ? 'add' : 'edit';

								if($obj->pg_service_ctl($cmd, $_POST) != 0){
									$svc_created = false;
								}

							}else if(!empty($pgr['svc_name'])){
								if($obj->pg_service_ctl('del', $pgr) != 0){
									$svc_created = false;
								}
							}
							
              if($svc_created && $obj->update($_POST)){
								$newId = $id;
							}
            } else {
							if(!empty($_POST['svc_name']) && $obj->pg_service_ctl('add', $_POST) != 0){
								$svc_created = false;
							}
							
							if($svc_created){
              	$newId = $obj->create($_POST);
							}
            }
						
						if($newId == 0){
							$result = ['success' => false, 'message' => 'PG Link create/update failed!'];
						}else{
							$result = ['success' => true, 'message' => 'PG Link successfully created/updated!', 'id' => $newId];
						}
        
				} else if(isset($_POST['delete'])) {
						
						$pg_res = $obj->getById($id);
						$pgr = pg_fetch_assoc($pg_res);
						pg_free_result($pg_res);
					
						
						$ref_ids = array();
						$tbls = array('map_pglink');
						
						foreach($tbls as $k){
							$rows = $database->getAll('public.'.$k, 'pglink_id = '.$id);
							foreach($rows as $row){
								$ref_ids[] = $row['map_id'];
							}
							
							if(count($ref_ids) > 0){
								break;
							}
						}						
						
						if(count($ref_ids) > 0){
							$result = ['success' => false, 'message' => 'Error: Can\'t delete because pglink is used in '.count($ref_ids).' maps(s) with ID(s) ' . implode(',', $ref_ids) . '!' ];
						
						}else if(!$obj->delete($id)){
							$result = ['success' => false, 'message' => 'PG Link Not Deleted!'];
						}else{
							if(isset($pgr['svc_name'])){
								$obj->pg_service_ctl('del', $pgr);
							}
							
							if(isset($_POST['drop'])){
								$database->drop($pgr['dbname']);
							}
							$result = ['success' => true, 'message' => 'PG Link Successfully Deleted!'];
						}
        } else if(isset($_POST['pwd_vis'])) {
					
					$proj_pass = $obj->getPassword($id);
					if($proj_pass === FALSE){
						$result = ['success' => false, 'message' => 'Failed to get password!'];
					}else{
						$result = ['success' => true, 'message' => $proj_pass];
					}
				} else if(isset($_POST['conn_info'])) {
					
					$conn_info = $obj->getConnInfo($id);
					if($conn_info === FALSE){
						$result = ['success' => false, 'message' => 'Failed to get conn_info!'];
					}else{
						if(str_starts_with($conn_info, 'host=localhost ')){
							$conn_info = 'host='.gethostname(). ' '.substr($conn_info, 15);
						}
						$result = ['success' => true, 'message' => $conn_info];
					}
				
				} else if(isset($_POST['schemas'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], "public");
					list($schemas, $err) = $proj_db->getSchemas($pgr['dbname'], $pgr['username']);
					$result = ['success' => true, 'schemas' => $schemas];
				
				} else if(isset($_POST['tables'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], $_POST['schema']);
					list($tables, $err) = $proj_db->getTables($_POST['schema']);
					$result = ['success' => true, 'tables' => $tables];
				
				} else if(isset($_POST['geoms'])) {
					$pg_res = $obj->getById($id);
					$pgr = pg_fetch_assoc($pg_res);
					pg_free_result($pg_res);
					
					$proj_db = new Database($pgr['host'], $pgr['dbname'], $pgr['username'], $pgr['password'], $pgr['port'], $_POST['schema']);
					list($geoms, $err)	= $proj_db->getGeomColumns($_POST['schema'], $_POST['tbl']);
					$result = ['success' => true, 'geoms' => $geoms];
				}
    }

    echo json_encode($result);
?>
