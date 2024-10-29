<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'datasources.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper" style="background: #fff;">
            
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                      <nav aria-label="breadcrumb"></nav>
                      <h1 class="mb-0 fw-bold">PostGIS Connections</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
													<button type="button" class="btn btn-primary text-white add-new" onclick="window.location.href='select_pg.php';" >
														<i class="fa fa-plus"></i>Add New</button><br>
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
				
				<div class="table-responsive">
				<table class="table custom-table">

					<thead>
						<tr>
							<!--<th data-name="id" data-editable='false'>ID</th>-->
							<th data-name="name">Name</th>
							<th data-name="svc_name">Service Name</th>
							<th data-name="host">Host</th>
							<th data-name="port">Port</th>
							<th data-name="dbname">Database</th>
							<th data-name="username">Username</th>
							<th data-name="password">Password</th>
							<th data-editable='false' data-action='true'>Actions</th>
						</tr>
					</thead>

					<tbody> <?php while($row = pg_fetch_object($rows)): ?> <tr data-id="<?=$row->id?>" align="left">
							<!--<td><?=$row->id?></td>-->
							<td><?=$row->name?></td>
							<td><?=$row->svc_name?></td>
							<td><?=$row->host?></td>
							<td><?=$row->port?></td>
							<td><?=$row->dbname?></td>
							<td><?=$row->username?></td>
							<td>******</td>
							<td class="block">
								<a class="conn_info" title="Show Connection" data-toggle="tooltip">
									<i class="material-icons">link</i>
								</a>
								<a class="pwd_vis" title="Show Password" data-toggle="tooltip">
									<i class="material-icons">visibility</i>
								</a>
								<a class="add" title="Add" data-toggle="tooltip" >
									<i class="material-icons">&#xE03B;</i>
								</a>
								<?php if($row->owner_id == $_SESSION[SESS_USR_KEY]->id){ ?>
								<a class="edit" title="Edit" data-toggle="tooltip">
									<i class="material-icons">&#xE254;</i>
								</a>
								<a class="delete" title="Delete" data-toggle="tooltip" style="color: red;">
									<i class="material-icons">&#xE872;</i>
								</a>
							<?php } ?>
							</td>
						</tr> <?php endwhile; ?> </tr>
					</tbody>
				</table>
		</div>

    <div class="row">
        <div class="col-6">
					<div id = "repThumbnail" class = "alert alert-success">
					   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
					   <strong>Note:</strong>Enter your credentials to local or remote DBs
					</div>
				<script type = "text/javascript">
				   $(function(){
				      $(".close").click(function(){
				         $("#repThumbnail").alert();
				      });
				   });
				</script>
			</div>
    </div>
                    
	  </div>
	</div>

		
