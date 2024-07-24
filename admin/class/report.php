<?php
    class Report_Class
    {
        private $table_name = 'jasper';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (repname,datasource_id,download_only,outname,name,description,is_grouped) "."VALUES('".
             $this->cleanData($data['repname'])."','".
             $this->cleanData($data['datasource_id'])."','".
			 $this->cleanData($data['download_only'])."','".
             $this->cleanData($data['outname'])."','".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['description'])."','".
			 intval($data['is_grouped'])."') RETURNING id";
            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {

							# insert into access groups
							$values = array();
							foreach($data['accgrps'] as $group_id){
								array_push($values, "(".$group_id.",".$row->id.")");
							}

							$sql = "insert into public.report_access (access_group_id,report_id) values ".implode(',', $values);
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

				function getRepAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.report_access where report_id='".intval($id)."')";
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

						$sql ="delete from public.report_access where report_id='".intval($id)."'";
						$rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

          $sql = "update public.jasper set repname='".
          $this->cleanData($data['repname'])."', datasource_id='".
          $this->cleanData($data['datasource_id'])."', download_only='".
		  $this->cleanData($data['download_only'])."', outname='".
          $this->cleanData($data['outname'])."', name='".
          $this->cleanData($data['name'])."', description='".
          $this->cleanData($data['description'])."', is_grouped='".
          intval($data['is_grouped'])."' where id = '".
          intval($data['id'])."'
          ";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old access groups
						$sql = "delete from public.report_access where report_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert access groups
						$values = array();
						foreach($data['accgrps'] as $group_id){
							array_push($values, "(".$group_id.",".$data['id'].")");
						}

						$sql = "insert into public.report_access (access_group_id,report_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
