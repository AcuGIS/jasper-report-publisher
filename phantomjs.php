<?PHP
    ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
		
		require('admin/incl/const.php');
		require('admin/class/database.php');
		require('admin/class/user.php');
		require('admin/class/access_group.php');

		const PHANTOMJS_BIN = '/usr/local/bin/phantomjs';	// Update with the path to your PhantomJS binary

    session_start(['read_and_close' => true]);
    if(!isset($_SESSION[SESS_USR_KEY])) {
        header('Location: login.php');
        exit;
    }

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
    $ids = [];

    if(isset($_GET['group_id']) && intval($_GET['group_id'])) {
        $group_id = intval($_GET['group_id']);
        $group = $database->get('groups', 'id = '.$group_id);

        if($group) {
            $reportids = explode(',', $group['reportids']);

            foreach($reportids as $id) {
                $rpt = $database->get('jasper', 'id = '.$id);
                if(!$rpt) die('Report with ID: '.$id. ' is not found');
                $ids[] = $id;
            }
        }
        else {
            die('Group with ID: '.$group_id. ' is not found');
        }

    }
    else if(isset($_GET['id']) && intval($_GET['id'])) {
        $id = intval($_GET['id']);
        $rpt = $database->get('jasper', 'id = '.$id . ' AND is_grouped = 0');
        if(!$rpt) die('Report with ID: '.$id. ' is not found');
        $ids[] = $id;
    }

		$user = $_SESSION[SESS_USR_KEY];
		$usr_obj = new user_Class($dbconn);
		$usr_grps = $usr_obj->getUserAccessGroups($user->id);

		$acc_rep_ids = array();
		if(count($usr_grps)){
			$acc_obj = new access_group_Class($dbconn);

			# get report IDs from access groups
			$usr_reps = $acc_obj->getGroupReports(array_keys($usr_grps));
			if(count($usr_reps)){
				$acc_rep_ids = array_keys($usr_reps);	// ids of reports user is allowed to access
			}
		}

		# check if user can access all ids
		foreach($ids as $id){
			if(!in_array($id, $acc_rep_ids)){
				die('Report with ID: '.$id. ' is not allowed');
			}
		}



    $_GET['phantomjs'] = 'true';
	
		$proto = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $url = $proto.'://'.$_SERVER['SERVER_NAME'].str_replace('phantomjs.php', 'view.php', $_SERVER['SCRIPT_NAME']).'?'.http_build_query($_GET);

    $rand = time().'-'.rand();
    // Set the paths to PhantomJS and the conversion script
    $conversionScript = __DIR__.'/phantomjs.js'; // Update with the path to your PhantomJS conversion script
		$htmlFile					= __DIR__.'/'.$rand.'.html'; //tempnam(sys_get_temp_dir(), 'html');
		$pdfFile					= __DIR__.'/'.$rand.'.pdf'; //tempnam(sys_get_temp_dir(), 'pdf');

    // Set the HTML code to convert to PDF
     $html = '<style>
                    .m-auto, #filterModal, nav, footer{display: none !important;}
                    main {padding-top: 0 !important;}
                    body {
                        position: relative;
                        border: 0px;
                        width: 210mm;
                        height: 297mm;
                        padding: 0;
                        margin: 0;
                        zoom: 0.68;
                        /*margin-left: -100px;*/
                    }
              </style>'. file_get_contents($url);

    // Save the HTML to a file
    file_put_contents($htmlFile, $html);

    // Execute PhantomJS to convert the HTML to PDF
    exec('OPENSSL_CONF=/etc/ssl '.PHANTOMJS_BIN." $conversionScript $htmlFile $pdfFile");

    $name = date('Y-m-d_H_i_s').'.pdf';
    // Output the PDF file
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    readfile($pdfFile);

    // Clean up the temporary files
    #unlink($htmlFile);
    #unlink($pdfFile);



















    /*




    $client = Client::getInstance();
    $client->getEngine()->debug(true);
    $client->getEngine()->setPath('/usr/bin/phantomjs');

    //$request  = $client->getMessageFactory()->createRequest();
    //$response = $client->getMessageFactory()->createResponse();

    $request = $client->getMessageFactory()->createCaptureRequest('https://duniculeur.bz/', 'GET');
    $request->setOutputFile('/home/exhibit1836/public_html/viewer-only5/file.jpg');
    $request->setViewportSize($width, $height);
    $request->setCaptureDimensions($width, $height, $top, $left);


    // $request->addHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36');
    // $request->addHeader('Referer', 'https://duniculeur.bz/');
    // $request->addHeader('Host', 'duniculeur.bz');
    // $request->addHeader('Origin', 'https://duniculeur.bz');

    // $request->setUrl('https://github.com/');

    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);
    echo $response->getContent();
    print_r($request);
    print_r($response);
    die($response->getStatus().'mk');






    $_GET['phantomjs'] = 'true';
    $url = 'https://geoexhibit.com/viewer-only5view.php?'.http_build_query($_GET);

    //use JonnyW\PhantomJs\Client;

    $client = Client::getInstance();
    $client->getEngine()->setPath('/usr/bin/phantomjs');

    $request = $client->getMessageFactory()->createRequest($url, 'GET');
    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);

    if($response->getStatus() === 200) {

        // Dump the requested page content
        echo $response->getContent();
    }
    else {
        die($response->getStatus());
    }


    */




    /**
     * @see JonnyW\PhantomJs\Http\PdfRequest
     **/
    //$request = $client->getMessageFactory()->createPdfRequest($url, 'GET');

    //$request->setOutputFile('/home/exhibit1836/public_html/viewer-only5/document.pdf');
    //$request->setFormat('A4');
    //$request->setOrientation('landscape');
    //$request->setMargin('1cm');

    /**
     * @see JonnyW\PhantomJs\Http\Response
     **/
    //$response = $client->getMessageFactory()->createResponse();

    // Send the request
    //$client->send($request, $response);









?>
