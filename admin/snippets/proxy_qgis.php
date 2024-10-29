<?php
	const PERMALINK_DONT_COUNT = true;
	const QGIS_FILENAME = 'QGIS_FILE_VALUE';
	include('../../admin/incl/index_prefix.php');

	$format = '';
	if(preg_match('/FORMAT=([a-z]+)&/',$_SERVER['QUERY_STRING'], $matches)){
		$format = $matches[1];
	}

	if(strcasecmp($format, 'pdf') == 0){
		header("Content-type: application/pdf");
		header('Content-Disposition: attachment; filename="'.str_replace('.qgs', '.pdf', basename(QGIS_FILENAME)).'"');
	}else if(strcasecmp($format, 'png') == 0){
		header("Content-type: application/png");
		header('Content-Disposition: attachment; filename="'.str_replace('.qgs', '.png', basename(QGIS_FILENAME)).'"');
	}else{
		header("Content-type: text/xml");
	}
	readfile('http://localhost/cgi-bin/qgis_mapserv.fcgi?VERSION=1.3.0&map='.urlencode(QGIS_FILENAME).'&'.$_SERVER['QUERY_STRING']);
?>
