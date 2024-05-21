<?php

function template_modify_modules()
{
	global $txt, $context, $scripturl, $settings, $modSettings, $boardurl;

	echo '
	<div id="admincenter">
		<form name="epmodule" action="', $scripturl . '?action=admin;area=epmodules;sa=modify2" method="post" accept-charset="', $context['character_set'], '">';

	echo '
			<div class="title_bar">
				<h3 class="titlebg">
					', $txt['ep_modules'][$context['module_type']]['title'] . ' ' . $txt['ep_modsettings'], '
				</h3>
			</div>';

	if (isset($txt['ep_modules'][$context['module_type']]['info'])) {
		echo '
			<p class="information">', $txt['ep_modules'][$context['module_type']]['info'], '</p>';
	}

	echo '
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
			<div class="settings-grid">';

	foreach ($context['module'] as [$key, $type, $obj]) {
		echo '
				<label for="', $key, '">', $txt['ep_modules'][$type][$key]['title'], '</label>
				<span>';

		echo $obj;

		if (isset($txt['ep_modules'][$type][$key]['desc'])) {
			echo '<br>
					<span class="smalltext">', $txt['ep_modules'][$type][$key]['desc'], '</span>';
		}

		echo '
				</span>';
	}

	echo '
		</div>
			<hr class="hrcolor" />
		<p class="righttext">
		<input type="submit" name="save" id="btnsave" value="', $txt['save'], '" class="button_submit" />
		</p>
		</div>
		<span class="lowerframe"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="in" value="', $context['module_id'], '" />
			</form>
				<link rel="stylesheet" type="text/css" href="', $context['module_icon_url'], '/fugue-sprite.css" />

				<!-- Fugue Sprite by Alison Barrett <http://alisothegeek.com/> -->
				<!-- Fugue Icon Set by Yusuke Kamiyamane <http://pinvoke.com/> -->
				<!-- Fugue Icon Set is released under the Creative Commons Attribution 3.0 license <http://creativecommons.org/licenses/by/3.0/> -->

			<script src="https://cdn.jsdelivr.net/npm/fuzzysort@2.0.4/fuzzysort.min.js"></script>
			<script>
				const f = document.forms.epmodule;
				const i = new Listbox;
				i.init(f.module_icon);
				initGroupToggle(f);
				makeUpDownLinks(f);
				makeChecks(f);
			</script>
			</div>';
}

function template_manage_modules()
{
	global $txt, $context, $scripturl;

	echo '
		<form action="', $scripturl . '?action=admin;area=epmodules;sa=epsavemodules" method="post" accept-charset="', $context['character_set'], '">
			<div id="module_page">';

	template_show_layout();

	echo '
				<div> 
						<div class="cat_bar"> 
							<h3 class="catbg centertext">
								', $txt['ep_admin_modules_manage_col_disabled'], '
							</h3>
						</div>
						<span class="upperframe"><span></span></span>
						<div class="roundframe noup" id="disabled_module_container">';

	foreach ($context['modules'] as $module) {
		echo '
							<div class="' . (defined('SMF_VERSION') ? 'descbox' : 'plainbox') . ' centertext" data-id="' . $module . '">
								', $txt['ep_modules'][$module]['title'], '
							</div>';
	}

	echo '
						</div>
						<span class="lowerframe"><span></span></span>
					</div>
				</div>
				<div class="padding righttext">
					<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
				</div>
				<input type="hidden" name="in" value="', $context['selected_layout'], '" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>';
}

