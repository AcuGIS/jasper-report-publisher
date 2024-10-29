<?php
session_start(['read_and_close' => true]);
require('../incl/const.php');
require('../class/database.php');
require('../class/schedule.php');
require('../class/datasource.php');
require('../incl/jru-lib.php');

$result = ['success' => false, 'message' => 'Error while processing your request!'];
$optional_keys = array('email', 'email_subj', 'email_body', 'url_opt_params', /*'noemail',*/ 'email_tmpl');


if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS)) {
	echo json_encode($result);
	return;
}

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
$obj = new schedule_Class($database->getConn());

if(isset($_POST['datasource_id'])){
	$ds_obj = new datasource_Class($database->getConn());
	$ds = $ds_obj->getById($_POST['datasource_id']);
	$_POST['datasource'] = $ds['name'];
}

if(isset($_POST['delete']) || isset($_POST['update']) ){
	
	if(isset($_POST['update']) && !isset($_POST['email']) && !isset($_POST['noemail']) ){
		$result = ['success' => false, 'message' => 'No mail'];
		echo json_encode($result);
		return;
	}
	
	
	$sched = $obj->getById($_POST['id']);
	if($sched === false){
		$result = ['success' => false, 'message' => 'No such schedule'];
		echo json_encode($result);
		return;
	}

	$sch_env  = get_jasper_home().'/schedules/'.$_POST['id'].'_env.sh';

	$old_period = $sched['cron_period'];
	if($old_period == 'never'){
		$lines = [' '];
		$ln = 0;
	}else{
	  $cronfile = get_jri_cronfile($old_period);  #file with old cron entry
		$lines = file($cronfile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		#get line number from schedule id
	  $ln = find_cron_ln($lines, $_POST['id']);
	}

	if($ln != -1){

		if(isset($_POST['delete'])){
			$lines[$ln] = "\n";
			unlink($sch_env);
			
			if($obj->delete($_POST['id'])){
				$result = ['success' => true, 'message' => 'Schedule '.$_POST['id'].' deleted!'];	
			}else{
				$result = ['success' => false, 'message' => 'Schedule '.$_POST['id'].' deleted but record delete failed!'];
			}
			
	  }else{
			if(!isset($_POST['noemail'])){	// nomail checkbox is not posted, if uncheck
				$_POST['noemail'] = 'f';
			}
	
			if(!$obj->update($_POST)){
				$result = ['success' => false, 'message' => 'Schedule record update failed!'];
			}else if($_POST['cron_period'] != $old_period){ #if period is different, files are different
				$lines[$ln] = "\n";
		    $_POST['create'] = true; #change mode to create
				unlink($sch_env);

		  }else{  #period is the same, so just update line
		    $lines[$ln] = build_cronline();
				$result = ['success' => true, 'message' => 'Schedule updated!'];
		  }
		}
		
		if($old_period != 'never'){
			write_file($lines, $cronfile);
			if(str_ends_with($cronfile, '.crontab')){
				shell_exec('crontab -u www-data '.$cronfile);
			}
		}
		
  }else{
		$result = ['success' => false, 'message' => 'Invalid fln'];
  }
}

if(isset($_POST['create'])){	// add mode
	
	if(!isset($_POST['update'])){
		
		if(!isset($_POST['noemail'])){	// nomail checkbox is not posted, if uncheck
			$_POST['noemail'] = 'f';
		}else if($_POST['noemail'] == 't'){	// if nomail, optional fields are not posted
			foreach($optional_keys as $k){
				$_POST[$k] = '';
			}
		}
		
		$_POST['id'] = $obj->create($_POST);
	}
	
	if($_POST['id'] == 0){
		$result = ['success' => false, 'message' => 'Schedule record create failed!'];
	}else {
		
		if($_POST['cron_period'] == 'never'){
			build_sch_env($_POST);
		}else{
			$cronfile = get_jri_cronfile($_POST['cron_period']);

		  if($_POST['cron_period'] == 'custom'){

		    #if file doesn't exist, add a header
		    if(!is_file($cronfile)){
		      $fh = fopen($cronfile, "w");
					fwrite($fh, 'SHELL=/bin/sh'."\n");
		      fwrite($fh, 'PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin'."\n");
					fwrite($fh, '# m h dom mon dow command'."\n");
		      fclose($fh);
		    }
		  }

			$fh = fopen($cronfile, "a");
			fwrite($fh, build_cronline()."\n");
		  fclose($fh);

			if($_POST['cron_period'] == 'custom'){
				shell_exec('crontab -u www-data '.$cronfile);
			}
		}

		# copy to remote server
		$mode = isset($_POST['update']) ? 'updated' : 'created';
		$result = ['success' => true, 'message' => 'Schedule '.$_POST['id'].' '.$mode.'!'];
	}
}

echo json_encode($result);