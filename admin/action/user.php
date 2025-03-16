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
			
				if(isset($_POST['save'])){
				
				    if($id > 0) {	// only updates
				        $result = $obj->getById($id);
    					$old_row = pg_fetch_assoc($result);
    					pg_free_result($result);
            
                        if($_POST['accesslevel'] != $old_row['accesslevel']){  // if user changes roles
                            if($_POST['accesslevel'] == 'Admin'){   // becomes admin
                                $email_user = explode('@', $old_row['email'])[0];
                                $_POST['ftp_user']    = user_Class::uniqueName($email_user);
                                $_POST['pg_password'] = user_Class::randomPassword();
                            }else if($old_row['accesslevel'] == 'Admin'){ // drops admin
                                user_Class::delete_ftp_user($old_row['ftp_user']);
                                $database->drop_user($old_row['ftp_user']);
                                $_POST['ftp_user'] = $_POST['pg_password'] = '';
                            }
                        }
                        
    					if($obj->update($_POST) > 0){
                            $result = $obj->getById($id);
                            $row = pg_fetch_assoc($result);
                            pg_free_result($result);
                            
    						if($_POST['accesslevel'] == 'Admin'){
                                if(empty($old_row['ftp_user'])){    //if user wasn't admin
                                    user_Class::create_ftp_user($row['ftp_user'], $row['email'], $row['password']);
                    				$database->create_user($row['ftp_user'], $row['pg_password']);
                                }else if($old_row['password'] != $row['password']){ //if password is changed
                                    user_Class::update_ftp_user($row['ftp_user'], $row['password']);
                                }
    						}
    						$result = ['success' => true, 'message' => 'User Successfully Updated!',
                                'id' => $id, 'password' => $row['password'] ];
    					}else{
    						$result = ['success' => false, 'message' => 'Failed to update user!'];
    					}
					}else{
					    if($_POST['accesslevel'] == 'Admin'){
                            $email_user = explode('@', $_POST['email'])[0];
                            $_POST['ftp_user'] = user_Class::uniqueName($email_user);
                            $_POST['pg_password'] = user_Class::randomPassword();
						}else{
						  $_POST['ftp_user'] = $_POST['pg_password'] = '';
						}
                    
        				$newId = $obj->create($_POST);
        				if($newId > 0){
                            if($_POST['accesslevel'] == 'Admin'){
                                $result = $obj->getById($newId);
                                $row = pg_fetch_assoc($result);
                                pg_free_result($result);
                                
                                user_Class::create_ftp_user($_POST['ftp_user'], $_POST['email'], $row['password']);
                        	    $database->create_user($_POST['ftp_user'], $_POST['pg_password']);
                            }

           					$result = ['success' => true, 'message' => 'User Successfully Created!', 'id' => $newId];
        				}else{
       					    $result = ['success' => false, 'message' => 'Failed to update user!'];
        				}
					}
        
				} else if(isset($_POST['delete']) && ($id != SUPER_ADMIN_ID)) {

					$ref_ids = array();
					$ref_name = null;
					$tbls = array('pglink', 'gslink');
					
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
							$database->drop_user($row['ftp_user']);
						}
	          $result = ['success' => $ret_val, 'message' => 'Data Successfully Deleted!'];
					}
        }
    }

    echo json_encode($result);
?>
