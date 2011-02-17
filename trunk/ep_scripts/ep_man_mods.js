/**************************************************************************************
* ep_man_mods.js                                                                      *
***************************************************************************************
* Envision Portal                                                                     *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

var $j = jQuery.noConflict();

$j(document).ready(function() {
	$j(".module_container").disableSelection();
	$j("#messages").disableSelection();

	// Make all module containers sortable and connect them, too, so they might recieve each other's items
	$j(".module_container").each(function() {
		$j(this).sortable({
			cancel: "a",
			connectWith: ".module_container",
			helper: "clone",
			forceHelperSize: true,
			opacity: 0.6,
			revert: true,
			tolerance: "pointer",
		});
	});

	$j(".disabled_module_container").draggable({
		connectToSortable: ".module_container",
		helper: "clone",
		forceHelperSize: true,
		opacity: 0.6,
		revert: true,
		tolerance: "pointer",
		revert: "invalid"
	});


	$j("#save").click(function() {
		var submit_data = "";
		$j(".DragBox").each(function() {
			submit_data += $j(this).parent().attr("id") + "[]=" + $j(this).attr("id").replace("envisionmod_", "") + "&";
		});
		$j(".check_enabled").each(function() {
			submit_data += $j(this).attr("id") + "=" + ($j(this).is(":checked") ? 1 : 0) + "&";
		});
		$j.ajax({
			type: "POST",
			url: smf_prepareScriptUrl(smf_scripturl) + "action=admin;area=epmodules;sa=epsavemodules;xml;js_save;" + sessVar + "=" + sessId,
			data: submit_data,
			success: function(data) {
				$j("#messages").html("<div id=\"profile_success\"></div>");
				$j("#profile_success").html("<strong>" + modulePositionsSaved + "</strong>")
				.append("<br />" + data+clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$j("#messages").html("<div id=\"profile_error\"></div>");
				$j("#profile_error").html("<strong>" + errorString + "</strong>" + textStatus)
				.append("<br />" + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			}
		});
	});


	$j(".DragBox").dblclick(function() {
		$j(this).fadeOut();

		$j.ajax({
			type: "POST",
			url: smf_prepareScriptUrl(smf_scripturl) + "action=admin;area=epmodules;sa=removemodule;xml;" + sessVar + "=" + sessId,
			data: "data=" + $j(this).attr("id")
		});
	});
});