<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/report.php');
    require('class/user.php');

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();

    $obj = new Report_Class($dbconn);
    $rows = $obj->getRows();

    $obj = new user_Class($dbconn);
    $users_obj = $obj->getRows();
    $users = [];
    while($us = pg_fetch_object($users_obj)) {
        $users[$us->id] = $us->name;
    }

    if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
        header('Location: ../login.php');
        exit;
    }
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">

			<style type="text/css">
				body {
					background-color: #ffffff;
				}
				.card {
					background: white;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.05);
					margin-bottom: 5px;
					border: none;
					width: 100%;
					height: 100%;
				}
				.card-header {
					background: none;
					border-bottom: 1px solid #e9ecef;
					padding: 20px;
					height: 100%;
				}
				.system-card .card-header {
					background-color: #fff5f5;
				}
				.datasources-card .card-header {
					background-color: #f5fff5;
				}
				.reports-card .card-header {
					background-color: #f5f5ff;
				}
				.more-card .card-header {
					background-color: #fffff5;
				}
				.card-body {
					padding: 20px;
				}
				.link-group {
					display: flex;
					flex-direction: column;
					gap: 5px;
					margin-top: 15px;
				}
				.link-group a {
					color: #495057;
					text-decoration: none;
					transition: color 0.2s;
					display: block;
					padding: 2px 0;
					line-height: 1.3;
				}
				.link-group a:hover {
					color: #0d6efd;
				}
				.card-title {
					color: #495057;
					font-size: 1.25rem;
					margin: 0;
					display: flex;
					align-items: center;
					gap: 10px;
					padding-bottom: 15px;
					border-bottom: 1px solid #e9ecef;
				}
				.card-title i {
					color: #495057;
				}
				.btn {
					padding: 8px 16px;
					border-radius: 4px;
					font-weight: 500;
					transition: all 0.2s;
					border: none;
					text-decoration: none;
					display: inline-block;
				}
				.btn:hover {
					transform: translateY(-1px);
					box-shadow: 0 2px 4px rgba(0,0,0,0.1);
				}
				.btn-danger {
					background-color: #dc3545;
					color: white;
				}
				.btn-success {
					background-color: #28a745;
					color: white;
				}
				.btn-primary {
					background-color: #0d6efd;
					color: white;
				}
				.btn-warning {
					background-color: #ffc107;
					color: #212529;
				}
				.btn-info {
					background-color: #0dcaf0;
					color: white;
				}
				.help-section {
					background: white;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.05);
					padding: 30px;
					margin-top: 30px;
				}
				.help-section h3 {
					color: #495057;
					font-size: 1.5rem;
					margin-bottom: 20px;
					font-weight: 600;
				}
				.help-section h4 {
					color: #495057;
					font-size: 1.1rem;
					margin-bottom: 15px;
					font-weight: 500;
				}
				.help-section ul {
					list-style: none;
					padding: 0;
					margin: 0;
				}
				.help-section ul li {
					margin-bottom: 10px;
				}
				.help-section ul li a {
					color: #495057;
					text-decoration: none;
					transition: color 0.2s;
				}
				.help-section ul li a:hover {
					color: #0d6efd;
				}
				.page-title {
					color: #495057;
					font-size: 2rem;
					font-weight: 600;
					margin-bottom: 30px;
				}
				.container-fluid {
					padding: 30px;
				}
				.button-group {
					display: flex;
					gap: 10px;
					flex-wrap: wrap;
					justify-content: flex-start;
				}
				.row {
					margin-bottom: 30px;
				}
				.row > [class*='col-'] {
					margin-bottom: 30px;
				}
			</style>

 <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
  <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>

</head>

<body>




   
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">



        <?php const MENU_SEL = 'index.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
       
        <div class="page-wrapper" style="background-color:#fff!important">
            <div class="container-fluid">
                <h1 class="page-title" style="font-size:32px">Jasper Report Publisher</h1>

                <div class="row">
                    <!-- System Section -->
                    <div class="col-md-6">
                        <div class="card system-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="mdi mdi-clock"></i>
                                    System
                                </h4>
                                <div class="link-group">
                                    <a href="registration.php">Add User</a>
                                    <a href="access_groups.php">Groups</a>
                                    <a href="services.php">Restart Tomcat</a>
                                    <a href="files.php?p=">File Browser</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Sources Section -->
                    <div class="col-md-6">
                        <div class="card datasources-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="mdi mdi-database"></i>
                                    Data Sources
                                </h4>
                                <div class="link-group">
                                    <a href="datasources.php">All</a>
                                    <a href="datasources.php?tab=pg">PostGIS</a>
                                    <a href="datasources.php?tab=gs">GeoServer</a>
                                    <a href="datasources.php?tab=import">Create Database</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Jasper Reports Section -->
                    <div class="col-md-6">
                        <div class="card reports-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="mdi mdi-file-chart"></i>
                                    Jasper Reports
                                </h4>
                                <div class="link-group">
                                    <a href="publish.php">Publish</a>
                                    <a href="schedules.php">Schedule</a>
                                    <a href="reporting.php">Run</a>
                                    <a href="parameters.php">Parameters</a>
                                    <a href="contexts.php">Context</a>
                                    <a href="reports.php">Dashboard Report</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- More Section -->
                    <div class="col-md-6">
                        <div class="card more-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="mdi mdi-map"></i>
                                    More
                                </h4>
                                <div class="link-group">
                                    <a href="maps.php">QGIS and R</a>
                                    <a href="linkss.php">Links</a>
                                    <a href="permalinks.php">Share</a>
                                    <a href="../index.php">Frontend</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="help-section">
                    <h3>Help Topics</h3>
                    <div class="row">
                        <div class="col-md-3">
                            <h4>System</h4>
                            <ul>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Users</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/usergroups/index.html" target="_blank">User Groups</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/tomcat/index.html" target="_blank">Services</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/email/index.html" target="_blank">Email Configuration</a></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h4>Data Sources</h4>
                            <ul>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/data/index.html" target="_blank">All</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">PostGIS</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">GeoServer</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Create</a></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h4>Jasper Reports</h4>
                            <ul>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/publish/index.html" target="_blank">Publish</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/schedule/index.html" target="_blank">Schedule</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/parameters/index.html" target="_blank">Parameters</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/context/index.html" target="_blank">Context</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/run/index.html" target="_blank">Manual Run</a></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h4>QGIS & R</h4>
                            <ul>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/qgis/index.html" target="_blank">QGIS Docs</a></li>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/r/index.html" target="_blank">R Docs</a></li>
                            </ul>
                            <h4>Links</h4>
                            <ul>
                                <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/links/index.html" target="_blank">Publish</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
           
            <footer class="footer text-center">

            </footer>
           
        </div>
        
    </div>
    
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
