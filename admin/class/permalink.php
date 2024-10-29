<?php
    class permalink_Class
    {
        private $table_name = 'permalink';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

				function getExpires($interval){
					if(!preg_match("/[a-z]/i", $interval)){	// if its just numbers (ex. 2024-02-20 18:35:07.616773)
						return ['expires' => $interval];		// it comes from update
					}
					
					$sql = "SELECT CURRENT_TIMESTAMP + '".$interval."' as expires";
					$result = pg_query($this->dbconn, $sql);
					if(!$result){
						return false;
					}
					
					$row = pg_fetch_assoc($result);
					return $row;
				}

        function create($data)
        {
						
						 $row = $this->getExpires($data['expires']);
						 if($row === false){
							 return [0,0,0];
						 }
					
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (map_id,page,description,query,expires,visits_limit,hash) "."VALUES('".
						 $this->cleanData($data['map_id'])."','".
						 $this->cleanData($data['page'])."','".
						 $this->cleanData($data['description'])."','".
						 $this->cleanData($data['query'])."','".
						 $row['expires']."','".
						 $this->cleanData($data['visits_limit'])."','".
             $this->cleanData($data['hash'])."') RETURNING id,created,expires";
						
						 $result = pg_query($this->dbconn, $sql);
						 if(!$result){
							 return [0,0,0];
						 }
						 
            $row = pg_fetch_object($result);
            if($row) {
              return [$row->id,$row->created,$row->expires];
            }
            return [0,0,0];
        }

        function getRows()
        {
					$sql ="select * from public." .$this->table_name;
					$sql .= " ORDER BY id DESC";
          return pg_query($this->dbconn, $sql);
        }

				function getRowsArr(){
						$rv = array();

						$sql = "select id,name from public.".$this->table_name;
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						pg_free_result($result);
            return $rv;
        }

        function getById($id){
          $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
          return pg_query($this->dbconn, $sql);
        }
				
				function getByHash($hash){
          $sql ="select * from public." .$this->table_name . " where hash='".$hash."'";
          return pg_query($this->dbconn, $sql);
        }
			
			function deleteByMap($map_id){
				$sql ="delete from public." .$this->table_name . " where map_id='".intval($map_id)."'";
				$result = pg_query($this->dbconn, $sql);
				if(!$result){
					return 0;
				}
				$rv = (pg_affected_rows($result) > 0);
				pg_free_result($result);
				return $rv;
			}
				
       function delete($id){
          $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
          $result = pg_query($this->dbconn, $sql);
					if(!$result){
						return 0;
					}
					$rv = (pg_affected_rows($result) > 0);
					pg_free_result($result);
					return $rv;
       }

       function update($data) {
				 
					 $row = $this->getExpires($data['expires']);
					 if($row === false){
						 return 0;
					 }
				 
          $sql = "update public.".$this->table_name." set description='".
					$this->cleanData($data['description'])."', page='".
					$this->cleanData($data['page'])."', query='".
					$this->cleanData($data['query'])."', expires='".
					$this->cleanData($data['expires'])."', visits_limit='".
          $this->cleanData($data['visits_limit'])."' where id = '".intval($data['id'])."' ";
					
					$result = pg_query($this->dbconn, $sql);
					if(!$result){
						return 0;
					}
					return pg_affected_rows($result);
       }
			 
			 function updateVisits($data) {
				 
          $sql = "update public.".$this->table_name." set visits='".
					$this->cleanData($data['visits'])."' where id = '".intval($data['id'])."' ";
					
					$result = pg_query($this->dbconn, $sql);
					if(!$result){
						return 0;
					}
					return pg_affected_rows($result);
       }

			 function getMap($hash, $countme){
				 $sql = "select * FROM ".$this->table_name." where hash='".$hash."'";
				 
				 $result = pg_query($this->dbconn, $sql);
				 if(!$result || (pg_num_rows($result) == 0)){
					 return null;
				 }
				 
				 
				 $row = pg_fetch_assoc($result);
				 pg_free_result($result);
				 
				 if($countme){
					 // update the visited permalink counter
					 $visits = intval($row['visits']) + 1;
					 $limit  = intval($row['visits_limit']);
					 
					 if($limit != 0){
						 if($visits > $limit){
							$this->delete($row['id']); 
							return null;	//expired by visits
						}/* NOTE: can't delete, because after last visit, we use permalink in data_{file,pg,gs}.php!
						else if($visits == $limit){	//last visit
							 $this->delete($row['id']);	// delete permalink
							 return $row;
						 }*/
					 }
					 
					 $row['visits'] = $visits;
					 $this->updateVisits($row);
				 }
				 
				 // check time
				 if (time() >= strtotime($row['expires'])) {
					 $this->delete($row['id']); 
					 return null;	//expired by time
				 }
				 
				 return $row;
			 }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
