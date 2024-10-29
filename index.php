<?php
    session_start(['read_and_close' => true]);
		require('admin/incl/const.php');
		require('admin/class/database.php');
    require('admin/class/report.php');
		require('admin/class/user.php');
		require('admin/class/link.php');
		require('admin/class/access_groups.php');

    if(!isset($_SESSION[SESS_USR_KEY])) {
        header('Location: login.php');
        exit;
    }

    if(isset($_POST['logout'])) {
        unset($_SESSION[SESS_USR_KEY]);
        header('Location: login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $obj			= new Report_Class($database->getConn());
		$usr_obj	= new user_Class($database->getConn());
		$acc_obj	= new access_group_Class($database->getConn());

		$users = $obj->getRows();

    $user = $_SESSION[SESS_USR_KEY];
		$usr_grps = $usr_obj->getUserAccessGroups($user->id);

		# reports from access groups
		$group_rows = array();
		$rows1 = array();
		$rows2 = array();
		
		if(count($usr_grps)){
			$usr_grps_keys = array_keys($usr_grps);
			
			# get report IDs from access groups
			$usr_reps = $acc_obj->getGroupReports($usr_grps_keys);
			if(count($usr_reps)){
				$group_rows = $database->getAll('jasper', "id IN (".implode(',', array_keys($usr_reps)).") AND is_grouped = 0", 'id');
			}

			# reports from report groups we own
			$usr_rep_grps = $acc_obj->getGroupReportGroups($usr_grps_keys);
			if(count($usr_rep_grps)){
				$usr_rep_grp_ids = array_keys($usr_rep_grps);
	    	$rows1 = $database->getAll('groups', "id IN (".implode(',', $usr_rep_grp_ids).")", 'id');
			}
			
			#links from access groups we are in
			$usr_links = $acc_obj->getGroupLinks($usr_grps_keys);
			if(count($usr_links)){
				$usr_link_ids = array_keys($usr_links);
	    	$rows2 = $database->getAll('links', "id IN (".implode(',', $usr_link_ids).")", 'id');
			}
			
			$usr_map_grps = $acc_obj->getGroupMapGroups($usr_grps_keys);
			if(count($usr_map_grps)){
				$usr_map_grps_ids = implode(',', array_keys($usr_map_grps));
				$rows3 = $database->getAll('map', "id IN (".$usr_map_grps_ids.")",	'id');
			}
		}

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Jasper Report Publisher</title>



    <!-- Bootstrap core CSS -->
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .card {
          box-shadow: 0 0.15rem 0.55rem rgba(0, 0, 0, 0.1);
          transition: box-shadow 0.3s ease-in-out;
        }

        .card:hover {
          box-shadow: 0 0.35rem 0.85rem rgba(0, 0, 0, 0.3);
        }
        .col {
            padding-right: calc(var(--bs-gutter-x) * .75);
            padding-left: calc(var(--bs-gutter-x) * .75);
        }

@media (min-width: 768px) {
    .row-cols-md-4>* {
        flex: 0 0 auto;
        width: 20%;
    }
}

@media (min-width: 992px) {
    .py-lg-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
}


    </style>

  </head>
  <body>

<header>

  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
                <strong> &nbsp;Jasper Report Publisher</strong>
      </a>

<?php
if(($user->accesslevel == 'Admin') || ($user->accesslevel == 'Devel')){
  echo '<a href="admin/index.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>';
}
?>


      <a href="logout.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>


    </div>
  </div>
</header>


<main style="background-color:#edf0f2">

  <section class="py-5 text-left container" style="padding-bottom: 0rem!important;">
    <div class="row py-lg-5">
      <div class="col-lg-6 col-md-8 mx-auto" style="margin-left: 5px!important;">
        <h1 class="fw-light"><?php echo $_SESSION[SESS_USR_KEY]->name;?> Reports

  </h1>
        <p class="lead text-muted">Reports</p>
      </div>
    </div>
  </section>
  <div class="album py-5 bg-light">
    <div class="container">







        <div class="row row-cols-1 row-cols-md-4 g-4">

					<?PHP foreach($group_rows as $row) { ?>
						<?PHP
								$image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png";

								if(strtolower($row['download_only']) === 'yes') {
									$url = 'download.php?type=pdf&view=yes&id='.$row['id'];
								}else{
									$url = 'view.php?id='.$row['id'];
								}
						?>

					<div class="col">
						<a href="<?=$url?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
								<div class="card">
									<div class="card-body">
										<h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
									</div>
									<div class="px-3">
										<div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: contain; background-position: center center;"></div>
									</div>
									<?PHP if($row['description']) { ?>
											<div class="card-body">
												<p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
											</div>
									<?PHP } ?>
								</div>
						</a>
					</div>

					<?PHP } ?>



          <?PHP foreach($rows1 as $row) { ?>
            <?PHP
                $image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png";
            ?>

          <div class="col">
            <a href="view.php?group_id=<?=$row['id']?>" style="text-decoration:none; color: #6c757d!important; font-size: 1.15rem; font-weight: 300;">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
                  </div>
                  <div class="px-3">
                    <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: contain; background-position: center center;"></div>
                  </div>
                  <?PHP if($row['description']) { ?>
                      <div class="card-body">
                        <p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
                      </div>
                  <?PHP } ?>
                </div>
            </a>
          </div>
          <?PHP } ?>

					
					<?PHP foreach($rows2 as $row) { ?>
            <?PHP
                $image = file_exists("assets/links/{$row['id']}.png") ? "assets/links/{$row['id']}.png" : "assets/links/default.png";
            ?>

          <div class="col">
            <a href="<?=$row['url']?>" target="_blank" style="text-decoration:none; color: #6c757d!important; font-size: 1.15rem; font-weight: 300;">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
                  </div>
                  <div class="px-3">
                    <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: contain; background-position: center center;"></div>
                  </div>
									<?PHP if($row['description']) { ?>
                      <div class="card-body">
                        <p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
                      </div>
                  <?PHP } ?>
                </div>
            </a>
          </div>
          <?PHP } ?>

					<?php foreach($rows3 as $row) {		
						$image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png"; ?>
						<div class="col">
								<a href="apps/<?=$row['id']?>/index.php" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
									<div class="card">
										<div class="card-body">
											<h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
										</div>
										<div class="px-3">
											 <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: contain; background-position: center center;"></div>
										</div>
										<?PHP if($row['description']) { ?>
											<div class="card-body">
												<p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
											</div>
									<?PHP } ?>
									</div>
							</a>
						</div>
				<?php } ?>





</div>

    </div>
  </div>

</main>

<footer class="text-muted py-5">
  <div class="container">
    <p class="float-end mb-1">
<a href="#" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">Back to top</a>    </p>
  </div>
</footer>
    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
