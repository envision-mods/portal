<?php
/**************************************************************************************
* ManageEnvisionmodules.php                                                           *
***************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

/**
 * This file handles Envision Portal's module management settings.
 *
 * @package source
 * @copyright 2009-2010 Envision Portal
 * @license http://envisionportal.net/index.php?action=about;sa=legal Envision Portal License (Based on BSD)
 * @link http://envisionportal.net Support, news, and updates
 * @see ManageEnvisionModules.template.php
 * @since 1.0
 * @version 1.1
*/

if (!defined('SMF'))
	die('Hacking attempt...');

// !!! Needs much work

/**
 * Loads some general settings parameters to help minimize code duplication.
 *
 * @param array $subActions Array of all the subactions. The format of this array is 'mySubAction' => 'functionToCall'. Default is an empty array.
 * @param string $defaultAction A string which holds the default sub action. Default is an empty string.
 * @since 1.0
 * @todo condense this function with {@link Modules()}; why this avoids repetition I have no ides =/
 */
function loadGeneralSettingParameters3($subActions = array(), $defaultAction = '')
{
	global $context, $txt, $sourcedir, $envisionModules, $restrictedNames;

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	// Language Files needed, load EnvisionModules 1st so that it can't overwrite any default Envision Strings.
	loadLanguage('ep_languages/EnvisionModules+ep_languages/EnvisionHelp+ep_languages/ManageEnvisionModules+ManageSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	// load the template and the style sheet needed
	loadTemplate('ep_template/ManageEnvisionModules', 'ep_css/envisionportal');

	$restrictedNames = $context['ep_restricted_names'];

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));

	// Manage Modules section will have it's own unique template function!
	if ($_REQUEST['sa'] == 'epmanmodules')
		$context['sub_template'] = 'manage_modules';

	$context['sub_action'] = $_REQUEST['sa'];
}

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function Modules()
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$subActions = array(
		'epmanmodules' => 'ManageEnvisionModules',
		'epsavemodules' => 'SaveEnvisionModules',
		'epaddmodules' => 'AddEnvisionModules',
		'epinstallmodule' => 'InstallEnvisionModule',
		'epuninstallmodule' => 'UninstallEnvisionModule',
		'epdeletemodule' => 'DeleteEnvisionModule',
		'modify' => 'ModifyModule',
		'modify2' => 'ModifyModule2',
		'removemodule' => 'RemoveModule',
		'epaddlayout' => 'AddEnvisionLayout',
		'epaddlayout2' => 'AddEnvisionLayout2',
		'epdellayout' => 'DeleteEnvisionLayout',
		'epeditlayout' => 'EditEnvisionLayout',
		'epeditlayout2' => 'EditEnvisionLayout2',
	);

	loadGeneralSettingParameters3($subActions, 'epmanmodules');

	if ($context['sub_action'] != 'uploadmod')
		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => &$txt['ep_admin_modules'],
			'help' => $txt['ep_admin_modules_help'],
			'description' => $txt['ep_admin_modules_desc'],
			'tabs' => array(
				'epmanmodules' => array(
					'description' => $txt['ep_admin_modules_manmodules_desc'],
				),
				'epaddmodules' => array(
					'description' => $txt['ep_admin_modules_addmodules_desc'],
				),
			),
		);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

/**
 * Loads the list of modules to manage.
 *
 * @since 1.0
 */
