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
    $paramrow = $database->get('parameters', 'reportid = '.$id);
		$drow = $database->get('datasource', 'id = '.$row['datasource_id']);
		
		if(!$row || !in_array(_get('type'), ['csv', 'html', 'html2', 'docx', 'jxl', 'pdf', 'pptx', 'rtf', 'xlsx'])){
			die('Invalid Request');
		}

    if($paramrow && $paramrow['ptype'] == 'dropdown') {
        $params = [];
        if($paramrow) {
            $params = explode(',', $paramrow['pvalues']);

            if( !in_array(_get($paramrow['pname']),  $params) ) {
                $_GET[$paramrow['pname']] = $params[0];
            }

        }
    } else if($paramrow && $paramrow['ptype'] == 'query') {
        $DB_pvalues = explode(',', $paramrow['pvalues']);
        $pParameters = [];

        foreach($DB_pvalues as $v) {
            if($v) {
                $pParameters[$v] =  isset($_GET[$v]) ? $_GET[$v] : '';
            }
        }
    }

    
    header("Content-disposition: attachment; filename=\"".$row['outname']."-".date('Y-m-d-H:i:s')."."._get('type')."\"");

    $query = array_filter($_GET, "myFilter");
    unset($query['id']);
    $query = http_build_query($query);

    readfile('http://localhost:8080/JasperReportsIntegration/report?_repName='.$row['repname'].'&_repFormat='._get('type').'&_dataSource='.$drow['name'].'&'.$query);
?>