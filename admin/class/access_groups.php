<?php
    class access_group_Class
    {
        private $table_name = 'access_groups';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
            $sql = "INSERT INTO PUBLIC." .$this->table_name." (name) "."VALUES('".$this->cleanData($data['name'])."') RETURNING id";
            $result = pg_query($this->dbconn, $sql);
						if(!$result){
							return 0;
						}
						
						$row = pg_fetch_object($result);
						pg_free_result($result);

            if($row) {
							# insert report access
							$values = array();
							foreach($data['userids'] as $user_id){
								array_push($values, "(".$user_id.",".$row->id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$ret = pg_query($this->dbconn, $sql);

              return $row->id;
            }
            return 0;
        }

        function getAccessGroups()
        {
            $sql = "select * from public." .$this->table_name . " ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }
				
				function getRowsArr(){
						$rv = array();
						$result = $this->getAccessGroups();
						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						pg_free_result($result);
            return $rv;
        }
				
				function getByUserId($user_id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.user_access where user_id='".intval($user_id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getAccessGroupsArr(){
						$rv = array();

						$sql = "select id,name from public.".$this->table_name;
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupUsers($gids){
						$rv = array();

						$sql = "select id,name from public.user WHERE id in (select user_id from public.user_access where access_group_id in (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupReports($gids){
						$rv = array();

						$sql = "select id,name from public.jasper WHERE id in (select report_id from public.report_access where access_group_id in (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupReportGroups($gids){
						$rv = array();

						$sql = "select id,name from public.groups WHERE id in (SELECT report_group_id from public.group_access where access_group_id IN (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }
				
				function getGroupLinks($gids){
						$rv = array();

						$sql = "select id,url from public.links WHERE id in (SELECT link_id from public.link_access where access_group_id IN (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['url'];
						}
            return $rv;
        }
				
				function getGroupMapGroups($gids){
						$rv = array();

						$sql = "select id,name from public.map WHERE id in (SELECT map_id from public.map_access where access_group_id IN (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }
				
        function getGroupById($id){
            $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

       function delete($id){

				 $sql ="delete from public.user_access where access_group_id='".intval($id)."'";
				 $rv = pg_query($this->dbconn, $sql);

				 $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
				 return pg_query($this->dbconn, $sql);
       }

       function update($data=array()) {
          $sql = "update public.access_groups set name='".$this->cleanData($data['name'])."' where id = '".intval($data['id'])."' ";
          $result = pg_query($this->dbconn, $sql);
          if(!$result){
              return false;
          }
          $rv = pg_affected_rows($result);

					if($rv > 0){

						$sql ="delete from public.user_access where access_group_id='".intval($data['id'])."'";
	 				 	$rv = pg_query($this->dbconn, $sql);

						# insert report access
						$values = array();

						foreach($data['userids'] as $user_id){
							array_push($values, "(".$user_id.",".$data['id'].")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
						return true;
					}
			return false;
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
