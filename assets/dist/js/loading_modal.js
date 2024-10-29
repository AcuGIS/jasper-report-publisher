$(document).ready(function() {
	
	$('#loading').hide();
	if(document.getElementById('htmlwidget_container')){
		$('#htmlwidget_container').css('inset', '50px 0px 0px 0px');
		window.dispatchEvent(new Event('resize'));
	}
	
	$(document).on("click", ".copy", function() {
		var obj = $("#conn-modal-body");
		var temp = $("<input>");
		$("body").append(temp);
		temp.val(obj.text()).select();
		temp.focus();
		document.execCommand("copy");
		temp.remove();
	});

	$(document).on("click", "#fg-permalink", function() {
			let act_url = '../../admin/action/permalink.php';
			
			//href = /apps/57/index.php#11/41.8036/-87.6407
			if((/#\d+\/\d+\.\d+\/\d+\.\d+/).test(window.location.href)){
				let path = window.location.href.split('/apps/')[1];	// 57/index.php#11/41.8036/-87.6407
				let page = 'index.php';
				let loc = path.split('#');
				let map_id = loc[0].split('/');
				act_url = act_url + '?id=' + map_id[0] + '&loc=' + loc[1] + '&page=' + page;
			}else{
				let parts = window.location.href.split('/');	// http://URL/apps/57/index.php
				let page = parts[5].split('?')[0];
				let map_id = parts[4];
				let loc = '';	// not used in plots
				act_url = act_url + '?id=' + map_id + '&loc=' + loc + '&page=' + page;
			}
			
			$.get(act_url, function (data){
				const response = $.parseJSON(data);
				if(response.success){
					var url = window.location.origin + '/' + response.url;
					$('#conn-modal-body').html( '<a href="' + url + '" target="_blank" style="text-decoration: none !important;">' + url +'</a>');
					$('#conn_modal').modal('show');
				}
			});
	});
	
	$(document).on("click", "#fg-infobox", function() {
		$('#infobox_modal').modal('show');
	});
});
