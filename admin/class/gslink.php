<?php
    class gslink_Class
    {
        private $table_name = 'gslink';
        private $dbconn = null;
				private $owner_id = null;

				private function rest_get($url, $username, $password){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_TIMEOUT, 5);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
					curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
					$json = curl_exec ($ch);
					$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
					curl_close ($ch);
					return [$status_code, $json];
				}

        function __construct($dbconn, $owner_id) {
            $this->dbconn = $dbconn;
						$this->owner_id = intval($owner_id);
        }
				
        function create($data)
        {	
					$name 		= $this->cleanData($data['name']);
					$url 		= $this->cleanData($data['url']);
					$username	= $this->cleanData($data['username']);
					$password = $this->cleanData($data['password']);
					
            $sql = "INSERT INTO PUBLIC." .$this->table_name."
            (name,url,username,password,owner_id) ".
						"VALUES('".$name."','".$url."','".$username."','".$password."',".$this->owner_id.") RETURNING id";
             
						$result = pg_query($this->dbconn, $sql);
						if(!$result){
							return 0;
						}
						
						$row = pg_fetch_object($result);
						pg_free_result($result);
						
            if($row) {
							return $row->id;
            }
            return 0;
        }

        function getRows()
        {
          $sql  = "select * from public." .$this->table_name;
					if($this->owner_id != SUPER_ADMIN_ID){
						$sql .= " WHERE owner_id = ".$this->owner_id;
					}
					$sql .= " ORDER BY id DESC";
          return pg_query($this->dbconn, $sql);
        }
				
				function getArr(){
						$rv = array();

						$result = $this->getRows();

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						pg_free_result($result);
            return $rv;
        }

        function getById($id){
            $sql = "select * from public.".$this->table_name." where id=".$id;
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
							die(pg_last_error($this->dbconn));
						}
						return $result;
        }
				
				function getPassword($id){
				 $result = $this->getById($id);
				 if(pg_num_rows($result) == 0){
					 return false;
				 }
				 
				 $row = pg_fetch_object($result);
				 pg_free_result($result);
				 return $row->password;
			 }
			 
			 function getWorkspaces($url, $username, $password){
				 list($status_code, $js) = $this->rest_get($url.'/rest/workspaces', $username, $password);
				 
				 if(($status_code != 200) || empty($js)){
					 return ['success' => false, 'message' => 'cURL failed with status code '. $status_code];
				 }else {
					 // extract workspaces
					 $data = json_decode($js, JSON_OBJECT_AS_ARRAY);
					 $workspaces = array();
					 if(isset($data['workspaces']['workspace'])){
						 foreach($data['workspaces']['workspace'] as $w){
							 $workspaces[] = $w['name'];
						 }
					 }
					 
					 return ['success' => true, 'workspaces' => $workspaces];
				 }
			 }
			 
			 function getLayers($url, $username, $password, $workspace){
				 list($status_code, $js) = $this->rest_get($url.'/rest/layers', $username, $password);
				 
				 if(($status_code != 200) || empty($js)){
					 return ['success' => false, 'message' => 'cURL failed with status code '. $status_code];
				 }else {
					 // extract workspaces
					 $data = json_decode($js, JSON_OBJECT_AS_ARRAY);
					 $layers = array();
					 if(isset($data['layers']['layer'])){
						 foreach($data['layers']['layer'] as $w){
							 $v = explode(':', $w['name']);
							 if($v[0] == $workspace){
								 $layers[] = $v[1];
							 }
						 }
					 }
					 
					 return ['success' => true, 'layers' => $layers];
				 }
			 }

       function delete($id){
				 $sql ="delete from public." .$this->table_name . " where id=".$id;
      	 $result = pg_query($this->dbconn, $sql);
				 if($result){
					 $rv = (pg_affected_rows($result) > 0);
					 pg_free_result($result);
					 return $rv;
				 }else{
					 return false;
				 }
       }

       function update($data=array())
       {

          $sql = "update public.".$this->table_name." set name='".$this->cleanData($data['name'])
					."', url='".$this->cleanData($data['url'])
					."', username='".$this->cleanData($data['username'])
					."', password='".$this->cleanData($data['password'])
					."' where id = '".intval($data['id'])."'";
					
					$result = pg_query($this->dbconn, $sql);
					if($result) {
						$rv = (pg_affected_rows($result) > 0);
						pg_free_result($result);
						return $rv;
					}
					return false;
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
			 
			 function isOwnedByUs($id){
 				
 				if($this->owner_id == SUPER_ADMIN_ID){	// if Super Admin
 					return true;
 				}
 				
 				$sql = "select * from public.".$this->table_name." where id=".$id." and owner_id=".$this->owner_id;
				$result = pg_query($this->dbconn, $sql);
				if(!$result){
					return false;
				}
				$rv = (pg_num_rows($result) > 0);
				pg_free_result($result);
				return $rv;
 			}
	}
