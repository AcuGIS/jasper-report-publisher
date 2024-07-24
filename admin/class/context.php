<?php
    class context_Class
    {
        private $table_name = 'inputs';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,input,report_id) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['input'])."','".
             $this->cleanData($data['report_id'])."') RETURNING id";


            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
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

        function getById($id){

            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

       function delete($id)
       {

            $sql ="delete from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

         $sql = "update public.inputs set name='".
          $this->cleanData($data['name'])."', input='".
          $this->cleanData($data['input'])."', report_id='".
          $this->cleanData($data['report_id'])."' where id = '".
          intval($data['id'])."' ";
          return pg_affected_rows(pg_query($this->dbconn, $sql));
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
