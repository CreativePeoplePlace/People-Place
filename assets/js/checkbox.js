jQuery(document).ready(function($){
	$("#pp_categorydiv input[type=checkbox]").each(function(){
		$check = $(this);
		var checked = $check.attr("checked") ? ' checked="checked"' : '';
		var item = '<input type="radio" id="' + $check.attr("id") + '" name="' + $check.attr("name") + '"' + checked + ' value="' + $check.val() + '"/>';
		$check.replaceWith( item );
	});
});