<?php
/**************************************************************************************
* ManageEnvisionModules.php                                                           *
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
	global $context, $txt, $settings, $envisionModules, $restrictedNames;

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	// Language Files needed, load EnvisionModules first so that it can't overwrite any default Envision Strings.
	loadLanguage('ep_languages/EnvisionHelp+ep_languages/ManageEnvisionModules');

	// load the template and the style sheet needed
	loadTemplate('ep_template/ManageEnvisionModules', 'ep_css/envisionportal');

	$restrictedNames = $context['ep_restricted_names'];

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));

	// Manage Modules section will have it's own unique template function!
	if ($_REQUEST['sa'] == 'epmanmodules')
		$context['sub_template'] = 'manage_modules';

	$context['sub_action'] = $_REQUEST['sa'];

	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>';

	if (isset($_REQUEST['xml']))
		$context['template_layers'] = array();
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
	$context['in_url'] = $scripturl . '?action=admin;area=epmodules;sa=epmanlayout';

	if (empty($context['layout_list']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => 0,
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
		var postUrl = "action=admin;area=epmodules;sa=epmanlayout;xml;";
		var postUrl2 = "action=admin;area=epmodules;xml;partial";
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
	global $smcFunc;

	checkSession();

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
	global $context, $smcFunc, $txt, $helptxt, $scripturl, $settings;

	// Load the default module configurations.
	$module_context = ep_load_module_context();

	// Load user-defined module configurations.
	$request = $smcFunc['db_query']('', '
		SELECT
			name, em.type AS module_type, value
		FROM {db_prefix}ep_module_positions AS emp
			LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			LEFT JOIN {db_prefix}ep_module_field_data AS emd ON (emd.id_module_position = emp.id_position)
		WHERE emp.id_position = {int:id_position}',
		array(
			'id_position' => $_GET['in'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_type = $row['module_type'];

		if (!empty($row['name']))
			$data[$row['name']] = array(
				'value' => $row['value'],
		);
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
			'help' => isset($helptxt['ep_module_' . $module_type . '_' . $key]) ? 'ep_module_' . $module_type . '_' . $key : 'ep_' . $key,
			'label' => isset($txt['ep_module_' . $module_type . '_' . $key]) ? 'ep_module_' . $module_type . '_' . $key : 'ep_' . $key,
		);

		if (isset($field['options']) && is_string($field['options']) && strpos($field['options'], ';'))
			$field['options'] = explode(';', $field['options']);

		if (isset($field['preload']) && function_exists($field['preload']))
			$field = array_replace_recursive($field, $field['preload']($field));
	}

	$context['ep_module'] = $data;
	$context['ep_module_type'] = $module_type;
	$context['page_title'] = $txt['ep_modify_mod'];
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=modify2;in=' . $_GET['in'];
	$context['sub_template'] = 'modify_modules';
}

function ModifyModule2()
{
	global $smcFunc, $context, $txt;

	// Figure out which fields did not change and remove them.
	$request = $smcFunc['db_query']('', '
		SELECT
			name, em.type AS module_type, value
		FROM {db_prefix}ep_module_positions AS emp
			LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			LEFT JOIN {db_prefix}ep_module_field_data AS emd ON (emd.id_module_position = emp.id_position)
		WHERE emp.id_position = {int:id_position}',
		array(
			'id_position' => $_GET['in'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_type = $row['module_type'];

		if (!empty($row['name']))
			$data[$row['name']] = array(
				'value' => $row['value'],
		);
	}

	$module_context = ep_load_module_context();
	$info = $module_context[$module_type];
	$fields_to_save = array();
	ep_fill_default_fields($info);

	if (!empty($data))
		$data = array_replace_recursive($info, $data);
	else
		$data = $info;

	foreach ($data as $key => $field)
	{
		if (isset($_POST[$key]) && is_array($_POST[$key]))
			$fields_to_save[$key] = implode(',', $_POST[$key]);

		if ($field['type'] == 'check' && !isset($_POST[$key]))
			$fields_to_save[$key] = 0;

		if (!empty($field['order']))
			$fields_to_save[$key] = implode(',', $_POST[$key . 'order']) . ';' . $_POST[$key];

		if ($field['value'] != $_POST[$key])
			$fields_to_save[$key] = $_POST[$key];
	}

	// Update them, ignoring the ones they left alone.
	foreach ($fields_to_save as $key => $field)
	{
		// If the field's value is an array, concatenate it.
		if (is_array($field))
			$field = implode(',', $field);

		$smcFunc['db_insert']('replace',
			'{db_prefix}ep_module_field_data',
			array(
				'id_module_position' => 'int', 'name' => 'string', 'value' => 'string'
			),
			array(
				$_GET['in'], $key, $field
			),
			array('name', 'id_module_position')
		);
	}

	// Looks like we're done here. Depart.
	if (!isset($_REQUEST['xml']))
		redirectexit('action=admin;area=epmodules;sa=modify;in=' . $_GET['in']);
	else
	{
		$context['template_layers'] = array();
		$context['sub_template'] = 'generic_xml';
		$context['xml_data'] = array(
			'items' => array(
				'identifier' => 'item',
				'children' => array(
					array(
						'value' => $txt['save'],
					),
				),
			),
		);
	}
}

/**
 * Finds all values for the db_select parameter type.
 *
 * @param array $db_select parsed parameter. Default is an empty array.
 * @param int $param_id the parameter's ID.
 * @return array all the fields retrieved from the database table; empty array if something went wrong.
 * @since 1.0
 */
