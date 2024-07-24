<?php
    session_start(['read_and_close' => true]);
		require('../incl/const.php');
    require('../class/database.php');
		require('../class/datasource.php');
		require('../incl/jru-lib.php');
	
		# Add a child to root of $APP/WEB-INF/web.xml and $TOMCAT_HOME/conf/context.xml 
		function dom_xml_save($filename, $xmlstr){
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			$doc->loadXML($xmlstr);
			file_put_contents($filename, $doc->saveXML());
		}
		
		function ctx_xml_rm($filename, $name){
			$xml = simplexml_load_file($filename);
			list($elem) = $xml->xpath('/Context/Resource[@name="jdbc/'.$name.'"]');
			if($elem){
				unset($elem[0]);
				$xml->asXML($filename);
			}
		}
		
		function web_xml_rm($filename, $name){
			$xml = simplexml_load_file($filename, null, LIBXML_NONET | LIBXML_NSCLEAN);
			foreach ($xml->getDocNamespaces() as $prefix => $namespace) {
			    $xml->registerXPathNamespace($prefix ?: 'x', $namespace);
			}
			
			list($elem) = $xml->xpath('/x:web-app/x:resource-ref/x:res-ref-name[text() = "'.$name.'"]');
			if($elem){
				unset($elem[0]);
				$xml->asXML($filename);
			}
		}
		
		function jri_add_pg_resource($ctxxml, $webxml, $name, $url, $user, $pass){
			$xml = simplexml_load_file($ctxxml);
			$res = $xml->addChild('Resource');
			$res->addAttribute('name',						'jdbc/'.$name);
			$res->addAttribute('auth', 						"Container");
			$res->addAttribute('type', 						"javax.sql.DataSource");
			$res->addAttribute('driverClassName', "org.postgresql.Driver");
			$res->addAttribute('maxTotal',				"20");
			$res->addAttribute('initialSize',			"0");
			$res->addAttribute('minIdle',					"0");
			$res->addAttribute('maxIdle',					"8");
			$res->addAttribute('maxWaitMillis',		"10000");
			$res->addAttribute('timeBetweenEvictionRunsMillis',	"30000");
			$res->addAttribute('minEvictableIdleTimeMillis',		"60000");
			$res->addAttribute('testWhileIdle',		"true");
			$res->addAttribute('validationQuery',	"select user");
			$res->addAttribute('maxAge',					"600000");
			$res->addAttribute('rollbackOnReturn',"true");
			$res->addAttribute('url',			$url);
			$res->addAttribute('username',$user);
			$res->addAttribute('password',$pass);
			dom_xml_save($ctxxml, $xml->asXML());
			
			$xml = simplexml_load_file($webxml);
			$res = $xml->addChild('resource-ref');
			$res->addChild('description',		'postgreSQL Datasource example');
			$res->addChild('res-ref-name',	$name);
			$res->addChild('res-type',			'javax.sql.DataSource');
			$res->addChild('res-auth',			'Container');
			dom_xml_save($webxml, $xml->asXML());
		}

		function jri_add_mysql_resource($ctxxml, $webxml, $name, $url, $user, $pass){
			$xml = simplexml_load_file($ctxxml);
			$res = $xml->addChild('Resource');
			$res->addAttribute('name',						'jdbc/'.$name);
			$res->addAttribute('auth', 						"Container");
			$res->addAttribute('type', 						"javax.sql.DataSource");
			$res->addAttribute('maxTotal',				"100");
			$res->addAttribute('maxIdle',					"20");
			$res->addAttribute('maxWaitMillis',		"10000");
			$res->addAttribute('driverClassName', "com.mysql.jdbc.Driver");
			$res->addAttribute('url',			$url);
			$res->addAttribute('username',$user);
			$res->addAttribute('password',$pass);
			dom_xml_save($ctxxml, $xml->asXML());
			
			
			$xml = simplexml_load_file($webxml);
			$res = $xml->addChild('resource-ref');
			$res->addChild('description',		'MySQL Datasource example');
			$res->addChild('res-ref-name',	$name);
			$res->addChild('res-type',			'javax.sql.DataSource');
			$res->addChild('res-auth',			'Container');
			dom_xml_save($webxml, $xml->asXML());
		}

		function jri_add_mssql_resource($ctxxml, $webxml, $name, $url, $user, $pass){
			$xml = simplexml_load_file($ctxxml);
			$res = $xml->addChild('Resource');
			$res->addAttribute('name',						'jdbc/'.$name);
			$res->addAttribute('auth', 						"Container");
			$res->addAttribute('type', 						"javax.sql.DataSource");
			$res->addAttribute('maxTotal',				"100");
			$res->addAttribute('maxIdle',					"30");
			$res->addAttribute('maxWaitMillis',		"10000");
			$res->addAttribute('driverClassName', "com.microsoft.sqlserver.jdbc.SQLServerDriver");
			$res->addAttribute('url',			$url);
			$res->addAttribute('username',$user);
			$res->addAttribute('password',$pass);
			dom_xml_save($ctxxml, $xml->asXML());
			
			$xml = simplexml_load_file($webxml);
			$res = $xml->addChild('resource-ref');
			$res->addChild('description',		'MSSQL Datasource example');
			$res->addChild('res-ref-name',	$name);
			$res->addChild('res-type',			'javax.sql.DataSource');
			$res->addChild('res-auth',			'Container');
			dom_xml_save($webxml, $xml->asXML());
		}
		
		function prop_rm_ds($filename, $ds_name){
			$ds_line = -1;
			
			$lines = file($filename, FILE_IGNORE_NEW_LINES);

			foreach($lines as $ln => $line) {
				if($line == '[datasource:'.$ds_name.']'){
					$ds_line = $ln;
					break;
				}
			}
			
			# comment out the datasource
			unset($lines[$ds_line]);
			$ds_line = $ds_line + 1;

			foreach(DS_KEYS as $k){
				$line = $lines[$ds_line];
				if(str_starts_with($line, $k.'=')){
					unset($lines[$ds_line]);
				}
				$ds_line = $ds_line + 1;
			}
			
			// save back to file
			$fp = fopen($filename, 'w');
			foreach($lines as $l){
				fwrite($fp, $l."\n");
			}
			fclose($fp);
			
			return $ds_line;
		}
		
		function prop_add_ds($filename, $ds){
			$fp = fopen($filename, 'a');
			fwrite($fp, "\n");
			fwrite($fp, '[datasource:'.$ds['name']."]\n");
			foreach(DS_KEYS as $k){
				fwrite($fp, $k.'='.$ds[$k]."\n");
			}
			fclose($fp);
		}
		
    $result = ['success' => false, 'message' => 'Error while processing your request!'];

    if(isset($_SESSION[SESS_USR_KEY]) && in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
			
			$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
			$obj = new datasource_Class($database->getConn());

			# get property file
			$propfile = get_prop_file();
			$catalina_home = get_catalina_home();
						
			$webxml = $catalina_home.'/webapps/JasperReportsIntegration/WEB-INF/web.xml';
			$ctxxml = $catalina_home.'/conf/context.xml';

			$ds_id = 0;
			$ds_name = '';
			
			if(isset($_POST['id'])){
				$ds_id = intval($_POST['id']);
				
				$row = $obj->getById($ds_id);
				if($row === false){
					$result = ['success' => false, 'message' => 'Datasource doesn\'t exist!'];
					echo json_encode($result);
					exit;
				}
				$ds_name = $row['name'];
			}
			
      if(isset($_POST['save'])) {
        
        if($ds_id > 0) { // update
					
					if(!$obj->update($_POST)){
						$result = ['success' => false, 'message' => 'Datasource record update failed!'];
					}else{
						$ds_line = prop_rm_ds($propfile, $ds_name);

						if($ds_line == -1){
							$result = ['success' => false, 'message' => 'Datasource not found!'];
						}else{
							prop_add_ds($propfile, $_POST);
							
							if($_POST['type'] == 'jndi'){
								// update web.xml and context.xml							
								ctx_xml_rm($ctxxml, $ds_name);
								web_xml_rm($webxml, $ds_name);
								
											if(str_starts_with($_POST['url'], 'jdbc:postgresql')){		jri_add_pg_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
								}else if(str_starts_with($_POST['url'], 'jdbc:mysql')){				jri_add_mysql_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
								}else if(str_starts_with($_POST['url'], 'jdbc:sqlserver')){		jri_add_mssql_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
								}
							}
							
							$result = ['success' => true, 'message' => 'Datasource updated!'];
						}
					}

        } else { // insert

					$newId = $obj->create($_POST);
					if($newId == 0){
						$result = ['success' => true, 'message' => 'Datasource create failed'];
					}else{
						
						// remove any existing ds with same name
						prop_rm_ds($propfile, $_POST['name']);
						
						// append datasource to property file
						prop_add_ds($propfile, $_POST);

						if($_POST['type'] == 'jndi'){
							// update web.xml and context.xml
							
										if(str_starts_with($_POST['url'], 'jdbc:postgresql')){		jri_add_pg_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
							}else if(str_starts_with($_POST['url'], 'jdbc:mysql')){				jri_add_mysql_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
							}else if(str_starts_with($_POST['url'], 'jdbc:sqlserver')){		jri_add_mssql_resource(	$ctxxml, $webxml, $_POST['name'], $_POST['url'], $_POST['username'], $_POST['password']);
							}
						}	
					
						$result = ['success' => true, 'message' => 'Datasource Successfully Saved!', 'id' => $newId];
					}
        }
      
			} else if(isset($_POST['delete'])) {
				
				$row = $obj->getById($ds_id);
				
				$ref_ids = array();
				$ref_name = null;
				$acc_tbls = array('jasper', 'schedule');
				
				foreach($acc_tbls as $k){
					$rows = $database->getAll($k, 'datasource_id = '.$ds_id);							
					foreach($rows as $row){
						$ref_ids[] = $row['id'];
					}
					
					if(count($ref_ids) > 0){
						$ref_name = $k;
						break;
					}
				}						
				
				if(count($ref_ids) > 0){

					$result = ['success' => false, 'message' => 'Error: Can\'t delete because '.$ref_name.'(s) ' . implode(',', $ref_ids) . ' rely on datasource!' ];
				
				}else if(($row === false) || !$obj->delete($ds_id)){
					$result = ['success' => false, 'message' => 'Datasource delete failed!'];
				}else{
					$ds_line = prop_rm_ds($propfile, $ds_name);
					
					if($ds_line == -1){
						$result = ['success' => false, 'message' => 'Datasource '.$ds_name.'not found!'];
					}else{
						
						if($row['type'] == 'jndi'){
							// update web.xml and context.xml
							ctx_xml_rm($ctxxml, $ds_name);
							web_xml_rm($webxml, $ds_name);
						}
						
						$result = ['success' => true, 'message' => 'Data Successfully Deleted!'];
					}
				}
			}
    }

    echo json_encode($result);
?>
