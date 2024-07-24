<?php
	const CRON_PERIOD = array('custom', 'hourly', 'daily', 'weekly', 'monthly');
	const REP_FORMATS = array('pdf', 'html', 'html2', 'rtf', 'xls', 'jxl', 'csv', 'xlsx', 'pptx', 'docx');
	const DS_KEYS			= array('type', 'name', 'url', 'username', 'password');
	const SCH_KEYS		= array('cron_period', 'name', 'format', 'datasource', 'filename', 'email', 'email_subj', 'email_body', 'url_opt_params', 'noemail', 'email_tmpl');
	const JRI_REPORT_SCRIPT = '/usr/local/bin/gen_jri_report.sh';

function get_jri_cronfile($period){
	if($period == 'custom'){
		return get_jasper_home().'/jri_schedule.crontab';
	}else{
		return '/etc/cron.'.$period.'/jri_schedules.sh';
	}
}

function read_env_file($filename){
	$rv = array();
	$lines = file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	foreach($lines as $line){

		$pos = strpos($line, '=');
		$k = substr($line, 0, $pos);
		$v = substr($line, $pos + 1);

		#remove quotes
		$len = strlen($v);
		if(($len > 0) && ($v[0] == '"') && ($v[$len-1] == '"')){
			$v = substr($v, 1, -1);
		}

		$rv[$k] = $v;
	}
	return $rv;
}

function write_env_file($vars, $filename){
	$fp = fopen($filename, 'w');
	foreach($vars as $k => $v){
		
		if(strpbrk($v, "'")){	// if we have single quotes
			$v = str_replace("'", "'\''", $v);	//escape them
		}
		
		if(strpbrk($v, " \t&$()`")){
			fwrite($fp, $k."='".$v."'\n");
		}else{
			fwrite($fp, $k.'='.$v."\n");
		}
	}
	fclose($fp);
}

function write_file($lines, $filename){
	$fp = fopen($filename, 'w');
	foreach($lines as $line){
		fwrite($fp, "$line\n");
	}
	fclose($fp);
}

function get_catalina_home(){
	$vars = read_env_file('/etc/environment');
	if(isset($vars['CATALINA_HOME'])){
		if(substr($vars['CATALINA_HOME'], -1) == '/'){
			$vars['CATALINA_HOME'] = substr($vars['CATALINA_HOME'], 0, -1);
		}
	}
	return $vars['CATALINA_HOME'];
}

function get_jasper_home(){
	return get_catalina_home().'/jasper_reports';
}

function get_prop_file(){
  return get_jasper_home().'/conf/application.properties';
}

function my_exec($cmd){
	$output = null;
	$retval = null;
	exec($cmd.' 2>&1', $output, $retval);
	return $output;
}

function get_all_rep_ids(){
	$rv = array();
	$report_dir = get_jasper_home().'/reports';
	
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($report_dir));
	$it->rewind();
	while($it->valid()) {
		$report = $it->key();
		if(is_file($report) && str_ends_with($report, '.jrxml')){
			$report_folder = str_replace('.jrxml', '', $it->getSubPathName());
	    array_push($rv, $report_folder);
		}
		$it->next();
	}

  sort($rv, SORT_STRING);

	return $rv;
}

function build_sch_env($schedule, $run_env = false){
	# NOTE: use unique ID to avoid two users using the same x_env.sh file!
	$env_id = ($run_env) ? 1000000 + $_SESSION[SESS_USR_KEY]->id : $schedule['schid'];
	
	$sch_env = get_jasper_home().'/schedules/'.$env_id.'_env.sh';	

	if(isset($schedule['url_opt_params'])){
  	$optParams = preg_split('/,/', $schedule['url_opt_params']);
	}else{
		$optParams = array();
	}

	if(isset($schedule['email_body'])){
		$schedule['email_body'] = preg_replace('/(\r?\n)+/', '<\/br>/', $schedule['email_body']);
	}

  $vars = array('schid'=> $schedule['schid'],
              'REP_ID'=> $schedule['name'], 'REP_FORMAT'=>$schedule['format'],
              'REP_DATASOURCE'=>$schedule['datasource'], 'REP_FILE'=>$schedule['filename'],
              'OPT_PARAMS'=> implode('&', $optParams));
	
	$vars['RECP_EMAIL'] = isset($schedule['email'])					 ? $schedule['email']			 : '';
	$vars['EMAIL_SUBJ'] = isset($schedule['email_subj'])		 ? $schedule['email_subj'] : '';
	$vars['EMAIL_BODY'] = isset($schedule['email_body']) 	 	 ? $schedule['email_body'] : '';
	$vars['EMAIL_TEMPLATE'] = isset($schedule['email_tmpl']) ? $schedule['email_tmpl'] : '';
	
	write_env_file($vars, $sch_env);
	
	return $sch_env;
}

