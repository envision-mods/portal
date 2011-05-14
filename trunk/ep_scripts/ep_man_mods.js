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

	$j("#disabled_module_container").droppable({
		accept: ".module_container .draggable_module",
		drop: function(event, ui) {
			ui.helper.remove();
			ui.draggable.remove();
		},
	});

	$j("#save").click(function() {
		var submit_data = sessVar + "=" + sessId + "&";
		$j(".module_container").each(function() {
			if ($j(this).children().length != 0)
				$j(this).children(".draggable_module").each(function() {
					submit_data += $j(this).parent().attr("id") + "[]=" + $j(this).attr("id").replace("envisionmod_", "") + "&";
				});
			else
				submit_data += $j(this).attr("id") + "=0&";
		});
		$j(".check_enabled").each(function() {
			submit_data += $j(this).attr("id") + "=" + ($j(this).is(":checked") ? 1 : 0) + "&";
		});
		$j.ajax({
			type: "POST",
			url: smf_prepareScriptUrl(smf_scripturl) + "action=admin;area=epmodules;sa=epsavemodules;xml;js_save;" + sessVar + "=" + sessId,
			url: smf_prepareScriptUrl(smf_scripturl) + postUrl,
			data: submit_data,
			success: function(data) {
				$j("#messages").html("<div id=\"profile_success\"></div>");
				$j("#profile_success").html("<strong>" + modulePositionsSaved + "</strong>")
				.append("<br />" + data + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$j("#messages").html("<div id=\"profile_error\"></div>");
				$j("#profile_error").html("<strong>" + errorString + "</strong> " + textStatus)
				.append("<br />" + clickToClose)
				.hide()
				.click(function() {
					$j(this).fadeOut();
				})
				.fadeIn();
			}
		});
	});

	$j("#in").change(function() {
		ajax_indicator(true);
		$j.ajax({
			dataType: "text",
			type: "POST",
			url: smf_prepareScriptUrl(smf_scripturl) + postUrl2,
			data: "in=" + $j("#in option:selected").val(),
			success: function(data) {
				ajax_indicator(false);
				$j("#epmodulestable").html(data);
			}
		});
	});

	$j(".button_strip_add, .draggable_module a").click(function() {
		ajax_indicator(true);
		$j.ajax({
			dataType: "text",
			type: "GET",
			url: this.href + ";xml",
			success: function(data) {
				ajax_indicator(false);
				$j("#admincenter").replaceWith(data);
			}
		});
		return false;
	});

	$j(".button_strip_edit").click(function() {
		ajax_indicator(true);
		$j.ajax({
			dataType: "text",
			type: "GET",
			url: this.href + "in=" + $j("#in option:selected").val() + ";xml",
			success: function(data) {
				ajax_indicator(false);
				$j("#admincenter").replaceWith(data);
			}
		});
		return false;
	});

	$j(".module_container .DragBox").dblclick(function() {
		$j(this).fadeOut().remove();
	});
});