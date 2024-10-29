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
				
				/*function __destruct() {
					pg_close($this->connection);
				}*/

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
						pg_free_result($result);

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
						pg_free_result($result);

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
						pg_free_result($result);

            return $rows;
        }
				
				function buildGeoJSON($query){
					$qc_id = 0;	# NOTE: counter needed by qgis2web ?
					echo '{"type": "FeatureCollection",
				    	"features": [';

					$feature = array('type' => 'Feature');
					
					$result = pg_query($this->connection, $query);
					if ($result) {
						$sep = '';
						while ($row = pg_fetch_assoc($result)) {
							$feature['geometry'] = json_decode($row['geojson'], true);
							# Remove geojson and geometry fields from properties
							unset($row['geojson']);
							$row['qc_id'] = $qc_id; $qc_id = $qc_id + 1;
							$feature['properties'] = $row;
							
							echo $sep."\n".json_encode($feature, JSON_NUMERIC_CHECK);
							$sep = ',';
						}
						pg_free_result($result);
					}
					
					echo ']}';
					
					return 0;
				}
				
				function find_srid($schema, $tbl, $geom){
					/*$query = "SELECT Find_SRID('".$schema."','".$tbl."','".$geom."')";
					$result = pg_query($this->connection, $query);

					if (!$result) {
						echo "An error occurred executing the query.\n";
						exit;
					}
					$row = pg_fetch_assoc($result);
					pg_free_result($result);
					
					return $row['find_srid'];*/
					return 4326;
				}
				
				function getGeoJSON($schema, $tbl, $geom){
					if(!empty($where)){
						$where = 'WHERE '.$where;
					}
					
					$srid = $this->find_srid($schema, $tbl, $geom);
					
					$query = 'SELECT *, public.ST_AsGeoJSON(public.ST_Transform(("'.$schema.'"."'.$tbl.'"."'.$geom.'"),'.$srid.')) AS geojson FROM "'.$schema.'"."'.$tbl.'"';
					return $this->buildGeoJSON($query);
				}
				
				function select1($field, $query) {
						$rows = array();
            $result = pg_query($this->connection, 'SELECT '.$field.' '.$query);

            if (!$result) {
              return [$rows, pg_last_error($this->connection)];
            }

            // Fetch all rows
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row[$field];
            }
						pg_free_result($result);

            return [$rows, ''];
        }
				
				function getDatabases($owner = null){
					$sql = " from pg_database where datname NOT LIKE 'template%'";
					if($owner){
						$sql .= " AND datdba = (SELECT usesysid from pg_user WHERE usename = '".$owner."')";
					}
					return $this->select1('datname', $sql);
				}
				
				function getSchemas($dbname, $owner){
					return $this->select1('schema_name', " FROM information_schema.schemata WHERE catalog_name = '".$dbname."' and schema_owner IN ('pg_database_owner', '".$owner."')");
				}
				
				function getTables($schema_name){
					return $this->select1('table_name', " FROM information_schema.tables WHERE table_schema = '".$schema_name."'");
				}
				
				function getGeomTables($dbname, $schema_name){
					return $this->select1('f_geometry_column', "FROM geometry_columns WHERE f_table_catalog = '".$dbname."' AND f_table_schema = '".$schema_name."'");
				}
				
				function getGeomColumns($sch_name, $tbl_name){
					return $this->select1('f_geometry_column', "from geometry_columns where f_table_schema = '$sch_name' and f_table_name = '$tbl_name'");
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
				
				function get_unique_dbname($name){
					
					$name = str_replace('.', '_', $name);
					$dbname = str_replace(' ', '_', $name);
					
					list($dbs,$err) = $this->getDatabases();
					
					$i = 1;
					while(in_array($dbname, $dbs)){
						$i = $i + 1;
						$dbname = $name.$i;
					}
					
					return $dbname;
				}
				
				function drop($dbname){
					$result = pg_query($this->connection, 'DROP DATABASE "'.$dbname.'"');
					pg_free_result($result);
				}
				
				function check_user_map_access($map_id, $user_id){
					$sql = 'SELECT map_id from public.map_access WHERE map_id = '.$map_id.' AND access_group_id in (SELECT access_group_id from public.user_access where user_id='.$user_id.')';
					$result = pg_query($this->connection, $sql);
					$access_granted = pg_num_rows($result);
					pg_free_result($result);
					
					return ($access_granted > 0);
				}
    }
?>
