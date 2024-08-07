function Listbox() {
	let selectedItem = null;
	let currentElement = null;
	let searchString = '';
	let searchTimeout = null;
	let items = {};

	/**
	 * Initialize the custom select box elements.
	 * @param {string} [selector] An optional selector representing native select elements.
	 */
	this.init = function (selector) {
		replaceNativeSelect(selector);
	}

	/**
	 * Replace a native select element with a custom select box.
	 * @param {object} select The native select.
	 */
	function replaceNativeSelect(select) {
		// Skip if the native select has already been processed
		if (select.nextElementSibling && select.nextElementSibling.classList.contains('fsb-select')) {
			return;
		}

		let selectedChar;
		const options = select.children;
		const parentNode = select.parentNode;
		const customSelect = document.createElement('span');
		const list0 = document.createElement('span');
		const list = document.createElement('span');

		list0.className = 'fsb-list';
		list0.setAttribute('role', 'listbox');
		list0.tabIndex = 0;

		list.className = 'fsb-list';
		list.setAttribute('role', 'listbox');
		list.tabIndex = 0;
		list.setAttribute('aria-label', select.labels[0].textContent);
		list.addEventListener("keydown", keydown);

		for (let i = 0; i < 26; i++) {
			const char = (i + 10).toString(36);
			const item = document.createElement('span');
			item.className = 'fsb-option char';
			item.textContent = char;
			item.setAttribute('role', 'option');
			if (select.options[select.selectedIndex].value[0] == char)
				selectedChar = item;
			list0.appendChild(item);
			items[char] = [];
		}
		for (let i = 0, len = options.length; i < len; i++) {
			const [item, itemLabel] = getItemFromOption(options[i]);
			item.dataset.id = i;
			items[itemLabel].push(item);
		}
		const inp = document.createElement("input");
		inp.type = "text";
		inp.addEventListener("input", function () {
			const results = fuzzysort.go(this.value, options, {limit: 100, threshold: -10000, key: 'value'});

			list.innerHTML = '';
			for (const result of results) {
				const item = document.createElement('span');
				item.className = 'fsb-option';
				item.innerHTML = fuzzysort.highlight(result);
				item.setAttribute('role', 'option');

				const icon = document.createElement('span');
				icon.className = 'fugue fugue-' + result.target;
				item.prepend(icon);
				list.append(item);
			}
		});

		select.className = 'fsb-select';
		select.style.display = 'none';
		parentNode.id = 'fsb-parent';
		select.after(inp, list0, list);

		if (selectedChar) {
			selectedChar.setAttribute('aria-selected', 'true');
			const char = selectedChar.textContent;
			selectItem0(selectedChar);
			scrollIfNeeded(selectedChar, list0);
			if (items[char])
				list.append(...items[char]);
			scrollIfNeeded(selectedItem, list);
		}
	}

	function scrollIfNeeded(element, container) {
		if (element.offsetTop < container.scrollTop)
			container.scrollTop = element.offsetTop;
		else {
			const offsetBottom = element.offsetTop + element.offsetHeight;
			const scrollBottom = container.scrollTop + container.offsetHeight;
			if (offsetBottom > scrollBottom)
				container.scrollTop = offsetBottom - container.offsetHeight;
		}
	}

	/**
	 * Generate a listbox item from a native select option.
	 * @param {object} option The native select option.
	 * @return {object} The listbox item, its selected state and its label.
	 */
	function getItemFromOption(option) {
		const item = document.createElement('span');
		const selected = option.selected;
		const text = option.text;
		const itemLabel = text !== '' ? text : '&nbsp;';

		item.className = 'fsb-option';
		item.innerHTML = itemLabel;
		item.setAttribute('role', 'option');
		item.setAttribute('aria-selected', selected);

		if (selected)
			selectedItem = item;

		const icon = document.createElement('span');
		icon.className = 'fugue fugue-' + itemLabel;
		item.prepend(icon);

		return [item, itemLabel[0]];
	}

	/**
	 * Set the selected item.
	 * @param {object} item The item to be selected.
	 */
	function selectItem0(item) {
		const list = item.parentNode;
		const sel = list.querySelector('[aria-selected="true"]');

		if (sel)
			sel.setAttribute('aria-selected', 'false');

		item.setAttribute('aria-selected', 'true');
	}

	/**
	 * Set the selected item.
	 * @param {object} item The item to be selected.
	 */
	function selectItem(item) {
		const list = item.parentNode;
		const originalSelect = list.previousElementSibling.previousElementSibling.previousElementSibling;

		if (selectedItem)
			selectedItem.setAttribute('aria-selected', 'false');

		item.setAttribute('aria-selected', 'true');
		selectedItem = item;
		originalSelect.selectedIndex = item.dataset.id;
		originalSelect.dispatchEvent(new Event('input', {bubbles: true}));
		originalSelect.dispatchEvent(new Event('change', {bubbles: true}));
	}

	/**
	 * Get the next item that matches a string.
	 * @param {object} list The active list box.
	 * @param {string} search The search string.
	 * @return {object} The item that matches the string.
	 */
	function getMatchingItem(list, search) {
		const items = [].map.call(list.children, item => item.textContent.trim().toLowerCase());
		const filterItems = str => items.filter(item => item.indexOf(str.toLowerCase()) === 0);
		const firstMatch = filterItems(search)[0];
		const hasRepeatedCharacters = str => {
			const characters = str.split('');
			return characters.every(char => char === characters[0]);
		};

		// If an exact match is found, return it
		if (firstMatch) {
			return list.children[items.indexOf(firstMatch)];

			// If the search string is the same character repeated multiple times
			// we need to cycle through the items starting with that character
		} else if (hasRepeatedCharacters(search)) {
			// Get all the items matching the character
			const matches = filterItems(search[0]);

			// The match we want depends on the length of the repeated string
			// e.g: "aa" means the second item starting with "a"
			const matchIndex = (search.length - 1) % matches.length;

			// Return the match
			const match = matches[matchIndex];
			return list.children[items.indexOf(match)];
		}

		return null;
	}

	/**
	 * Check if the the user is typing printable characters.
	 * @param {object} event A keydown event.
	 * @return {boolean} True if the key pressed is a printable character.
	 */
	function isTyping(event) {
		const {key, altKey, ctrlKey, metaKey} = event;

		if (key.length === 1 && !altKey && !ctrlKey && !metaKey) {
			if (searchTimeout) {
				window.clearTimeout(searchTimeout);
			}

			searchTimeout = window.setTimeout(() => {
				searchString = '';
			}, 500);

			searchString += key;
			return true;
		}

		return false;
	}

	/**
	 * Shortcut for addEventListener with delegation support.
	 * @param {object} context The context to which the listener is attached.
	 * @param {string} type Event type.
	 * @param {(string|function)} selector Event target if delegation is used, event handler if not.
	 * @param {function} [fn] Event handler if delegation is used.
	 */
	function addListener(context, type, selector, fn) {
		const matches = Element.prototype.matches || Element.prototype.msMatchesSelector;

		// Delegate event to the target of the selector
		if (typeof selector === 'string') {
			context.addEventListener(type, event => {
				if (matches.call(event.target, selector)) {
					fn.call(event.target, event);
				}
			});

			// If the selector is not a string then it's a function
			// in which case we need regular event listener
		} else {
			fn = selector;
			context.addEventListener(type, fn);
		}
	}

	function select(item) {
		switch (item.className) {
			case 'fsb-option char':
				const list = item.parentNode.nextSibling;
				const char = item.textContent;
				list.innerHTML = '';
				selectItem0(item);
				if (items[char])
					list.append(...items[char]);
				scrollIfNeeded(selectedItem, list);
				break;
			case 'fsb-option':
				selectItem(item);
				break;
		}
	}

	addListener(document, 'click', '.fsb-option', event => {
		select(event.target);
	});

	function keydown(event) {
		const item = selectedItem;
		const list = this;
		let preventDefault = true;

		switch (event.key) {
			case 'ArrowUp':
			case 'ArrowLeft':
				if (item.previousElementSibling) {
					select(item.previousElementSibling);
					scrollIfNeeded(selectedItem, list);
				}
				break;
			case 'ArrowDown':
			case 'ArrowRight':
				if (item.nextElementSibling) {
					select(item.nextElementSibling);
					scrollIfNeeded(selectedItem, list);
				}
				break;
			case 'Home':
				select(list.firstElementChild);
				scrollIfNeeded(selectedItem, list);
				break;
			case 'End':
				select(list.lastElementChild);
				scrollIfNeeded(selectedItem, list);
				break;
			case 'PageUp':
			case 'PageDown':
				// Disable Page Up and Page Down keys
				break;
			default:
				if (isTyping(event)) {
					const thisItem = getMatchingItem(list, searchString);

					if (thisItem)
						select(thisItem);

					scrollIfNeeded(selectedItem, list);
				} else {
					preventDefault = false;
				}
		}

		if (preventDefault) {
			event.preventDefault();
		}
	};
}