function ManageEnvisionModules()
{
	global $context, $smcFunc, $txt, $scripturl, $modSettings, $settings, $envisionModules;

	$context['page_title'] = $txt['ep_admin_title_manage_modules'];

	if (empty($_SESSION['selected_layout']))
		$_SESSION['selected_layout'] = array(
			'id_layout' => 1,
			'name' => 'Homepage',
		);

	if (empty($_SESSION['layouts']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts
			WHERE id_member = {int:zero}',
			array(
				'zero' => 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$_SESSION['layouts'][$row['id_layout']] = $row['name'];
	}

	if (!empty($_POST['layout_picker']))
		$_SESSION['selected_layout'] = array(
			'id_layout' => (int) $_POST['layout_picker'],
			'name' => $_SESSION['layouts'][$_POST['layout_picker']],
		);

	loadLayout($_SESSION['selected_layout']['id_layout']);

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

	/*if (!isset($context['ep_columns']))
	{
		unset($_SESSION['selected_layout']);
		unset($_SESSION['layouts']);
		redirectexit('action=admin;area=epmodules;sa=epmanmodules');
	}*/

	$context['html_headers'] .= '
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_man_mods.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>
	<script type="text/javascript">
		var sessVar = "' . $context['session_var'] . '";
		var sessId = "' . $context['session_id'] . '";
		var errorString = "' . $txt['error_string'] . '";
		var modulePositionsSaved = "' . $txt['module_positions_saved'] . '";
		var clickToClose = "' . $txt['click_to_close'] . '";
	</script>';
}

/**
 * Saves the list of modules.
 *
 * @since 1.0
 */
function SaveEnvisionModules()
{
	global $smcFunc;

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

						// Insert a new row for a module aadded from the list on the right.
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

		/*$epcol_id = str_replace('epcol_', '', $epcol_idb);

		if (!is_array($_POST[$epcol_idb]))
			// Doing the enabled checkboxes...
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ep_layout_positions
				SET
					enabled = {int:enabled_value}
				WHERE id_layout_position = {int:epcol_id}',
				array(
					'epcol_id' => (int)str_replace('column_', '', $epcol_idb),
					'enabled_value' => (!empty($_POST[$epcol_idb]) ? 1 : 0),
				)
			);*/
	}

	// !!! Do we die here or use obexit(false)?
	die();
}

/**
 * Modifies all the settings and optional parameters for a odule/clone.
 *
 * @since 1.0
 */
function ModifyModule()
{
	global $context, $smcFunc, $txt, $helptxt;

	// Load the default module configurations.
	$module_context = ep_load_module_context();

	// Load user-defined module configurations.
	$request = $smcFunc['db_query']('', '
		SELECT
			name, emf.type, options, em.type AS module_type, value
		FROM {db_prefix}ep_module_positions AS emp
			LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			LEFT JOIN {db_prefix}ep_module_field_data AS emd ON (emd.id_module_position = emp.id_position)
			LEFT JOIN {db_prefix}ep_module_fields AS emf ON (emf.id_field = emd.id_field)
		WHERE emp.id_position = {int:id_position}',
		array(
			'id_position' => $_GET['in'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_type = $row['module_type'];

		if (!empty($row['type']))
			$data[$row['name']] = array(
				'type' => $row['type'],
				'value' => $row['value'],
		);

		if (!empty($row['options']))
			$data[$row['name']['options']] = $row['options'];
	}

	// If $module_type isn't set, the module could not be found.
	if (!isset($module_type))
		fatal_lang_error('ep_cannot_modify_module');

	// Merge the default and custom configs together.
	$info = $module_context[$module_type];
	ep_fill_default_fields($info);

	if (!empty($data))
		$data = array_replace_recursive($info, $data);
	else
		$data = $info;

	foreach ($data as $key => &$field)
	{
		$field += array(
			'help' => isset($helptxt['epmod_' . $module_type . '_' . $key]) ? 'epmod_' . $module_type . '_' . $key : 'ep_' . $key,
			'label' => isset($txt['epmod_' . $module_type . '_' . $key]) ? 'epmod_' . $module_type . '_' . $key : 'ep_' . $key,
		);

		if (isset($field['options']) && is_string($field['options']) && strpos($field['options'], ';'))
			$field['options'] = explode(';', $field['options']);

		switch ($field['type'])
		{
			case 'list_groups':
				$field['options'] = ep_list_groups($field['value']);
				break;

			case 'file_select': case 'icon_select':
				$files = array();
				ep_list_files__recursive($field['options'], $files);
				$field['options'] = array();

				foreach ($files as $file)
				{
					if ($file != 'index.php')
					{
						$new_file = explode('.', $file);
						$field['options'][] = $file;
						$txt['ep_' . $key . '_' . $file] = $smcFunc['ucfirst']($new_file[0]);
					}
				}
		}
	}

	$context['ep_module'] = $data;
	$context['ep_module_type'] = $module_type;
	$context['page_title'] = $txt['ep_modify_mod'];
	$context['sub_template'] = 'modify_modules';
}

function ModifyModule2()
{
	global $smcFunc, $context;

	// Figure out which fields did not change and remove them.
	$request = $smcFunc['db_query']('', '
		SELECT
			name, emf.type, options, em.type AS module_type, value
		FROM {db_prefix}ep_module_positions AS emp
			LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			LEFT JOIN {db_prefix}ep_module_field_data AS emd ON (emd.id_module_position = emp.id_position)
			LEFT JOIN {db_prefix}ep_module_fields AS emf ON (emf.id_field = emd.id_field)
		WHERE emp.id_position = {int:id_position}',
		array(
			'id_position' => $_GET['in'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_type = $row['module_type'];

		if (!empty($row['type']))
			$data[$row['name']] = array(
				'type' => $row['type'],
				'value' => $row['value'],
		);

		if (!empty($row['options']))
			$data[$row['name']['options']] = $row['options'];
	}

	$module_context = ep_load_module_context();
	$info = $module_context[$module_type];
	ep_fill_default_fields($info);

	if (!empty($data))
		$data = array_replace_recursive($info, $data);
	else
		$data = $info;

	foreach ($data as $key => $field)
	{
		if (isset($_POST[$key]) && is_array($_POST[$key]))
			$_POST[$key] = implode(',', $_POST[$key]);

		if ($field['type'] == 'check' && !isset($_POST[$key]))
			$_POST[$key] = 0;

		if ($field['value'] == $_POST[$key])
			unset($_POST[$key]);
	}

	// Update them, ignoring the ones they left alone.
	foreach ($_POST as $key => $field)
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				id_field
			FROM {db_prefix}ep_module_fields
			WHERE name = {string:key}',
			array(
				'key' => $key,
			)
		);
		list ($id_field) = $smcFunc['db_fetch_row']($request);

		// If the field's value is an array, concatenate it.
		if (is_array($field))
			$field = implode(',', $field);

		if (!empty($id_field))
		{
			$request = $smcFunc['db_query']('', '
				UPDATE {db_prefix}ep_module_field_data
				SET value = {string:value}
				WHERE id_field = {int:id_field}
					AND id_module_position = {int:id_module_position}',
				array(
					'id_field' => $id_field,
					'value' => $field,
					'id_module_position' => $_GET['in'],
				)
			);

			// Are we out of luck? Maybe it's a new field...
			if ($smcFunc['db_affected_rows']() != 1)
				$smcFunc['db_insert']('replace',
					'{db_prefix}ep_module_field_data',
					array(
						'id_field' => 'int', 'id_module_position' => 'int', 'value' => 'string'
					),
					array(
						$id_field, $_GET['in'], $field
					),
					array('id_field', 'id_module_position')
				);
		}
	}

	// Looks like were done here. Depart.
	redirectexit('action=admin;area=epmodules;sa=modify;in=' . $_GET['in']);
}

/**
 * Finds all values for the db_select parameter type.
 *
 * @param array $db_select parsed parameter. Default is an empty array.
 * @param int $param_id the parameter's ID.
 * @return array all the fields retrieved from the database table; empty array if something went wrong.
 * @since 1.0
 */
function ListDbSelects($db_select = array(), $param_id)
{
	global $smcFunc, $db_connection, $context;

	if (!is_array($db_select) || count($db_select) <= 1)
		return array();

	// Check to make sure they aren't the same column.
	if (trim($db_select['select1']) == trim($db_select['select2']))
		$query_select = $db['select1'];
	else
		$query_select = $db_select['select1'] . ', ' . $db_select['select2'];

	// Build the query, grabbing all results.
	$query = 'SELECT ' . $query_select . '
				FROM ' . $db_select['table'] . '
				ORDER BY NULL';

	// Execute the query, can't have any errors.
	$request = @$smcFunc['db_query']('',
		$query,
		array(
			'db_error_skip' => true,
		)
	);

	$db_error = $smcFunc['db_error']($db_connection);

	// Error with query somewhere.
	if (!empty($db_error))
		return array();

	// Table is empty.
	if ($smcFunc['db_num_rows']($request) == 0)
		return array();

	$return = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
		if (!isset($return[$param_id][$row[$db_select['select2']]]))
		{
			$return_val = (string) $smcFunc['htmlspecialchars'](un_htmlspecialchars($row[$db_select['select1']]));
			if (trim($return_val) != '')
				$return[$param_id][$row[$db_select['select2']]] = $return_val;
		}

	$smcFunc['db_free_result']($request);

	if (count($return) >= 1)
	{
		$return[$param_id] = array_unique($return[$param_id]);
		return $return[$param_id];
	}
	else
		return array();
}

/**
 * Gets a list of boards grouped by their categories.
 *
 * @return array a list of boards grouped by their categories.
 * @since 1.0
 */
function ListBoards()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name AS bName, c.id_cat, c.name AS cName
		FROM {db_prefix}boards AS b, {db_prefix}categories AS c
		WHERE b.id_cat = c.id_cat
		ORDER BY c.cat_order, b.board_order',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($all_boards[$row['id_cat']]))
			$all_boards[$row['id_cat']] = array(
				'category' => $row['cName'],
				'board' => array(),
			);

		$all_boards[$row['id_cat']]['board'][$row['id_board']] = $row['bName'];
	}
	$smcFunc['db_free_result']($request);

	// Return the array of categories and boards.
	return $all_boards;
}

/**
 * Parses a checklist.
 *
 * @param array $checked integer list of all items to be checked (have a mark in the checkbox). Default is an empty array.
 * @param array $checkStrings a list of items for the checklist. These items are the familiar $txt indexes used in language files. Default is an empty array.
 * @param array $order integer list specifying the order of items for the checklist. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the items parsed for displaying the checklist; empty array if something went wrong.
 * @since 1.0
 */
function ListChecks($checked = array(), $checkStrings = array(), $order = array(), $param_name, $param_id)
{
	global $context, $txt;

	if (empty($checked) || empty($checkStrings))
		return array();

	$all_checks['checks'][$param_id] = array();

	// Build the array
	foreach($checkStrings as $key => $name)
	{
		// Ordering?
		if (!empty($order))
		{
			$all_checks['checks'][$param_id][$order[$key]] = array(
				'id' => $order[$key],
				'name' => $txt['epmod_' . $param_name . '_' . $checkStrings[$order[$key]]],
				'checked' => in_array($order[$key], $checked) ? true : false,
			);
		}
		else
			$all_checks['checks'][$param_id][] = array(
				'id' => $key,
				'name' => $txt['epmod_' . $param_name . '_' . $name],
				'checked' => in_array($key, $checked) ? true : false,
			);
	}

	// Let's sort these arrays accordingly!
	if (!empty($order))
		$context['check_order' . $param_id] = implode(',', $order);
	else
		$context['check_order' . $param_id] = implode(',', array_keys($checkStrings));

	return $all_checks['checks'][$param_id];
}

/**
 * Gets all membergroups and filters them according to the parameters.
 *
 * @param string $checked comma-seperated list of all id_groups to be checked (have a mark in the checkbox). Default is an empty array.
 * @param string $unallowed comma-seperated list of all id_groups that are skipped. Default is an empty array.
 * @param array $order integer list specifying the order of id_groups to be displayed. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
 * @since 1.0
 */
function ep_list_groups($checked, $unallowed = '', $order = array(), $param_id = 0)
{
	global $context, $smcFunc, $txt;

	// We'll need this for loading up the names of each group.
	if (!loadLanguage('ManageBoards'))
		loadLanguage('ManageBoards');

	$checked = explode(',', $checked);
	$unallowed = explode(',', $unallowed);

	$ep_groups = array();

	if (!in_array('-1', $unallowed))
		// Guests
		$ep_groups = array(
			-1 => array(
				'id' => '-1',
				'name' => $txt['parent_guests_only'],
				'checked' => in_array('-1', $checked) || in_array('-3', $checked),
				'is_post_group' => false,
			)
		);

	if (!in_array('0', $unallowed))
	{
		// Regular Members
		if (!empty($ep_groups))
			$ep_groups += array(
				0 => array(
					'id' => '0',
					'name' => $txt['parent_members_only'],
					'checked' => in_array('0', $checked) || in_array('-3', $checked),
					'is_post_group' => false,
				)
			);
		else
			$ep_groups = array(
				0 => array(
					'id' => '0',
					'name' => $txt['parent_members_only'],
					'checked' => in_array('0', $checked) || in_array('-3', $checked),
					'is_post_group' => false,
				)
			);
	}

	// Load membergroups.
	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group > {int:is_zero}',
		array(
			'is_zero' => 0,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!in_array($row['id_group'], $unallowed))
		{
			$ep_groups[(int) $row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => trim($row['group_name']),
				'checked' => in_array($row['id_group'], $checked) || in_array('-3', $checked),
				'is_post_group' => $row['min_posts'] != -1,
			);
		}
	}
	$smcFunc['db_free_result']($request);

	// Let's sort these arrays accordingly!
	if (!empty($order))
	{
		$ep_groups = sortGroups($ep_groups, $order);

		if (!empty($param_id))
			$context['group_order' . $param_id] = implode(',', $order);
	}
	else
	{
		sort($ep_groups);

		if (!empty($param_id))
		{
			$context['group_order' . $param_id] = '';
			$x = 0;
			foreach ($ep_groups as $key => $value)
			{
				$x++;
				$context['group_order' . $param_id] .= $x < count($ep_groups) ? $value['id'] . ',' : $value['id'];
			}
		}
	}

	return $ep_groups;
}