function build_cmd_line($schedule){

  $cmd = JRI_REPORT_SCRIPT.' '.$schedule['schid']; # ex. gen_jri_report.sh /home/tomcat/apache-tomcat-8.5.50/schedules/1_env.sh
  if($schedule['noemail'] == 't'){
    $cmd .= ' nomail'; # ex. gen_jri_report.sh 1 nomail
  }

  return $cmd;
}

function get_upload_dirs(){
	$dirs = array();

	$directory = get_jasper_home().'/reports';
	
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
	$it->rewind();
	while($it->valid()) {
		if(is_dir($it->key()) && !str_ends_with($it->getFilename(), '..')){
			$dname = substr($it->getSubPathName(), 0, -2);
			array_push($dirs, '/'.$dname);
		}
		$it->next();
	}

	sort($dirs, SORT_STRING);

	return $dirs;
}

function get_all_reports($report_dname, $filename){

	$files = array();

	$jasper_home = get_jasper_home();

	$report_dir = $jasper_home.'/reports/'.$report_dname.'/';
	
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($report_dir));
	$it->rewind();
	while($it->valid()) {
		# find /home/tomcat/apache-tomcat-9.0.73/jasper_reports/reports/ -type f -regex ".*/[_0-9]+cherry.pdf"
		if(is_file($it->key()) && preg_match('/[_0-9]+'.$filename.'/', $it->getSubPathName(), $matches)){
			array_push($files, $it->getSubPathName());
		}
		$it->next();
	}
	
	sort($files, SORT_STRING);

  return $files;
}

function get_email_templates(){

	$templates = array();
  $template_dir = get_jasper_home().'/email_tmpl/';
	
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($template_dir));
	$it->rewind();
	while($it->valid()) {
		$report = $it->key();
		if(is_file($report) && str_ends_with($report, '.html')){
			array_push($templates, $it->getSubPathName());
		}
		$it->next();
	}
	
	sort($templates, SORT_STRING);

  return $templates;
}

function build_cronline(){
  $cron_line = '';
  if($_POST['cron_period'] == 'custom'){
    $cron_line .= $_POST['cron_custom'].' ';
  }
	build_sch_env($_POST);
  $cron_line .= build_cmd_line($_POST);

  return $cron_line;
}

function find_cron_ln($lines, $schid){
	
	foreach($lines as $ln => $line) {
		if(!str_starts_with($line,'#') && preg_match('/\/usr\/local\/bin\/gen_jri_report\.sh '.$schid.'( nomail)?$/', $line)){  #script line
			#@daily root /usr/local/bin/gen_jri_report.sh 1 [nomail]
			#5 0 * * * root /usr/local/bin/gen_jri_report.sh 1 [nomail]
			#/usr/local/bin/gen_jri_report.sh 1 [nomail]
			return $ln;
		}
	}
	return -1;
}

function get_jri_cron_custom($id){
	$cronfile = get_jri_cronfile('custom');
	$lines = file($cronfile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	
	$ln = find_cron_ln($lines, $id);
	if($ln == -1){
		return '';
	}else{
		$pos = strpos($lines[$ln], ' /usr/local/bin/gen_jri_report.sh ');
		return substr($lines[$ln], 0, $pos);
	}
}
?>
