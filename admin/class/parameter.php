<?php
    class parameter_Class
    {
        private $table_name = 'parameters';
        private $dbconn = null;
        
        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }
        
        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."  
             (reportid,ptype,pvalues,pname) "."VALUES('".
             $this->cleanData($data['reportid'])."','".
             $this->cleanData($data['ptype'])."','".
             $this->cleanData($data['pvalues'])."','".
			 $this->cleanData($data['pname'])."') RETURNING id";
            
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
     
          $sql = "update public.parameters set reportid='".
          $this->cleanData($data['reportid'])."', ptype='".
          $this->cleanData($data['ptype'])."', pvalues='".
          $this->cleanData($data['pvalues'])."', pname='".
          $this->cleanData($data['pname'])."' where id = '".
          intval($data['id'])."' ";
          return pg_affected_rows(pg_query($this->dbconn, $sql));
       }
       
       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}