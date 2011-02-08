/**************************************************************************************
* envisionportal.js                                                                      *
***************************************************************************************
* Envision Portal                                                                        *
* Forum Portal Modification Project founded by ccbtimewiz (ccbtimewiz@ccbtimewiz.com) *
* =================================================================================== *
* Software by:                  Envision Portal Team (http://envisionportal.net/team/)     *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2009-2010 by:       Envision Portal Team (http://envisionportal.net/team/)     *
* Support, News, Updates at:    http://envisionportal.net/                              *
**************************************************************************************/

var start = 0;
var colLStart = 0;
var colRStart = 0;
var rlit = 0;
var rrit = 0;
var mHeights = new Array();
var cWidthLeft = null;
var cWidthRight = null;

function collapseModule(type, targetId)
{
	var test = null;
	var epheader = null;

	// Block Style
	if (document.getElementById("ep_" + type + "module_" + targetId) != test)
	{
		epheader = document.getElementById("ep_" + type + "module_" + targetId);
		removeClassName(epheader, "block_header");
	}
}

function expandModule(type, targetId)
{
	var test = null;
	var epheader = null;

	// Block Style
	if (document.getElementById("ep_" + type + "module_" + targetId) != test)
	{
		epheader = document.getElementById("ep_" + type + "module_" + targetId);
		addClassName(epheader, "block_header");
	}
}

function collapseModuleAnim(type, targetId, epmodule, speed)
{
	var epmoduleimage = type + "collapse_" + targetId;
	var epheader = null;
	var test = null;

	// Block Style
	if (document.getElementById("ep_" + type + "module_" + targetId) != test)
		epheader = document.getElementById("ep_" + type + "module_" + targetId);

	var module = document.getElementById(epmodule);
	var modHeight = module.offsetHeight;
	module.style.overflowY = "hidden";

	if (!mHeights[targetId] && start == 0)
	{
		document.cookie = "ep_" + type + "module_height_" + targetId + "=" + modHeight;
		mHeights[targetId] = modHeight;
	}

	var minHeight = 0;
	var moveBy = Math.round(speed * 10);
	var intId = setInterval(function() {
		var curHeight = module.offsetHeight;
		var newHeight = curHeight - moveBy;
		if (newHeight > minHeight)
		{
			start = 1;
			module.style.height = newHeight + "px";
		}
		else {
			clearInterval(intId);
			module.style.height = "0px";
			if(epheader != test)
				removeClassName(epheader, "block_header");

			start = 0;
			module.style.display = "none";
		}
	}, 30);
}

function expandModuleAnim(type, targetId, epmodule, speed)
{
	var epmoduleimage = type + "collapse_" + targetId;
	var epheader = null;
	var test = null;

	// Block Style
	if (document.getElementById("ep_" + type + "module_" + targetId) != test)
		epheader = document.getElementById("ep_" + type + "module_" + targetId);

	var module = document.getElementById(epmodule);

	if (epheader != test)
		addClassName(epheader, "block_header");

	module.style.height = "0px";
	module.style.overflowY = "hidden";
	module.style.display = "";
	if (!mHeights[targetId])
		var match = getCookie("ep_" + type + "module_height_" + targetId);
	else
		var match = mHeights[targetId];

	var modHeight = parseInt(match);
	var moveBy = Math.round(speed * 10);

	var intId = setInterval(function() {
		var curHeight = module.offsetHeight;
		var newHeight = curHeight + moveBy;
		if (newHeight < modHeight)
		{
			module.style.height = newHeight + "px";
			start = 1;
		}
		else {
			clearInterval(intId);
			if(epheader != test)
				module.style.overflowY = "hidden";
			else
				module.style.overflowY = "auto";

			module.style.height = "";
			start = 0;
		}
	}, 30);
}

function getCookie(c_name)
{
	if (document.cookie.length > 0)
	{
		var c_start = document.cookie.indexOf(c_name + "=");
		if (c_start!=-1)
		{
			c_start = c_start + c_name.length + 1;
			var c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1) c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		}
	}
	return "";
}

function addClassName(oElement, sClass)
{
	oElement.className += " " + sClass;
}

function removeClassName(oElement, sClass)
{
	oElement.className = oElement.className.replace(" " + sClass, "");
}

function replaceClassName(oElement, sClassFind, sClassReplace)
{
	oElement.className = oElement.className.replace(sClassFind, sClassReplace);
}
