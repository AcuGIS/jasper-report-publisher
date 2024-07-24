<?php
    class user_Class
    {
        private $table_name = 'user';
        private $dbconn = null;
				
				public static function randomPassword() {
				    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
				    $pass = array(); //remember to declare $pass as an array
				    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
				    for ($i = 0; $i < 10; $i++) {
				        $n = rand(0, $alphaLength);
				        $pass[] = $alphabet[$n];
				    }
				    return implode($pass); //turn the array into a string
				}
				
        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,email,password,accesslevel) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['email'])."','".
             password_hash($data['password'], PASSWORD_DEFAULT)."','".
             $this->cleanData($data['accesslevel'])."') RETURNING id";

						 $result = pg_query($this->dbconn, $sql);
            if(!$result){
							return 0;
						}
						
						$row = pg_fetch_object($result);
						pg_free_result($result);

            if($row) {

							# insert user groups
							$values = array();
							foreach($data['groups'] as $group_id){
								array_push($values, "(".$row->id.",".$group_id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$ret = pg_query($this->dbconn, $sql);

              return $row->id;
            }
            return 0;

            //return pg_affected_rows(pg_query($this->dbconn, $sql));
        }

        function getRows()
        {
            $sql ="select * from public." .$this->table_name . "
            ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }

				function getRowsArr(){
						$rv = array();

						$sql = "select id,name from public.".$this->table_name;
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

        function getById($id){

            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

				function loginCheck($pwd, $email){
					$sql ="select * from public.user where email = '".$this->cleanData($email)."'";
					$result = pg_query($this->dbconn,$sql);
					if(!$result || pg_num_rows($result) == 0){
						return null;
					}
					$row = pg_fetch_object($result);
					pg_free_result($result);

					if (password_verify($pwd, $row->password)) {
						return $row;
					}
					return null;
				}

				function getUserAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.user_access where user_id='".intval($id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

       function delete($id)
       {
					 $sql ="delete from public.user_access where user_id='".intval($id)."'";
					 $rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

          $id = intval($data['id']);
				 	$row = pg_fetch_object($this->getById($id));
					
          $sql = "update public.user set name='".
          				$this->cleanData($data['name'])."', email='".
									$this->cleanData($data['email'])."'";
					
					if($row->password != $data['password']){	# if password is changed
						$hashpassword = password_hash($data['password'], PASSWORD_DEFAULT);
          	$sql .= ", password='".$hashpassword."'";
					}

          $sql .= ", accesslevel='".$this->cleanData($data['accesslevel']).
								 	"' where id = '".$id."'";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old groups
						$sql = "delete from public.user_access where user_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert user groups
						$values = array();
						foreach($data['groups'] as $group_id){
							array_push($values, "(".$data['id'].",".$group_id.")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}

					return $rv;
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
