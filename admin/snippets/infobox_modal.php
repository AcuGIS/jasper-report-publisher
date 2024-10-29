<div id="infobox_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<p>Information</p>
			</div>
			
			<div class="modal-body" id="info-modal-body"><?php readfile(DATA_DIR.'/'.MAP_ID.'/infobox.html'); ?></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
