<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/map.php');
		require('../class/permalink.php');
		
    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
			
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
			$obj			= new permalink_Class($database->getConn());
			$id 			= 0;
			
			if($_SERVER['REQUEST_METHOD'] == 'GET'){	// if called from map directly
				// make a 1 visit permalink, expiring in 1 hour
				if(!empty($_GET['permalink'])){
					$row = $obj->getMap($_GET['permalink']);
					$_POST['map_id'] = $row['map_id'];
					$_POST['query'] = str_replace('permalink='.$_GET['permalink'].'&', '', $_SERVER['QUERY_STRING']);
					$_POST['page'] = $row['page'];
				}else{
					$_POST['map_id'] = $_GET['id'];
					$_POST['query'] = $_GET['loc'];
					$_POST['page'] = $_GET['page'];
				}
				$_POST['description'] = 'Permalink for map '.$_POST['map_id'];
				$_POST['visits_limit'] = 1;
				$_POST['expires'] = "1 hour";
				$_POST['save'] = true;
			}else{
				$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
			}
			
				if(isset($_POST['save'])) {
          $newId = 0;

          if($id > 0) { // update
            if($obj->update($_POST)){
							$result = ['success' => true, 'message' => 'Permalink Successfully Updated!'];
						}else{
							$result = ['success' => false, 'message' => 'Permalink Not Updated!'];
						}
          
					} else { // insert
						
						$map_obj = new map_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
						$res 		 = $map_obj->getById($_POST['map_id']);
						$map_row = pg_fetch_assoc($res);
						
						$hash_data  = $_SERVER['HTTP_USER_AGENT'].date('m/d/Y h:i:s a', time());
						$hash_data .= $_POST['map_id'].$_POST['description'].$_POST['expires'].$_POST['visits_limit'];
						$_POST['hash'] = hash('md5', $hash_data);
            
						list($newId,$created,$expires) = $obj->create($_POST);
						if($newId > 0){

							$perma_url = 'apps/'. $_POST['map_id'].'/'.$_POST['page'].'?permalink='.$_POST['hash'];
							
							$result = ['success' => true, 'message' => 'Data Successfully Saved!',
								'id' => $newId, 'hash' => $_POST['hash'], 'url'=> $perma_url,'created'=>$created, 'expires'=>$expires ];
								
						}else{
							$result = ['success' => false, 'message' => 'Data Not Saved!'];
						}
          }

          
      } else if(isset($_POST['delete'])) {
				if($obj->delete($id)){
					$result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
				}else{
					$result = ['success' => false, 'message' => 'Error: Data Not Deleted!'];
				}
      }
    }

    echo json_encode($result);
?>
