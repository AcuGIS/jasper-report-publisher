<?PHP
    ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

		require('admin/incl/const.php');
    require('admin/class/database.php');
		require('admin/class/user.php');
    session_start(['read_and_close' => true]);

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);

    if(isset($_GET['phantomjs'])) {
        $user = null;
    }
    else {
			require('admin/class/access_groups.php');

        if(!isset($_SESSION[SESS_USR_KEY])) {
            header('Location: login.php');
            exit;
        }
        $user = $_SESSION[SESS_USR_KEY];


				$usr_obj = new user_Class($database->getConn());
				$usr_grps = $usr_obj->getUserAccessGroups($user->id);

				if(count($usr_grps)){
					$acc_obj = new access_group_Class($database->getConn());

					# get report IDs from access groups
					$usr_reps = $acc_obj->getGroupReports(array_keys($usr_grps));
					$acc_rep_ids = array();
					if(count($usr_reps)){
						$acc_rep_ids = array_keys($usr_reps);	// ids of reports user is allowed to access
					}
				}
    }


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

		if($user){
			# check if user can access all ids
			foreach($ids as $id){
				if(!in_array($id, $acc_rep_ids)){
					die('Report with ID: '.$id. ' is not allowed');
				}
			}
		}

    function _get($name) {
        return isset($_GET[$name]) ? $_GET[$name] : '';
    }

    function myFilter($var){
        return ($var !== NULL && $var !== FALSE && $var !== "");
    }


    $ptype = '';
    $pname = '';
    $params = [];
    $pParameters = [];

    foreach($ids as $id) {
        $paramrows = $database->getAll('parameters', 'reportid = '.$id);
        
        foreach($paramrows as $paramrow){
            if(!$ptype) {
                $ptype = $paramrow['ptype'];
                $pname = $paramrow['pname'];
            }

            if($paramrow['ptype'] == 'dropdown') {

                if($paramrow) {
                    $params = explode(',', $paramrow['pvalues']);

                    if( !in_array(_get($paramrow['pname']),  $params) ) {
                        $_GET[$paramrow['pname']] = $params[0];
                    }

                }
            }
            else if($paramrow['ptype'] == 'query') {
                $DB_pvalues = explode(',', $paramrow['pvalues']);

                foreach($DB_pvalues as $v) {
                    if($v) {
                        $pParameters[$v] =  isset($_GET[$v]) ? $_GET[$v] : '';
                    }
                }
            }
        }
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>JRI Map Viewer</title>

<!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">-->

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.min.js"></script>

    <style type="text/css">
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

      nav {
        position: sticky; top: 0!important;
      }

.context
{

}
.context:empty
{
    padding-right:200px;
}

.panel {
    margin-bottom: 20px;
    background-color: #fff;
    border: 1px solid transparent;
    border-radius: 4px;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
    box-shadow: 0 1px 1px rgba(0,0,0,.05);
    
}


 

    </style>

  </head>
  <body>
      <?PHP //if(!isset($_GET['phantomjs'])) { ?>
    <nav>
<header>

  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
               <strong> &nbsp;Jasper Report Publisher</strong>
      </a>

<?php
if($user && $user->accesslevel == 'Admin') {
  echo '<a href="admin/index.php" style="text-decoration:none!important; color: #fff!important; font-size: 1.25rem!important; font-weight: 300!important;">Administration</a>';
}
?>


      <a href="/" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Return to Dashboard</a>


    </div>
  </div>
</header>

</nav>

<main style="padding-top: 25px;">


<?PHP
    // prepare query
    $query = $queryID = array_filter($_GET, "myFilter");

    $queryID = http_build_query($queryID);

    unset($query['id']);
    unset($query['group_id']);
    unset($query['phantomjs']);

    $query_array = $query;
    $query = http_build_query($query);
?>


<?PHP if(count($ids) == 1) { ?>
<div class="m-auto py-4" style="max-width: 590px; padding-bottom: 50px">
    <span> </span> &nbsp;&nbsp;
    <a href="download.php?type=pdf&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as PDF" data-bs-content="Download Report as PDF File"><img style="width: 40px;" src="assets/images/pdf.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=docx&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as DOCX" data-bs-content="Download Report as DOCX File"><img style="width: 40px;" src="assets/images/docx.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=csv&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as CSV" data-bs-content="Download Report as CSV File"><img style="width: 40px;" src="assets/images/csv.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=html&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as HTML" data-bs-content="Download Report as HTML File"><img style="width: 40px;" src="assets/images/html.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=html2&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as HTML2" data-bs-content="Download Report as HTML2 File"><img style="width: 40px;" src="assets/images/html2.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=pptx&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as PPTX" data-bs-content="Download Report as PPTX File"><img style="width: 40px;" src="assets/images/ppt.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=rtf&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as RTF" data-bs-content="Download Report as RTF File"><img style="width: 40px;" src="assets/images/rtf.png"/></a> &nbsp;&nbsp;
    <a href="download.php?type=xlsx&<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as XLSX" data-bs-content="Download Report as Excel File"><img style="width: 40px;" src="assets/images/xls.png"/></a>&nbsp;&nbsp;
    <a href="./" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Return to Dashboard" data-bs-content="Return to Dashboard">&nbsp;&nbsp;&nbsp;<img style="width: 40px;" src="assets/images/back.png"/></a>
</div>
<?PHP } else { ?>
<div class="m-auto py-4" style="max-width: 590px;">
    <span> </span> &nbsp;&nbsp;
    <a href="phantomjs.php?<?=$queryID?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Export as PDF" data-bs-content="Download Report as PDF File"><img style="width: 40px;" src="assets/images/pdf.png"/></a> &nbsp;&nbsp;
    <a href="./" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" title="Return to Dashboard" data-bs-content="Return to Dashboard">&nbsp;&nbsp;&nbsp;<img style="width: 40px;" src="assets/images/back.png"/></a>
</div>
<?PHP } ?>

<?PHP if($ptype) { ?>
<div class="m-auto" style="max-width: 590px; margin-bottom: 30px !important;">
    <div class="container">
        <div class="row">
            <div class="col">
                <table>
                <?PHP foreach($query_array as $k => $q) { ?>
                    <tr>
                        <th><?=$k?>: </th>
                        <td> <?=$q?></td>
                    </tr>
                <?PHP } ?>
                </table>
            </div>
            <div class="col">
                <button type="buttom" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i>  Report Filter Selection</button>
            </div>
        </div>
    </div>

    <div class="px-5">
    </div>
</div>
<?PHP } ?>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel">Filter Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="filterForm">
            <input type="hidden" name="id" value="<?=_get('id')?>" />
            <input type="hidden" name="group_id" value="<?=_get('group_id')?>" />

            <?PHP if($ptype == 'dropdown' && $params) { ?>
                <div class="mb-3">
                  <label for="<?=$pname?>" class="form-label"><?=$pname?></label>
                  <select name="<?=$pname?>" class="form-control">
                    <?PHP foreach($params as $p) { ?>
                    <option <?=(_get($pname) == $p ? 'selected' : '')?> value="<?=$p?>"><?=$p?></option>
                    <?PHP } ?>
                  </select>
                </div>

            <?PHP } else if($ptype == 'query') { ?>
                <h6><?=$pname?></h6>

                <?PHP foreach($pParameters as $k => $v) { ?>
                <div class="mb-3">
                  <label for="<?=$k?>" class="form-label"><?=$k?></label>
                  <input type="text" name="<?=$k?>" class="form-control" id="<?=$k?>" placeholder="<?=$k?>" value="<?=$v?>" />
                </div>
                <?PHP } ?>

            <?PHP } else { ?>
                <p> Sorry, No Filter Record Found!</p>
            <?PHP } ?>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onClick="$('#filterForm').submit();">Apply</button>
      </div>
    </div>
  </div>
</div>


<?PHP if(isset($_GET['phantomjs'])) { ?>
<div id="context">
<div class="panel panel-default position-relative" style="position: relative;">
  <div class="panel-body">
<p>&nbsp;</p>
            <?php
                $hasContent = false;
				foreach($ids as $i) {
                    $row = $database->get('inputs', 'report_id = '.$id);

                    if($row) {
            			echo $row['input'];
            			if($row['input']) $hasContent = true;
            		 }
                }
            ?>


</div>
</div></div>
<div class="shadow p-3 mb-5 bg-white rounded">
<table>
    <tr>

            <?PHP
                foreach($ids as $i) {
                    $row = $database->get('jasper', 'id = '.intval($i));
                    $repFormat = $row['download_only'] ? str_replace('%3D', '=', trim(urlencode($row['download_only']))) : '';

                    if($row) {
												$drow = $database->get('datasource', 'id = '.$row['datasource_id']);
                        echo '<div class="col">';
                            readfile('http://localhost:8080/JasperReportsIntegration/report?_repName='.$row['repname'].'&_repFormat=html&_dataSource='.$drow['name'].'&'.$query.'&'.$repFormat);
                        echo '</div>';

                    }
                }
            ?>

    </tr>
</table>
<?PHP } else { ?>



<div class="container" style="display: flex;">

    <div id="context">
        <div class="panel panel-default position-relative" style="position: relative;" >
            <div class="panel-body">
                <p>&nbsp;</p>

				<?php
				$hasContent = false;
				foreach($ids as $i) {
                    $row = $database->get('inputs', 'report_id = '.$id);

                    if($row) {
			            echo $row['input'];
			            if($row['input']) $hasContent = true;
		            }
                }
                $hasContent = false;
                ?>
            </div>
        </div>
    </div>



    <div class="row" id="report-container" data-content="<?= ($hasContent ? 'true' : 'false') ?>">


            <?PHP
                foreach($ids as $i) {
                    $row = $database->get('jasper', 'id = '.intval($i));
                    $repFormat = $row['download_only'] ? str_replace('%3D', '=', trim(urlencode($row['download_only']))) : '';

                    if($row) {
											$drow = $database->get('datasource', 'id = '.$row['datasource_id']);
                        echo '<div class="col">';
												
												$url = 'http://localhost:8080/JasperReportsIntegration/report?_repName='.$row['repname'].'&_repFormat=html&_dataSource='.$drow['name'].'&'.$query.'&'.$repFormat;
												$cookiefile = '/tmp/qwc2cookie'.session_id();
													
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $url);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
												curl_setopt($ch, CURLOPT_COOKIESESSION, true);
												curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
												
												$response = curl_exec($ch);
												if (curl_error($ch)) {
													unlink($cookiefile);
													echo '';
												}else{
													curl_close($ch);
													echo $response;
												}
                        echo '</div>';

                    }
                }
            ?>

    </div>
</div>
<?PHP } ?>


</main>


<footer class="text-muted py-5" style="clear:both">
  <div class="container">
    <p class="float-end mb-1">
<a href="#" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">Back to top</a>    </p>
  </div>
</footer>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script>
        $(function () {
          $('[data-bs-toggle="popover"]').popover();


          var hasContent = $('#report-container').attr('data-content');
          if(hasContent === 'false') {
              $('#report-container').css('width', '100%');
          }
          else {
              var contextWidth = (1320-$('#report-container').width()) / 1;
              $('#context').css('width', contextWidth + 'px');
          }

        });
    </script>

  </body>
</html>