/**
 * Sorts checkboxes in an order defined by the $orderArray. Used by {@link ListGroups()}.
 *
 * @since 1.0
 * @todo finish this document
 */
function sortGroups($array, $orderArray)
{
	if (isset($ordered))
		unset($ordered);

    $ordered = array();
    foreach($orderArray as $key => $value)
	{
        if(array_key_exists($value, $array))
		{
			$ordered[$key] = array(
				'id' => $array[$value]['id'],
				'name' => $array[$value]['name'],
				'checked' => $array[$value]['checked'],
				'is_post_group' => $array[$value]['is_post_group'],
			);
			unset($array[$value]);
        }
    }
    return $ordered + $array;
}

/**
 * Loads module's icon.
 *
 * @since 1.0
 * @todo finish this document
 */
function moduleLoadIcon($icon)
{
	global $context, $boarddir, $modSettings;

	// Default context.
	$context['module']['icon'] = array(
		'selection' => $icon == '' ? '' : $icon,
	);

	if (file_exists($boarddir . '/' . $modSettings['ep_icon_directory'] . '/' . $icon))
		$context['module']['icon'] += array(
			'server_pic' => $icon == '' ? '' : $icon
		);
	else
		$context['module']['icon'] += array(
			'server_pic' => ''
		);

	// Get a list of all of the icons.
	$context['epicon_list'] = array();
	$context['icons'] = is_dir($boarddir . '/' . $modSettings['ep_icon_directory']) ? getEnvisionIcons('', 0) : array();

	// Second level selected icon...
	$context['icon_selected'] = substr(strrchr($context['module']['icon']['server_pic'], '/'), 1);

	return true;
}

/**
 * Recursive function to retrieve envision portal icon files. Used by {@link moduleLoadIcon()}.
 *
 * @since 1.0
 * @todo finish this document
 */
function getEnvisionIcons($directory, $level)
{
	global $context, $txt, $modSettings, $boarddir;

	$result = array();

	// Open the directory..
	$dir = dir($boarddir . '/' . $modSettings['ep_icon_directory'] . (!empty($directory) ? '/' : '') . $directory);
	$dirs = array();
	$files = array();

	if (!$dir)
		return array();

	while ($line = $dir->read())
	{
		if (in_array($line, array('.', '..', 'index.php')))
			continue;

		if (is_dir($boarddir . '/' . $modSettings['ep_icon_directory'] . '/' . $directory . (!empty($directory) ? '/' : '') . $line))
			$dirs[] = $line;
		else
			$files[] = $line;
	}
	$dir->close();

	// Sort the results...
	natcasesort($dirs);
	natcasesort($files);

	if ($level == 0)
	{
		$result[] = array(
			'filename' => '',
			'checked' => empty($context['module']['icon']['server_pic']),
			'name' => $txt['no_icon'],
			'is_dir' => false
		);
	}

	foreach ($dirs as $line)
	{
		$tmp = getEnvisionIcons($directory . (!empty($directory) ? '/' : '') . $line, $level + 1);
		if (!empty($tmp))
			$result[] = array(
				'filename' => htmlspecialchars($line),
				'checked' => strpos($context['module']['icon']['server_pic'], $line . '/') !== false,
				'name' => '[' . htmlspecialchars(str_replace('_', ' ', $line)) . ']',
				'is_dir' => true,
				'files' => $tmp
		);
		unset($tmp);
	}

	foreach ($files as $line)
	{
		$filename = substr($line, 0, (strlen($line) - strlen(strrchr($line, '.'))));
		$extension = substr(strrchr($line, '.'), 1);

		// Make sure it is an image.
		if (strcasecmp($extension, 'gif') != 0 && strcasecmp($extension, 'jpg') != 0 && strcasecmp($extension, 'jpeg') != 0 && strcasecmp($extension, 'png') != 0 && strcasecmp($extension, 'bmp') != 0)
			continue;

		$result[] = array(
			'filename' => htmlspecialchars($line),
			'checked' => $line == $context['module']['icon']['server_pic'],
			'name' => htmlspecialchars(str_replace('_', ' ', $filename)),
			'is_dir' => false
		);
		if ($level == 1)
			$context['epicon_list'][] = $directory . '/' . $line;
	}
	return $result;
}

/**
 * Uploads a module.
 *
 * @since 1.0
 * @todo finish this document
 */