function makeChecks(f)
{
	// Set state dependinng on the status of the checkboxes
	const update = (els, b) =>
	{
		let checkedCount = 0;
		for (const el of els)
			if (el.checked)
				checkedCount++;

		b.checked = checkedCount == els.length;
		b.indeterminate = checkedCount > 0 && checkedCount != els.length;
	};

	for (const div of f)
		if (div.nodeName == "FIELDSET")
		{
			const aEls = [...div.elements];
			if (!aEls.every(o => o.nodeName == "INPUT" && o.type == "checkbox"))
				continue;

			// Add master checkbox to the legend
			var
				a = document.createElement("legend"),
				b = document.createElement("input"),
				c = document.createElement("label");
			b.type = "checkbox";
			c.append(b, div.dataset.c || div.firstElementChild.textContent);
			a.appendChild(c);
			update(div.elements, b);

			// Prepend it to the fieldset if there is no legend.
			if (div.firstElementChild.tagName == 'LEGEND')
				div.firstElementChild.replaceWith(a);
			else
				div.prepend(a);

			aEls.forEach(el => el.addEventListener("click", update.bind(null, aEls, b)));
			b.addEventListener("click", function(els)
			{
				for (const o of els)
					if (o.nodeName == "INPUT" && o.type == "checkbox")
						o.checked = this.checked;
			}.bind(b, aEls));
		}
}

