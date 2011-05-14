function moduleFields(oOptions)
{
	this.opt = oOptions;
}

moduleFields.prototype.send = function ()
{
	if (typeof document.querySelectorAll != "undefined")
	{
		var x = [
			this.opt.sSessionVar + '=' + this.opt.sSessionId
		];
		var parent = document.getElementById("epmodule");
		element = parent.querySelectorAll("input, select, textarea");
		var i = element.length;
		while (i--)
		{
			if (typeof element[i].options != "undefined")
				x[i] = element[i].name + "=" + escape(element[i].options[element[i].selectedIndex].value.replace(/&#/g, "&#38;#").php_to8bit()).replace(/\+/g, "%2B");
			else
				x[i] = element[i].name + "=" + escape(element[i].value.replace(/&#/g, "&#38;#").php_to8bit()).replace(/\+/g, "%2B");

			if (element[i].type == "checkbox" && element[i].checked == false)
				x[i] = "";
			}

		ajax_indicator(true);
		sendXMLDocument.call(this, this.opt.sUrl, x.join("&"), this.draftReply);

		return false;
	}
}

moduleFields.prototype.draftReply = function (XMLDoc)
{
	// First we pause to create an illusion of doinng something.
	var date = new Date();
	var curDate = null;
	do
		curDate = new Date();
	while (curDate - date < 500);

	ajax_indicator(false);

	var oDiv = document.createElement("div");
	oDiv.id = "profile_success";
	setInnerHTML(oDiv, XMLDoc.getElementsByTagName('item')[0].childNodes[0].nodeValue);
	document.getElementById("admincenter").insertBefore(oDiv, document.getElementById("epmodule"));
}