function UploadModule($reservedNames = array(), $installed_functions = array())
{
	global $txt, $context, $modSettings, $settings, $sourcedir, $boarddir;

	validateSession();

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	require_once($sourcedir . '/ep_source/Subs-Package.php');

	if ($_FILES['ep_modules']['error'] === UPLOAD_ERR_OK)
	{
		// Check for tar.gz or zip files.
		$tar_gz_pos = strpos(strtolower($_FILES['ep_modules']['name']), '.tar.gz');
		$zip_pos = strpos(strtolower($_FILES['ep_modules']['name']), '.zip');

		if (($tar_gz_pos === false || $tar_gz_pos != strlen($_FILES['ep_modules']['name']) - 7) && ($zip_pos === false || $zip_pos != strlen($_FILES['ep_modules']['name']) - 4))
			fatal_lang_error('module_upload_error_type', false);

		// Make sure it has a valid filename.
		$_FILES['ep_modules']['name'] = parseString($_FILES['ep_modules']['name'], 'uploaded_file');

		// Extract it to this directory.
		$pathinfo = pathinfo($_FILES['ep_modules']['name']);
		$module_path = $boarddir . '/envisionportal/modules/' . basename($_FILES['ep_modules']['name'],'.'.$pathinfo['extension']);

		// Check if name already exists, or restricted, or doesn't have a name.
		if (is_dir($module_path) || in_array(substr($_FILES['ep_modules']['name'], 0, strpos($_FILES['ep_modules']['name'], '.')), $reservedNames) || substr($_FILES['ep_modules']['name'], 0, strpos($_FILES['ep_modules']['name'], '.')) == '')
			fatal_lang_error('module_restricted_name', false);

		// Extract the package.
		$context['extracted_files'] = read_tgz_file($_FILES['ep_modules']['tmp_name'], $module_path);

		foreach ($context['extracted_files'] as $file)
			if (basename($file['filename']) == 'info.xml')
			{
				// Parse it into an xmlArray.
				loadClassFile('Class-Package.php');
				$moduleInfo = new xmlArray(file_get_contents($module_path . '/' . $file['filename']));

				// !!! Error message of some sort?
				if (!$moduleInfo->exists('module[0]'))
					fatal_lang_error('module_package_corrupt', false);

				// End the loop. We found our man!
				break;
			}
			else
				continue;

		$moduleInfo = $moduleInfo->path('module[0]');
		$module = $moduleInfo->to_array();


		if (isset($module['name']) && trim($module['name']) != '')
		{
			if (!isset($p_mod_name))
				$p_mod_name = $module['name'];
		}
		else
			fatal_lang_error('module_restricted_name', false);

		// Module already exists, remove it entire package and error out.
		if (is_dir($boarddir . '/envisionportal/modules/' . $p_mod_name))
		{
			function unlinkModule($dir)
			{
				if($dh = @opendir($dir))
				{
					while (false !== ($obj = readdir($dh)))
					{
						if($obj == '.' || $obj == '..')
							continue;

						if (!@unlink($dir . '/' . $obj))
							unlinkModule($dir.'/'.$obj);
					}
					closedir($dh);
					@rmdir($dir);
				}
				return;
			}
			unlinkModule($module_path);
			fatal_lang_error('module_restricted_name', false);
		}

		// Handle the title and description of the module.
		if (trim($module['title']) == '')
			fatal_lang_error('module_has_no_title', false);

		if (trim($module['description']) == '')
			fatal_lang_error('module_no_description', false);

		$main_count = 0;
		$all_functions = array();
		$all_files = array();
		$func_files = array();

		// Whoaa, some MAJOR ERROR CHECKING HERE!
		if ($moduleInfo->exists('file'))
		{
			$filetag = $moduleInfo->set('file');

			foreach ($filetag as $files => $path)
			{
				if ($path->exists('function'))
				{
					$functag = $path->set('function');

					foreach($functag as $func => $function)
					{
						if ($function->exists('main'))
						{
							$main_func = $function->fetch('main');

							// We'll need to check the function name and see if it's safe to use.
							if (trim($main_func) == '')
								fatal_lang_error('invalid_function_name', false);

							// Only letters, numbers, and underscores for function names.
							if (parseString($main_func, 'function_name', false) == 1)
								fatal_lang_error('invalid_function_name', false);

							$all_functions[] = $main_func;

							$main_count++;
						}
						else
						{
							$other_funcs = $function->fetch('');

							if (trim($other_funcs) == '')
								fatal_lang_error('invalid_other_function_name', false);

							// Only letters, numbers, and underscores for function names.
							if (parseString($other_funcs, 'function_name', false) == 1)
								fatal_lang_error('invalid_other_function_name', false);

							$all_functions[] = $other_funcs;
						}
					}
				}
				else
					fatal_lang_error('file_missing_functions', false);

				// Now checking all filepaths.
				if (!$path->exists('@path'))
					fatal_lang_error('module_missing_files', false);
				else
				{
					$filepath = $path->fetch('@path');
					$filepath = trim($filepath);

					// Checking for a valid filepath here.
					if (parseString($filepath, 'filepath', false) == 1)
						fatal_lang_error('module_invalid_filename', false);

					$extension = strtolower(substr($filepath, -4));
					if ($extension !== false && $extension == '.php' && $filepath != '')
					{
						if (in_array($filepath, $all_files))
							fatal_lang_error('module_has_file_defined_already', false);
						else
						{
							$all_files[] = $filepath;

							foreach($all_functions as $funcVal)
								$func_files[$funcVal] = $filepath;
						}
					}
					else
						fatal_lang_error('module_invalid_filename', false);
				}
			}
		}

		if (count($all_files) < 1)
			fatal_lang_error('module_has_no_files', false);

		if (empty($main_count) || $main_count >= 2)
			fatal_lang_error('module_has_no_main_function', false);

		if (!isset($module['name']))
			fatal_lang_error('module_has_no_name', false);

		// Checking current functions
		foreach($all_functions as $funcName)
			if (function_exists($funcName))
				fatal_lang_error('module_function_already_exists', false);

		// Checking functions for modules that are installed.
		if (count($installed_functions) > 1)
		{
			foreach ($installed_functions as $tempfunc)
			{
				$split_functions = explode('+', $tempfunc);
				foreach ($split_functions as $temp)
					if (in_array($temp, $all_functions))
						fatal_lang_error('module_function_already_exists', false);
			}
		}

		if (isset($module['iconsdir']) && trim($module['iconsdir']) != '')
		{
			$module['iconsdir'] = trim($module['iconsdir']);
			$module['iconsdir'] = parseString($module['iconsdir'], 'folderpath');
		}

		$mod_langs = array();

		if (isset($module['languages']['english']['main']) && parseString($module['languages']['english']['main'], 'filepath', false) != 1 && is_array($module['languages']))
			$languages_dir = $settings['default_theme_dir'] . '/languages';
		else
			fatal_lang_error('invalid_language_filepath', false);

		// Make sure the language files are good and add them to the master.
		foreach($module['languages'] as $lang => $langFile)
			foreach ($langFile as $type => $value)
			{
				$lFile = trim($value);

				if (parseString($lFile, 'filepath', false) == 1)
					fatal_lang_error('invalid_language_filepath', false);

				writeLanguage($module_path, $lFile, strtolower(trim($lang)) . (strtolower(trim($type)) == 'utf8' ? '-utf8' : ''), $module['name']);
			}

		// Handling the icons and the icon path.
		$valid_icons = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

		foreach ($context['extracted_files'] as $file)
		{
			$file_contents = file_get_contents($module_path . '/' . $file['filename']);
			$filename = basename($file['filename']);
			$is_icon = isset($module['iconsdir']) ? $file['filename'] != $module['iconsdir'] . '/' && strpos($file['filename'], $module['iconsdir'] . '/') !== false : false;
			$extension = $is_icon ? substr(strrchr($filename, '.'), 1) : '';

			// Uploading icons...
			if (!empty($extension) && !empty($module['iconsdir']) && empty($modSettings['ep_disable_custommod_icons']))
			{
				// All valid icon images.
				if (in_array(strtolower($extension), $valid_icons))
				{
					$icon_path = $context['epmod_icon_dir'] . $module['name'];

					// Only if the directory doesn't exist already.
					if (!is_dir($context['epmod_icon_dir'] . $module['name']))
						@mkdir($icon_path, 0755);

					// Protect the new icons directory!
					if (!file_exists($icon_path . '/index.php'))
						@copy($context['epmod_icon_dir'] . 'index.php', $icon_path . '/index.php');

					// Cache it!
					if (!file_exists($context['epmod_icon_dir'] . $module['name'] . '/.htaccess'))
						@copy($context['epmod_icon_dir'] . '.htaccess', $icon_path . '/.htaccess');

					// Place the icons.
					file_put_contents($icon_path . '/' . $filename, $file_contents);

					// Set the rights
					@chmod($icon_path . '/' . $filename, 0666);

					// Escape outta here.
					continue;
				}
			}

			if (isset($module['iconsdir']) && $file['filename'] == $module['iconsdir'] . '/')
				continue;

			// Looping through each function here.
			foreach($all_files as $phpfile)
			{
				$fPath = strpos($phpfile, '/') !== false ? '/' : '' . $phpfile;

				if ($file['filename'] != $fPath)
					continue;

				// Build the array of functions for this file.
				$file_func_names = array();
				foreach($func_files as $funcName => $funcFile)
					if ($funcFile == $phpfile)
						$file_func_names[] = $funcName;

				// Get rid of functions that are not defined within info.xml.
				RemoveUndefinedFunctions($module_path . '/' . $file['filename'], $file_func_names);
			}
		}
	}
	else
	{
		if (!empty($txt['epamerr_' . $_FILES['ep_modules']['error']]))
			fatal_lang_error('epamerr_' . $_FILES['ep_modules']['error'], false);
		else
			fatal_lang_error('epamerr_unknown', false);
	}

	// This should always return true because we already checked this earlier, but what the hell.
	if (!is_dir($boarddir . '/envisionportal/modules/' . $p_mod_name))
		rename($module_path, $boarddir . '/envisionportal/modules/' . $p_mod_name);

	// Time to go...
	redirectexit('action=admin;area=epmodules;sa=epaddmodules');
}

/**
 * Adds module language strings to the main module file
 *
 * @param string $dir the path to the file you want to write using file_put_contents.
 * @param string $contents the file data.
 * @since 1.0
 */

