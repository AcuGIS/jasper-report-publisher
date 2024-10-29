<div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
		data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

		<?php const MENU_SEL = 'datasources.php';
			include("incl/topbar.php");
			include("incl/sidebar.php");
		?>
	
		<div class="page-wrapper">
			 
				<div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
						<div class="row align-items-center">
								<div class="col-6">
										<nav aria-label="breadcrumb">

											</nav>
										<h1 class="mb-0 fw-bold">Datasources</h1>
								</div>
								<div class="col-6">
										<div class="text-end upgrade-btn">
											<button type="button" class="btn btn-primary text-white add-new">
												<i class="fa fa-plus"></i> Add New </button><br>
										</div>
								</div>
						</div>
				</div>
				
				<div class="container-fluid">
					
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link <?php if($tab == 'ds') { ?> active <?php } ?>" href="datasources.php?tab=ds">Datasource</a> </li>
						<li class="nav-item"><a class="nav-link <?php if($tab == 'pg') { ?> active <?php } ?>" href="datasources.php?tab=pg">PostGIS</a> </li>
						<li class="nav-item"><a class="nav-link <?php if($tab == 'gs') { ?> active <?php } ?>" href="datasources.php?tab=gs">GeoServer</a></li>
						<li class="nav-item"><a class="nav-link <?php if($tab == 'import') { ?> active <?php } ?>" href="datasources.php?tab=import">Create</a></li>
					</ul>
					
			<table class="table custom-table" id="sortTable">
			<thead>
				<tr>
					<th data-name="id" data-editable='false'>#</th>
					<th data-name="type" data-type="select">Type</th>
					<th data-name="name">Name</th>
					<th data-name="url">URL</th>
					<th data-name="username">Username</th>
					<th data-name="password">Password</th>
					<th data-editable='false' data-action='true'>Actions</th>
				</tr>
			</thead>

			<tbody> <?php foreach($datasources as $ds_name => $ds){ ?> <tr data-id="<?=$ds_name?>" align="left">
				<td><?=$ds_name?></td>
				<td data-type="select" data-value="<?=$ds['type']?>"><?=$ds['type']?></td>
				<td><?=$ds['name']?></td>
				<td><?=$ds['url']?></td>
				<td><?=$ds['username']?></td>
				<td data-pwd="<?=$ds['password']?>">******</td>
					<td>
						<?php if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin'){ ?>
						<a class="add" title="Add" data-toggle="tooltip">			 <i class="material-icons">&#xE03B;</i></a>
						<a class="edit" title="Edit" data-toggle="tooltip">		 <i class="material-icons">&#xE254;</i></a>
						<a class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
					<?php } ?>
						<a class="pwd_vis" title="Show Password" data-toggle="tooltip" style="color: grey;"><i class="material-icons">visibility</i></a>
					</td>
				</tr> <?php } ?>
			</tbody>
		</table>				
		</div>
	</div>
</div>