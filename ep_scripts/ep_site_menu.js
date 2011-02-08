/**************************************************************************************
* epSiteMenu.js                                                                       *
***************************************************************************************
* Envision Portal                                                                        *
* Forum Portal Modification Project founded by ccbtimewiz (ccbtimewiz@ccbtimewiz.com) *
* =================================================================================== *
* Software by:                  Envision Portal Team (http://envisionportal.net/team/)     *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2009-2010 by:       Envision Portal Team (http://envisionportal.net/team/)     *
* Support, News, Updates at:    http://envisionportal.net/                              *
**************************************************************************************/
var siteMenu;

function loadSiteMenu()
{
	var test = null;
	document.getElementById("ep_sitemenu").parentNode.style.overflowY = "hidden";
	siteMenu = new epSiteMenu("ep_menu");
	siteMenu.init();
	// Check param...
	if (document.getElementById("epAllowAll") !== test)
		siteMenu.oneSmOnly = false;
}

function epSiteMenu(id) {
	if (!document.getElementById || !document.getElementsByTagName)
		return false;
	this.menu = document.getElementById(id);
	this.submenus = this.menu.getElementsByTagName("div");
	this.remember = true;
	this.speed = 3;
	this.markCurrent = true;
	this.oneSmOnly = true;
}

epSiteMenu.prototype.init = function() {
	var mainInstance = this;

	for (var i = 0; i < this.submenus.length; i++)
	{
		if (this.submenus[i].getElementsByTagName("span")[0].className.indexOf("no_subs") >= 0) continue;
		this.submenus[i].getElementsByTagName("span")[0].onclick = function() {
			mainInstance.toggleMenu(this.parentNode);
		};
	}

	if (this.markCurrent) {
		var links = this.menu.getElementsByTagName("a");
		for (var i = 0; i < links.length; i++)
			if (links[i].href == document.location.href) {
				if (links[i].parentNode.className.indexOf("no_subs") >= 0)
					links[i].className = "no_subs eptitle_current";
				else if(links[i].className.indexOf("ep_menuheader") >= 0)
					links[i].className = "subs eptitle_current";
				else
					links[i].className = "sm epsm_current";
				break;
			}
	}
	if (this.remember) {
		var regex = new RegExp("epsitemenu_" + encodeURIComponent(this.menu.id) + "=([01]+)");
		var match = regex.exec(document.cookie);
		if (match) {
			var states = match[1].split("");
			for (var i = 0; i < states.length; i++)
				this.submenus[i].className = (states[i] == 0 ? "epsm_collapsed" : "epsm_expanded");
		}
	}
};

epSiteMenu.prototype.toggleMenu = function(submenu) {
	if (submenu.className == "epsm_collapsed")
		this.expandMenu(submenu);
	else
		this.collapseMenu(submenu);
};

epSiteMenu.prototype.expandMenu = function(submenu) {
	var fullHeight = submenu.getElementsByTagName("span")[0].offsetHeight;
	var links = submenu.getElementsByTagName("a");
	for (var i = 0; i < links.length; i++)
		fullHeight += links[i].offsetHeight;
	var moveBy = Math.round(this.speed * links.length);

	var mainInstance = this;
	var smintId = setInterval(function() {
		var curHeight = submenu.offsetHeight;
		var newHeight = curHeight + moveBy;
		if (newHeight < fullHeight)
			submenu.style.height = newHeight + "px";
		else {
			clearInterval(smintId);
			submenu.style.height = "";
			submenu.className = "epsm_expanded";
			mainInstance.memorize();
		}
	}, 30);
	this.collapseOthers(submenu);
};

epSiteMenu.prototype.collapseMenu = function(submenu) {
	var minHeight = submenu.getElementsByTagName("span")[0].offsetHeight;
	var moveBy = Math.round(this.speed * submenu.getElementsByTagName("a").length);
	var mainInstance = this;
	var smintId = setInterval(function() {
		var curHeight = submenu.offsetHeight;
		var newHeight = curHeight - moveBy;
		if (newHeight > minHeight)
			submenu.style.height = newHeight + "px";
		else {
			clearInterval(smintId);
			submenu.style.height = "";
			submenu.className = "epsm_collapsed";
			mainInstance.memorize();
		}
	}, 30);
};

epSiteMenu.prototype.collapseOthers = function(submenu) {
	if (this.oneSmOnly) {
		for (var i = 0; i < this.submenus.length; i++)
			if (this.submenus[i] != submenu && this.submenus[i].className != "epsm_collapsed")
				this.collapseMenu(this.submenus[i]);
	}
};

epSiteMenu.prototype.expandAll = function() {
	var oldOneSmOnly = this.oneSmOnly;
	this.oneSmOnly = false;
	for (var i = 0; i < this.submenus.length; i++)
		if (this.submenus[i].className == "epsm_collapsed")
			this.expandMenu(this.submenus[i]);
	this.oneSmOnly = oldOneSmOnly;
};

epSiteMenu.prototype.collapseAll = function() {
	for (var i = 0; i < this.submenus.length; i++)
		if (this.submenus[i].className != "epsm_collapsed")
			this.collapseMenu(this.submenus[i]);
};

epSiteMenu.prototype.memorize = function() {
	if (this.remember) {
		var states = new Array();
		for (var i = 0; i < this.submenus.length; i++)
			states.push(this.submenus[i].className == "epsm_collapsed" ? 0 : 1);
		var d = new Date();
		d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
		document.cookie = "epsitemenu_" + encodeURIComponent(this.menu.id) + "=" + states.join("") + "; expires=" + d.toGMTString() + "; path=/";
	}
};