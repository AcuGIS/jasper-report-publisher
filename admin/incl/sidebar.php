<?php function sidebar_row($page, $icon, $label, $dropdown_items = null){ ?>
			<li class="sidebar-item" <?php if(MENU_SEL == $page){ ?> selected <?php } ?> style="border-top: 0px solid #e9ecef; border-bottom: 0px solid #e9ecef; padding: 8px 0;">
				<div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
					<a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=$page?>" aria-expanded="false" style="color: #495057!important; flex-grow: 1; padding: 0 15px;">
						<i class="mdi mdi-<?=$icon?>" style="color: #495057!important;"></i><span	class="hide-menu" style="color: #495057!important;"><?=$label?></span>
					</a>
					<?php if($dropdown_items): ?>
					<div class="dropdown" style="position: relative; margin-right: 10px;">
						<button class="btn btn-link" style="
							color: #495057!important;
							padding: 0;
							font-size: 16px;
							text-decoration: none;
						" onclick="event.stopPropagation(); toggleDropdown(this)">
							⋮
						</button>
						<div class="dropdown-menu" style="
							display: none;
							position: absolute;
							right: 0;
							background-color: white;
							box-shadow: 0 2px 5px rgba(0,0,0,0.1);
							border-radius: 4px;
							padding: 5px 0;
							min-width: 150px;
							z-index: 1000;
						">
							<?php foreach($dropdown_items as $item): ?>
							<a class="dropdown-item" href="<?=$item['link']?>" style="
								display: block;
								padding: 8px 15px;
								color: #495057!important;
								text-decoration: none;
								transition: background-color 0.2s;
							" onmouseover="this.style.backgroundColor='#f8f9fa'"
							  onmouseout="this.style.backgroundColor='transparent'"><?=$item['label']?></a>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</li>
<?php } ?>

<aside class="left-sidebar" data-sidebarbg="skin6" style="background-color: #f8f9fa!important;">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar" style="background-color: #f8f9fa!important;">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav" style="background-color: #f8f9fa!important;">
			<ul id="sidebarnav" style="background-color: #f8f9fa!important; margin: 0; padding: 0;">
				<?php
				sidebar_row('index.php', 					'view-dashboard',						'Dashboard');
				
				if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {	// only admins have acess to user/group creation
					sidebar_row('users.php', 					'account-settings-variant', 'Users and Groups', [
						['link' => 'users.php', 'label' => 'Users'],
						['link' => 'registration.php', 'label' => 'Add User'],
						['link' => 'access_groups.php', 'label' => 'Groups']
					]);
					sidebar_row('services.php',	'account-multiple', 				'Services', [
						['link' => 'access_groups.php?action=add', 'label' => 'Add Group'],
						['link' => 'access_groups.php?action=list', 'label' => 'List Groups']
					]);
				}
				echo "<hr style='border-color: #e9ecef!important;'>";	// JRI Publisher
				sidebar_row('datasources.php', 'database', 'Datasources', [
					['link' => 'datasources.php', 'label' => 'All'],
					['link' => 'datasources.php?tab=ds', 'label' => 'PostGIS'],
					['link' => 'datasources.php?tab=gs', 'label' => 'GeoServer'],
					['link' => 'datasources.php?tab=import', 'label' => 'Create']
				]);
				sidebar_row('publish.php',				'publish',									'Publish');	
				sidebar_row('schedules.php',			'clock',											'Schedules', [
					['link' => 'schedules.php', 'label' => 'Schedules'],
					['link' => 'schedules.php?tab=R', 'label' => 'R']
				]);
				sidebar_row('reporting.php',			'file-chart',								'Run Reports');
				
				echo "<hr style='border-color: #e9ecef!important;'>";	// JRI Viewer
				sidebar_row('reports.php', 				'file',								'Dashboard Reports');
				sidebar_row('contexts.php', 		'comment-account-outline', 	'Report Contexts', [
					['link' => 'contexts.php', 'label' => 'List Contexts'],
					['link' => 'edit_context.php', 'label' => 'Add Context']
				]);
				sidebar_row('links.php',				'link',									'Links');
				sidebar_row('parameters.php', 		'filter', 									'Parameters');
				echo "<hr style='border-color: #e9ecef!important;'>";	// 
				sidebar_row('maps.php',						'chart-bar',							 				'QGIS and R', [
					['link' => 'maps.php', 'label' => 'List'],
					['link' => 'edit_map.php', 'label' => 'Add New']
				]);


				//sidebar_row('groups.php', 				'group',										'Report Groups');
				
				//sidebar_row('links.php', 					'account-settings-variant', 'Links');
				//echo "<hr>";
				//sidebar_row('files.php?p=',				'folder',										'Files');
				//sidebar_row('permalinks.php',			'share-variant',						'Share');
				echo "<hr style='border-color: #e9ecef!important;'>";
				sidebar_row('../index.php',				'exit-to-app',							'Front End');
				sidebar_row('../logout.php',			'logout',										'Log Out');
				?>
			</ul>
		</nav>
	</div>
</aside>

<script>
function toggleDropdown(button) {
	const dropdownMenu = button.nextElementSibling;
	dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
	document.querySelectorAll('.dropdown-menu').forEach(menu => {
		menu.style.display = 'none';
	});
});
</script>
