/**************************************************************************************
* epShoutbox.js                                                                       *
***************************************************************************************
* Envision Portal                                                                        *
* Forum Portal Modification Project founded by ccbtimewiz (ccbtimewiz@ccbtimewiz.com) *
* =================================================================================== *
* Software by:                  Envision Portal Team (http://envisionportal.net/team/)     *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2009-2010 by:       Envision Portal Team (http://envisionportal.net/team/)     *
* Support, News, Updates at:    http://envisionportal.net/                              *
**************************************************************************************/

// This sets the overall refresh rate for all Ssoutboxes.
var req = new Array();
var Timer = new Array();

var lastShout = [];

var refreshRate = [];
var memberColor = [];
var shoutboxID = [];
var maxCount = [];
var allowedBBC = [];
var textSize = [];
var parseBBC = [];
var maxCount = [];
var maxChars = [];

// IE8, FF2, Opera 9.4, Safari 2 and other old browsers do not have getElementsByClassName, unfortunately.
if (typeof(document.getElementsByClassName) == "undefined")
	document.getElementsByClassName = function()
	{
		elements = this.getElementsByTagName('*');
		foundElements = [];
		if (arguments[0]);
			for (i = 0; i < elements.length; i++)
				if (elements[i].className == arguments[0])
					foundElements.push(elements[i]);

		return foundElements;
	}

function toggle(element)
{
	if (element.style.display == 'none' || element.style.display == '')
		element.style.display = 'block';
	else
		element.style.display = 'none';
}

