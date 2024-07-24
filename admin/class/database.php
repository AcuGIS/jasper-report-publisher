<?PHP
    class Database {
        private $connection;

        function __construct($db_host, $db_name, $db_user, $db_pass, $db_port, $db_schema) {
          $this->connection = pg_connect("dbname={$db_name} user={$db_user} password={$db_pass} host={$db_host} port={$db_port}");
        }
				
				function is_connected(){
					if($this->connection === false){
						return false;
					}
					return (pg_connection_status($this->connection) == PGSQL_CONNECTION_OK);
				}
				
    	function modify($str) {
        		return ucwords(str_replace("_", " ", $str));
    	}

				function getConn() {
					return $this->connection;
				}
        function getAll($table, $where = '', $orderby = '') {
            $orderby = $orderby ? 'ORDER BY '.$orderby : '';
            $where = $where ? 'WHERE '.$where : '';

            $query = "SELECT * FROM {$table} {$where} {$orderby}";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        }


        function get($table, $where = '') {
            if(is_numeric($where)) {
                $where = "id = ".intval($where);
            }
            else if (empty($where)) {
                $where = "1";
            }

            $query = "SELECT * FROM {$table} WHERE $where";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch one rows
            $row = pg_fetch_assoc($result);

            return $row;
        }


        /* Select Query */
        function select($query) {
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        }
				
				function create_user($dbuser, $pass) {
					$sql = 'CREATE USER "'.$dbuser.'" WITH PASSWORD \''.$pass.'\'';
					$result = pg_query($this->connection, $sql);
					if (!$result) {
						return false;
					}
					pg_free_result($result);
					return true;
				}
				
				function create_user_db($dbname, $dbuser, $pass) {
					
					$sqls = array('CREATE DATABASE "'.$dbname.'" WITH OWNER "'.$dbuser.'"',
												'GRANT all privileges on database "'.$dbname.'" to "'.$dbuser.'"');

					foreach($sqls as $sql){
					 $result = pg_query($this->connection, $sql);
					 if (!$result) {
						 return false;
					 }
					 pg_free_result($result);
					}

					return true;
				}
				
				function create_extensions($extensions){
					foreach($extensions as $ext){
					 $result = pg_query($this->connection, 'CREATE EXTENSION IF NOT EXISTS '.$ext);
					 if (!$result) {
						 return false;
					 }
					 pg_free_result($result);
					}
					return true;
				}
    }
?>
