<?php
		const DS_TYPES		= array('jdbc', 'jndi');
			
    class datasource_Class
    {
        private $table_name = 'datasource';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,type,url,username,password) "."VALUES('".
						 $this->cleanData($data['name'])."','".
						 $this->cleanData($data['type'])."','".
						 $this->cleanData($data['url'])."','".
             $this->cleanData($data['username'])."','".
             $this->cleanData($data['password'])."') RETURNING id";

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
            $sql ="select * from public." .$this->table_name . "
            ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }

				function getArr($where = ''){
						$rv = array();

						$sql = "select * from public.".$this->table_name;
						if($where){
							$sql .= ' WHERE '.$where;
						}
						
						$result = pg_query($this->dbconn, $sql);
						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row;
						}
            return $rv;
        }

				function getById($id){
          $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
          $result = pg_query($this->dbconn, $sql);
					if(!$result){
						return false;
					}
					
					if(pg_num_rows($result) == 0){
						return false;
					}
					$row = pg_fetch_assoc($result);
					pg_free_result($result);
					return $row;
        }
	
       function delete($id)
       {		
				 $condition = ['id' => $id];
				 $res = pg_delete($this->dbconn, $this->table_name, $condition);
				 return ($res) ? 1 : 0;
       }

       function update($data=array())
       {

				 $condition = ['id' => $_POST['id']];
				 unset($_POST['id']); unset($_POST['save']); unset($_POST['update']);
				 return pg_update($this->dbconn, $this->table_name, $_POST, $condition);
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
