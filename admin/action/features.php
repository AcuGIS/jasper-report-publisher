<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/map.php');
		require('../class/app.php');
		require('../class/permalink.php');
		require('../../admin/class/access_groups.php');
		
    $result = ['success' => false, 'message' => 'Error while processing your request!'];
		
		$id = empty($_POST['id']) ? 0 : intval($_POST['id']);
		
		if(isset($_GET['permalink'])){

			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
			$obj = new permalink_Class($database->getConn(), 0);			
			$row = $obj->getMap($_GET['permalink'], 0);
			
			if(($row == null) || ($id != $row['map_id'])){
				$result = ['success' => false, 'message' => 'Sorry permalink is invalid or expired!!'];
			}else{
				$result['success'] = true;
			}

		}else if(isset($_SESSION[SESS_USR_KEY])) {

			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
			$acc_obj = new access_group_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);

			$usr_grps = $acc_obj->getByUserId($_SESSION[SESS_USR_KEY]->id);
			if(!count($usr_grps)){
				$result = ['success' => false, 'message' => 'Sorry, no access group!'];
			}else{
				$usr_grps = $acc_obj->getGroupMapGroups(array_keys($usr_grps));
				if(!count($usr_grps) || !isset($usr_grps[$id])){
					$result = ['success' => false, 'message' => 'Sorry, access not allowed!'];
				}else{
					$result['success'] = true;
				}
			}
		}

    if($result['success']) {

			if(isset($_POST['features'])) {
					
					$html_dir = APPS_DIR.'/'.$id;
					
					if(is_file($html_dir.'/proxy_qgis.php')){
						$content = file_get_contents($html_dir.'/proxy_qgis.php');
						if(preg_match('/const QGIS_FILENAME = \'(.*)\';/', $content, $matches)){
							
							$qgis_path = '';
							if(!isset($_POST['from_map'])){
								$qgis_path = '../apps/'.$id.'/';
							}
							
							$html = App::qgis_features_html($id, $matches[1], $qgis_path);
						}else{
							$html = '<p>QGIS Filename not found</p>';
						}

					}else{
						$html = '<p>No QGIS proxy found</p>';
					}
					
					$result = ['success' => true, 'html' => $html];
				}
    }

    echo json_encode($result);
?>
