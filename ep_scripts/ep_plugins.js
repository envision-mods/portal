if (document.querySelectorAll)
{
	var elements = document.querySelectorAll("#ep_plugins_list div.options");
	var x = elements.length;

	if (x != 0)
	{
		while (x--)
		{
			var img = new Image();
			img.onmouseover = function()
			{
				this.style.opacity = 0.75;
			}
			img.onmouseout = function()
			{
				this.style.opacity = 1;
			}
			img.onclick = function()
			{
				if (this.src == smf_default_theme_url + "/images/admin/switch_off.png")
				{
					this.src = smf_default_theme_url + "/images/admin/switch_on.png";
					document.getElementById("c_" + this.parentNode.id).value = 1;
				}
				else
				{
					this.src = smf_default_theme_url + "/images/admin/switch_off.png";
					document.getElementById("c_" + this.parentNode.id).value = 0;
				}
			}
			if (document.getElementById(elements[x].id +  "_radio_off").checked)
				img.src = smf_default_theme_url + "/images/admin/switch_off.png";
			else
				img.src = smf_default_theme_url + "/images/admin/switch_on.png";

			img.style.cursor = "pointer";
			elements[x].innerHTML = "";
			elements[x].appendChild(img);
		}
	}
}
