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
		</style>


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
                    <div class="col-6">

                        <h1 class="mb-0 fw-bold">Dashboard</h1>
                    </div>
                    <div class="col-6">

                    </div>
                </div>
            </div>
           
            <div class="container-fluid">

				<!-- 7. Stats card --><div class="row">
          <div class="              d-flex
              border-bottom
              title-part-padding
              px-0
              mb-3
              align-items-center
            "
          >

          </div>
          <div class="row">
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="reports.php"
                class="card bg-secondary text-white w-100 card-hover">
                <div class="card-body" style="text-decoration: none">
                  <div class="d-flex align-items-center">
                    <span class="ri-shopping-basket-2-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white" style="text-decoration: none">Reports</h4>
                    <h6 class="card-text fw-normal text-white-50" style="text-decoration: none">
                      Add and edit reports
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="parameters.php"
                class="card bg-warning text-white w-100 card-hover"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="bi bi-cup-straw display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">Parameters</h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Add and edit Parameters
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="groups.php"
                class="card bg-danger text-white w-100 card-hover"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="ri-calendar-event-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Report Groups
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Add and edit Report Groups
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="show_input.php"
                class="card bg-primary text-white w-100 card-hover"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="ri-apple-fill display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Contexts
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Add and Edit Contexts
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="users.php"
                class="card bg-info text-white w-100 card-hover"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="ri-folders-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">Users</h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Manage Users
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="../index.php"
                class="card bg-success text-white w-100 card-hover"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="ri-spam-2-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Front End
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      View front end without logging out
                    </h6>
                  </div>
                </div>
              </a>
            </div>

          </div>
         





                
                <div class="row">
                    
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
