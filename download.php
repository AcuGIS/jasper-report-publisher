<?PHP
    session_start(['read_and_close' => true]);
		require('admin/incl/const.php');
    require('admin/class/database.php');
		
		function _get($name) {
        return isset($_GET[$name]) ? $_GET[$name] : '';
    }

    function myFilter($var){
        return ($var !== NULL && $var !== FALSE && $var !== "");
    }
		
		if(!isset($_SESSION[SESS_USR_KEY])) {
				header('Location: login.php');
				exit;
		}
				
    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $row = $database->get('jasper', $id);
    $drow = $database->get('datasource', 'id = '.$row['datasource_id']);
		
		if(!$row || !in_array(_get('type'), ['csv', 'html', 'html2', 'docx', 'jxl', 'pdf', 'pptx', 'rtf', 'xlsx'])){
			die('Invalid Request');
		}
    
    header("Content-disposition: attachment; filename=\"".$row['outname']."-".date('Y-m-d-H:i:s')."."._get('type')."\"");

    $query = array_filter($_GET, "myFilter");
    unset($query['id']);
    $query = http_build_query($query);

    readfile('http://localhost:8080/JasperReportsIntegration/report?_repName='.$row['repname'].'&_repFormat='._get('type').'&_dataSource='.$drow['name'].'&'.$query);
?>