function template_show_layout()
{
	global $txt, $context, $scripturl;

	echo '
			<div id="ep_main">';

	foreach ($context['ep_cols'] as $col) {
		printf('
				<div style="--area: %d / %d / span %d / span %d">
					<div class="cat_bar"><h3 class="catbg centertext">' .
			($col['is_smf'] ? '%8$s</h3></div>' : '
						<label><input type="checkbox" name="enabled[%d]"%s>%s</label>
					</h3></div>') . '
						<span class="upperframe"><span></span></span>
						<div class="roundframe noup" data-id="%5$d">',
			$col['x'],
			$col['y'],
			$col['rowspan'],
			$col['colspan'],
			$col['id'],
			$col['enabled'] ? ' checked' : '',
			$txt['ep_admin_modules_manage_col_section'],
			$txt['ep_is_smf_section']
		);

		if (!$col['is_smf'] && $col['modules'] != []) {
			foreach ($col['modules'] as $module) {
				echo '
						<div class="' . (defined('SMF_VERSION') ? 'descbox' : 'plainbox') . ' centertext">
							' . $module['module_title'] . ' [<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modify;in=' . $module['id'] . '">' . $txt['modify'] . '</a>]
							<input type="hidden" name="modules[' . $col['id'] . '][]" value="' . $module['id'] . '" />
						</div>';
			}
		}

		echo '
					</div>
					<span class="lowerframe"><span></span></span>
				</div>';
	}
	echo '
			</div>';
}

/**
 * Template used to upload and activate/deactivate and delete third-party modules.
 *
 * @since 1.0
 */
function template_add_modules()
{
	global $txt, $context, $scripturl, $settings;

	echo '
			<div id="admincenter">
				<div class="cat_bar">
					<h3 class="catbg">
						', $context['page_title'], '
					</h3>
				</div>';

	if (empty($context['module_info'])) {
		echo '
				<div class="information">', $txt['no_modules'], '</div>';
	}

	if (!empty($context['module_info'])) {
		echo '
				<table border="0" width="100%" cellspacing="1" cellpadding="4" id="stats">
					<tr class="titlebg" valign="middle" align="center">
					<td align="left" width="25%">', $txt['module_name'], '</td>
					<td align="left" width="75%">', $txt['module_description'], '</td>
				</tr>';

		// Print the available modules
		foreach ($context['module_info'] as $name => $module) {
			$alternate = 0;
			echo '
					<tr class="windowbg', $alternate ? '2' : '', '" valign="middle" align="center">
						<td align="left" width="25%"><strong>', $module['title'], '</strong><br />',
			(isset($module['install_link']) ? '<a href="' . $module['install_link'] . '">' . $txt['module_install'] . '</a>' : ''),
			(isset($module['uninstall_link']) ? '<a href="' . $module['uninstall_link'] . '">' . $txt['module_uninstall'] . '</a>' : ''),
			(isset($module['settings_link']) ? ' | <a href="' . $module['settings_link'] . '">' . $txt['module_settings'] . '</a>' : ''), ' | <a href="' . $module['delete_link'] . '">' . $txt['module_delete'] . '</a>',
			'</td>
						<td align="left" width="75%">', $module['description'], '</td>';

			echo '
					</tr>';

			// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, et)
			$alternate = !$alternate;
		}

		echo '
				</table>';
	}
	echo '
				<br />
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['ep_upload_module'], '
					</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
						<form action="', $scripturl, '?action=admin;area=epmodules;sa=epaddmodules" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" >
							<dl class="settings">
								<dt>
									<strong>', $txt['module_to_upload'], '</strong>
								</dt>
								<dd>
									<input name="ep_modules" type="file" class="input_file" size="38" />
								</dd>
							</dl>
							<div class="righttext">
								<input name="upload" type="submit" value="' . $txt['module_upload'] . '" class="button_submit" />
								<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							</div>
						</form>
					</div>
					<span class="botslice"><span></span></span>
				</div></div>';
}

function template_form_above(): void
{
	global $context, $scripturl;

	echo '
		<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" name="epmodule">
			<div class="title_bar">
				<h3 class="titlebg">
					', $context['page_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe noup">';
}

function template_errors_above(): void
{
	global $context, $txt;

	if (!empty($context['errors'])) {
		echo '
					<div class="errorbox" id="errors">
						<b>', $txt[$context['error_title']], '</b>
						<ul>';

		foreach ($context['errors'] as $error) {
			echo '
							<li>', $error, '</li>';
		}

		echo '
						</ul>
					</div>';
	}
}

function template_errors_below(): void
{
}

function template_add_layout()
{
	global $txt, $context;

	echo '
			<div class="settings-grid">
				<p>', $txt['ep_layout_name'], '</p>
				<input type="text" name="name" value="', $_POST['name'] ?? '', '" />
				<div>
					<p>', $txt['layout_sections'], '</p>
					<p class="smalltext">', $txt['layout_sections_desc'], '</p>
					<div id="layout-grid">';

	foreach ($context['ep_cols'] as $i => $col) {
		printf(
			'
						<fieldset style="--col: %d / %d; --row: %d / %d;" class="windowbg largetext">%d</fieldset>',
			$col['col'] + 1,
			$col['colspan'] + $col['col'] + 1,
			$col['row'] + 1,
			$col['rowspan'] + $col['row'] + 1,
			$i
		);
	}

	echo '
			</div>
			</div>
			<div class="layout-settings-grid">';

	foreach ($context['ep_cols'] as $i => $col) {
		printf(
			'
				<fieldset>
					<legend>%d</legend>
					<label>%s <input type="text" name="ep_cols[%1$d][col]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="ep_cols[%1$d][colspan]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="ep_cols[%1$d][row]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="ep_cols[%1$d][rowspan]" value="%d" size="4" maxlen="2" /></label>
					<label><input type="checkbox" name="ep_cols[%1$d][enabled]"%s>%s</label>
					<label><input type="radio" name="smf" value="%1$d"%s>%s</label>
					<label><input type="checkbox" name="ep_cols[%1$d][remove]">%s</label>',
			$i,
			$txt['ep_column'],
			$col['col'],
			$txt['ep_colspan'],
			$col['colspan'],
			$txt['ep_row'],
			$col['row'],
			$txt['ep_rowspan'],
			$col['rowspan'],
			$col['enabled'] ? ' checked' : '',
			$txt['enabled'],
			$col['is_smf'] ? ' checked' : '',
			$txt['ep_is_smf_section'],
			$txt['remove']
		);

		echo '
				</fieldset>';
	}
	echo '
			</div>
				<span>', $txt['layout_actions'], '</span>
						<span>
							<fieldset class="group_perms">
								<legend> ', $txt['select_smf_actions'], '</legend>';

	foreach ($context['available_actions'] as $action => $checked) {
		echo '
								<label>
									<input type="checkbox" name="smf_actions[]" value="', $action, '"', $checked ? ' checked' : '', ' />', $action, '
								</label>
								<br>';
	}

	echo '
							</fieldset>
						</span>
					<span><p>', $txt['select_user_defined_actions'], '</p><p><a href="', $GLOBALS['scripturl'], '?action=helpadmin;help=ep_layout_actions" onclick="return reqWin(this.href);" class="help"><img src="', $GLOBALS['settings']['images_url'], '/helptopics.gif" alt="', $txt['help'], '" /> ', $txt['help'], '</a></p></span>
				<span data-more="', $txt['ep_add_action'], '" data-r="', $txt['ep_remove_action'], '" style="display: grid; grid-template-columns: 1fr; gap: 0.5em;">';

	foreach ($context['layout_actions'] as $action => $checked) {
		echo '
					<input type="text" name="layout_actions[]" value="', $action, '" />';
	}

	echo '
					<span class="smalltext">', $txt['select_user_defined_actions_desc'], '</span>';

	echo '
				</span>
		</div>
			<script>
				highlightSections(document.forms.epmodule);
				initExpandableActions(document.forms.epmodule.elements[\'layout_actions[]\']);
			</script>';
}

function template_form_below(): void
{
	global $context, $txt;

	echo '
		<p class="righttext">
		<input type="submit" name="save" id="btnsave" value="', $txt['save'], '" class="primary_btn" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
		</p>
		</div>
		<span class="lowerframe"><span></span></span>
		</form>';
}

function template_delete_layout(): void
{
	global $context, $txt;

	echo '
							<fieldset>
								<legend> ', $txt['select_layout_to_delete'], '</legend>';

	foreach ($context['layouts'] as $id => $name) {
		echo '
								<label>
									<input type="checkbox" name="layouts[]" value="', $id, '"', $context['selected_layout'] == $id ? ' checked' : '', ' />', $name, '
								</label>
								<br>';
	}

	echo '
							</fieldset>
			<script>
				makeChecks(document.forms.epmodule);
			</script>';
}

function template_select_layout_above()
{
	global $context, $scripturl, $txt;

	echo '
		<div class="righttext">
			<form action="', $scripturl, '?action=admin;area=epmodules;sa=epeditlayout" method="get" accept-charset="', $context['character_set'], '">
				<select name="in">';

	foreach ($context['layout_list'] as $id_layout => $layout_name) {
		echo '
					<option value="', $id_layout, '"', $context['selected_layout'] == $id_layout ? ' selected' : '', '>', $layout_name, '</option>';
	}

	echo '
				</select>
				<input type="submit" value="', $txt['admin_search_go'], '" class="button_submit" />
				<input type="hidden" name="action" value="admin" />
				<input type="hidden" name="sa" value="', $context['current_subaction'], '" />
				<input type="hidden" name="area" value="epmodules" />
			</form>
		</div>
		<br class="clear" />';
}

function template_select_layout_below()
{
}

function template_edit_layout()
{
	global $context, $scripturl, $txt;

	echo '
			<input type="hidden" name="in" value="', $context['selected_layout'], '" />
			<div class="settings-grid">
				<p>', $txt['ep_layout_name'], '</p>
				<input type="text" name="name" value="', $_POST['name'] ?? $context['layout_name'] ?? '', '" />
				<div>
					<p>', $txt['layout_sections'], '</p>
					<p class="smalltext">', $txt['layout_sections_desc'], '</p>
					<div id="layout-grid">';

	foreach ($context['ep_cols'] as $i => $col) {
		printf(
			'
						<fieldset style="--area: %d / %d / span %d / span %d;" class="windowbg largetext">%d</fieldset>',
			$col['x'],
			$col['y'],
			$col['rowspan'],
			$col['colspan'],
			$col['id'],
		);
	}

	echo '
			</div>
			</div>
			<div class="layout-settings-grid">';

	foreach ($context['ep_cols'] as $col) {
		printf(
			'
				<fieldset>
					<legend>%d</legend>
					<label>%s <input type="text" name="col[%1$d]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="colspan[%1$d]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="row[%1$d]" value="%d" size="4" maxlen="2" /></label>
					<label>%s <input type="text" name="rowspan[%1$d]" value="%d" size="4" maxlen="2" /></label>
					<label><input type="checkbox" name="enabled[%1$d]"%s>%s</label>
					<label><input type="radio" name="smf" value="%1$d"%s>%s</label>
					<label><input type="checkbox" name="remove[]" value="%1$d">%s</label>',
			$col['id'],
			$txt['ep_column'],
			$_POST['col'][$col['id']] ?? $col['y'],
			$txt['ep_colspan'],
			$_POST['colspan'][$col['id']] ?? $col['colspan'],
			$txt['ep_row'],
			$_POST['row'][$col['id']] ?? $col['x'],
			$txt['ep_rowspan'],
			$_POST['rowspan'][$col['id']] ?? $col['rowspan'],
			$col['enabled'] ? ' checked' : '',
			$txt['enabled'],
			$col['is_smf'] ? ' checked' : '',
			$txt['ep_is_smf_section'],
			$txt['remove']
		);

		if (!$col['is_smf'] && isset($col['modules']) && $col['modules'] != []) {
			echo '<ul>';

			foreach ($col['modules'] as $module) {
				echo '
						<li>
							' . $module['module_title'] . ' [<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modify;in=' . $module['id'] . '">' . $txt['modify'] . '</a>]
						</li>';
			}
			echo '</ul>';
		}

		echo '
				</fieldset>';
	}
	echo '
			</div>
				<span>', $txt['layout_actions'], '</span>
						<span>
							<fieldset class="group_perms">
								<legend> ', $txt['select_smf_actions'], '</legend>';

	foreach ($context['available_actions'] as $action => $checked) {
		echo '
								<label>
									<input type="checkbox" name="smf_actions[]" value="', $action, '"', $checked ? ' checked' : '', ' />', $action, '
								</label>
								<br>';
	}

	echo '
							</fieldset>
						</span>
					<span><p>', $txt['select_user_defined_actions'], '</p><p><a href="', $scripturl, '?action=helpadmin;help=ep_layout_actions" onclick="return reqWin(this.href);" class="help"><img src="', $GLOBALS['settings']['images_url'], '/helptopics.gif" alt="', $txt['help'], '" /> ', $txt['help'], '</a></p></span>
				<span data-more="', $txt['ep_add_action'], '" data-r="', $txt['ep_remove_action'], '" style="display: grid; grid-template-columns: 1fr; gap: 0.5em;">';

	foreach ($context['layout_actions'] as $action => $checked) {
		echo '
					<input type="text" name="layout_actions[]" value="', $action, '" />';
	}

	echo '
					<span class="smalltext">', $txt['select_user_defined_actions_desc'], '</span>';

	echo '
				</span>
	</div>
			<script>
				highlightSections(document.forms.epmodule);
				initExpandableActions(document.forms.epmodule.elements[\'layout_actions[]\']);
			</script>';
}

function template_db_select($field, $key)
{
	global $txt;

	echo '
					<div id="db_select_option_list_', $key, '"">';

	foreach ($field['options'] as $select_key => $select_value) {
		echo '
							<div id="db_select_container_', $field['label'], '_', $select_key, '"><input type="radio" name="', $key, '" id="', $field['label'], '_', $select_key, '" value="', $select_key, '"', ($select_key == $field['value'] ? ' checked="checked"' : ''), ' class="input_check" /> <label for="', $field['label'], '_', $select_key, '" id="label_', $field['label'], '_', $select_key, '">', $select_value, '</label> <span id="db_select_edit_', $field['label'], '_', $select_key, '" class="smalltext">(<a href="#" onclick="epEditDbSelect(', $key, ', \'', $field['label'], '_', $select_key, '\'); return false;" id="', $field['label'], '_', $select_key, '_db_custom_more">', $txt['ep_edit'], '</a>', $select_key != 1 ? ' - <a href="#" onclick="epDeleteDbSelect(' . $key . ', \'' . $field['label'] . '_' . $select_key . '\'); return false;" id="' . $field['label'] . '_' . $select_key . '_db_custom_delete">' . $txt['delete'] . '</a>' : '', ')</span></div>';
	}

	echo '
					</div>
						<script type="text/javascript"><!-- // --><![CDATA[
							function epEditDbSelect(key, label)
							{
								var parent = document.getElementById(\'db_select_edit_\' + label);
								var child = document.getElementById(label + \'_db_custom_more\');
								var newElement = document.createElement("input");
								newElement.type = "text";
								newElement.value = document.getElementById(\'label_\' + label).innerHTML;
								newElement.name = "edit_" + key;
								newElement.id = "edit_" + key;
								newElement.className = "input_text";
								newElement.setAttribute("size", 30);

								parent.insertBefore(newElement, child);
								newElement.focus();
								newElement.select();

								document.getElementById(\'label_\' + label).style.display = \'none\';
								child.style.display = \'none\';

								newElement = document.createElement("span");
								newElement.innerHTML = " <a href=\"#\" onclick=\"epSubmitEditDbSelect(" + key + ", \'" + key + "\'); return false;\">', $txt['ep_submit'], '</a> - <a href=\"#\" onclick=\"epCancelEditDbSelect(\'" + key + "\', \'" + label + "\'); return false;\">', $txt['ep_cancel'], '</a> - ";
								newElement.id = "db_select_edit_buttons_" + key;

								document.getElementById(\'db_select_edit_\' + label).insertBefore(newElement, document.getElementById(key + \'_db_custom_delete\'));

								return true;
							}

							function epSubmitEditDbSelect(key, label)
							{
								var send_data = "data=" + escape(document.getElementById("edit_" + key).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B") + "&key=" + key + "&key=" + key;
								var url = smf_prepareScriptUrl(smf_scripturl) + "action=envision;sa=dbSelect;xml";

								sendXMLDocument(url, send_data);

								var parent = document.getElementById(\'db_select_edit_\' + key);

								document.getElementById(key + \'_db_custom_more\').style.display = \'\';
								document.getElementById(\'label_\' + key).innerHTML = document.getElementById("edit_" + key).value;
								document.getElementById(\'label_\' + key).style.display = \'\';
								parent.removeChild(document.getElementById(\'db_select_edit_buttons_\' + key));
								parent.removeChild(document.getElementById(\'edit_\' + key));

								return true;
							}

							function epCancelEditDbSelect(key, label)
							{
								var parent = document.getElementById(\'db_select_edit_\' + label);

								parent.removeChild(document.getElementById(\'db_select_edit_buttons_\' + key));
								parent.removeChild(document.getElementById(\'edit_\' + key));
								document.getElementById(label + \'_db_custom_more\').style.display = \'\';
								document.getElementById(\'label_\' + label).style.display = \'\';

								return true;
							}

							function epDeleteDbSelect(key, label)
							{
								var parent = document.getElementById(\'db_select_container_\' + key);

								newElement = document.createElement("span");
								newElement.innerHTML = document.getElementById(\'label_\' + key).innerHTML + " <span class=\"smalltext\">(', $txt['ep_deleted'], ' - <a href=\"#\" onclick=\"epRestoreDbSelect(" + key + ", \'" + key + "\'); return false;\">', $txt['ep_restore'], '</a>)</span>";
								newElement.id = "db_select_deleted_" + key;

								parent.appendChild(newElement);
								oHidden = addHiddenElement("epModule", document.getElementById(\'label_\' + key).innerHTML, "epDeletedDbSelects_" + key);
								oHidden.id = "epDeletedDbSelects_" + key;
								oHidden.name = "epDeletedDbSelects_" + key + "[]";

								document.getElementById(key).style.display = \'none\';
								document.getElementById(\'label_\' + key).style.display = \'none\';
								document.getElementById(\'db_select_edit_\' + key).style.display = \'none\';

								return true;
							}

							function epRestoreDbSelect(key, label)
							{
								var parent = document.getElementById(\'db_select_container_\' + key);
								var child = document.getElementById(\'db_select_deleted_\' + key);

								parent.removeChild(child);
								document.forms["epModule"].removeChild(document.getElementById("epDeletedDbSelects_" + key));

								document.getElementById(key).style.display = \'\';
								document.getElementById(\'label_\' + key).style.display = \'\';
								document.getElementById(\'db_select_edit_\' + key).style.display = \'\';

								return true;
							}

							function epInsertBefore(oParent, oChild, sType)
							{
								var parent = document.getElementById(oParent);
								var child = document.getElementById(oChild);
								var newElement = document.createElement("input");
								newElement.type = sType;
								newElement.value = "";
								newElement.name = "', $key, '_db_custom[]";
								newElement.className = "input_text";
								newElement.setAttribute("size", "' . $field['size'] . '");
								newElement.setAttribute("style", "display: block");

								parent.insertBefore(newElement, child);

								return true;
							}
						// ]]></script>
					<div id="', $key, '_db_custom_container" class="smalltext">
							<a href="#" onclick="epInsertBefore(\'', $key, '_db_custom_container\', \'', $key, '_db_custom_more\', \'text\'); return false;" id="', $key, '_db_custom_more">(', $txt['ep_add_another'], ')</a>
					</div>';
}

?>