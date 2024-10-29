<?php 
	session_start(['read_and_close' => true]);
	require('../incl/const.php');
	require('../class/database.php');
	require('../class/pglink.php');
	require('../class/user.php');
	
	function rrmdir($dir) {
	 if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
			 if ($object != "." && $object != "..") {
				 if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
					 rrmdir($dir. DIRECTORY_SEPARATOR .$object);
				 else
					 unlink($dir. DIRECTORY_SEPARATOR .$object);
			 }
		 }
		 rmdir($dir);
	 }
	}

	function unzip_me($zipname){
		$ext_dir = '/tmp/ext/';
		if(!is_dir($ext_dir)){
			mkdir($ext_dir);
		}

		$zip = new ZipArchive;
		$res = $zip->open($zipname);
		if ($res === TRUE) {
			$zip->extractTo($ext_dir);
			$zip->close();
		} else {
			echo 'Error: Failed to open'.$zipname;
		}
		return $ext_dir;
	}
	
	function scan_for_shapes($source_dir){
		$files = array();
		$banned = array('.', '..');

		$cdir = scandir($source_dir);
   	foreach ($cdir as $key => $value){
			if(!in_array($value, $banned) && str_ends_with($value, '.shp')){
				$files[$value] = $source_dir.'/'.$value;
			}
		}
		return $files;
	}
	
	function exec_cmd($cmd, $pg_pass){
		$descriptorspec = array(
		   0 => array("pipe", "r"),
		   1 => array("pipe", "w"),
		   2 => array("pipe", "w")
		);
		
		$cwd = '/tmp';
		$env = array('PGPASSWORD' => $pg_pass);
		
		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
		
		if (is_resource($process)) {
		    // $pipes now looks like this:
		    // 0 => writeable handle connected to child stdin
		    // 1 => readable handle connected to child stdout
		    // Any error output will be appended to /tmp/error-output.txt

		    fclose($pipes[0]);

		    $out = stream_get_contents($pipes[1]);
		    fclose($pipes[1]);
				
				$err = stream_get_contents($pipes[2]);
		    fclose($pipes[2]);

		    // It is important that you close any pipes before calling
		    // proc_close in order to avoid a deadlock
		    $return_value = proc_close($process);

		    return [$return_value, $out, $err];
		}else{
			return [1, '', 'Failed to start '.$cmd];
		}
	}
?>

<h1>Import Results</h1>
<?php
  if(isset($_SESSION[SESS_USR_KEY]) && ($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') && 
			(isset($_FILES['source']) || isset($_POST['create_only'])) ){
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$obj = new pglink_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
						
		
		$dbuser = $_SESSION[SESS_USR_KEY]->ftp_user;
		$dbpass = $_SESSION[SESS_USR_KEY]->pg_password;
		
		putenv('PGPASSWORD='.$dbpass);
		$extensions = array('hstore','postgis');
		
		$dbname = $_POST['dbname'];
		$dbname = $database->get_unique_dbname($dbname);
		
		if(!$database->create_user_db($dbname, $dbuser, $dbpass)){
			$err = pg_last_error($database->getConn());
			echo '<p><b>Error:</b>'.$err.'</p>';
			die();
		}
		
		// make new db postgis
		$imp_db = new Database(DB_HOST, $dbname, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		if(!$imp_db->is_connected()){
			echo 'Error: Failed to connect to '.$dbname;
			die();
		}
		$imp_db->create_extensions($extensions);
		pg_close($imp_db->getConn());
			
		if(empty($_POST['create_only']) && isset($_FILES['source'])){
			$sources = array();

			if(is_array($_FILES['source']['name'])){
				$total = count($_FILES['source']['name']);
				for($i=0; $i < $total; $i++) {
					$name 		= $_FILES["source"]["name"][$i];
					$tmp_name = $_FILES['source']['tmp_name'][$i];
					$sources[$name] = $tmp_name;
				}
			}else{
				$name 		= $_FILES["source"]["name"];
				$tmp_name = $_FILES['source']['tmp_name'];
				$sources[$name] = $tmp_name;
			}

			$files = array();	#file to be imported
			$sdirs = array();
			
			foreach($sources as $name => $tmp_name){
				#Check if its a .zip archive
				if(str_ends_with($name, '.zip')){
					$source_dir 	= unzip_me($tmp_name);
					$files = scan_for_shapes($source_dir);
					array_push($sdirs,$source_dir);
				}else{
					$files[$name] = $tmp_name;
				}
			}
				
			// process files
			foreach($files as $name => $fpath) {
				
				// import data into new database
				$ext 		= pathinfo($name, PATHINFO_EXTENSION);
				//$dbname = pathinfo($name, PATHINFO_FILENAME);
				
				if(($ext == 'gpkg') || ($ext == 'shp')){
					$cmd = "ogr2ogr -preserve_fid -f 'PostgreSQL' PG:\"dbname=".$dbname." user=".$dbuser."\" '".$fpath."'";
				
				}else if($ext == 'dump'){
					$cmd = 'pg_restore -U '.$dbuser.' -d '.$dbname.' -Fc '.$fpath;
					
				}else if($ext == 'sql'){
					$cmd = 'psql -U '.$dbuser.' -d '.$dbname.' < '.$fpath;
				}else{
					continue;
				}
				
				echo '<h2>Importing '.$name.'</h2>';
				list($rc, $out, $err) = exec_cmd($cmd, $dbpass);

				echo '<p><b>CMD:</b>'.$cmd.'</p>';
				echo '<p><b>Return Code:</b>'.$rc.'</p>';
				echo '<pre>'.$err.$out.'</pre>';
				
				if($rc != 0){
					$database->drop($dbname);
					if(isset($_POST['create_link'])){
						unset($_POST['create_link']);
					}
					break;
				}
			} // end for
			
			foreach($sdirs as $source_dir){
				if(is_dir($source_dir)){
					rrmdir($source_dir);	// clean up uploaded files
				}
			}
		}


		if(isset($_POST['create_link'])){
			$newConId = -1;
			$data = ['name' => $dbname, 'host' => DB_HOST, 'port' => DB_PORT,
							 'username' => $dbuser, 'password' => $dbpass, 'dbname' => $dbname, 'svc_name' => $dbname];
			$newConId = $obj->create($data);
			echo '<p><b>Link ID:</b>'.$newConId.'</p>';
			if($obj->pg_service_ctl('add', $data) == 0){
				echo '<p><b>SVC Name:</b>'.$data['svc_name'].'</p>';
			}else{
				echo '<p><b>SVC Name:</b>Error: Failed to add!</p>';
			}
		}

	}else{
		?><p>Error while processing your request!</p><?php
	}
?>
