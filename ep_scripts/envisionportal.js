function initModuleToggles(bIsGuest, sSessionId, sSessionVar) {
	const t = document.getElementById("ep_main");
	const fn = function (el, event) {
		if (this.textContent == "+") {
			this.textContent = "-";
			el.nextElementSibling.style.display = "";
			el.nextElementSibling.nextElementSibling.style.display = "";
			el.nextElementSibling.nextElementSibling.nextElementSibling.style.display = "";

			if (bIsGuest)
				localStorage.setItem('ep_hide_module_' + el.dataset.id, '0');
			else
				smf_setThemeOption('ep_hide_module_' + el.dataset.id, '0', null, sSessionId, sSessionVar, null);
		} else {
			this.textContent = "+";
			el.nextElementSibling.style.display = "none";
			el.nextElementSibling.nextElementSibling.style.display = "none";
			el.nextElementSibling.nextElementSibling.nextElementSibling.style.display = "none";

			if (bIsGuest)
				localStorage.setItem('ep_hide_module_' + el.dataset.id, '1');
			else
				smf_setThemeOption('ep_hide_module_' + el.dataset.id, '1', null, sSessionId, sSessionVar, null);
		}

		event.preventDefault();
	};

	for (const col of t.children)
		if (col.className == "ep_col")
			for (const el of col.children)
				if (el.firstElementChild && el.firstElementChild.className == "ep_upshrink catbg") {
					const a = document.createElement("a");
					a.href = "#";
					a.addEventListener("click", fn.bind(a, el));
					if ((bIsGuest && localStorage.getItem('ep_hide_module_' + el.dataset.id) == '1') || (!bIsGuest && el.dataset.collapsed == '1')) {
						a.append("+");
						el.nextElementSibling.style.display = "none";
						el.nextElementSibling.nextElementSibling.style.display = "none";
						el.nextElementSibling.nextElementSibling.nextElementSibling.style.display = "none";
					} else {
						a.append("-");
					}
					el.firstElementChild.appendChild(a);
				}
}

function ep_change_theme(obj) {
	obj.parentNode.previousElementSibling.firstElementChild.src = obj.options[obj.selectedIndex].dataset.url;
}