function loadShouts()
{
	var alldivs = document.getElementsByClassName("ep_Reserved_Vars_Shoutbox");

	for (var i = 0; i < alldivs.length; i++)
	{
  		var moduleID = alldivs[i].getAttribute("moduleid");
		memberColor[moduleID] = alldivs[i].getAttribute("membercolor");
		shoutboxID[moduleID] = alldivs[i].getAttribute("shoutboxid");
		maxCount[moduleID] = alldivs[i].getAttribute("maxcount");
		maxChars[moduleID] = alldivs[i].getAttribute("maxchars");
		textSize[moduleID] = alldivs[i].getAttribute("textsize");
		parseBBC[moduleID] = alldivs[i].getAttribute("parsebbc");
		allowedBBC[moduleID] = alldivs[i].getAttribute("allowedbbc");
		lastShout[moduleID] = alldivs[i].getAttribute("lastshout");
		maxCount[moduleID] = alldivs[i].getAttribute("maxcount");
		refreshRate[moduleID] = alldivs[i].getAttribute("refreshrate");

		// Fix the shoutbox's width.
		var container = document.getElementById("shoutbox_area" + moduleID);
		var parent = container.parentNode;
		var margin = parent.offsetWidth - container.clientWidth;

		if (!is_opera)
			if (margin != 0)
				if (is_ie7 || is_ie8)
					container.width = (parent.clientWidth - margin) + 'px';
				else
					container.style.width = (parent.clientWidth - margin) + 'px';
			else
				if (is_ie7 || is_ie8)
					container.width = '14em';
				else
					container.style.width = '14em';

		var form = document.getElementById("post_shoutbox" + moduleID);

		if (form === null)
			startShouts(moduleID);
		else
		{
			// We are dealing with a logged in member with permissions here.
			form.onsubmit = function() {
				sendShout(this.getAttribute("moduleid"));
				return false;
			};

			startShouts(moduleID);

			// Bind events to the three icons when clicked...
			document.getElementById("toggle_smileys_div" + moduleID).onclick = function() {toggle(document.getElementById("shout_smileys" + this.parentNode.getAttribute("moduleid")))};
			document.getElementById("toggle_font_styles_div" + moduleID).onclick = function() {toggle(document.getElementById("shout_font_styles" + this.parentNode.getAttribute("moduleid")))};
			document.getElementById("toggle_history_div" + moduleID).onclick = function() {window.location = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=shout_history;membercolor=" + memberColor[this.parentNode.getAttribute("moduleid")] + ";shoutboxid=" + shoutboxID[this.parentNode.getAttribute("moduleid")] + ";maxcount=" + maxCount[this.parentNode.getAttribute("moduleid")];};
		}
	}
}

function insertCode(sCode, sFunc, moduleID, sArea)
{
	if (sFunc == "replace")
		replaceText(sCode, document.getElementById("shout_input" + moduleID));
	else if (sFunc == "surround")
		surroundText("[" + sCode + "]", "[/" + sCode + "]", document.getElementById("shout_input" + moduleID));

	document.getElementById("shout_input" + moduleID).focus();
	toggle(document.getElementById("shout_" + sArea + moduleID));
}

function sendShout(moduleID)
{
	var send_data = "shoutmessage=" + escape(document.getElementById("shout_input" + moduleID).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B");
	var url = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=shoutbox;xml;send_shout;allowedbbc=" + allowedBBC[moduleID] + ";shoutboxid=" + shoutboxID[moduleID] + ";maxchars=" + maxChars[moduleID] + ";" + sessVar + "=" + sessId;

	sendXMLDocument(url, send_data);

	document.getElementById("shout_input" + moduleID).value = "";
	document.getElementById("shout_input" + moduleID).focus();
}

function startShouts(moduleID)
{
	clearTimeout(Timer[moduleID]);
	getShouts(moduleID);
}

function getShouts(moduleID)
{
	var newClass = (document.getElementById("shoutbox_area" + moduleID).firstChild !== null ? (document.getElementById("shoutbox_area" + moduleID).firstChild.className.indexOf("windowbg2") == 0 ? "windowbg" : "windowbg2") : 0);

	var url = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=shoutbox;xml;get_shouts=" + lastShout[moduleID] + ";class=" + newClass + ";membercolor=" + memberColor[moduleID] + ";maxcount=" + maxCount[moduleID] + ";shoutboxid=" + shoutboxID[moduleID] + ";textsize=" + textSize[moduleID] + ";parsebbc=" + parseBBC[moduleID] + ";moduleid=" + moduleID + ";population=" + document.getElementById("shoutbox_area" + moduleID).childNodes.length + ";maxcount=" + maxCount[moduleID] + ";" + sessVar + "=" + sessId;

	if (lastShout[moduleID] !== null)
		getXMLDocument(url, writeShouts);
}

function writeShouts(XMLDoc)
{
	var shoutData = XMLDoc.getElementsByTagName("item");

	if (shoutData.length > 0)
	{
		var moduleId = shoutData[0].getAttribute("moduleid");

		if (shoutData[shoutData.length - 1].getAttribute("lastshout") != "undefined")
		{
			if (lastShout[shoutData[0].getAttribute("moduleid")] == shoutData[shoutData.length - 1].getAttribute("lastshout") || shoutData[shoutData.length - 1].getAttribute("lastshout") === null)
			{
				Timer[shoutData[0].getAttribute("moduleid")] = setTimeout(function() {startShouts(shoutData[0].getAttribute("moduleid"));}, refreshRate[shoutData[0].getAttribute("moduleid")]);
				return;
			}

			for (var i = 0; i < shoutData.length; i++)
				if (shoutData[i].firstChild.nodeValue != 0)
					document.getElementById("shoutbox_area" + moduleId).innerHTML = shoutData[i].firstChild.nodeValue + document.getElementById("shoutbox_area" + moduleId).innerHTML;

			lastShout[shoutData[0].getAttribute("moduleid")] = shoutData[shoutData.length - 1].getAttribute("lastshout");
			Timer[shoutData[0].getAttribute("moduleid")] = setTimeout(function() {startShouts(shoutData[0].getAttribute("moduleid")); }, refreshRate[shoutData[0].getAttribute("moduleid")]);

			while (document.getElementById("shoutbox_area" + shoutData[0].getAttribute("moduleid")).childNodes.length > maxCount[shoutData[0].getAttribute("moduleid")])
				document.getElementById("shoutbox_area" + shoutData[0].getAttribute("moduleid")).removeChild(document.getElementById("shoutbox_area" + shoutData[0].getAttribute("moduleid")).lastChild);

			element = document.getElementById("shoutbox_area" + shoutData[0].getAttribute("moduleid")).childNodes;
			var i = element.length;
			while (i--)
			{
				if (i % 2 == 0)
					element[i].className = "windowbg2";
				else
					element[i].className = "windowbg";
			}

			document.getElementById("shoutbox_area" + shoutData[0].getAttribute("moduleid")).lastChild.style.borderBottom = "none";
		}
	}
}

function removeShout(shout, moduleID)
{
	var shoutContainer = shout.parentNode.parentNode;
	var send_data = "id_shout=" + shout.id;
	var url = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=shoutbox;xml;" + "delete_shout;" + "moduleid=" + moduleID + ";" + sessVar + "=" + sessId;

	sendXMLDocument(url, send_data);

	var shoutID = shout.parentNode;
	var shoutHolder = shoutID.parentNode;

	if (shoutID.parentNode.lastChild)
	{
		var url = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=shoutbox;xml;get_shouts=" + (shoutID.parentNode.lastChild.id.replace("shout_", "") - 1) + ";membercolor=" + memberColor[moduleID] + ";maxcount=" + maxCount[moduleID] + ";shoutboxid=" + shoutboxID[moduleID] + ";textsize=" + textSize[moduleID] + ";parsebbc=" + parseBBC[moduleID] + ";moduleid=" + moduleID + ";maxcount=" + maxCount[moduleID] + ";" + sessVar + "=" + sessId;

		getXMLDocument(url, appendShout);
	}

	element = shoutID.parentNode.childNodes;
	var i = element.length;

	while (i--)
	{
		if (i % 2 == 0)
			element[i].className = "windowbg2";
		else
			element[i].className = "windowbg";
	}

	shoutID.parentNode.removeChild(shoutID);

	// Remove the last border if there are other shouts present.
	if (shoutHolder.hasChildNodes())
		document.getElementById("shoutbox_area" + moduleID).lastChild.style.borderBottom = "none";
}

function appendShout(XMLDoc)
{
	var shoutData = XMLDoc.getElementsByTagName("item");

	if (shoutData.length > 0)
	{
		var moduleId = shoutData[0].getAttribute("moduleid");

		if (shoutData[shoutData.length - 1].getAttribute("lastshout") != "undefined")
		{
			for (var i = 0; i < shoutData.length; i++)
				if (shoutData[i].firstChild.nodeValue != 0)
					document.getElementById("shoutbox_area" + moduleId).innerHTML += shoutData[i].firstChild.nodeValue;

			element = document.getElementById("shoutbox_area" + moduleId).childNodes;

			if (element !== null)
			{
				var i = element.length;

				while (i--)
				{
					if (i != element.length - 1)
						element[i].style.borderBottom = "1px black dashed";

					if (i % 2 == 0)
						element[i].className = "windowbg2";
					else
						element[i].className = "windowbg";
				}
			}
		}
	}
}