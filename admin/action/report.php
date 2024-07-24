<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
    require('../class/report.php');
		
    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	    $obj = new Report_Class($database->getConn());
			$users = $obj->getRows();
		
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
      
			if(isset($_POST['save'])) {
            $newId = 0;

            if($id) { // update
                $obj->update($_POST);
            }
            else { // insert
                $newId = $obj->create($_POST);
            }

            $result = ['success' => true, 'message' => 'Data Successfully Saved!', 'id' => $newId];
        }

        else if(isset($_POST['delete'])) {
					
					$ref_ids = array();
					$ref_name = null;
					$acc_tbls = array('inputs');
					
					foreach($acc_tbls as $k){
						$rows = $database->getAll($k, 'report_id = '.$id);							
						foreach($rows as $row){
							$ref_ids[] = $row['id'];
						}
						
						if(count($ref_ids) > 0){
							$ref_name = $k;
							break;
						}
					}						
					
					if(count($ref_ids) > 0){

						$result = ['success' => false, 'message' => 'Error: Can\'t delete because '.$ref_name.'(s) ' . implode(',', $ref_ids) . ' rely on report!' ];
					}else{
						$ret_val = $obj->delete(intval($_POST['id']));
            $result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
					}
        }
    }

    echo json_encode($result);
?>