function writeLanguage($module_path, $lFile, $language, $mod_name)
{
	global $settings;

	$languages_dir = $settings['default_theme_dir'] . '/languages';

	// This holds the current file we are working on.
	$curr_lang_file = $languages_dir . '/EnvisionModules.' . $language . '.php';

	// If the language file doesn't exist, skip it.
	if (!file_exists($curr_lang_file))
		return false;

	// Open for reading the contents, 8 MB should be more than enough size.
	$ffile = fopen($module_path . '/' . $lFile, 'rb');
	$fcontent = fread($ffile, 8192);
	fclose($ffile);

	// Strip out php tags if they exist.
	$fcontent = parseString($fcontent, 'phptags');

	if (trim($fcontent) != '')
	{
		// So we start off with no code.
		$code = '';

		// Reading the current language file.
		$fp = fopen($curr_lang_file, 'rb');
		while (!feof($fp))
		{
			$output = fgets($fp, 16384);

			// get rid of all opening and closing php tags...
			$output = parseString($output, 'phptags');
			$code .= $output;
		}
		fclose($fp);

		// Write it into the file.
		$fo = fopen($curr_lang_file, 'wb');

		// This will help for when we have to remove the language strings for the module.
		$module_begin_comment = '// ' . ' Envision Portal Module - ' . $mod_name . ' BEGIN...';
		$module_end_comment = '// ' . ' Envision Portal Module - ' . $mod_name . ' END!';

		fwrite($fo, '<?php' . "\n" . $code . "\n" . $module_begin_comment . "\n" . $fcontent . $module_end_comment . "\n\n" . '?>');
		fclose($fo);
	}

	// Clean the cache so that the language strings are ready to be used.
	clean_cache();
}


/**
 * Handles installation of a module and custom creation of a module.
 *
 * @since 1.0
 */
function AddEnvisionModules()
{
	global $context, $txt, $sourcedir, $restrictedNames, $smcFunc;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	$context['page_title'] = $txt['ep_admin_title_add_modules'];

	$context['sub_template'] = 'add_modules';

	// We'll just require it, since if Envision is disabled, would be a problem with this.
	require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');

	$context['module_info'] = GetEnvisionAddedModules();

	// Saving?
	if (isset($_POST['upload']))
	{
		// Get all Installed functions.
		$request = $smcFunc['db_query']('', '
		SELECT
			name, functions
		FROM {db_prefix}ep_modules
		WHERE functions != {string:empty_string}',
			array(
				'empty_string' => '',
			)
		);

		$installed_functions = array();
		$installed_names = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_functions[] = $row['functions'];
			$installed_names[] = $row['name'];
		}
		$smcFunc['db_free_result']($request);

		UploadModule(array_merge($restrictedNames, $installed_names), $installed_functions);

		// Clean the cache so that the language strings are ready to be used.
		clean_cache();
		redirectexit('action=admin;area=epmodules;sa=epaddmodules');
	}
}

/**
 * Installs an added module into all layouts for the admin, placing them into the disabled modules section!
 *
 * @since 1.0
 */
function InstallEnvisionModule()
{
	global $context, $sourcedir, $smcFunc, $txt, $restrictedNames;

	// Only the Admin here...
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// We want to define our variables now...
	$AvailableModules = array();
	$name = $_GET['name'];

	if ($dir = opendir($context['epmod_modules_dir']))
	{
		$dirs = array();
		while ($file = readdir($dir))
		{
			$retVal = GetEnvisionModuleInfo('', '', $context['epmod_modules_dir'], $file, $name, true);
			if ($retVal === false)
				continue;
			else
				$module_info[$file] = $retVal;
		}
	}

	// Gives us all functions for that module, separated by a "+" sign.
	$file_functions = $module_info[$name]['functions'];

	// Now let's get all installed functions from modules.
	$request = $smcFunc['db_query']('', '
		SELECT
			functions
		FROM {db_prefix}ep_modules
		WHERE functions != {string:empty_string}',
		array(
				'empty_string' => '',
		)
	);

	$installed_functions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$installed_functions[] = $row['functions'];

	$smcFunc['db_free_result']($request);

	// Check for duplicate module function names, if found, can not install.
	foreach($installed_functions as $key => $func)
	{
		foreach (explode('+', $installed_functions[$key]) as $fName)
			if (in_array($fName, explode('+', $file_functions)))
				fatal_lang_error('module_function_duplicates', false);
	}

	// Installing...
	$request = $smcFunc['db_query']('', '
		SELECT
			dg.id_group, dl.id_layout, dlp.id_layout_position, dmp.position
		FROM {db_prefix}ep_groups AS dg
			LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group)
			LEFT JOIN {db_prefix}ep_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout AND dlp.enabled = {int:disabled})
			LEFT JOIN {db_prefix}ep_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position)
		WHERE dg.id_member = {int:zero}',
		array(
				'zero' => 0,
				'disabled' => -1,
		)
	);

	$disabled_sections = array();
	$positions = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($disabled_sections[$row['id_group']][$row['id_layout']]))
			$disabled_sections[$row['id_group']][$row['id_layout']] = array(
				'info' => $module_info[$name],
				'id_layout_position' => $row['id_layout_position']
			);

		// Increment the positions...
		if (!is_null($row['position']))
		{
			if (!isset($positions[$row['id_layout']][$row['id_layout_position']]))
				$positions[$row['id_layout']][$row['id_layout_position']] = 1;
			else
				$positions[$row['id_layout']][$row['id_layout_position']]++;
		}
		else
			$positions[$row['id_layout']][$row['id_layout_position']] = 0;
	}

	$smcFunc['db_free_result']($request);

	ksort($disabled_sections, SORT_NUMERIC);
		foreach($disabled_sections as $g => $layout)
			ksort($disabled_sections[$g], SORT_NUMERIC);

	foreach($disabled_sections as $group => $gLayout)
	{
		foreach($disabled_sections[$group] as $id => $module)
		{
			$default_layout = $group == 1 && $id == 1 ? true : false;

			// We really need an id_module for clones.
			if (!$default_layout && !isset($id_module))
				continue;

			// Add the module info to the database
			$columns = array(
				'name' => 'string',
				'title' => 'string',
				'title_link' => 'string',
				'target' => 'int',
				'icon' => 'string',
				'functions' => 'string',
				'files' => 'string',
			);

			$data = array(
				(string) $name,
				$module['info']['title'],
				!empty($module['info']['title_link']) ? $module['info']['title_link'] : '',
				!empty($module['info']['target']) ? $module['info']['target'] : 0,
				$module['info']['icon'],
				$file_functions,
				$module['info']['files'],
			);

			if (!$default_layout)
			{
				$columns = array_merge($columns, array('id_module' => 'int', 'id_member' => 'int'));
				$data = array_merge($data, array((int) $id_module, 0));
			}

			$keys = $default_layout ? array('id_module', 'name') : array('id_clone', 'id_module', 'id_member');

			$table_name = $default_layout !== false ? 'ep_modules' : 'ep_module_clones';

			$smcFunc['db_insert']('ignore', '{db_prefix}' . $table_name,  $columns, $data, $keys);

			// We need to tell the parameters table which ID was inserted
			$iid = $smcFunc['db_insert_id']('{db_prefix}' . $table_name, $default_layout !== false ? 'id_module' : 'id_clone');

			if ($default_layout)
				$id_module = $iid;

			// parameters
			$columns = array(
				'id_module' => 'int',
				'name' => 'string-255',
				'type' => 'string-16',
				'value' => 'string-65536',
			);

			$keys = array(
				'id_param',
				'id_clone',
				'id_module',
			);

			if (!$default_layout)
				$columns = array_merge(array('id_clone' => 'int'), $columns);

			// Any parameters that came with the module are also processed
			foreach ($module['info']['params'] as $param_name => $param)
			{
				$data = array(
					$iid,
					$id_module,
					$param_name,
					$param['type'],
					$param['value'],
				);

				if ($default_layout)
				{
					unset($data[1]);
					$data = array_values($data);
				}

				$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_parameters', $columns, $data, $keys);
			}

			// One more to go - insert the layout.
			$columns = array(
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'id_module' => 'int',
				'id_clone' => 'int',
				'position' => 'int',
			);

			$data = array(
				(int) $module['id_layout_position'],
				(int) $id,
				$default_layout !== false ? (int) $iid : 0,
				$default_layout !== false ? 0 : (int) $iid,
				empty($positions[$id][$module['id_layout_position']]) ? 0 : (int) $positions[$id][$module['id_layout_position']],
			);

			$keys = array(
				'id_position',
				'id_layout_position',
				'id_layout',
				'id_module',
				'id_clone',
			);
			$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_positions',  $columns, $data, $keys);
		}
	}

	// Time to go...
	redirectexit('action=admin;area=epmodules;sa=epaddmodules');
}

