<header>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link  href="../../assets/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../../assets/dist/js/bootstrap.bundle.min.js"></script>

				
  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;RMaps</strong>
      </a>
			<?php 
			if(isset($_SESSION[SESS_USR_KEY])){
						if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') { ?>
			<a href="../../admin/index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>
			<a id="fg-permalink" href="#" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Share</a>
			<a id="fg-infobox" href="#" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Info</a>
      <a href="../../logout.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>
			<?php } else { ?>
			<a id="fg-infobox" href="#" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Info</a>
			<a href="../../index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Back to Dashboard</a>
			<?php }
			} ?>
    </div>
  </div>
</header>

<link  href="../../assets/dist/css/loading_modal.css" rel="stylesheet">
<script src="../../assets/dist/js/loading_modal.js"></script>
<link rel="stylesheet" href="../../assets/dist/css/maps.css?<?=filemtime('../../assets/dist/css/maps.css')?>">
<link rel="stylesheet" href="thismap.css?<?=filemtime('thismap.css')?>">

<div id="loading">
	<img id="loading-image" src="../../assets/images/ajax-loader.gif" alt="Loading..." />
</div>