function ep_db_select($db_select, $param_id = 0)
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
		if (!isset($return[$param_id][$row[$db_select['select1']]]))
		{
			$return_val = $smcFunc['htmlspecialchars']($row[$db_select['select2']]);
			if (trim($return_val) != '')
				$return[$param_id][$row[$db_select['select1']]] = $return_val;
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
function ep_list_boards()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name AS bName, c.id_cat, c.name AS cName, c.cat_order, b.board_order
		FROM {db_prefix}categories AS c
			INNER JOIN {db_prefix}boards AS b ON (b.id_cat = c.id_cat)');

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($all_boards[$row['cat_order']]))
			$all_boards[$row['cat_order']] = array(
				'id' => $row['id_cat'],
				'name' => $row['cName'],
				'boards' => array(),
			);

		$all_boards[$row['cat_order']]['boards'][$row['board_order']] = array(
				'id' => $row['id_board'],
				'name' => $row['bName'],
			);
	}

	// Return the array of categories and boards.
	return $all_boards;
}

/**
 * Gets a list of bb codes.
 *
 * @return array a list of bb codes.
 * @since 1.0
 */
function ep_list_bbc($bbcChoice)
{
	global $context, $txt;

	// What are the options, eh?
	$bbcChoice = explode(';', $bbcChoice);
	$temp = parse_bbc(false);
	$bbcTags = array();
	foreach ($temp as $tag)
		$bbcTags[] = $tag['tag'];

	$bbcTags = array_unique($bbcTags);

	// Now put whatever BBC options we may have into context too!
	$bbc_sections = array();
	foreach ($bbcTags as $bbc)
		if (empty($modSettings['bbc_disabled_' . $bbc]))
			$bbc_sections[$bbc] = array(
				'id' => $bbc,
				'name' => $bbc,
				'title' => isset($txt['bbc_title_' . $bbc]) ? $txt['bbc_title_' . $bbc] : $txt['bbcTagsToUse_select'],
				'checked' => in_array($bbc, $bbcChoice),
			);

	return $bbc_sections;
}

/**
 * Parses a checklist.
 *
 * @param string $checked comma seperated list of values for the checklist. If a semicolon is found, the numbers before it coorrespond to the order.
 * @param array $checkStrings a list of items for the checklist. These items are the familiar $txt indexes used in language files. Default is an empty array.
 * @param array $order integer list specifying the order of items for the checklist. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the items parsed for displaying the checklist; empty array if something went wrong.
 * @since 1.0
 */