function initGroupToggle(f) {
	for (const div of f)
		if (div.nodeName == "FIELDSET" && div.className == "group_perms") {
			var
				el = document.createElement("a"),
				l = div.firstElementChild,
				a = document.createElement("a");
			el.textContent = '[ ' + l.textContent + ' ]';
			el.href = "#";
			el.style.display = "";
			el.addEventListener("click", function (event) {
				div.style.display = "";
				this.style.display = "none";
				event.stopPropagation();
				event.preventDefault();
			});
			div.style.display = "none";
			div.parentNode.appendChild(el);
			a.textContent = l.textContent;
			a.href = "#";
			a.style.display = "";
			a.addEventListener("click", function (event) {
				div.style.display = "none";
				el.style.display = "";
				event.stopPropagation();
				event.preventDefault();
			});
			l.textContent = "";
			l.appendChild(a);
		}
}

function makeUpDownLinks(f) {
	for (const div of f)
		if (div.nodeName == "FIELDSET" && div.classList.contains("ordered-checklist"))
			for (const o of div.elements)
				if (o.nodeName == "INPUT" && o.type == "checkbox") {
					var
						el = document.createElement("a"),
						l = o.parentNode.parentNode,
						ul = l.parentNode,
						a = document.createElement("a");
					el.textContent = '[ ' + ul.parentNode.dataset.up + ' ]';
					el.href = "#";
					el.addEventListener("click", function (event) {
						var wrapper = this.parentElement;

						if (wrapper.previousElementSibling)
							wrapper.parentNode.insertBefore(wrapper, wrapper.previousElementSibling);

						event.preventDefault();
					});
					a.textContent = '[ ' + ul.parentNode.dataset.down + ' ]';
					a.href = "#";
					a.addEventListener("click", function (event) {
						var wrapper = this.parentElement;

						if (wrapper.nextElementSibling)
							wrapper.parentNode.insertBefore(wrapper.nextElementSibling, wrapper);

						event.preventDefault();
					});
					l.append(el, a);
				}
}

