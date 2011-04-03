/**************************************************************************************
* ep_admin.js                                                                         *
***************************************************************************************
* Envision Portal                                                                     *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

// Checks for number, else sets it to 0.
Number.prototype.NaN0 = function()
{
	return isNaN(this) ? 0 : this;
}

// Simulates Php's trim() function.
String.prototype.trim = function ()
{
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

function swap_action(type)
{
	switch(type.id)
	{
		case "action_choice_smf_actions":
			document.getElementById("action_smf_actions").style.display = "";
			document.getElementById("action_user_defined").style.display = "none";
			document.getElementById("action_user_defined2").style.display = "none";
			break;
		case "action_choice_user_defined":
			document.getElementById("action_smf_actions").style.display = "none";
			document.getElementById("action_user_defined").style.display = "";
			document.getElementById("action_user_defined2").style.display = "";
			break;
	}
}

function addAction()
{
	var user_defined = false;
	var actions = document.getElementById("actions");
	if (document.getElementById("action_smf_actions").style.display == "none")
	{
		var user_defined = true;
		var udefined = document.getElementById("udefine").value;
		udefined = udefined.php_strtolower();
		udefined = udefined.trim();
		if (udefined == "") return;
		var p = exceptions.length;
		while (p--)
			if (udefined == exceptions[p]) return;

		document.getElementById("udefine").value = "";
	}
	var layouts = document.getElementById("lay_right");
	var action_list = document.getElementById("actions_list");
	var opt = document.createElement("option");
	var nextIn = action_list.options.length;
	var action_val = user_defined == false ? actions.options[actions.selectedIndex].text : udefined;
	var i = action_list.options.length;
	while (i--)
		if (action_list.options[i].text == action_val)
			return;

	action_list.options.add(opt);
	opt.text = action_val;
	var hidden = document.createElement("input");
	hidden.name = 'layout_actions[]';
	hidden.id = "envision_action" + nextIn;
	hidden.type = 'hidden';
	hidden.value = action_val;
	layouts.appendChild(hidden);
}

function removeActions()
{
	var action_list = document.getElementById("actions_list");
	var parent = document.getElementById("lay_right");

	// Remember selected items.
	// Opera deselects all selected options when removing any of them, so this fixes that.
	var is_selected = [];
	for (var i = 0; i < action_list.options.length; ++i)
		is_selected[i] = action_list.options[i].selected;

	// Remove selected items.
	i = action_list.options.length;
	while (i--)
	{
		if (is_selected[i])
		{
			action_list.remove(i);
			parent.removeChild(document.getElementById("envision_action" + i));
		}
	}

	var s = 0;
	// Reorder them
	for(var p=0; p < parent.childNodes.length; p++)
	{
		if (parent.childNodes[p].nodeName == '#text') continue;
		var action = parent.childNodes[p].id;

		if (action.indexOf('envision_action') == 0)
		{
			parent.childNodes[p].id = 'envision_action' + s;
			s++;
		}
	}

	return true;
}

function moveDown(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	for(i=0;i<elements.length;i++)
	{
		if(elements[i]==element)
		{
			var x = (i+1) % (elements.length);
			element.parentNode.insertBefore(element.cloneNode(true), (x>0?elements[x].nextSibling:elements[x]));
			element.parentNode.removeChild(element);
		}
	}
}

function moveUp(element)
{
	var elements = element.parentNode.getElementsByTagName(element.nodeName);
	for(i=0;i<elements.length;i++)
	{
		if(elements[i]==element)
		{
			element.parentNode.insertBefore(element.cloneNode(true), (i-1>=0?elements[i-1]:elements[elements.length-1].nextSibling));
			element.parentNode.removeChild(element);
		}
	}
}

function toggleBBCDisabled(section, disable)
{
	var i = document.forms.bbcForm.length;
	while (i--)
	{
		if (typeof(document.forms.bbcForm[i].name) == "undefined" || (document.forms.bbcForm[i].name.substr(0, 11) != "enabledTags") || (document.forms.bbcForm[i].name.indexOf(section) != 11))
			continue;

		document.forms.bbcForm[i].disabled = disable;
	}
	document.getElementById("bbc_" + section + "_select_all").disabled = disable;
}

// Deleting/Editing a Layout thats currently selected!
function submitLayout(confirmText, url, sessVar, sessId)
{
	if (confirmText != "editlayout")
	{
		var delLayout = confirm(confirmText);
		if (!delLayout)
			return;
	}

	var layoutForm = document.forms.urLayouts;
	layoutForm.action = url + sessVar + "=" + sessId;
	layoutForm.submit();
}

function sortOptions(optionList)
{
	var arrToSort = [];
	for (var i = 0; i < optionList.length; i++)
	{
		arrToSort[i] = [];
		arrToSort[i][0] = optionList.options[i].text;
		arrToSort[i][1] = optionList.options[i];
	}

	arrToSort.sort();
	optionList.length = 0;
	// var s = arrToSort.length;
	for (var s = 0; s < arrToSort.length; s++)
		optionList.options.add(arrToSort[s][1]);
}

function addRow()
{
	var oTable = document.getElementById("edit_layout");
	var oTr = oTable.insertRow(-1);
	oTr.className = "titlebg2";
	oTr.id = "row_" + totalRows;
	var rowText = rowString + ' ' + (totalRows + 1);
	var cellSpans = smfLayout ? "6" : "5";

	var oCell = oTr.insertCell(-1);
	oCell.setAttribute("align", "center");
	oCell.setAttribute("colspan", cellSpans);
	oCell.innerHTML = '<label for="inputrow_' + totalRows + '">' + rowText + '</label> <input id="inputrow_' + totalRows + '" type="checkbox" class="' + checkClass + '" onclick="invertChecks(this, this.form, \'check_' + totalRows + '_\');" />';

	var selEle = document.getElementById("selAddColumn");
	var newOpt = document.createElement("option");
	newOpt.value = totalRows;
	newOpt.text = rowText;
	selEle.options.add(newOpt);
	totalRows++;
}

function deleteSelected(oConfirm)
{
	var delSel = confirm(oConfirm);
		if (!delSel)
			return;

	var currIndex = 0;
	var smfRow = -1;
	var smfColumn = -1;
	var row = -1;
	var hasColumn = false;
	var sel = document.getElementById('selAddColumn');
	var oTable = document.getElementById("edit_layout");
	var currIndex = totalColumns + totalRows + 1;
	var sCurrIndex = [];

	var i = oTable.childNodes.length;
	while (i--)
	{
		var nodename = oTable.childNodes[i].nodeName;
		if (nodename == '#text') continue;
		nodename = nodename.toLowerCase();
		if (nodename != 'tbody')
			continue;

		if (oTable.childNodes[i].hasChildNodes)
		{
			var p = oTable.childNodes[i].childNodes.length;
			while (p--)
			{
				var pNodeName = oTable.childNodes[i].childNodes[p].nodeName;
				if (pNodeName == '#text') continue;
				pNodeName = pNodeName.toLowerCase();
				var oId = oTable.childNodes[i].childNodes[p].id;

				if (!oId) continue;

				if (oId.indexOf("tr_") != 0 && oId.indexOf("row_") != 0)
					continue;

				currIndex--;

				// Columns...
				if (oId.indexOf("tr_") == 0)
				{
					var iColumn = oId.split("_");
					var colCheck = document.getElementById("check_" + iColumn[1] + "_" + iColumn[2] + "_" + iColumn[3]);

					if (!colCheck || (parseInt(iColumn[1]) == rowPos && parseInt(iColumn[2]) == colPos && parseInt(iColumn[3]) == layoutPos))
					{
						smfRow = parseInt(iColumn[1]);
						smfColumn = parseInt(iColumn[2]);
						continue;
					}

					if (currIndex == 2 && !hasColumn)
						continue;

					if (colCheck.checked)
					{
						if (parseInt(iColumn[1]) == 0 && rowPos == 0 && colPos == 0 && currIndex == 3 && !hasColumn)
							continue;

						if (rowPos == 0 && colPos == 0)
						{
							if (parseInt(iColumn[1]) == 1)
							{
								if (!hasColumn)
								{
									sCurrIndex[sCurrIndex.length] = "column_" + currIndex + "_" + iColumn[3];
									continue;
								}
							}
							else if (parseInt(iColumn[1]) == 0)
							{
								if (!hasColumn)
								{
									sCurrIndex[sCurrIndex.length] = "column_" + currIndex + "_" + iColumn[3];
									continue;
								}
							}
						}

						document.getElementById("remove_positions").value += "_" + parseInt(iColumn[3]);
						oTable.deleteRow(currIndex);
						totalColumns--;

						if (parseInt(iColumn[3]) == 0)
							newColumns--;
					}
					else
					{
						row = parseInt(iColumn[1]);
						hasColumn = true;
					}
				}
				else
				{
					// Rows...
					var iRow = oId.split("_");
					var rowCheck = document.getElementById("inputrow_" + iRow[1]);

					if ((rowCheck.checked && smfRow != parseInt(iRow[1]) && row != parseInt(iRow[1])) || ((row != parseInt(iRow[1]) || row == -1) && smfRow != parseInt(iRow[1])))
					{
						if (parseInt(iRow[1]) == 0 && !hasColumn)
							continue;
						else
						{
							if (parseInt(iRow[1]) == 1 && rowPos == 0 && colPos == 0 && !hasColumn)
							{
								sCurrIndex[sCurrIndex.length] = "row_" + currIndex + "_" + iRow[1];
								continue;
							}
							else
							{
								sel.remove(parseInt(iRow[1]));
								oTable.deleteRow(currIndex);
								totalRows--;
							}
						}
					}
				}
			}
		}
	}

	// Another check...
	if (sCurrIndex.length > 0)
	{
		var it = 0;
		var isFound = false;
		var arLen = sCurrIndex.length;
		for (var a = 0; a < arLen; a++)
		{
			var sIs = sCurrIndex[a].split("_");
			var xRowCol = sIs[0];

			if (hasColumn)
			{
				if (xRowCol == "column")
				{
					if (parseInt(sIs[2]) == 0)
						newColumns--;

					totalColumns--;
				}
				else
				{
					sel.remove(parseInt(sIs[2]));
					totalRows--;
				}
				document.getElementById("remove_positions").value += "_" + parseInt(sIs[2]);
				oTable.deleteRow(parseInt(sIs[1]));
				continue;
			}
			else
			{
				var oLast = sCurrIndex[arLen - 1];
				var oLastVal = oLast.split("_");

				// Skip it
				if (oLastVal[0] == "row" && sIs[0] != "row" && it < 1)
				{
					isFound = true;
					it++;
					continue;
				}
				else
				{
					if (isFound && sIs[0] == "row")
						continue;

					if (xRowCol == "column")
					{
						if (parseInt(sIs[2]) == 0)
							newColumns--;

						totalColumns--;
					}
					else
					{
						sel.remove(parseInt(sIs[2]));
						totalRows--;
					}
					document.getElementById("remove_positions").value += "_" + parseInt(sIs[2]);
					oTable.deleteRow(parseInt(sIs[1]));
				}
			}
		}
	}

	// Finally, make it look purdy...
	var s = oTable.childNodes.length;
	var currRow = -1;
	var firstRow = 0;
	var currCol = 0;

	while (s--)
	{
		var nodename = oTable.childNodes[s].nodeName;
		if (nodename == '#text') continue;
		nodename = nodename.toLowerCase();
		if (nodename != 'tbody')
			continue;

		if (oTable.childNodes[s].hasChildNodes)
		{
			var trChilds = oTable.childNodes[s].childNodes.length;
			for (sc = 0; sc < trChilds; sc++)
			{
				var psNodeName = oTable.childNodes[s].childNodes[sc].nodeName;
				if (psNodeName == '#text') continue;
				psNodeName = psNodeName.toLowerCase();
				var oTrId = oTable.childNodes[s].childNodes[sc].id;

				if (!oTrId) continue;

				if (oTrId.indexOf("tr_") != 0 && oTrId.indexOf("row_") != 0)
					continue;

				// Left over Columns
				if (oTrId.indexOf("tr_") == 0)
				{
					var osTrId = oTrId.split("_");
					var currSuffix = oTrId.substring(3);
					var currLen = currSuffix.length;

					oTable.childNodes[s].childNodes[sc].id = "tr_" + currRow + "_" + currCol + "_" + osTrId[3];

					if (oTable.childNodes[s].childNodes[sc].hasChildNodes)
					{
						var scTd = oTable.childNodes[s].childNodes[sc].childNodes.length;
						while (scTd--)
						{
							if(oTable.childNodes[s].childNodes[sc].childNodes[scTd].nodeName=='#text') continue;
							var scTdId = oTable.childNodes[s].childNodes[sc].childNodes[scTd].id;
							if (!scTdId) continue;

							var elPreLen = scTdId.length - currLen;
							var elPrefix = scTdId.substr(0, elPreLen);

							oTable.childNodes[s].childNodes[sc].childNodes[scTd].id = elPrefix + currRow + "_" + currCol + "_" + osTrId[3];

							if (oTable.childNodes[s].childNodes[sc].childNodes[scTd].hasChildNodes)
							{
								var scEle = oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes.length;

								while (scEle--)
								{
									if (oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].nodeName=='#text') continue;
									var scEleId = oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].id;

									if (!scEleId) continue;

									var scElPreLen = scEleId.length - currLen;
									var scElPrefix = scEleId.substr(0, scElPreLen);

									oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].id = scElPrefix + currRow + "_" + currCol + "_" + osTrId[3];

									if (scEleId.indexOf("column_") == 0)
										oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].innerHTML = columnString + " " + (currCol + 1);

									if (scEleId.indexOf("radio_") == 0)
										oTable.childNodes[s].childNodes[sc].childNodes[scTd].childNodes[scEle].setAttribute("onclick", "javascript:smfRadio('" + currRow + "', '" + currCol + "', '" + osTrId[3] + "');");
								}
							}
						}
					}

					if (rowPos == parseInt(osTrId[1]) && colPos == parseInt(osTrId[2]) && layoutPos == parseInt(osTrId[3]))
					{
						rowPos = currRow;
						colPos = currCol;
					}

					currCol++;
				}
				else if (oTrId.indexOf("row_") == 0)
				{
					// Left over Rows...
					var cRow = currRow + 1;

					oTable.childNodes[s].childNodes[sc].id = "row_" + cRow;

					if (oTable.childNodes[s].childNodes[sc].hasChildNodes)
					{
						var rTd = oTable.childNodes[s].childNodes[sc].childNodes.length;
						while (rTd--)
						{
							if (oTable.childNodes[s].childNodes[sc].childNodes[rTd].nodeName=='#text') continue;

							if (oTable.childNodes[s].childNodes[sc].childNodes[rTd].hasChildNodes)
							{
								var rTdEl = oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes.length;
								while (rTdEl--)
								{
									rNodeName = oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].nodeName;

									if (rNodeName=='#text') continue;

									rNodeName = rNodeName.toLowerCase();

									if (rNodeName == 'label')
									{
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].setAttribute("for", "inputrow_" + cRow);
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].innerHTML = rowString + " " + (cRow + 1);
									}
									else if (rNodeName == 'input')
									{
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].id = "inputrow_" + cRow;
										oTable.childNodes[s].childNodes[sc].childNodes[rTd].childNodes[rTdEl].setAttribute("onclick", "invertChecks(this, this.form, 'check_" + cRow + "_');");
									}
								}
							}
						}
					}

					// Update the select box too....
					sel.options[cRow].value = cRow;
					sel.options[cRow].text = rowString + " " + (cRow + 1);

					currRow++;
					currCol = 0;
				}
			}
		}
	}
}

function addColumn()
{
	var columns = 0;
	var sel = document.getElementById("selAddColumn");
	var selVal = parseInt(sel.options[sel.selectedIndex].value);
	var oTable = document.getElementById("edit_layout_tbody");

	var i = oTable.childNodes.length;
	while (i--)
	{
		var rId = oTable.childNodes[i].id;

		if (!rId) continue;

		if (rId.indexOf("tr_" + selVal) == 0)
			columns++;
	}

	var columnIdVal = selVal + "_" + columns;

	getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=envision;sa=insertcolumn;xml;insert=' + columnIdVal.php_urlencode() + ";layout=" + document.getElementById("layout_picker").value, getIdLayoutPosition__callback);
}

function invertChecks(oCheckbox, oForm, idStr)
{
	var i = oForm.length;
	while (i--)
	{
		if (oForm[i].id.indexOf(idStr) != 0)
			continue;

		oForm[i].checked = oCheckbox.checked;
	}
}

// Simple function to add a hidden element for form submission
function addHiddenElement(formName, sValue, sName)
{
	var parent = document.forms[formName];
	var oHidden = document.createElement("input");
	oHidden.type = "hidden";
	oHidden.value = sValue;
	oHidden.name = sName;

	parent.appendChild(oHidden);

	return oHidden;
}

// Simple function to remove all hidden elements from an element
function removeHiddenElements(formName)
{
	var parent = document.forms[formName];
	element = parent.getElementsByTagName("input");
	var i = element.length;
	while (i--)
		if (element[i].type.indexOf("hidden") == 0)
			parent.removeChild(element[i]);
}

/**
 * Adds several hidden inputs to the edit layouts form
 *
 * As its name suggests, this function is run right before the form to edit a layout is submitted.
 *
 * The hidden inputs that are created use somewhat obscure values.
 * - cId is the colum IDs. This is a bit tricky to understand. It has three integers seperated by underscores: the first integer is the row number, or x_pos for the database; the second is the column, or y_pos; and the third and final number is the position, id_layout_position.
 *
 * @todo check the colspans and ensure they are evenly distributted.
 * @since 1.0
 */
function beforeLayoutEditSubmit()
{
	var oTable = document.getElementById("edit_layout_tbody");

	for (var i = 0; i < oTable.childNodes.length; i++)
	{
		var rId = oTable.childNodes[i].id;

		if (!rId) continue;

		if (rId.indexOf("row_") > -1)
		{
			x_pos = rId.replace("row_", "");
			y_pos = 0;
		}

		if (rId.indexOf("tr_") > -1)
		{
			addHiddenElement("epFlayouts", x_pos + "_" + y_pos + "_" + rId.replace("tr_", ""), "cId[]");
			y_pos++;
		}
	}
}