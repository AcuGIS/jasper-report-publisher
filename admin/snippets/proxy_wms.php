<?php
	const PERMALINK_DONT_COUNT = true;
	include('../../admin/incl/index_prefix.php');

	const BASE_URL = 'BASE_URL_VALUE';
	readfile(BASE_URL.'?'.$_SERVER['QUERY_STRING']);
?>
