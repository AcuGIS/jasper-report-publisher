<?php function sidebar_row($page, $icon, $label){ ?>
			<li class="sidebar-item" <?php if(MENU_SEL == $page){ ?> selected <?php } ?> >
			<a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=$page?>" aria-expanded="false" style="color: #fff!important;">
				<i class="mdi mdi-<?=$icon?>"></i><span	class="hide-menu"><?=$label?></span>
			</a>
		</li>
<?php } ?>

<aside class="left-sidebar" data-sidebarbg="skin6">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<?php
				sidebar_row('index.php', 					'view-dashboard',						'Dashboard');
				
				if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {	// only admins have acess to user/group creation
					sidebar_row('users.php', 					'account-settings-variant', 'Users');
					sidebar_row('access_groups.php',	'account-multiple', 				'User Groups');
				}
				echo "<hr>";	// JRI Publisher
				sidebar_row('services.php',				'play',	 										'Services');
				sidebar_row('datasources.php', 		'database',									'Datasources');
				sidebar_row('publish.php',				'publish',									'Publish');	
				sidebar_row('schedules.php',			'book',											'Schedules');
				sidebar_row('reporting.php',			'chart-pie',								'Reporting');
				sidebar_row('files.php?p=',				'folder',										'Files');
				echo "<hr>";	// JRI Viewer
				sidebar_row('reports.php', 				'file-chart',								'Reports');
				sidebar_row('contexts.php', 		'comment-account-outline', 	'Report Contexts');
				sidebar_row('groups.php', 				'group',										'Report Groups');
				sidebar_row('parameters.php', 		'filter', 									'Parameters');
				sidebar_row('links.php', 					'account-settings-variant', 'Links');
				echo "<hr>";
				sidebar_row('../index.php',				'exit-to-app',							'Front End');
				sidebar_row('../logout.php',			'logout',										'Log Out');
				?>
			</ul>
		</nav>
	</div>
</aside>
