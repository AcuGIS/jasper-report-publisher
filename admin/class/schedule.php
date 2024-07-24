<?php
    class schedule_Class
    {
        private $table_name = 'schedule';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {	
           $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (cron_period, name, format, datasource_id, filename, email, email_subj, email_body, url_opt_params, noemail, email_tmpl) "."VALUES('".
						 $this->cleanData($data['cron_period'])."','".
						 $this->cleanData($data['name'])."','".
						 $this->cleanData($data['format'])."','".
						 $this->cleanData($data['datasource_id'])."','".
             $this->cleanData($data['filename'])."','".
						 $this->cleanData($data['email'])."','".
						 $this->cleanData($data['email_subj'])."','".
						 $this->cleanData($data['email_body'])."','".
						 $this->cleanData($data['url_opt_params'])."','".
						 $this->cleanData($data['noemail'])."','".
             $this->cleanData($data['email_tmpl'])."') RETURNING id";
						
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

       function update($data=array()){
				 $sql = "update public.".$this->table_name." set name='".$this->cleanData($data['name'])
				 ."', cron_period='".$this->cleanData($data['cron_period'])
				 ."', format='".$this->cleanData($data['format'])
				 ."', datasource_id='".$this->cleanData($data['datasource_id'])
				 ."', filename='".$this->cleanData($data['filename']);
				 if(isset($data['noemail'])){
					 $sql .= "', noemail='".$this->cleanData($data['noemail']);
				 }else{
					 $sql .= "', email='".$this->cleanData($data['email'])
					 ."', email_subj='".$this->cleanData($data['email_subj'])
					 ."', email_body='".$this->cleanData($data['email_body'])
					 ."', email_tmpl='".$this->cleanData($data['email_tmpl']);
				 }
				 
				 $sql .= "', url_opt_params='".$this->cleanData($data['url_opt_params'])
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
	}
