<?php
/**************************************************************************************
* EnvisionProfile.php                                                                 *
/**************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function Layouts()
{
	global $context, $txt;

	loadTemplate('ep_template/ManageEnvisionModules');
	loadLanguage('ep_languages/ManageEnvisionModules');

	// Load up all the tabs...
	$context[$context['profile_menu_name']]['tab_data'] = array(
		'title' => $txt['ep_my_layouts'],
		'tabs' => array(
			'manage' => array(
				'description' => $txt['ep_manage_layouts_desc'],
			),
			'add' => array(
				'description' => $txt['ep_add_layout_desc'],
			),
			'managemodules' => array(
				'description' => $txt['ep_admin_modules_manmodules_desc'],
			),
		),
	);

	// Format: 'sub-action' => 'function',
	$sub_actions = array(
		'manage' => 'Display',
		'add' => 'AddEnvisionLayout',
		'add2' => 'AddEnvisionLayout2',
		'edit' => 'EditEnvisionLayout',
		'edit2' => 'EditEnvisionLayout2',
		'actions' => 'Actions',
		'managemodules' => 'ManageEnvisionModules',
		'savemodules' => 'SaveEnvisionModules',
	);

	// Default to sub action 'manage'
	if (!isset($_GET['sa']) || !isset($sub_actions[$_GET['sa']]))
		$_GET['sa'] = 'manage';

	// Calls a function based on the sub-action
	$sub_actions[$_GET['sa']]();
}

function Display()
{
	global $context, $txt, $user_info, $scripturl, $sourcedir, $smcFunc;

	isAllowedTo('ep_create_layouts');

	// Figure out the permissions. This is a scary bunch!
	if ($user_info['id'] == $context['id_member'])
	{
		$can_modify = allowedTo('ep_modify_layouts_own');
		$can_delete = allowedTo('ep_delete_layouts_own');
		$can_import = allowedTo('ep_import_layouts_own');
		$can_export = allowedTo('ep_export_layouts_own');
	}
	else
	{
		$can_modify = allowedTo('ep_modify_layouts_any');
		$can_delete = allowedTo('ep_delete_layouts_any');
		$can_import = allowedTo('ep_import_layouts_any');
		$can_export = allowedTo('ep_export_layouts_any');
	}

	// Our options for our list.
	$listOptions = array(
		'id' => 'ep_list_member_layouts',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=profile;area=layouts;u=' . $context['id_member'],
		'default_sort_col' => 'layout_name',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'file' => $sourcedir . '/ep_source/Subs-EnvisionLayouts.php',
			'function' => 'list_getLayouts',
			'params' => array(
				'el.id_member = {int:id_member}',
				array('id_member' => $context['id_member']),
			),
		),
		'get_count' => array(
			'file' => $sourcedir . '/ep_source/Subs-EnvisionLayouts.php',
			'function' => 'list_getNumLayouts',
			'params' => array(
				'id_member = {int:id_member}',
				array('id_member' => $context['id_member']),
			),
		),
		'no_items_label' => $txt['ep_no_member_layouts'],
		'columns' => array(
			'layout_name' => array(
				'header' => array(
					'value' => $txt['ep_layout_name'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;area=layouts;sa=edit;in=%1$d;u=' . $context['id_member'] . '">%2$s</a>',
						'params' => array(
							'id_layout' => false,
							'name' => false,
						),
					),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'name',
					'reverse' => 'name DESC',
				),
			),
			'action_list' => array(
				'header' => array(
					'value' => $txt['ep_action_list'],
				),
				'data' => array(
					'db' => 'action_list',
					'class' => 'centertext',
				),
			),
			'edit' => array(
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;area=layouts;sa=edit;in=%1$d;u=' . $context['id_member'] . '">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_layout' => false,
						),
					),
					'class' => 'centertext',
				),
			),
			'managemodules' => array(
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;area=layouts;sa=managemodules;in=%1$d;u=' . $context['id_member'] . '">' . $txt['ep_admin_modules'] . '</a>',
						'params' => array(
							'id_layout' => false,
						),
					),
					'class' => 'centertext',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="delete[]" value="%1$d" class="input_check" />',
						'params' => array(
							'id_layout' => false,
						),
					),
					'class' => 'centertext',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=profile;area=layouts;sa=actions;u=' . $context['id_member'],
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '',
				'class' => 'floatright',
			),
		),
	);

	// Remove the actions with denied permissions.
	if (!$can_modify)
		unset($listOptions['columns']['edit'], $listOptions['columns']['managemodules']);

	if ($can_import)
		$listOptions['additional_rows'][0]['value'] .= '
					<input type="submit" name="import" value="' . $txt['ep_import_layouts'] . '" class="button_submit" />';

	if ($can_export)
		$listOptions['additional_rows'][0]['value'] .= '
					<input type="submit" name="export" value="' . $txt['ep_export_layouts'] . '" class="button_submit" />
					<input type="submit" name="exportall" value="' . $txt['ep_export_all_layouts'] . '" class="button_submit" />';

	if ($can_delete)
		$listOptions['additional_rows'][0]['value'] .= '
					<input type="submit" name="remove" value="' . $txt['ep_remove_layouts'] . '" onclick="return confirm(' . JavaScriptEscape($txt['ep_confirm_remove_layouts']) . ');" class="button_submit" />
					<input type="submit" name="removeall" value="' . $txt['ep_remove_all_layouts'] . '" onclick="return confirm(' . JavaScriptEscape($txt['ep_confirm_remove_all_layouts']) . ');" class="button_submit" />';

	if (!$can_delete && !$can_import && !$can_export)
		unset($listOptions['columns']['check'], $listOptions['form'], $listOptions['additional_rows']);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'ep_list_member_layouts';
}

/**
 * Loads the form for the admin to add a layout.
 *
 * @since 1.0
 */
