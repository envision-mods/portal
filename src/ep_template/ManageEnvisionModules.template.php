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
		<input type="submit" name="save" id="btnsave" value="', $txt['save'], '" class="button' . (defined('SMF_VERSION') ? '' : '_submit') . '" />
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
					<input type="submit" name="save" id="save" value="', $txt['save'], '" class="button' . (defined('SMF_VERSION') ? '' : '_submit') . '" />
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

function template_form_above(): void
{
	global $context, $scripturl;

	echo '
		<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" name="epmodule">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="' . (defined('SMF_VERSION') ? 'windowbg' : 'roundframe') . ' noup">';
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
						<fieldset style="--col: %d / %d; --row: %d / %d;" class="' . (defined('SMF_VERSION') ? '' : 'window') . 'bg largetext">%d</fieldset>',
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
				highlightSections(document.forms.epmodule, \'' . (defined('SMF_VERSION') ? '' : 'window') . 'bg\');
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
				<input type="submit" value="', $txt['admin_search_go'], '" class="button' . (defined('SMF_VERSION') ? '' : '_submit') . '" />
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
						<fieldset style="--area: %d / %d / span %d / span %d;" class="' . (defined('SMF_VERSION') ? '' : 'window') . 'bg largetext">%d</fieldset>',
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
				highlightSections(document.forms.epmodule, \'' . (defined('SMF_VERSION') ? '' : 'window') . 'bg\');
				initExpandableActions(document.forms.epmodule.elements[\'layout_actions[]\']);
			</script>';
}
