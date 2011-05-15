function epc_Form(oOptions)
{
	this.opt = oOptions;
}

epc_Form.prototype.send = function ()
{
	if (typeof document.querySelectorAll != "undefined")
	{
		var x = [
			this.opt.sSessionVar + '=' + this.opt.sSessionId
		];
		var parent = document.getElementById("admincenter");
		parent = parent.getElementsByTagName("form")[0];
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

epc_Form.prototype.draftReply = function (XMLDoc)
{
	ajax_indicator(false);
	genericRedirect(smf_prepareScriptUrl(smf_scripturl) + XMLDoc.getElementsByTagName('item')[0].childNodes[0].nodeValue);
}