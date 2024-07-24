<?php
    class groups_Class
    {
        private $table_name = 'groups';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {		
						$reportids = implode(',', $data['reportids']);
						
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name, description, reportids) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['description'])."','".
             $this->cleanData($reportids)."') RETURNING id";

            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
								# insert into access groups
								$values = array();
								foreach($data['accgrps'] as $group_id){
									array_push($values, "(".$group_id.",".$row->id.")");
								}

								$sql = "insert into public.group_access (access_group_id,report_group_id) values ".implode(',', $values);
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

				function getReportGroups(){
						$rv = array();

						$sql = "select id,name from public.groups";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGrpAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.group_access where report_group_id='".intval($id)."')";
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

       function delete($id)
       {

						$sql ="delete from public.group_access where report_group_id='".intval($id)."'";
						$rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {
				 	$reportids = implode(',', $data['reportids']);
					
          $sql = "update public.groups set name='".
          $this->cleanData($data['name'])."', description='".
          $this->cleanData($data['description'])."', reportids='".
          $this->cleanData($reportids)."' where id = '".
          intval($data['id'])."' ";
          $rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old access groups
						$sql = "delete from public.group_access where report_group_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert access groups
						$values = array();
						foreach($data['accgrps'] as $group_id){
							array_push($values, "(".$group_id.",".$data['id'].")");
						}

						$sql = "insert into public.group_access (access_group_id,report_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