/**
 * Uninstalls an added module from all layouts.
 *
 * @since 1.0
 */
function UninstallEnvisionModule()
{
	global $context, $smcFunc, $txt, $restrictedNames;

	// Extra security!
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// isset is better for this.
	if (isset($_GET['name']))
		$name = $_GET['name'];
	elseif (isset($context['delete_modname']) && trim($context['delete_modname']) != '')
		$name = $context['delete_modname'];

	// Can't seem to find it.
	if (!isset($name))
		fatal_lang_error('epmod_uninstall_error', false);

	// Does it exist, eg. is it installed?
	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dmp.id_param, dmc.id_clone
		FROM {db_prefix}ep_modules AS dm
			LEFT JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.type = {string:file_input})
			LEFT JOIN {db_prefix}ep_module_clones AS dmc ON (dmc.id_module = dm.id_module)
		WHERE dm.name = {string:name}',
		array(
			'zero' => 0,
			'file_input' => 'file_input',
			'name' => $name,
		)
	);

	// Trying to uninstall something that doesn't exist!
	if ($smcFunc['db_num_rows']($request) == 0)
		if (isset($context['delete_modname']))
			return;
		else
			redirectexit('action=admin;area=epmodules;sa=epaddmodules');

	$module_info = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($module_info['id']))
		{
			$module_info['id'] = !empty($row['id_module']) ? $row['id_module'] : '';
			$module_info['params'] = array();
			$module_info['clones'] = array();
		}

		// Getting all file_input param ids.
		if (!empty($row['id_param']))
			$module_info['params'][] = $row['id_param'];

		if (!empty($row['id_clone']))
			$module_info['clones'][] = $row['id_clone'];
	}
	$smcFunc['db_free_result']($request);

	// Check to be sure we have a module id value before continuing.
	if (empty($module_info['id']))
		if (isset($context['delete_modname']))
			return;
		else
			redirectexit('action=admin;area=epmodules;sa=epaddmodules');

	// Get rid of clones that = 0, cause we don't wanna trip over SMF.
	$module_info['clones'] = array_values(array_filter($module_info['clones']));

	// Selecting the positions.
	if (isset($module_info['clones'][0]))
		$query = 'id_module = {int:id_module} || id_clone IN ({array_int:id_clones})';
	else
		$query = 'id_module = {int:id_module}';

	$request = $smcFunc['db_query']('', '
		SELECT
			id_position, id_layout_position, id_layout, position
		FROM {db_prefix}ep_module_positions
		WHERE ' . $query,
		array(
			'zero' => 0,
			'id_module' => $module_info['id'],
			'id_clones' => $module_info['clones'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_info['position'][$row['id_layout']]['pos' . $row['id_position'] . $row['position'] . '_' . $row['id_layout_position']] = $row['position'];
		$module_info['id_positions'][] = $row['id_position'];
	}

	$smcFunc['db_free_result']($request);

	// Remove all module and clone positions from the layout!
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_positions
		WHERE id_position IN ({array_int:id_positions})',
		array(
			'id_positions' => $module_info['id_positions'],
		)
	);

	foreach($module_info['position'] as $id_layout => $id_layout_pos)
	{
		foreach($id_layout_pos as $key => $position_val)
		{
			$lPos = explode('_', $key);
			$lPosId = (int) $lPos[1];

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ep_module_positions
				SET
					position = position - 1
				WHERE position > {int:position} AND id_layout = {int:id_layout} AND id_layout_position = {int:id_layout_position}',
				array(
					'id_layout' => (int) $id_layout,
					'position' => (int) $position_val,
					'id_layout_position' => $lPosId,
				)
			);
		}
	}

	// Let's remove rows via file_input parameter type.
	if (isset($module_info['params'][0]))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_files
			WHERE id_param IN ({array_int:id_params})',
			array(
				'id_params' => $module_info['params'],
			)
		);

		function unlinkFiles($dir)
		{
			if($dh = @opendir($dir))
			{
				while (false !== ($obj = readdir($dh)))
				{
					if($obj == '.' || $obj == '..')
						continue;

					if (!@unlink($dir . '/' . $obj))
						unlinkFiles($dir.'/'.$obj);
				}
				closedir($dh);
				@rmdir($dir);
			}
			return;
		}

		// Remove module's files via file_input.
		unlinkFiles($context['epmod_files_dir'] . $name);
	}

	// Remove all clones
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_clones
		WHERE id_module={int:id_module}',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Remove all modules
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_modules
		WHERE id_module = {int:id_module}',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Remove the parameters
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_parameters
		WHERE id_module = {int:id_module}',
		array(
			'id_module' => $module_info['id'],
		)
	);

	// Deleting a module.
	if (isset($context['delete_modname']))
		return;

	// Where did they uninstall from?
	$redirect = 'action=admin;area=epmodules;sa=epaddmodules';
	redirectexit($redirect);
}

/**
 * Removes a module from a layout.
 *
 * @since 1.0
 */
function RemoveModule()
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_positions
		WHERE id_position = {int:id_position}',
		array(
			'id_position' => str_replace('envisionmod_', '', $_POST['data']),
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_field_data
		WHERE id_layout_position = {int:id_position}',
		array(
			'id_position' => str_replace('envisionmod_', '', $_POST['data']),
		)
	);

	die($_POST['data']);
	}

/**
 * Removes a module with all its files from the filesystem.
 *
 * @since 1.0
 */
function DeleteEnvisionModule()
{
	global $context, $modSettings, $txt, $settings;

	// Extra security here.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// We want to define our variables now...
	$name = $_GET['name'];

	$delete_icons = empty($modSettings['ep_enable_custommod_icons']);

	function DeleteAllModuleData($dir, $deleteRootToo)
	{
		if(!$dh = @opendir($dir))
			return;

		while (false !== ($obj = readdir($dh)))
		{
			if($obj == '.' || $obj == '..')
				continue;

			if (!@unlink($dir . '/' . $obj))
				DeleteAllModuleData($dir.'/'.$obj, true);
		}
		closedir($dh);
		if ($deleteRootToo)
			@rmdir($dir);

		return;
	}

	// Before deleting, is it uninstalled?
	$context['delete_modname'] = $name;
	UninstallEnvisionModule();
	unset($context['delete_modname']);

	// Removing icons?
	if ($delete_icons)
		DeleteAllModuleData($context['epmod_icon_dir'] . $name, true);

	// Now we need to get the language and strings that need to be removed.
	$moduleInfo = file_get_contents($context['epmod_modules_dir'] . '/' . $name . '/info.xml');
	loadClassFile('Class-Package.php');
	$moduleInfo = new xmlArray($moduleInfo);

	// !!! Error message of some sort?
	if (!$moduleInfo->exists('module[0]'))
		fatal_lang_error('module_package_corrupt', false);

	$moduleInfo = $moduleInfo->path('module[0]');
	$module = $moduleInfo->to_array();

	if (isset($module['languages']) && is_array($module['languages']))
	{
		$languages_dir = $settings['default_theme_dir'] . '/languages';
		$mod_langs = array();
		$mod_langs = $module['languages'];
		// So we'll do all languages they have defined in here.
		foreach($mod_langs as $lang => $langFile)
		{
			// the language... english, british_english, russian, etc. etc.
			$language = $lang;

			foreach ($langFile as $utfType => $value)
			{
				$utf8 = $utfType == 'utf8' ? '-utf8' : '';

				// This holds the current file we are working on.
				$curr_lang_file = $languages_dir . '/EnvisionModules.' . $language . $utf8 . '.php';

				// This will help for when we have to remove the language strings for the module.
				$module_begin_comment = '// ' . ' Envision Portal Module - ' . $name . ' BEGIN...';
				$module_end_comment = '// ' . ' Envision Portal Module - ' . $name . ' END!';

				$fp = fopen($curr_lang_file, 'rb');
				$content = fread($fp, 163845);
				fclose($fp);

				// Searching within the string, extracting only what we need.
				$start = strpos($content, $module_begin_comment);
				$end = strpos($content, $module_end_comment);

				// We can't do this unless both are found.
				if ($start !== false && $end !== false)
				{
					$begin = substr($content, 0, $start);
					$finish = substr($content, $end + strlen($module_end_comment));

					$new_content = $begin . $finish;

					// Write it into the file, or create the file.
					$fo = fopen($curr_lang_file, 'wb');
					@fwrite($fo, $new_content);
					fclose($fo);
				}
			}
		}
	}

	// Last, but not least, remove the files.
	DeleteAllModuleData($context['epmod_modules_dir'] . '/' . $name, true);

	// A light heart and an easy step paves the way ;)
	redirectexit('action=admin;area=epmodules;sa=epaddmodules');
}