function highlightSections(f, cls) {
	const s = {};
	for (const div of f) {
		if (div.nodeName == "FIELDSET" && div.parentNode.id == 'layout-grid') {
			// Section id numbers (id_layout_position)
			s[div.textContent] = true;

			// Highlight section boxes when visual elements on the left are clicked.
			div.addEventListener("click", function () {
				for (const el of f)
					if (el.nodeName == "INPUT" && el.parentNode.parentNode.children[1] && el.parentNode.parentNode.children[1] === el.parentNode && el.parentNode.parentNode.firstElementChild.textContent == this.textContent)
						el.focus();
			});
		}

		if (div.parentNode.parentNode.firstElementChild && s[div.parentNode.parentNode.firstElementChild.textContent]) {
			// Highlight section boxes when any associated input box gains focus.
			div.addEventListener("focus", function () {
				const n = this.parentNode.parentNode;

				for (const el of f)
					if (el.nodeName == "FIELDSET" && el.parentNode.id == 'layout-grid')
						el.className = (el.textContent == n.firstElementChild.textContent ? 'highlight2 ' + (cls == 'bg' ? 'windowbg'  : '') : cls) + ' largetext';

				n.className = 'highlight2';
			});

			// Release all highlights whenever any relevant input gives the cold shoulder.
			div.addEventListener("blur", function () {
				for (const el of f)
					if (el.nodeName == "FIELDSET" && el.parentNode.id == 'layout-grid')
						el.className = cls + ' largetext';

				this.parentNode.parentNode.className = '';
			});
		}
	}
}

function initExpandableActions(f) {
	const fn = () => {
		var a = document.createElement("a");
		a.textContent = '[ ' + l.dataset.r + ' ]';
		a.href = "#";
		a.addEventListener("click", function (event) {
			this.previousSibling.remove();
			this.remove();
			event.stopPropagation();
			event.preventDefault();
		});

		return a;
	};

	var
		o = f[f.length - 1] ?? f,
		l = o.parentNode,
		a = document.createElement("a");
	a.textContent = '[ ' + l.dataset.more + ' ]';
	a.href = "#";
	a.addEventListener("click", function (event) {
		const o = f[f.length - 1] ?? f;
		const el = document.createElement("input");
		el.type = "text";
		el.name = o.name;
		el.className = "input_text";
		if (o && o.nextSibling)
			o.nextSibling.after(el);
		else
			l.lastChild.previousElementSibling.before(el);
		el.focus();
		el.after(fn());
		event.stopPropagation();
		event.preventDefault();
	});
	l.append(a);
	l.style.gridTemplateColumns = "1fr auto";

	if (f.length)
		for (var i = 0, n = f.length; i < n; i++)
			f[i].after(fn());
	else
		f.after(fn());
}

function initAceEdior(f, el, mode) {
	var editDiv = document.createElement('div');
	editDiv.style.width = el.style.width;
	editDiv.style.height = el.style.height;
	el.style.display = 'none';
	el.before(editDiv);

	var editor = ace.edit(editDiv);
	editor.getSession().setValue(el.textContent);
	editor.getSession().setMode("ace/mode/" + mode);

	f.addEventListener('submit', () => {
		el.textContent = editor.getSession().getValue();
	});

	for (let o of f.type)
		o.addEventListener('change', () => {
			editor.getSession().setMode("ace/mode/" + o.dataset.mode);
		});
}

function makeSortables() {
	const el = document.getElementById("module_page");

	for (const c of el.lastElementChild.children[2].children) {
		const icon = document.createElement('span');
		icon.className = 'glyphicon glyphicon-move my-handle';
		c.ariaHidden = "true";
		c.prepend(icon);
	}

	new Sortable(el.lastElementChild.children[2], {
		group: {
			name: 'shared',
			pull: 'clone',
			put: false
		},
		handle: ".my-handle",
		sort: false
	});

	for (const div of el.firstElementChild.children) {
		// Skip if SMF colum.
		if (div.children[0].children[0].children.length) {
			for (const c of div.children[2].children) {
				const icon = document.createElement('span');
				icon.className = 'glyphicon glyphicon-move my-handle';
				c.ariaHidden = "true";
				c.prepend(icon);
			}

			new Sortable(div.children[2], {
				group: 'shared',
				handle: ".my-handle",
				onAdd(evt) {
					if (evt.item.lastElementChild.tagName != 'INPUT') {
						var oHidden = document.createElement("input");
						oHidden.type = "hidden";
						oHidden.value = evt.item.dataset.id;
						oHidden.name = 'modules[' + evt.to.dataset.id + '][]';
						evt.item.appendChild(oHidden);
					}
				},
				onRemove(evt) {
					evt.item.lastElementChild.name = 'modules[' + evt.to.dataset.id + '][]';
				},
			});
		}
	}
}