function ep_list_checks($checked = array(), $checkStrings = array(), $order = array(), $param_name, $param_id)
{
	global $context, $txt;

	if (empty($checked) || empty($checkStrings))
		return array();

	if (is_string($checked) && strpos($checked, ';'))
	{
		$checked = explode(';', $checked);
		$order = explode(',', $checked[0]);
		$checked = explode(',', $checked[1]);
	}

	if (is_string($checked) && strpos($checked, ','))
	$checked = explode(',', $checked);

	$all_checks['checks'][$param_id] = array();

	// Build the array
	foreach ($checkStrings as $key => $name)
	{
		// Ordering?
		if (!empty($order))
		{
			$all_checks['checks'][$param_id][$order[$key]] = array(
				'id' => $order[$key],
				'name' => $txt[$param_name . '_' . $checkStrings[$order[$key]]],
				'checked' => in_array($order[$key], $checked) ? true : false,
			);
		}
		else
			$all_checks['checks'][$param_id][] = array(
				'id' => $key,
				'name' => $txt[$param_name . '_' . $name],
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
 * @param bool $permissions whether or not to filter out the inherited groups. Default is false.
 * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
 * @since 1.0
 */
function ep_list_groups($checked, $unallowed = '', $order = array(), $permissions = false)
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
		WHERE id_group > {int:is_zero}' . ($permissions ? '
			AND id_parent = {int:not_inherited}' : '') . ($permissions && empty($modSettings['permission_enable_postgroups']) ? '
			AND min_posts = {int:min_posts}' : ''),
		array(
			'is_zero' => 0,
			'not_inherited' => -2,
			'min_posts' => -1,
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
 * This function checks the file input for several issues.
 * - an error is sent if thee upload did not work out.
 * - The name is sanitized so all invalid characters aree removed.
 * - Loads SMF's package extractor, read_tgz_file(), and extracts the stored files.
 * - Redirects back when all is done.
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

	require_once($sourcedir . '/Subs-Package.php');

	if ($_FILES['ep_modules']['error'] === UPLOAD_ERR_OK)
	{
		// Make sure it has a valid filename.
		$_FILES['ep_modules']['name'] = ep_parse_string($_FILES['ep_modules']['name'], 'uploaded_file');

		// Extract it to this directory.
		$pathinfo = pathinfo($_FILES['ep_modules']['name']);

		// We need to do this for PHP < 5.2.0...
		if (empty($pathinfo['filename']))
			$pathinfo['filename'] = basename($_FILES['ep_modules']['name']);

		$module_path = $context['ep_module_modules_dir'] . '/' . $pathinfo['filename'];

		// Check if name already exists, or restricted, or doesn't have a name.
		if (is_dir($module_path) || in_array($pathinfo['filename'], $reservedNames) || $pathinfo['filename'] == '')
			fatal_lang_error('module_restricted_name', false);

		// Extract the package.
		$context['extracted_files'] = read_tgz_file($_FILES['ep_modules']['tmp_name'], $module_path);

		foreach ($context['extracted_files'] as $file)
			if (basename($file['filename']) == 'module.xml')
			{
				// Parse it into an xmlArray.
				loadClassFile('Class-Package.php');
				$module_info = new xmlArray(file_get_contents($module_path . '/' . $file['filename']));

				if (!$module_info->exists('module[0]'))
					fatal_lang_error('module_package_corrupt', false);

				// End the loop. We found our man!
				break;
			}
			else
				continue;

		$module_info = $module_info->path('module[0]');
		$module = $module_info->to_array();

		// Handle the title and description of the module.
		if (trim($module['title']) == '')
			fatal_lang_error('module_has_no_title', false);

		if (trim($module['description']) == '')
			fatal_lang_error('module_no_description', false);
	}
	else
	{
		if (!empty($txt['epamerr_' . $_FILES['ep_modules']['error']]))
			fatal_lang_error('epamerr_' . $_FILES['ep_modules']['error'], false);
		else
			fatal_lang_error('epamerr_unknown', false);
	}

	// Time to go...
	redirectexit('action=admin;area=epmodules;sa=epaddmodules');
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

	$context['module_info'] = listModules();

	// Saving?
	if (isset($_POST['upload']))
	{
		// Get all Installed functions.
		$request = $smcFunc['db_query']('', '
		SELECT
			type
		FROM {db_prefix}ep_modules');

		$installed_names = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$installed_names[] = $row['type'];

		uploadModule($installed_names);
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

	if ($dir = opendir($context['ep_module_modules_dir']))
	{
		$dirs = array();
		while ($file = readdir($dir))
		{
			$retVal = ep_get_module_info('', '', $context['ep_module_modules_dir'], $file, $name, true);
			if ($retVal === false)
				continue;
			else
				$module_info[$file] = $retVal;
		}
	}

	$columns = array(
		'type' => 'string',
	);

	$data = array(
		$name,
	);

	$keys = array('id_module', 'type');

	$smcFunc['db_insert']('ignore', '{db_prefix}ep_modules',  $columns, $data, $keys);

	// Assuming they set their module up right, integrate it!
	ep_add_hook('load_module_files', $name . '/scripts/script.php');
	ep_add_hook('load_module_language_files', $name . '/languages');
	ep_add_hook('load_module_fields', 'module_' . $name . '_fields');

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

	$name = $_GET['name'];

	// Can't seem to find it.
	if (empty($name))
		fatal_lang_error('ep_module_uninstall_error', false);

	uninstallModule($name);

	redirectexit('action=admin;area=epmodules;sa=epaddmodules');
}

/**
 * Uninstalls an added module from all layouts.
 *
 * @since 1.0
 */
function uninstallModule($name = '')
{
	global $context, $smcFunc, $txt, $restrictedNames;

	// Does it exist, eg. is it installed?
	$request = $smcFunc['db_query']('', '
		SELECT
			em.id_module, emp.id_position
		FROM {db_prefix}ep_modules AS em
			LEFT JOIN {db_prefix}ep_module_positions AS emp ON (emp.id_module = em.id_module)
		WHERE em.type = {string:name}',
		array(
			'name' => $name,
		)
	);

	// Trying to uninstall something that doesn't exist!
	if ($smcFunc['db_num_rows']($request) == 0)
		return;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Remove all positions
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_positions
			WHERE id_module = {int:id_module}',
			array(
				'id_module' => $row['id_module'],
			)
		);

		// Remove all modules
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_modules
			WHERE id_module = {int:id_module}',
			array(
				'id_module' => $row['id_module'],
			)
		);

		// Remove the parameters
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_field_data
			WHERE id_module_position = {int:id_position}',
			array(
				'id_position' => $row['id_position'],
			)
		);
	}

	ep_remove_hook('load_module_files', $name . '/scripts/script.php');
	ep_remove_hook('load_module_language_files', $name . '/languages');
	ep_remove_hook('load_module_fields', 'module_' . $name . '_fields');
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
	global $context, $sourcedir;

	// Extra security here.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// We want to define our variables now...
	$name = $_GET['name'];

	uninstallModule($name);

	// Last, but not least, remove the files.
	require_once($sourcedir . '/Subs-Package.php');
	deltree($context['ep_module_modules_dir'] . '/' . $name);

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
	global $context, $txt, $smcFunc, $scripturl;

	// Just a few precautionary measures.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	$context['page_title'] = $txt['add_layout_title'];
	$context['sub_template'] = 'add_layout';
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=epaddlayout2';

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
	global $context, $txt, $smcFunc, $sourcedir;

	// Just a few precautionary measures.
	if (!allowedTo('admin_forum'))
		return;

	validateSession();
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = 0;

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

	$iid = addLayout($layout_name, 0, $layout_actions, $insert_positions);

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
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$id_layout = !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('no_layout_selected', false);

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
	global $context, $smcFunc, $txt, $scripturl;

	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	// We are editing a layout, not adding one.
	$context['edit_layout'] = true;

	// Variables in here are recycled
	AddEnvisionLayout();

	$selected_layout = !empty($_GET['in']) ? (int) $_GET['in'] : fatal_lang_error('cant_find_layout_id', false);
	$context['page_title'] = $txt['edit_layout_title'];
	$context['sub_template'] = 'edit_layout';
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=epeditlayout2;in=' . $selected_layout;

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
	require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');

	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$layout_positions = array();
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

	editLayout($selected_layout, $layout_name, 0, $layout_actions, $layout_positions, $_POST['smf_radio'], $_POST['remove_positions']);;

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
			'type' => 'select',
			'preload' => create_function('$field', '
				global $context, $smcFunc, $txt;

				$files = array();
				ep_list_files__recursive($context[\'ep_module_template\'], $files);
				$field[\'options\'] = array();

				foreach ($files as $file)
				{
					if ($file != \'index.php\')
					{
						$new_file = explode(\'.\', $file);
						$field[\'options\'][] = $file;
						$txt[\'ep_module_template_\' . $file] = $smcFunc[\'ucfirst\']($new_file[0]);
					}
				}

				return $field;'),
			'value' => 'default.php',
		),
		'module_header_display' => array(
			'type' => 'select',
			'options' => array('enabled', 'disable', 'collapse'),
			'value' => 'enabled',
		),
		'module_icon' => array(
			'type' => 'select',
			'preload' => create_function('&$field', '
				global $context, $smcFunc, $txt;

				$files = array();
				ep_list_files__recursive($context[\'ep_module_icon_dir\'], $files);
				$field[\'options\'] = array();

				foreach ($files as $file)
				{
					if ($file != \'index.php\')
					{
						$new_file = explode(\'.\', $file);
						$field[\'options\'][] = $file;
						$txt[\'ep_module_icon_\' . $file] = $smcFunc[\'ucfirst\']($new_file[0]);
					}
				}

				return $field;'),
			'iconpreview' => true,
			'url' => $context['ep_module_icon_url'],
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
			'type' => 'callback',
			'callback_func' => 'list_groups',
			'preload' => create_function('&$field', '
				$field[\'options\'] = ep_list_groups($field[\'value\']);

				return $field;'),
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

?>