/**
 * Stops execution of the adding of layouts form if there are errors detected.
 *
 * @since 1.0
 */
function layoutPostError($layout_errors, $sub_template, $layout_name = '', $curr_actions = array(), $selected_layout = 0)
{
	global $context, $txt, $smcFunc;

	$context['page_title'] = $txt[$sub_template . '_title'];

	$context['sub_template'] = $sub_template;

	$context['current_actions'] = array();
	$context['layout_error'] = array(
		'messages' => array(),
	);

	foreach ($layout_errors as $error_type)
	{
		$context['layout_error'][$error_type] = true;
		if (isset($txt['ep_' . $error_type]))
			$context['layout_error']['messages'][] = $txt['ep_' . $error_type];
	}

	if (!empty($curr_actions))
		$context['current_actions'] += $curr_actions;

	if (!empty($layout_name))
		$context['layout_name'] = $layout_name;

	$context['layout_styles'] = array(
		1 => 'ep_',
		2 => 'omega',
	);

	$context['selected_layout'] = !empty($selected_layout) ? $selected_layout : 0;

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

	if (isset($context['edit_layout']))
		$context['edit_layout'] = true;

	$edit_layout_query = array('curr_layout' => $_SESSION['selected_layout']['id_layout']);
	$query_array = isset($context['edit_layout']) ? array_merge(array('one' => 1, 'zero' => 0), $edit_layout_query) : array('one' => 1, 'zero' => 0);

	// Need to add all actions, except this layouts actions to the exceptions within this group, so we don't add them twice for different layouts.
	$request = $smcFunc['db_query']('', '
		SELECT action
			FROM {db_prefix}ep_layouts AS el
				LEFT JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout)
			WHERE ela.action = {int:current_layout}
				AND el.id_member = {int:zero}',
			array(
				'current_layout' => $_SESSION['selected_layout']['id_layout'],
				'zero' => 0,
			)
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$exceptions = array_merge($exceptions, explode(',', $row['actions']));

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

	// No check for the previous submission is needed.
	checkSubmitOnce('free');

	// Acquire a new form sequence number.
	checkSubmitOnce('register');
}

/**
 * Loads the form for the admin to add a layout.
 *
 * @since 1.0
 */
function AddEnvisionLayout()
{
	global $context, $txt, $smcFunc;

	// Just a few precautionary measures.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	$context['page_title'] = $txt['add_layout_title'];
	$context['sub_template'] = 'add_layout';

	// Setting some defaults.
	$context['selected_layout'] = 1;
	$context['layout_error'] = array();
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
			WHERE ela.action = {int:current_layout}
				AND el.id_member = {int:zero}',
			array(
				'current_layout' => $_SESSION['selected_layout']['id_layout'],
				'zero' => 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$exceptions += explode(',', $row['actions']);
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
	global $context, $txt, $smcFunc;

	// Just a few precautionary measures.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// We need to pass the user's ID (zero, admin :P)
	$_POST['id_member'] = 0;

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = 0;

	ep_call_hook('add_layout', array(&$_POST));

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
		return layoutPostError($layout_errors, 'add_layout', $layout_name, $layout_actions, $selected_layout);

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	$id_group = 1;

	if (!empty($selected_layout))
		$insert_positions = ep_get_predefined_layouts($selected_layout);
	else
		fatal_lang_error('ep_layout_unknown', false);

	$layout_name = $smcFunc['htmlspecialchars']($_POST['layout_name']);

	// Add the module info to the database
	$columns = array(
		'name' => 'string-65',
	);

	$data = array(
		$layout_name,
	);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layouts',  $columns, $data, $keys);

	// We need to tell the actions table which ID was inserted
	$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layouts', 'id_layout');

	// Add the module info to the database
	$columns = array(
		'id_layout' => 'int',
		'action' => 'string',
	);
	if (count($layout_actions) == 1)
		$data = array(
			$iid,
			$layout_actions[0],
		);
	else
		foreach ($layout_actions as $layout_action)
			$data[] = array(
				$iid,
				$layout_action,
			);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_actions',  $columns, $data, $keys);

	// One more to go - insert the layout.
	$columns = array(
		'id_layout' => 'int',
		'x_pos' => 'int',
		'y_pos' => 'int',
		'colspan' => 'int',
		'status' => 'string',
	);

	$keys = array(
		'id_layout',
		'id_layout_position',
	);

	foreach ($insert_positions as $insert_position)
	{
		$data = array(
			$iid,
			$insert_position['x_pos'],
			$insert_position['y_pos'],
			$insert_position['colspan'],
			$insert_position['status'],
		);

		$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);
	}

	$_SESSION['selected_layout'] = array(
		'id_layout' => $iid,
		'name' => $layout_name,
	);

	$_SESSION['layouts'][$iid] = $layout_name;

	redirectexit('action=admin;area=epmodules;sa=epmanmodules');
}

/**
 * Calls {@link EnvisionDeleteLayout()} to delete a layout specified in $_POST['layout_picker'].
 *
 * @since 1.0
 */
function DeleteEnvisionLayout()
{
	global $txt, $sourcedir;

	if (!allowedTo('admin_forum'))
	  return;

	checkSession('get');

	$id_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('no_layout_selected', false);

	if (!deleteLayout($id_layout))
		fatal_lang_error('no_layout_selected', false);
	else
		redirectexit('action=admin;area=epmodules;sa=epmanmodules');
}

/**
 * Loads the form for the admin to edit a layout.
 *
 * @since 1.0
 */
