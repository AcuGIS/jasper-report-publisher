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
				a {
text-decoration:none!important;
}




ul {
  list-style-type: none;
padding: 0; /* Remove padding */
  margin: 0; /* Remove margins */
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
       
        <div class="page-wrapper">
            
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6" style="padding-left:100px">

                        <h2 class="mb-0 fw-bold">Jasper Publisher</h2>
 

                    </div>
                    
                </div>
            </div>


           <br>
            <div class="container-fluid" style="padding-left:130px">


<div class="row">
    <div class="card" style="width: 80%;">
        <div class="row">
            <div class="col-3" style="padding: 18px;">
               <h4  style="font-size: 22px;"><i class="mdi mdi-clock" style="color:orange!important; "></i> System </h4>
            </div>
            
          
            
            <div class="col-5" style="display: flex;align-items: center;">
                <a href="registration.php" button type="button" class="btn btn-danger" >Add User</a>&nbsp;<a href="access_groups.php" button type="button" class="btn btn-danger" >Groups</a>&nbsp;<a href="services.php" button type="button" class="btn btn-danger" >Restart Tomcat</a>&nbsp;<a href="files.php?p=" button type="button" class="btn btn-danger" >File Browser</a>
            </div>
            
        </div>
    </div>
</div>




<div class="row">
    <div class="card" style="width: 80%;">
        <div class="row">
            <div class="col-3" style="padding:18px">
              <h4  style="font-size: 22px;"><i class="mdi mdi-database" style="color:orange!important; "></i> Data Sources </h4>
            </div>
            
              <!--<div class="col-2">
                <div style="padding:20px">
                    <p class="card-text">placeat?</p>
                </div>
            </div>-->
            
            <div class="col-5" style="display: flex;align-items: center;">
                <a href="datasources.php"button type="button" class="btn btn-success">All</a>&nbsp;<a href="datasources.php?tab=pg" button type="button" class="btn btn-success">PostGIS</a>&nbsp;<a href="datasources.php?tab=gs" button type="button" class="btn btn-success">GeoServer</a>&nbsp;<a href="datasources.php?tab=import" button type="button" class="btn btn-success">Create Database</a>
            </div>
            
        </div>
    </div>
</div>

<div class="row">
    <div class="card" style="width: 80%;">
        <div class="row">
            <div class="col-3" style="padding:18px">
              <h4  style="font-size: 22px;"><i class="mdi mdi-file-chart" style="color:orange!important; "></i> Jasper Reports </h4>
            </div>
            
            <!--<div class="col-2">
                <div style="padding:20px">
                    <p class="card-text">placeat?</p>
                </div>
            </div>-->
            
            <div class="col-6" style="display: flex;align-items: center;">
                <a href="publish.php" button type="button" class="btn btn-primary">Publish</a>&nbsp;<a href="schedules.php" button type="button" class="btn btn-primary">Schedule</a>&nbsp;<a href="reporting.php" button type="button" class="btn btn-primary">Run
</a>&nbsp;<a href="parameters.php" button type="button" class="btn btn-primary">Parameters

</a>&nbsp;<a href="contexts.php" button type="button" class="btn btn-primary">Context
</a>&nbsp;<a href="reports.php" button type="button" class="btn btn-primary">Dashboard Report</a>
            </div>
            
        </div>
    </div>
</div>



<div class="row">
    <div class="card" style="width: 80%;">
        <div class="row">
            <div class="col-3" style="padding:18px">
<h4  style="font-size: 22px;"><i class="mdi mdi-map" style="color:orange!important; "></i> More </h4>
            </div>
            
              
            
            <div class="col-5" style="display: flex;align-items: center;">
                <a href="maps.php" button type="button" class="btn btn-warning" >QGIS and R</a>&nbsp;<a href="linkss.php" button type="button" class="btn btn-info" >Links</a>&nbsp;<a href="permalinks.php" button type="button" class="btn btn-info" >Share</a>&nbsp;<a href="../index.php" button type="button" class="btn btn-success" >Frontend</a>
            </div>
            
        </div>
    </div>
</div>







                <div class="row align-items-center">
                    <div class="col-6">

                        <h3 class="mb-0 fw-bold"><br>Help Topics</h3>
 

                    </div>
                    
                </div>



       

          
          <div class="row" >
            <div class="col-auto" style="padding-right:30px">
                 <h3> System </h3>

<ul>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Users</a> </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/usergroups/index.html" target="_blank">User Groups</a> </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/tomcat/index.html" target="_blank">Services</a> </li>
<!--<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Files</a> </li>-->
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/email/index.html" target="_blank">Email Configuration</a> </li>

</ul>

            </div>


 <div class="col-auto" style="padding-right:30px">
                 <h3> Data Sources </h3>

<ul>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/data/index.html" target="_blank">All</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">PostGIS</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">GeoServer</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/users/index.html" target="_blank">Create</a>  </li>

</ul>

            </div>



            <div class="col-auto" style="padding-right:30px">
                  <h3> Jasper Reports</h3>

<ul>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/publish/index.html" target="_blank">Publish</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/schedule/index.html" target="_blank">Schedule</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/parameters/index.html" target="_blank">Parameters</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/context/index.html" target="_blank">Context</a>  </li>
<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/run/index.html" target="_blank">Manual Run</a>  </li>




</ul>
            </div>
            <div class="col-auto" style="padding-right:30px">
<h3> QGIS</h3>
               <ul>

<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/qgis/index.html" target="_blank">Docs</a>  </li>

</ul>
            </div>
            <div class="col-auto" style="padding-right:30px">
               <h3> R </h3>
               <ul>

<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/r/index.html" target="_blank">Docs </a> </li>


</ul>
             </div>

 <div class="col-auto" style="padding-right:30px">
<h3> Links  </h3>
               <ul>

<li> <a href="https://jasper-report-publisher.docs.acugis.com/en/latest/components/links/index.html" target="_blank">Publish </a> </li>

</ul>
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
