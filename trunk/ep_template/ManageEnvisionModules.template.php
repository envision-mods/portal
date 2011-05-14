<?php
// Version 1.0; ManageEnvisionModules

/**
 * This file handles showing Envision Portal's module management settings.
 *
 * @package template
 * @since 1.0
*/

/**
 * Template used to modify the options of modules/clones.
 *
 * @since 1.0
 */
function template_modify_modules()
{
	global $txt, $context, $scripturl, $settings, $modSettings, $boardurl;

	echo '
	<div id="admincenter">
		<form name="epmodule" id="epmodule" action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="moduleFieldsSendingHandler.send(); return false;">';

	echo '
			<div class="title_bar">
				<h3 class="titlebg">
					', $txt['ep_module_' . $context['ep_module_type']] . $txt['ep_modsettings'], '
				</h3>
			</div>';

	if (isset($txt['ep_module_info_' . $context['ep_module_type']]))
		echo '
			<p class="information">', $txt['ep_module_info_' . $context['ep_module_type']], '</p>';

	echo '
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
			<dl class="settings">';

	// Now loop through all the fields.
	foreach ($context['ep_module'] as $key => $field)
	{
		echo '
				<dt>';

		if (!empty($field['help']))
			echo '
					<a id="setting_' . $key . '" href="' . $scripturl . '?action=helpadmin;help=' . $field['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" border="0" /></a>
					<span>';

		echo '
						<label for="', $field['label'], '">', $txt[$field['label']], '</label>
					</span>
				</dt>
				<dd>';

		// Want to put something infront of the box?
		if (!empty($field['preinput']))
			echo '
					', $field['preinput'];

		switch ($field['type'])
		{
			case 'text': case 'int':
				echo '
					<input type="text" name="', $key, '" id="', $field['label'], '"value="', $field['value'], '" class="input_text" />';
				break;

			case 'large_text': case 'html':
				echo '
					<textarea class="w100" name="', $key, '" id="', $field['label'], '">', $field['value'], '</textarea>';
				break;

			case 'check':
				echo '
					<input type="checkbox" name="', $key, '" id="', $field['label'], '"', (!empty($field['value']) ? ' checked="checked"' : ''), ' value="1" class="input_check" />';
				break;

			case 'select':
				echo '
					<select name="', $key, '" id="', $field['label'], '"';

				if (!empty($field['iconpreview']))
					echo ' onchange="javascript:document.getElementById(\'', $field['label'], '_preview\').src = \'', $field['url'], '\' + this.options[this.selectedIndex].value;"';

				echo '>';

				// Is this some code to generate the options?
				if (!is_array($field['options']))
					$field['options'] = eval($field['options']);
				// Assuming we now have some!
				if (is_array($field['options']))
					foreach ($field['options'] as $option)
					{
						if (is_array($option))
						{
							echo '
						<optgroup label="', $option['name'], '">';
							foreach ($option['boards'] as $board)
								echo '
							<option value="', $board['id'], '"', ($board['id'] == $field['value'] ? ' selected="selected"' : ''), '>', $board['name'], '</option>';
							echo '
						</optgroup>';
						}
						else
							echo '
						<option value="', $option, '"', ($option == $field['value'] ? ' selected="selected"' : ''), '>', $txt[$field['label'] . '_' . $option], '</option>';
					}

				echo '
					</select>';

				if (!empty($field['iconpreview']))
					echo '
					<img id="', $field['label'], '_preview" class="iconpreview" src="', $field['url'], $field['value'], '" />';
				break;

			case 'callback':
				if (isset($field['callback_func']) && function_exists('template_' . $field['callback_func']))
					$callback_func = 'template_' . $field['callback_func'];
					$callback_func($field, $key);
		}

		// Want to put something after the box?
		if (!empty($field['postinput']))
			echo '
					', $field['postinput'];
	}

	echo '
		</dl>
			<hr class="hrcolor" />
		<p class="righttext">
		<input type="submit" name="save" id="btnsave" value="', $txt['save'], '" class="button_submit" />
		</p>
		</div>
		<span class="lowerframe"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>
			</div>

		<script type="text/javascript"><!-- // --><![CDATA[
			var moduleFieldsSendingHandler = new moduleFields({
				sUrl: \'', $context['post_url'], ';xml\',
				sSelf: \'addShoutSendingHandler\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			});
		// ]]></script>
			<br class="clear" />';
}

/**
 * Template used to manage the position of modules/clones.
 *
 * @since 1.0
 */
function template_manage_modules()
{
	global $txt, $context, $scripturl, $settings, $user_info, $options;

	// Build the normal button array.
	$envision_buttons = array(
		'add' => array('text' => 'add_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=admin;area=epmodules;sa=epaddlayout;' . $context['session_var'] . '=' . $context['session_id']),
		'edit' => array('text' => 'edit_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => 'javascript:void(0);', 'custom' => 'onclick="javascript:submitLayout(\'editlayout\', \'' . $scripturl . '?action=admin;area=epmodules;sa=epeditlayout;\', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\');"'),
		'del' => array('text' => 'delete_layout', 'image' => 'reply.gif', 'lang' => true, 'url' => 'javascript:void(0);', 'custom' => 'onclick="javascript:submitLayout(\'' . $txt['confirm_delete_layout'] . '\', \'' . $scripturl . '?action=admin;area=epmodules;sa=epdellayout;\', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\');"'),
	);

	if ($_SESSION['selected_layout']['name'] == 'Homepage')
		unset($envision_buttons['del']);

	echo '
	<div class="floatleft w100">
		<div class="floatright">
			<form action="', $context['in_url'], '" method="post" accept-charset="', $context['character_set'], '">
				<select onchange="this.form.submit();" name="in" class="w100">';

		foreach ($context['layout_list'] as $id_layout => $layout_name)
			echo '
					<option value="', $id_layout, '"', ($_SESSION['selected_layout']['id_layout'] == $id_layout ? ' selected="selected"' : ''), '>', $layout_name, '</option>';

	echo '
				</select>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>';

	template_button_strip($envision_buttons, 'right');

	echo '
		</div>
				<div id="messages"></div></div>
				<div class="module_page floatright">
					<div class="disabled module_holder">
						<div class="cat_bar block_header">
							<h3 class="catbg centertext">
								', $txt['ep_admin_modules_manage_col_disabled'], '
							</h3>
						</div>
						<div class="roundframe blockframe" id="disabled_module_container">';

	if (!empty($context['ep_all_modules']))
		foreach ($context['ep_all_modules'] as $module)
			echo '
							<div class="DragBox plainbox draggable_module disabled_module_container centertext" id="envisionmod_' . $module['type'] . '">
								<p>
									', $module['module_title'], '
								</p>
							</div>';

	echo '
						</div>
						<span class="lowerframe"><span></span></span>
					</div>
					<div class="clear"></div>
				</div>
				<div class="module_page floatleft">';

	echo '
					<table>';

	foreach ($context['ep_columns'] as $row_id => $row_data)
	{
		echo '
						<tr class="tablerow', $row_id, '" valign="top">';

		foreach ($row_data as $column_id => $column_data)
		{
				echo '
							<td class="tablecol_', $column_id, '" colspan="', $column_data['colspan'], '">

								<div id="module_container_', $column_data['id_layout_position'], '" class="enabled w100">
									<div class="cat_bar block_header">
										<h3 class="catbg centertext">
											', (!$column_data['is_smf'] ? '<input type="checkbox" ' . (!empty($column_data['enabled']) ? 'checked="checked" ' : '') . 'id="column_' . $column_data['id_layout_position'] . '" class="check_enabled input_check" /><label for="column_' . $column_data['id_layout_position'] . '">' . $txt['ep_admin_modules_manage_col_section'] . '</label>' : $txt['ep_is_smf_section']), '
										</h3>
									</div>
									<div class="roundframe blockframe ', (!$column_data['is_smf'] ? 'module' : 'smf'), '_container" id="ep', (!$column_data['is_smf'] ? 'col_' . $column_data['id_layout_position'] : 'smf'), '">';

					if (!empty($column_data['modules']))
					{
						foreach ($column_data['modules'] as $module => $id)
						{
							if ($id['is_smf'])
							{
								echo '
											<div class="smf_content" id="smfmod_', $id['id'], '"><strong>', $txt['ep_smf_mod'], '</strong></div>
											<script type="text/javascript"><!-- // --><![CDATA[
												var smf_container = document.getElementById("smfmod_', $id['id'], '").parentNode;
												smf_container.className = "roundframe blockframe";
											// ]]></script>';
								continue;
							}
							echo '
											<div class="DragBox plainbox draggable_module centertext" id="envisionmod_' . $id['id'] . '">
												<p>
													', $id['module_title'], ' ', $id['modify_link'], '
												</p>
											</div>';
						}
					}
					echo '
										</div>
										<span class="lowerframe"><span></span></span>
									</div>
								</td>';
		}

				echo '
							</tr>';
	}
	echo '
						</table>
						<span class="botslice"><span></span></span>
					</div>
					<br class="clear" />
						<div class="padding righttext">
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
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
			<div id="admincenter">';

	echo '
				<div class="cat_bar">
					<h3 class="catbg">
						', $context['page_title'], '
					</h3>
				</div>';

	if (empty($context['module_info']))
		echo '
				<div class="information">', $txt['no_modules'], '</div>';

	if (!empty($context['module_info']))
	{
		echo '
				<table border="0" width="100%" cellspacing="1" cellpadding="4" class="tborder" id="stats">
					<tr class="titlebg" valign="middle" align="center">
					<td align="left" width="25%">', $txt['module_name'], '</td>
					<td align="left" width="75%">', $txt['module_description'], '</td>
				</tr>';

		// Print the available modules
		foreach ($context['module_info'] as $name => $module)
		{
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

				// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, etc.)
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
									<input name="ep_modules" type="file" class="input_file" size="38">
								</dd>
							</dl>
							<div class="righttext">
								<input name="upload" type="submit" value="' . $txt['module_upload'] . '" class="button_submit">
								<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							</div>
						</form>
					</div>
					<span class="botslice"><span></span></span>
				</div></div>
			<br class="clear" />';
}

function template_basic_layout()
{
	global $txt, $context, $scripturl, $settings;

		echo '
				<dt>
					<a id="setting_layoutname" href="', $scripturl, '?action=helpadmin;help=ep_layout_name" onclick="return reqWin(this.href);" class="help">
						<img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" />
					</a>
					<span', (isset($context['layout_error']['no_layout_name']) || isset($context['layout_error']['layout_exists']) ? ' class="error"' : ''), '>
						', $txt['ep_layout_name'], ':
					</span>
				</dt>
				<dd>
					<input type="text" name="layout_name" ', (!empty($context['layout_name']) ? 'value="' . $context['layout_name'] . '" ' : ''), 'class="input_text layout_style" />
				</dd>
				<dt>
					<a id="setting_actions" href="', $scripturl, '?action=helpadmin;help=ep_layout_actions" onclick="return reqWin(this.href);" class="help">
						<img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" />
					</a>
					<span>
						<strong>', $txt['ep_action_type'], '</strong><br />
						<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_smf_actions" value="smf_actions" checked="checked" class="input_radio" />
						<label for="action_choice_smf_actions">' . $txt['select_smf_actions'] . '</label><br />', '
						<input type="radio" onclick="swap_action(this); return true;" name="action_choice" id="action_choice_user_defined" value="user_defined" class="input_radio" />
						<label for="action_choice_user_defined">' . $txt['select_user_defined_actions'] . '</label>
					</span>
				</dt>
				<dd>
				<div class="floatleft" id="action_smf_actions">
					<select id="actions" name="epLayout_smf_actions" class="layout_style_max;" onfocus="selectRadioByName(document.forms.epFlayouts.action_choice, \'smf_actions\');">';

	foreach ($context['available_actions'] as $action)
		echo '
							<option value="', $action, '">', $action, '</option>';

	echo '
					</select>
				</div>
				<div id="action_user_defined2" class="smalltext">', $txt['select_user_defined_actions_desc'], '</div>
				<div class="floatleft" id="action_user_defined">
					<input id="udefine" type="text" name="epLayout_user_defined" size="34" value="" onfocus="selectRadioByName(document.forms.epFlayouts.action_choice, \'user_defined\');" class="input_text" />
				</div>
				<div class="ep_leftpadding floatleft">
					<input type="button" value="', $txt['ep_add_action'], '" onclick="javascript:addAction();" class="button_submit smalltext" />
				</div>
				<script type="text/javascript"><!-- // --><![CDATA[
					// This is shown by default.
					document.getElementById("action_smf_actions").style.display = "";
					document.getElementById("action_user_defined").style.display = "none";
					document.getElementById("action_user_defined2").style.display = "none";
					document.getElementById("action_choice_smf_actions").checked = true;
				// ]]></script>
				</dd>
				<dt>
					<span>
						<a id="setting_curr_actions" href="', $scripturl, '?action=helpadmin;help=ep_layout_curr_actions" onclick="return reqWin(this.href);" class="help">
							<img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" />
						</a>', $txt['layout_actions'], '
					</span>
				</dt>
				<dd>
					<select id="actions_list" name="layouts" class="layout_style" size="10" multiple class="layout_list', (isset($context['layout_error']['no_actions']) ? ' layout_error' : ''), '">';

	foreach ($context['current_actions'] as $cur_action)
		echo '
						<option value="', $cur_action, '">', $cur_action, '</option>';

	echo '
					</select><br />
					<input type="button" value="', $txt['ep_remove_actions'], '" onclick="javascript:removeActions();" class="button_submit smalltext" />
				</dd>';
}

/**
 * Template used to add a new layout.
 *
 * @since 1.0
 */
function template_add_layout()
{
	global $txt, $context, $scripturl, $settings;

	echo '
	<div id="admincenter">
		<script type="text/javascript"><!-- // --><![CDATA[
			var nonallowed_actions = \''. implode('|', $context['unallowed_actions']) . '\';
			var exceptions = nonallowed_actions.split("|");
		// ]]></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>
		<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '"', !empty($context['force_form_onsubmit']) ? ' onsubmit="' . $context['force_form_onsubmit'] . '"' : '', '>
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['add_layout'], '
			</h3>
		</div>
		<span class="upperframe"><span></span></span>
		<div class="roundframe">';

						// If there were errors when adding the Layout, show them.
						if (!empty($context['layout_error']['messages']))
						{
							echo '
			<div class="errorbox">
				<strong>', $txt['layout_error_header'], '</strong>';

							foreach ($context['layout_error']['messages'] as $error)
								echo '
					<div class="error">', $error, '</div>';

							echo '
				</dl>
			</div>';
						}

					echo '
			<dl class="settings">';

	template_basic_layout();

	echo '
							<dt><span><a id="setting_layout_style" href="', $scripturl, '?action=helpadmin;help=ep_layout_style" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_style'], '</span></dt>
							<dd>
									<select name="layout_style" class="layout_style">';

		foreach ($context['layout_styles'] as $num => $layout_style)
			echo '
										<option value="', $num, '"', ($context['selected_layout'] == $num ? ' selected="selected"' : ''), '>', $txt['layout_style_' . $layout_style], '</option>';

		echo '
									</select>
							</dd>
						</dl>
						<hr class="hrcolor" />
						<div id="lay_right" class="righttext">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

		foreach ($context['current_actions'] as $k => $cur_action)
			echo
									'<input id="envision_action', $k, '" name="layout_actions[]" type="hidden" value="', $cur_action, '" />';

		echo '
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
						</div>
					</div>
					<span class="lowerframe"><span></span></span>
				</form>
	</div>';
}

/**
 * Template used to edit an existing layout.
 *
 * @since 1.0
 */
function template_edit_layout()
{
	global $txt, $context, $scripturl, $settings;

	echo '
	<div id="admincenter">
		<script type="text/javascript"><!-- // --><![CDATA[
			var nonallowed_actions = \''. implode('|', $context['unallowed_actions']) . '\';
			var exceptions = nonallowed_actions.split("|");
			var columnString = ', JavaScriptEscape($txt['ep_column']), ';
			var rowString = ', JavaScriptEscape($txt['ep_row']), ';
			var newColumns = 0;
			// Some error variables here.
			var delAllRowsError = ', JavaScriptEscape($txt['ep_cant_delete_all']), ';
		// ]]></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>
		<form name="epFlayouts" id="epLayouts"  action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="beforeLayoutEditSubmit()">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['edit_layout'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">';

	// If there were errors when editing the layout, show them.
	if (!empty($context['layout_error']['messages']))
	{
		echo '
				<div class="errorbox">
					<strong>', $txt['edit_layout_error_header'], '</strong>';

		foreach ($context['layout_error']['messages'] as $error)
			echo '
					<div class="error">', $error, '</div>';

		echo '
				</div>';
	}

		echo '
				<dl class="settings">';

	if ($context['show_smf'])
		template_basic_layout();

	echo '
					<dt><span', (isset($context['layout_error']['no_sections']) ? ' class="error"' : ''), '><a id="setting_curr_sections" href="', $scripturl, '?action=helpadmin;help=ep_layout_curr_sections" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a>', $txt['layout_sections'], '
					</span></dt>
				</dl>
				<table class="table_grid" width="100%" cellspacing="0" id="edit_layout">
					<thead>
						<tr class="catbg">
							<th class="first_th" scope="col">', $txt['ep_columns_header'],'</th>
							<th scope="col">', $txt['colspans'],'</th>
							<th scope="col">', $txt['enabled'], '</th>';

	if ($context['show_smf'])
		echo '
							<th scope="col">', $txt['ep_is_smf_section'], '</th>';

	echo '
							<th class="last_th" scope="col"><input id="all_checks" type="checkbox" class="input_check" onclick="invertChecks(this, this.form, \'check_\');" /></th>
						</tr>
					</thead>
					<tbody id="edit_layout_tbody" class="centertext">';

	$rows = array();
	$xRow = 0;
	$i = 0;
	foreach ($context['ep_columns'] as $column)
	{
		$rows[] = $xRow + 1;
		$windowbg = '';
		$pCol = 0;

		echo '
						<tr class="titlebg2" id="row_', $xRow, '">
							<td colspan="', ($context['show_smf'] ? '6' : '5'), '">
								<label for="inputrow_', $xRow, '">', $txt['ep_row'], ' ', ($xRow + 1), '
								</label>
								<input id="inputrow_', $xRow, '" type="checkbox" class="input_check" onclick="invertChecks(this, this.form, \'check_', $xRow, '_\');" />
							</td>
						</tr>';

		foreach ($column as $section)
		{
			$i++;

			if ($section['is_smf'] && $context['show_smf'])
			{
				$smfRow = $xRow;
				$smfCol = $pCol;
				$smfSection = $section['id_layout_position'];
			}

				echo '
						<tr class="windowbg', $windowbg, '" id="tr_', $section['id_layout_position'], '">
							<td id="tdcolumn_', $xRow, '_', $pCol, '_', $section['id_layout_position'], '">
								<div class="floatleft">
									<a href="javascript:void(0);" onclick="javascript:moveUp(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();">
										<img src="' . $context['epadmin_image_url'] . '/ep_up.png" class="imgbox" />
									</a>
									<a href="javascript:void(0);" onclick="javascript:moveDown(this.parentNode.parentNode.parentNode);" onfocus="if(this.blur)this.blur();">
										<img src="', $context['epadmin_image_url'], '/ep_down.png" class="imgbox" />
									</a>
									<span class="ep_edit_column">', $txt['ep_column'], ' ', $pCol + 1, '</span>
								</div>
							</td>
							<td>
								<input type="text" name="colspans[', $section['id_layout_position'], ']" size="5" value="', (isset($_POST['colspans'][$section['id_layout_position']]) ? $_POST['colspans'][$section['id_layout_position']] : $section['colspan']), '" class="', (in_array($section['id_layout_position'], $context['colspans_error_ids']) ? 'layout_error ' : ''), 'input_text" />
							</td>
							<td>', (!$section['is_smf'] ? '
								<input type="checkbox" name="enabled[' . $section['id_layout_position'] . ']"' . ($section['enabled'] ? ' checked="checked"' : '') . ' class="input_check" />' : ''), '
							</td>';

				if ($context['show_smf'])
					echo '
							<td>
								<input type="radio" name="smf_radio"', $section['is_smf'] ? ' checked="checked"' : '', ' value="' . $section['id_layout_position'], '" class="input_radio" />
							</td>';

				echo '
							<td>', (!$section['is_smf'] ? '
								<input type="checkbox" id="check_' . $xRow . '_' . $pCol . '_' . $section['id_layout_position'] . '" name="section[]" class="input_check" />' : ''), '
							</td>';

				if ($context['show_smf'] && $section['is_smf'])
				{
					$smf_section = $section['id_layout_position'];

					echo '
							<input type="hidden" name="old_smf_pos" value="', $section['id_layout_position'], '" />';
				}

					echo '
						</tr>';

				$windowbg = $windowbg == '2' ? '' : '2';

				$pCol++;
			}

			$xRow++;
		}

		echo '
					</tbody>
				</table>';

			echo '
				<div class="floatright">
					<p class="righttext">
						<label for="add_column">', $txt['ep_add_column'], '</label>
							<select id="selAddColumn">';

			foreach ($rows as $key => $value)
				echo '
								<option value="', $key, '">', $txt['ep_row'], ' ', $value, '</option>';

		echo '
							</select>
							<input type="button" class="button_submit" value="', $txt['ep_add_column_button'], '" onclick="javascript:addColumn();" />
						</p>
						<p class="righttext">
							<input type="button" class="button_submit" value="', $txt['ep_add_row'], '" onclick="javascript:addRow();" />
							<input type="button" class="button_submit" value="', $txt['ep_edit_remove_selected'], '" onclick="javascript:deleteSelected(', JavaScriptEscape($txt['confirm_remove_selected']), ');" />
						</p>
					</div>
					<br class="clear" />
					<div>
						<hr class="hrcolor" />
						<div id="lay_right" class="righttext">', ($context['show_smf'] ? '
							<input type="hidden" id="smf_section" name="smf_id_layout_position" value="' . $smf_section . '" />' : ''), '
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />', (!empty($_POST['layout_picker']) ? '
							<input type="hidden" id="layout_picker" name="layout_picker" value="' . $_POST['layout_picker'] . '" />' : ''), '
							<input type="hidden" id="remove_positions" name="remove_positions" value="" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

	foreach ($context['current_actions'] as $k => $cur_action)
		echo '
							<input id="envision_action', $k, '" name="layout_actions[]" type="hidden" value="', $cur_action, '" />';

	echo '
							<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button_submit" />
						</div>
					</div>
					<span class="lowerframe"><span></span></span>
				</div>
			</form>
		</div>';
}

function template_list_groups($field, $key)
{
	global $txt;

	echo '
					<fieldset id="', $field['label'], '_group_perms">
						<legend>
							<a href="#" onclick="document.getElementById(\'', $field['label'], '_group_perms\').style.display = \'none\';document.getElementById(\'', $field['label'], '_group_perms_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a>
						</legend>';

		$all_checked = true;

		// List all the groups to configure permissions for.
		foreach ($field['options'] as $group)
		{
			echo '
							<div id="permissions_', $group['id'], '">
								<label for="check_group', $group['id'], '">
									<input type="checkbox" class="input_check" name="', $key, '[]" value="', $group['id'], '" id="check_group', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' />
									<span', ($group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : ''), '>', $group['name'], '</span>
								</label>
							</div>';

			if (!$group['checked'])
				$all_checked = false;
		}

		echo '
						<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'', $field['label'], '_groups[]\');" id="check_group_all"', $all_checked ? ' checked="checked"' : '', ' />
						<label for="check_group_all">
							<em>', $txt['check_all'], '</em>
						</label>
						<br />
					</fieldset>
					<a href="#" onclick="document.getElementById(\'', $field['label'], '_group_perms\').style.display = \'block\'; document.getElementById(\'', $field['label'], '_group_perms_groups_link\').style.display = \'none\'; return false;" id="', $field['label'], '_group_perms_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
					<script type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("', $field['label'], '_group_perms").style.display = "none";
						document.getElementById("', $field['label'], '_group_perms_groups_link").style.display = "";
					// ]]></script>';
}

function template_checklist($field, $key)
{
	global $txt;

	$all_checked = true;

	// List all the groups to configure permissions for.
	foreach ($field['options'] as $group)
	{
		echo '
							<div id="checklist_', $key, '_', $group['id'], '_div"', !empty($field['float']) ? ' class="floatleft list_bbc"' : '', '><label for="checklist_', $key, '_', $group['id'], '">
								<input type="checkbox" class="input_check" name="', $key, '[]" value="', $group['id'], '" id="checklist_', $key, '_', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' />
							', $group['name'], '</label>';

		if (!empty($field['order']))
			echo '
								<span style="padding-left: 10px;">
									<a href="javascript:void(0);" onclick="moveUp(this.parentNode.parentNode);">' . $txt['checks_order_up'] . '</a> |
									<a href="javascript:void(0);" onclick="moveDown(this.parentNode.parentNode);">' . $txt['checks_order_down'] . '</a>
								</span>
								<input type="hidden" name="', $key, 'order[]" value="', $group['id'], '" />';

		echo '
							</div>';

		if (!$group['checked'])
			$all_checked = false;
	}

	echo '
						<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'', $key, '[]\');" id="checklist_', $key, '_all"', $all_checked ? ' checked="checked"' : '', ' />
						<label for="checklist_', $key, '_all">
							<em>', $txt['check_all'], '</em>
						</label>';
	}

function template_db_select($field, $key)
{
	global $txt;

				echo '
					<div id="db_select_option_list_', $key, '"">';

				foreach ($field['options'] as $select_key => $select_value)
					echo '
							<div id="db_select_container_', $field['label'], '_', $select_key, '"><input type="radio" name="', $key, '" id="', $field['label'], '_', $select_key, '" value="', $select_key, '"', ($select_key == $field['value'] ? ' checked="checked"' : ''), ' class="input_check" /> <label for="', $field['label'], '_', $select_key, '" id="label_', $field['label'], '_', $select_key, '">', $select_value ,'</label> <span id="db_select_edit_', $field['label'], '_', $select_key, '" class="smalltext">(<a href="#" onclick="epEditDbSelect(', $key, ', \'', $field['label'], '_', $select_key, '\'); return false;" id="', $field['label'], '_', $select_key, '_db_custom_more">', $txt['ep_edit'], '</a>', $select_key != 1 ? ' - <a href="#" onclick="epDeleteDbSelect(' . $key . ', \'' . $field['label'] . '_' . $select_key . '\'); return false;" id="' . $field['label'] . '_' . $select_key . '_db_custom_delete">' . $txt['delete'] . '</a>' : '', ')</span></div>';

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