function EditEnvisionLayout()
{
	global $context, $smcFunc, $txt;

	if (!allowedTo('admin_forum'))
	  return;

	validateSession();

	// We are editing a layout, not adding one.
	$context['edit_layout'] = true;

	// Variables in here are recycled
	AddEnvisionLayout();

	$context['page_title'] = $txt['edit_layout_title'];
	$context['sub_template'] = 'edit_layout';

	$selected_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('cant_find_layout_id', false);

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
	global $context, $txt, $smcFunc;

	// Just a few precautionary measures.
	 if (!allowedTo('admin_forum'))
	  return;

	validateSession();

	// We need to pass the user's ID (zero, admin :P)
	$_POST['id_member'] = 0;

	ep_call_hook('edit_layout', array(&$_POST));

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('cant_find_layout_id', false);

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
		$x_pos[$id_layout_position] = (int) $row;
		$y_pos[$id_layout_position] = (int) $col;
		$colspans[$id_layout_position] = (int) $_POST['colspans'][$id_layout_position];
		$status[$id_layout_position] = !empty($_POST['enabled'][$id_layout_position]) ? 'active' : 'inactive';
	}

	foreach ($regulatory_check as $key => $compare)
		if (isset($regulatory_check[$key + 1]) && $compare != $regulatory_check[$key + 1])
			$layout_errors[42] = 'layout_invalid';

	if (count($layout_errors) >= 1)
	{
		$context['layout_errors'] = true;
		EditEnvisionLayout();
		return layoutPostError($layout_errors, 'edit_layout', $layout_name, $layout_actions);
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	$layout_name = ($_SESSION['show_smf'] ? $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name']))) : '');

	// Update the name
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ep_layouts
		SET name = {string:layout_name}
		WHERE id_layout = {int:id_layout}',
		array(
			'layout_name' => $layout_name,
			'id_layout' => $selected_layout,
		)
	);

	// Delete old actions
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_layout_actions
		WHERE id_layout = {int:id_layout}',
		array(
			'id_layout' => $selected_layout,
		)
	);

	// Add the layout actions to the database
	$columns = array(
		'id_layout' => 'int',
		'action' => 'string',
	);

	if (count($layout_actions) == 1)
		$data = array(
			$selected_layout,
			$layout_actions[0],
		);
	else
		foreach ($layout_actions as $layout_action)
			$data[] = array(
				$selected_layout,
				$layout_action,
			);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_actions',  $columns, $data, $keys);

	// Update or add positions
	foreach ($_POST['cId'] as $data)
	{
		list (, , $id_layout_position) = explode('_', $data);

		if (strpos($id_layout_position, 'add') !== false)
		{
			// We have a new one to add.
			$columns = array(
				'id_layout' => 'int',
				'x_pos' => 'int',
				'y_pos' => 'int',
				'colspan' => 'int',
				'status' => 'string',
			);

			$keys = array(
				'id_layout',
				'id_layout_position',
			);

			$data = array(
				$selected_layout,
				$x_pos[$id_layout_position],
				$y_pos[$id_layout_position],
				$colspan[$id_layout_position],
				$status[$id_layout_position],
			);

			$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ep_layout_positions
			SET x_pos = {int:x_pos},
				y_pos = {int:y_pos},
				colspan = {int:colspan},
				status = {string:status}
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $id_layout_position,
				'x_pos' => $x_pos[$id_layout_position],
				'y_pos' => $y_pos[$id_layout_position],
				'colspan' => $colspans[$id_layout_position],
				'status' => $status[$id_layout_position],
			)
		);
	}

	if ($_SESSION['show_smf'] && $_POST['old_smf_pos'] != $_POST['smf_radio'])
	{
		// The admin has chosen to move SMF.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_positions
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $_POST['old_smf_pos'],
			)
		);
	}

	if (!empty($_POST['remove_positions']))
	{
		// The admin has chosen to remove some columns.
		$killdata = explode('_', $_POST['remove_positions']);

		// Remove the empty item
		unset($killdata[0]);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_layout_positions
			WHERE id_layout_position IN({array_int:remove_ids})',
			array(
				'remove_ids' => $killdata,
			)
		);
	}

	// Cleanup...
	unset($_SESSION['show_smf']);
	unset($regulatory_check);
	unset($val);

	// Update the session with the new name.
	if (!empty($layout_name))
	{
		$_SESSION['selected_layout'] = array(
			'id_layout' => (int) $selected_layout,
			'name' => $layout_name,
		);

		$_SESSION['layouts'][$selected_layout] = $layout_name;
	}

	redirectexit('action=admin;area=epmodules;sa=epmanmodules');
}

/**
 * Removes functions within a file that are not defined in the $functions array. But this will also remove functions within functions, so just don't do it!
 *
 * @param string $source absolute path to the file to check.
 * @param array $functions list of known functions to keep. All other functions are removed, including nested functions.
 * @since 1.0
 */
function RemoveUndefinedFunctions($source, $functions = array())
{
	$code = '';
	$remove = false;

	$fp = fopen($source, 'rb');
	while (!feof($fp))
	{
		$output = fgets($fp);
		$funcStart = strpos(strtolower(ltrim($output)), 'function');

		if ($funcStart !== false && $funcStart === 0)
		{
			foreach($functions as $funcName)
			{
				if (strpos($output, $funcName) !== false)
				{
					$code .= $output;
					$remove = false;
					break;
				}
				else
					$remove = true;
			}
			continue;
		}
		else
			if (substr($output, 0, 2) == '?>' || !$remove)
				$code .= $output;
	}
	fclose($fp);

	// Rewrite the file with the functions that are defined.
	$fo = fopen($source, 'wb');

	// Get rid of the extra lines...
	fwrite($fo, str_replace("\r\n", "\n", $code));

	fclose($fo);
}

/**
 * Loads all the section values minus the disabled modules section for any pre-defined layouts.
 *
 * @param int $style specifies which prese layout style to use.
 * - 1 - Default Envision Portal Layout)
 * - 2 - (OMEGA Layout) <--- This actually covers all layout styles, so no need for anymore!
 * @return array the layout formatted according to $style.
 *
 * @since 1.0
 */
function ep_get_predefined_layouts($style)
{
	// Here's Envision's default layout:
	switch ((int) $style)
	{
		case 2:
			// OMEGA
			return array(
				// row 0
				array(
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				),
				// row 1
				array(
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 2,
					'status' => 'active',
				),
				array(
					'x_pos' => 1,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				),
				// row 2
				array(
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				)
			);
			break;
		// Default - Envision Portal
		default:
			return array(
				// top
				array(
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'active'
				),
				// left
				array(
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				// middle
				array(
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				// right
				array(
					'x_pos' => 1,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				// bottom
				array(
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'inactive',
				)
			);
			break;
	}
}

/**
 * Removes all traces of a layout.
 *
 * @param int $id_layout the layout to delete
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function deleteLayout($id_layout)
{
	global $smcFunc, $user_info;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	checkSession('get');

	// !!! TODO: Check if the layout exists and find module fields linke to this layout

	ep_call_hook('add_layout', array(&$id_layout));

	foreach (array('layouts', 'layout_positions', 'layout_actions') as $table_name)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_' . $table_name . '
			WHERE id_layout = {int:id_layout}',
			array(
				'id_layout' => $id_layout,
			)
		);

	// Clear the sessions.
	unset($_SESSION['selected_layout']);
	unset($_SESSION['layouts']);

	return true;
}

/**
 * Populates the module's field list with standard fields. The field list which is passed as the parameter is added onto the default fields defined by this function.
 *
 * @param array $fields array of fields passed by reference
 * @since 1.0
 * */
function ep_fill_default_fields(&$fields)
{
	global $context;

	$new_fields = array(
		'module_title' => array(
			'type' => 'text',
		),
		'module_template' => array(
			'type' => 'file_select',
			'options' => $context['epmod_template'],
			'value' => 'default.php',
		),
		'module_header_display' => array(
			'type' => 'select',
			'options' => array('enabled', 'disable', 'collapse'),
			'value' => 'enabled',
		),
		'module_icon' => array(
			'type' => 'icon_select',
			'options' => $context['epmod_icon_dir'],
			'url' => $context['epmod_icon_url'],
			'value' => '',
		),
		'module_link' => array(
			'type' => 'text',
			'value' => '',
		),
		'module_target' => array(
			'type' => 'select',
			'options' => array('_self', '_parent', '_blank'),
			'value' => '_self',
		),
		'module_groups' => array(
			'type' => 'list_groups',
			'value' => '-3',
		),
	);

	$fields = array_replace_recursive($new_fields, $fields);
}

/**
 * Gets a list of all files in a given folder and calls itself recursively to list files in its subfollders.
 *
 * @param string $dir the directory to search in
 * @param array $files array of filenames passed by reference
 * @since 1.0
 */

function ep_list_files__recursive($dir, &$files)
{
	if (is_dir($dir))
		if ($current_dir = scandir($dir))
			foreach ($current_dir as $file)
				if ($file !== '.' && $file !== '..' && $file !== '.htaccess')
					if (!is_file($dir . $file))
						list_files__recursive($dir . $file . "/", $files);
					else
						$files[] = $file;
}

// We need to make sure that the layout name doesn't exist in any of the other layouts.
function checkLayoutName($layout_name)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_layout
		FROM {db_prefix}ep_layouts
		WHERE LOWER(name) = {string:layout_name}
			AND id_member = {int:zero}',
		array(
			'zero' => 0,
			'layout_name' => strtolower($layout_name),
		)
	);

	if ($smcFunc['db_num_rows']($request) !== 0)
	{
		list ($id_layout) = $smcFunc['db_fetch_row']($request);
		if (isset($_POST['layout_picker']) && $id_layout == $_POST['layout_picker'])
			return $layout_name;
		else
			return false;
	}
	else
		return $layout_name;
}
?>