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
	<link href="dist/css/admin.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div id="main-wrapper">
        <?php 
            const MENU_SEL = 'index.php';
            include("incl/topbar.php");
            include("incl/sidebar.php");
        ?>
       
        <div class="page-wrapper">
            <div class="page-header" style="border-bottom: 0px!Important;">
                <div>
                    <h1>Dashboard</h1>
                </div>
                
            </div>
            <!-- Dashboard Cards -->
            <div class="dashboard-grid" style="width:80%!important">
                <!-- System Card -->
                <div class="card" onclick="window.location='users.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-account"></i>
                        <span>Users</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-account"></i>
                            <h3>User Management</h3>
                            <p>Add and Edit Users</p>
                        </div>
                    </div>
                </div>


 <div class="card" onclick="window.location='access_groups.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-account-multiple"></i>
                        <span>Groups</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-account-multiple"></i>
                            <h3>Groups</h3>
                            <p>Create and Manage Groups</p>
                        </div>
                    </div>
                </div>

 <div class="card" onclick="window.location='services.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-cog"></i>
                        <span>Services</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-cog"></i>
                            <h3>System Management</h3>
                            <p>Add and Edit System Settings</p>
                        </div>
                    </div>
                </div>


 <div class="card" onclick="window.location='datasources.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-database"></i>
                        <span>Datasources</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-database"></i>
                            <h3>Datasources</h3>
                            <p>Add and Edit Databases</p>
                        </div>
                    </div>
                </div>


 <div class="card" onclick="window.location='publish.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-book"></i>
                        <span>Publish</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-book"></i>
                            <h3>Publish</h3>
                            <p>Publish Reports</p>
                        </div>
                    </div>
                </div>



                <!-- Data Sources Card -->
                <div class="card" onclick="window.location='schedules.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-clock"></i>
                        <span>Schedules</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-clock"></i>
                            <h3>Schedules</h3>
                            <p>Add and Edit Schedules</p>
                        </div>
                    </div>
                </div>

                <!-- Jasper Reports Card -->
                <div class="card" onclick="window.location='reporting.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-file-chart"></i>
                        <span>Run Report</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-file-chart"></i>
                            <h3>Run Reports</h3>
                            <p>Manually Run Scheduled Report</p>
                        </div>
                    </div>
                </div>

                <!-- More Card -->
                <div class="card" onclick="window.location='maps.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-gauge"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-gauge"></i>
                            <h3>Dashboard</h3>
                            <p>Manage Dashboard Reports</p>
                        </div>
                    </div>
                </div>


<div class="card" onclick="window.location='parameters.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-variable"></i>
                        <span>Parameters</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-variable"></i>
                            <h3>Parameters</h3>
                            <p>Manage Parameters</p>
                        </div>
                    </div>
                </div>



<div class="card" onclick="window.location='maps.php'" style="cursor: pointer;">
                    <div class="card-header">
                        <i class="mdi mdi-map"></i>
                        <span>Maps</span>
                    </div>
                    <div class="card-body">
                        <div class="card-content">
                            <i class="mdi mdi-map"></i>
                            <h3>Maps</h3>
                            <p>QGIS Maps and R Apps</p>
                        </div>
                    </div>
                </div>




            </div>

            <!-- Action Buttons Groups -->
            

            <!-- Help Topics -->
            <h2 class="help-title">Help Topics</h2>
            <div class="help-topics">
                <div>
                    <h3>System</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Users</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/usergroups/index.html" target="_blank">User Groups</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/tomcat/index.html" target="_blank">Services</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/email/index.html" target="_blank">Email Configuration</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Data Sources</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/data/index.html" target="_blank">All</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">PostGIS</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">GeoServer</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Create</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Jasper Reports</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/publish/index.html" target="_blank">Publish</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/schedule/index.html" target="_blank">Schedule</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/parameters/index.html" target="_blank">Parameters</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/context/index.html" target="_blank">Context</a></li>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/run/index.html" target="_blank">Manual Run</a></li>
                    </ul>
                </div>

                <div>
                    <h3>QGIS</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/qgis/index.html" target="_blank">Docs</a></li>
                    </ul>
                </div>

                <div>
                    <h3>R</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/r/index.html" target="_blank">Docs</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Links</h3>
                    <ul>
                        <li><a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/links/index.html" target="_blank">Publish</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
