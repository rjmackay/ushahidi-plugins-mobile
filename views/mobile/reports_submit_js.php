$(function() {
	// Highlight Selected Categories
	$("input[type=checkbox]").change( function() {
		if ($(this).is(":checked")) {
			$(this).parent().addClass("highlight");
		} else {
			$(this).parent().removeClass("highlight");
		}
	});
	
	// Fill Latitude/Longitude with selected city
	$("#select_city").change(function() {
		var lonlat = $(this).val().split(",");
		if ( lonlat[0] && lonlat[1] )
		{
			$("#latitude").attr("value", lonlat[1]);
			$("#longitude").attr("value", lonlat[0]);
			$("#location_name").attr("value", $('#select_city :selected').text());
		}
	});
	
	$("#category-column-1,#category-column-2").treeview({
        persist: "location",
        collapsed: true,
        unique: false
	});
});
	
function formSwitch(form_id, incident_id)
{
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to_switch_forms'); ?>?');
	if (answer){
		$('#form_loader').html('<img src="<?php echo url::file_loc('img')."media/img/loading_g.gif"; ?>">');
		$.post("<?php echo url::site().'reports/switch_form'; ?>", { form_id: form_id, incident_id: incident_id },
			function(data){
				if (data.status == 'success'){
					$('#custom_forms').html('');
					$('#custom_forms').html(decodeURIComponent(data.response));
					$('#form_loader').html('');
				}
		  	}, "json");
	}
}