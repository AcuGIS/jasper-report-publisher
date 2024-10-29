<?php
		const PG_SERVICE_CONF = '/var/www/data/qgis/pg_service.conf';
		
    class pglink_Class
    {
        private $table_name = 'pglink';
        private $dbconn = null;
				private $owner_id = null;

        function __construct($dbconn, $owner_id) {
            $this->dbconn = $dbconn;
						$this->owner_id = intval($owner_id);
        }
				
        function create($data)
        {	
					$name 		= $this->cleanData($data['name']);
					$host 		= $this->cleanData($data['host']);
					$port 		= $this->cleanData($data['port']);
					$username	= $this->cleanData($data['username']);
					$password = $this->cleanData($data['password']);
					$dbname		= $this->cleanData($data['dbname']);
					$svc_name	= $this->cleanData($data['svc_name']);
					
            $sql = "INSERT INTO PUBLIC." .$this->table_name."
            (name,host,port,username,password,dbname,svc_name, owner_id) ".
						"VALUES('".$name."','".$host."',".$port.",'".$username."','".$password."','".$dbname."','".$svc_name."',".$this->owner_id.") RETURNING id";
             
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
			 
			 function getConnInfo($id){
				 $result = $this->getById($id);
				 if(pg_num_rows($result) == 0){
					 return false;
				 }
				 
				 $row = pg_fetch_object($result);
				 pg_free_result($result);
				 
				 $conn_info = 'host='.$row->host.' port='.$row->port.' dbname='.$row->dbname.' user='.$row->username.' password='.$row->password;
				 
				 return $conn_info;
			 }
			 
			 function getConnInfoAssoc($id){
				 $rv = [];
				 
				 $conn_info =  $this->getConnInfo($id);
				 if($conn_info === false){
					 return false;
				 }
				 
				 $tokens = explode(' ', $conn_info);
				 foreach($tokens as $t){
					 if(strlen($t) > 0){
						 $v = explode('=', $t);
						 $rv[$v[0]] = $v[1];
				 	 }
				 }
				 
				 return $rv;
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
					."', host='".$this->cleanData($data['host'])
					."', port=".$this->cleanData($data['port'])
					.", username='".$this->cleanData($data['username'])
					."', password='".$this->cleanData($data['password'])
					."', dbname='".$this->cleanData($data['dbname'])
					."', svc_name='".$this->cleanData($data['svc_name'])
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
			
			function pg_service_ctl($cmd, $va){
 		 		$ini_data = (is_file(PG_SERVICE_CONF)) ? parse_ini_file(PG_SERVICE_CONF, true) : array();
				
				if($cmd == 'del'){
					unset($ini_data[$va['svc_name']]);
				}else{	// add or edit
					$ini_data[$va['svc_name']] = array('host' => $va['host'], 'port' => $va['port'], 'dbname' => $va['dbname'], 'user' => $va['username'], 'password' => $va['password']);
				}
				
				$content = '';
				foreach($ini_data as $svc_name => $kv){
					$content .= "\n".'['.$svc_name.']'."\n";
					foreach($kv as $k => $v){
						$content .= $k.'='.$v."\n";
					}
				}
				file_put_contents(PG_SERVICE_CONF, $content);

				return 0;
			}
	}
