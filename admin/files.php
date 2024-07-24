<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		require('incl/jru-lib.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
		
		define('FM_SELF_URL', $_SERVER['PHP_SELF']);
		define('JS_HOME', get_jasper_home());
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			require 'incl/filemanager.php';
			exit();
		}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">

<style>

.navbar-light .navbar-brand {
    background-color: #fff !important;
}
</style>

</head>

<body>
  
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'files.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
      
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">File Manager</h1>
                        <p>&nbsp;</p>
                    </div>
                </div>
            </div>

						<div class="row align-items-center">
							<?php
								define('FM_EMBED', true);
								require 'incl/filemanager.php';
							?>
			      </div>
        </div>
    </div>
    
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