function AddEnvisionLayout()
{
	global $context, $txt, $smcFunc, $scripturl, $settings;

	isAllowedTo('ep_create_layouts');
	$context['page_title'] = $txt['add_layout_title'];
	$context['sub_template'] = 'add_layout';
	$context['post_url'] = $scripturl . '?action=profile;area=layouts;sa=add2;u=' . $context['id_member'];

	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>';

	// Setting some defaults.
	$context['selected_layout'] = 1;
	$context['layout_name'] = '';
	$context['current_actions'] = array();

	// Load up the 2 predefined layout styles.
	$context['layout_styles'] = array(
		1 => 'ep_',
		2 => 'omega',
	);

	$exceptions = array(
		'print',
		'clock',
		'about:unknown',
		'about:mozilla',
		'modifycat',
		'.xml',
		'xmlhttp',
		'dlattach',
		'envisionaction',
		'envisionFiles',
		'printpage',
		'keepalive',
		'jseditor',
		'jsmodify',
		'jsoption',
		'suggest',
		'verificationcode',
		'viewsmfile',
		'viewquery',
		'editpoll2',
		'login2',
		'movetopic2',
		'post2',
		'quickmod2',
		'register2',
		'removetopic2'
	);

	// If editing a layout, find actions associated with it and add them to the exceptions list.
	if (isset($context['edit_layout']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT action
			FROM {db_prefix}ep_layouts AS el
				LEFT JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout)
			WHERE el.id_layout = {int:current_layout}',
			array(
				'current_layout' => $_GET['in'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['current_actions'][] = $row['action'];

		$exceptions += $context['current_actions'];
	}

	$countActions = count($context['smf_actions']);

	$remove_all = array();
	for ($i = 0; $i < $countActions; $i++)
	{
		// Remove the 2's.
		if (substr($context['smf_actions'][$i], -1) == '2')
			if (!in_array($context['smf_actions'][$i], $exceptions))
				$remove_all[] = $context['smf_actions'][$i];
	}

	if (!empty($remove_all))
		$remove_all += $exceptions;
	else
		$remove_all = $exceptions;

	$context['available_actions'] = array_diff($context['smf_actions'], $remove_all);

	// We do this so the user can type in 2's if they need them.
	$context['unallowed_actions'] = $exceptions;

	sort($context['available_actions']);

	$context['nonaction_choices'] = array(
		'topic',
		'board',
	);

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

/**
 * Adds the layout specified in the form from {@link AddEnvisionLayout()}.
 *
 * @since 1.0
 */
function AddEnvisionLayout2()
{
	global $context, $txt, $smcFunc, $sourcedir;

	checkSession();
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	isAllowedTo('ep_create_layouts');

	$id_member = $context['id_member'];

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = 0;

	ep_call_hook('add_member_layout', array(&$_POST));

	if (!empty($_POST['layout_name']))
		$layout_name = checkLayoutName(trim($_POST['layout_name']));
	else
		$layout_errors[] = 'no_layout_name';

	if ($layout_name === false)
		$layout_name = '';

	$i = 0;

	if (!empty($_POST['layout_actions']))
		foreach ($_POST['layout_actions'] as $laction)
		{
			preg_match('/((?:\[topic\]=)\d+)/', $laction, $matches);
			if (!empty($matches))
				$laction = $matches[1];

			$layout_actions[] = $laction;
		}
	else
		$layout_errors[] = 'no_actions';

	// Finally get the layout style they chose.
	$selected_layout = (int) $_POST['layout_style'];

	if (!empty($layout_errors))
	{
		foreach ($layout_errors as $error_type)
		{
			$context['layout_error'][$error_type] = true;
			if (isset($txt['ep_' . $error_type]))
				$context['layout_error']['messages'][] = $txt['ep_' . $error_type];
		}
		return AddEnvisionLayout();
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	$id_group = 1;

	if (!empty($selected_layout))
		$insert_positions = ep_get_predefined_layouts($selected_layout);
	else
		fatal_lang_error('ep_layout_unknown', false);

	$layout_name = $smcFunc['htmlspecialchars']($_POST['layout_name']);

	$iid = addLayout($layout_name, $id_member, $layout_actions, $insert_positions);

	$log_extra = array(
		'id_layout' => $iid,
		'layout_name' => $layout_name,
	);

	logEpAction('add_layout', $id_member, $log_extra);

	redirectexit('action=profile;area=layouts;u=' . $context['id_member']);
}

/**
 * A multi-purpose function which does one of three things:
 * - Calls {@link importLayout()} to import a layout from an XML file
 * - Calls {@link deleteLayout()} to delete a layout specified in $_POST['remove'].
 * - Calls {@link exportLayout()} to export a layout specified in $_POST['remove'].
 *
 * @since 1.0
 */
function Actions()
{
	global $smcFunc, $sourcedir, $context, $user_info;

	checkSession();

	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	if (isset($_POST['import']))
	{
		if ($user_info['id'] == $context['id_member'])
			isAllowedTo('ep_import_layouts_own');
		else
			isAllowedTo('ep_import_layouts_any');

		if (!importLayout($row[0]))
			fatal_lang_error('no_layout_selected', false);
		else
			logEpAction('import_layout', $context['id_member'], $log_extra);

		redirectexit('action=profile;area=layouts;u=' . $context['id_member']);
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_layout, name
		FROM {db_prefix}ep_layouts
		WHERE id_member = {int:id_member}' . (isset($_POST['delete']) ? '
			AND id_layout IN ({array_int:layouts})' : ''),
		array(
			'id_member' => $context['id_member'],
			'layouts' => isset($_POST['delete']) ? $_POST['delete'] : array(),
		)
	);

	while ($row = $smcFunc['db_fetch_row']($request))
	{
		$log_extra = array(
			'layout_name' => $row[1],
		);

		if (isset($_POST['removeall']) || isset($_POST['remove'], $_POST['delete']))
		{
			if ($user_info['id'] == $context['id_member'])
				isAllowedTo('ep_delete_layouts_own');
			else
				isAllowedTo('ep_delete_layouts_any');

			if (!deleteLayout($row[0]))
				fatal_lang_error('no_layout_selected', false);
			else
				logEpAction('delete_layout', $context['id_member'], $log_extra);
		}

		if (isset($_POST['exportall']) || isset($_POST['export'], $_POST['delete']))
		{
			if ($user_info['id'] == $context['id_member'])
				isAllowedTo('ep_export_layouts_own');
			else
				isAllowedTo('ep_export_layouts_any');

			if (!exportLayout($row[0]))
				fatal_lang_error('no_layout_selected', false);
			else
				logEpAction('export_layout', $context['id_member'], $log_extra);
		}
	}

	if (!isset($_POST['exportall']) && !isset($_POST['export']))
		redirectexit('action=profile;area=layouts;u=' . $context['id_member']);
}

/**
 * Loads the form for the admin to edit a layout.
 *
 * @since 1.0
 */
function EditEnvisionLayout()
{
	global $context, $scripturl, $txt;

	// We are editing a layout, not adding one.
	$context['edit_layout'] = true;

	if ($context['user']['id'] == $context['id_member'])
		isAllowedTo('ep_edit_layouts_own');
	else
		isAllowedTo('ep_edit_layouts_any');

	// Variables in here are recycled
	AddEnvisionLayout();

	$selected_layout = !empty($_GET['in']) ? (int) $_GET['in'] : fatal_lang_error('cant_find_layout_id', false);
	$context['page_title'] = $txt['edit_layout_title'];
	$context['sub_template'] = 'edit_layout';
	$context['post_url'] = $scripturl . '?action=profile;area=layouts;sa=edit2;u=' . $context['id_member'] . ';in=' . $selected_layout;


	if (!isset($context['row_pos_error_ids']))
	{
		$context['row_pos_error_ids'] = array();
		$context['col_pos_error_ids'] = array();
		$context['rowspans_error_ids'] = array();
		$context['colspans_error_ids'] = array();
	}

	loadLayout($selected_layout);

	foreach ($context['ep_columns'] as &$row_data)
		foreach ($row_data as &$column_data)
		{
			$column_data += array(
				'colspan' => $column_data['extra']['colspan'],
				'enabled' => $column_data['extra']['status'] == 'active',
			);

			unset($column_data['extra']);
		}

	$context['show_smf'] = strpos(strtolower(implode(',', $context['current_actions'])), '[home]') === false;
	$_SESSION['show_smf'] = strpos(strtolower(implode(',', $context['current_actions'])), '[home]') === false;
	if (isset($_POST['colspans']))
		$context = array_merge($context, $_POST);
}

/**
 * Edits the layout socified in the form loded from {@link EditEnvisionLayout()}.
 *
 * @since 1.0
 */
function EditEnvisionLayout2()
{
	global $context, $txt, $smcFunc, $sourcedir;

	checkSession();
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$id_member = $context['id_member'];

	if ($context['user']['id'] == $id_member)
		isAllowedTo('ep_edit_layouts_own');
	else
		isAllowedTo('ep_edit_layouts_any');

	ep_call_hook('edit_member_layout', array(&$_POST));

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$layout_positions = array();
	$selected_layout = !empty($_GET['in']) ? (int) $_GET['in'] : fatal_lang_error('cant_find_layout_id', false);

	if ($_SESSION['show_smf'])
	{
		if (!empty($_POST['layout_name']))
			$layout_name = checkLayoutName(trim($_POST['layout_name']));
		else
			$layout_errors[] = 'no_layout_name';

		if ($layout_name === false)
			$layout_name = '';

		$i = 0;

		if (!empty($_POST['layout_actions']))
			foreach($_POST['layout_actions'] as $laction)
			{
				preg_match('/((?:\[topic\]=)\d+)/', $laction, $matches);
				if (!empty($matches))
					$laction = $matches[1];

				$layout_actions[] = $laction;
			}
		else
			$layout_errors[] = 'no_actions';
	}

	$regulatory_check = array();
	$val = 0;
	$context['row_pos_error_ids'] = array();
	$context['col_pos_error_ids'] = array();
	$context['colspans_error_ids'] = array();

	foreach ($_POST['cId'] as $data)
	{
		list ($row, $col, $id_layout_position) = explode('_', $data);

		// Colspans must be integers
		if (!is_numeric($_POST['colspans'][$id_layout_position]))
		{
			$context['colspans_error_ids'][] = $id_layout_position;
			$layout_errors[104] = 'colspans_invalid';
		}

		// Customs? "Please remove your shoes, sir." Um, no, this is no airport!
		if (!isset($regulatory_check[$row]))
			$val = 0;

		$val = $val + ($_POST['colspans'][$id_layout_position] == 0 ? 1 : $_POST['colspans'][$id_layout_position]);
		$regulatory_check[$row] = $val;

		// Oh, this is the way we wash our variables....
		$layout_positions['x_pos'][$id_layout_position] = (int) $row;
		$layout_positions['y_pos'][$id_layout_position] = (int) $col;
		$layout_positions['colspans'][$id_layout_position] = (int) $_POST['colspans'][$id_layout_position];
		$layout_positions['status'][$id_layout_position] = !empty($_POST['enabled'][$id_layout_position]) ? 'active' : 'inactive';
		$layout_positions['is_smf'][$id_layout_position] = (int) ($_SESSION['show_smf'] && $id_layout_position == $_POST['smf_radio']);
	}

	foreach ($regulatory_check as $key => $compare)
		if (isset($regulatory_check[$key + 1]) && $compare != $regulatory_check[$key + 1])
			$layout_errors[42] = 'layout_invalid';

	if (!empty($layout_errors))
	{
		foreach ($layout_errors as $error_type)
		{
			$context['layout_error'][$error_type] = true;
			if (isset($txt['ep_' . $error_type]))
				$context['layout_error']['messages'][] = $txt['ep_' . $error_type];
		}
		return EditEnvisionLayout();
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	$layout_name = ($_SESSION['show_smf'] ? $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name']))) : '');

	editLayout($selected_layout, $layout_name, $id_member, $layout_actions, $layout_positions, $_POST['smf_radio'], $_POST['remove_positions']);

	// Cleanup...
	unset($_SESSION['show_smf']);
	unset($regulatory_check);
	unset($val);

	$log_extra = array(
		'id_layout' => $selected_layout,
		'layout_name' => $layout_name,
	);

	logEpAction('edit_layout', $id_member, $log_extra);

	redirectexit('action=profile;area=layouts;u=' . $context['id_member']);
}

/**
 * Loads the list of modules to manage.
 *
 * @since 1.0
 */
function ManageEnvisionModules()
{
	global $context, $smcFunc, $txt, $scripturl, $sourcedir, $settings;

	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$id_member = $context['id_member'];

	if ($context['user']['id'] == $id_member)
		isAllowedTo('ep_edit_layouts_own');
	else
		isAllowedTo('ep_edit_layouts_any');

	$context['in_url'] = $scripturl . '?action=profile;area=layouts;sa=managemodules;u=' . $context['id_member'];
	$context['page_title'] = $txt['ep_admin_title_manage_modules'];
	$context['sub_template'] = 'manage_modules';

	if (empty($context['layout_list']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id_member,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['layout_list'][$row['id_layout']] = $row['name'];
	}

	if (empty($context['layout_list']))
		fatal_lang_error('cant_find_layout_id', false);

	if (empty($_REQUEST['in']) || empty($context['layout_list'][$_REQUEST['in']]))
		$_REQUEST['in'] = key($context['layout_list']);

	$selected_layout = !empty($_REQUEST['in']) ? (int) $_REQUEST['in'] : fatal_lang_error('cant_find_layout_id', false);

	$_SESSION['selected_layout'] = array(
		'id_layout' => (int) $selected_layout,
		'name' => $context['layout_list'][$selected_layout],
	);

	loadLayout($_SESSION['selected_layout']['id_layout']);

	foreach ($context['ep_columns'] as &$row_data)
		foreach ($row_data as &$column_data)
		{
			$column_data += array(
				'colspan' => $column_data['extra']['colspan'],
				'enabled' => $column_data['extra']['status'] == 'active',
			);

			unset($column_data['extra']);
		}

	$request = $smcFunc['db_query']('', '
		SELECT
			type
		FROM {db_prefix}ep_modules');

	$module_context = ep_load_module_context();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['ep_all_modules'][] = array(
			'type' => $row['type'],
			'module_title' => $module_context[$row['type']]['module_title']['value'],
		);

	$context['html_headers'] .= '
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_man_mods.js"></script>
	<script type="text/javascript">
		var postUrl = "action=profile;area=layouts;sa=savemodules;u=' . $context['id_member'] . ';xml;";
		var sessVar = "' . $context['session_var'] . '";
		var sessId = "' . $context['session_id'] . '";
		var errorString = ' . JavaScriptEscape($txt['error_string']) . ';
		var modulePositionsSaved = ' . JavaScriptEscape($txt['module_positions_saved']) . ';
		var clickToClose = ' . JavaScriptEscape($txt['click_to_close']) . ';
	</script>';
}

/**
 * Saves the list of modules.
 *
 * @since 1.0
 */
function SaveEnvisionModules()
{
	global $context, $smcFunc, $sourcedir;

	checkSession();
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$id_member = $context['id_member'];

	if ($context['user']['id'] == $id_member)
		isAllowedTo('ep_edit_layouts_own');
	else
		isAllowedTo('ep_edit_layouts_any');

	ep_call_hook('edit_member_layout', array(&$_POST));

	foreach ($_POST as $epcol_idb => $epcol_data)
	{
		$epcol_id = str_replace('epcol_', '', $epcol_idb);

		if (is_array($epcol_data))
			foreach ($epcol_data as $position => $id_position)
				if (is_numeric($id_position))
					// Saving a module that was merely moved.
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}ep_module_positions
						SET
							position = {int:position},
							id_layout_position = {int:id_layout_position}
						WHERE id_position = {int:id_position}',
						array(
							'id_position' => (int) $id_position,
							'id_layout_position' => (int) $epcol_id,
							'position' => $position,
						)
					);
				else
				{
					if (is_numeric($epcol_id))
					{
						// First get the ID of the module type added.
						$request = $smcFunc['db_query']('', '
							SELECT id_module
							FROM {db_prefix}ep_modules
							WHERE type = {string:type}',
							array(
								'type' => str_replace('envisionmod_', '', $id_position),
							)
						);

						list ($id_module) = $smcFunc['db_fetch_row']($request);

						// Insert a new row for a module added from the list on the right.
						$smcFunc['db_insert']('insert',
							'{db_prefix}ep_module_positions',
							array(
								'id_layout_position' => 'int', 'id_module' => 'int', 'position' => 'int'
							),
							array(
								$epcol_id, $id_module, $position
							),
							array('id_position', 'id_layout_position', 'id_module')
						);
					}
				}
			else
			{
				if ($epcol_data == 0 && is_numeric($epcol_id))
					// Empty section. Remove what's there.
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}ep_module_positions
						WHERE id_layout_position = {int:id_layout_position}',
						array(
							'id_layout_position' => (int) $epcol_id,
						)
					);

				if (!is_numeric($epcol_id))
					// Doing the enabled checkboxes...
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}ep_layout_positions
						SET
							status = {string:status}
						WHERE id_layout_position = {int:epcol_id}',
						array(
							'epcol_id' => (int) str_replace('column_', '', $epcol_idb),
							'status' => !empty($_POST[$epcol_idb]) ? 'active' : 'inactive',
						)
					);
			}
	}

	// !!! Do we die here or use obexit(false)?
	die();
}

?>