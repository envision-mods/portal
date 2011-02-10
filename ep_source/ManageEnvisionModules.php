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
		'modifymod' => 'ModifyModule',
		'clonemod' => 'CloneEnvisionMod',
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
				dl.id_layout, dl.name
			FROM {db_prefix}ep_layouts AS dl
				LEFT JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			WHERE dl.id_group = dg.id_group',
			array(
				'one' => 1,
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

	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dm.name AS mod_name, dm.title AS mod_title, dlp.column, dlp.row, dl.actions,
			dmp.position, dlp.enabled, dmp.id_position, dlp.id_layout_position, dlp.id_layout_position AS original_id_layout_position,
			dmc.id_clone, dmc.name AS clone_name, dmc.title AS clone_title, dmc.is_clone
		FROM {db_prefix}ep_layout_positions AS dlp
			LEFT JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND dl.name = {string:layout_name} AND dl.id_layout = {int:id_layout})
			LEFT JOIN {db_prefix}ep_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position AND dmp.id_layout = dl.id_layout)
			LEFT JOIN {db_prefix}ep_module_clones AS dmc ON (dmp.id_clone = dmc.id_clone AND dmc.id_member = {int:zero})
			LEFT JOIN {db_prefix}ep_modules AS dm ON (dmp.id_module = dm.id_module)
			WHERE dlp.id_layout = dl.id_layout AND dlp.enabled != {int:invisible_layout}
		ORDER BY dlp.row',
		array(
			'layout_name' => $_SESSION['selected_layout']['name'],
			'one' => 1,
			'zero' => 0,
			'invisible_layout' => -2,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
		)
	);

	$old_row = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$is_clone = !empty($row['is_clone']) && !empty($row['id_clone']);

		if ($row['enabled'] == -1)
		{
			$row['id_layout_position'] = 0;
			$row['row'] = '0:0';
			$row['column'] = '0:0';
		}

		$_SESSION['layout_actions'] = explode(',', $row['actions']);
		$smf = (int) $row['id_clone'] + (int) $row['id_module'];
		$smf_col = empty($smf) && !is_null($row['id_position']);

		$current_row = explode(':', $row['row']);
		$current_column = explode(':', $row['column']);
		$context['span']['rows'][$row['original_id_layout_position']] = ($current_row[1] >= 2 ? ' rowspan="' . $current_row[1] . '"' : '');
		$context['span']['columns'][$row['original_id_layout_position']] = ($current_column[1] >= 2 ? ' colspan="' . $current_column[1] . '"' : '');
		if (!isset($ep_modules[$current_row[0]][$current_column[0]]) && !empty($row['id_layout_position']))
			$ep_modules[$current_row[0]][$current_column[0]] = array(
				'is_smf' => $smf_col,
				'id_layout_position' => $row['original_id_layout_position'],
				'column' => explode(':', $row['column']),
				'row' => explode(':', $row['row']),
				'enabled' => $row['enabled'],
				'disabled_module_container' => $row['enabled'] == -1,
			);

		if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
			$ep_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']] = array(
				'is_smf' => empty($smf),
				'id' => $row['id_position'],
				'title' => empty($row['id_clone']) ? $row['mod_title'] : $row['clone_title'],
				'is_clone' => $is_clone,
				'id_clone' => $row['id_clone'],
				'modify' => '<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['ep_admin_modules_manage_modify'] . '</a>',
				'clone' => '<a href="javascript:void(0)" js_link="' . $scripturl . '?action=admin;area=epmodules;sa=clonemod' . (!$is_clone ? ';mod' : '') . ';xml;' . (!empty($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . ($is_clone ? $txt['epmodule_declone'] : $txt['epmodule_clone']) . '</a>',
			);

		// Special case for disabled modules...
		if (!isset($ep_modules['disabled']) && empty($row['id_layout_position']))
			$ep_modules['disabled'] = array(
				'id_layout_position' => $row['original_id_layout_position'],
				'fake_id_layout_position' => $row['id_layout_position'],
				'column' => explode(':', $row['column']),
				'row' => explode(':', $row['row']),
				'enabled' => $row['enabled'],
			);

		if (!is_null($row['id_position']) && empty($row['id_layout_position']))
			$ep_modules['disabled']['modules'][] = array(
				'id' => $row['id_position'],
				'title' => empty($row['id_clone']) ? $row['mod_title'] : $row['clone_title'],
				'is_clone' => $is_clone,
				'id_clone' => $row['id_clone'],
				'modify' => '<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['ep_admin_modules_manage_modify'] . '</a>',
				'clone' => '<a href="javascript:void(0)" js_link="' . $scripturl . '?action=admin;area=epmodules;sa=clonemod' . (!$is_clone ? ';mod' : '') . ';xml;' . (!empty($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_module']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . ($is_clone ? $txt['epmodule_declone'] : $txt['epmodule_clone']) . '</a>',
			);
	}

	if (!empty($ep_modules))
	{
		ksort($ep_modules);

		foreach ($ep_modules as $k => $ep_module_rows)
		{
			ksort($ep_modules[$k]);
			foreach ($ep_modules[$k] as $key => $ep)
				if (is_array($ep_modules[$k][$key]))
					foreach($ep_modules[$k][$key] as $pos => $mod)
					{
						if ($pos != 'modules' || !is_array($ep_modules[$k][$key][$pos]))
							continue;

						ksort($ep_modules[$k][$key][$pos]);
					}
		}

		$context['ep_columns'] = $ep_modules;
	}

	if (!isset($context['ep_columns']))
	{
		unset($_SESSION['selected_layout']);
		unset($_SESSION['layouts']);
		redirectexit('action=admin;area=epmodules;sa=epmanmodules');
	}

	$_SESSION['dlpIepos'] = $context['ep_columns']['disabled']['id_layout_position'];

	$context['html_headers'] .= '
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_man_mods.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_admin.js"></script>
	<script type="text/javascript">
		var dlpIdPos = "' . $_SESSION['dlpIepos'] . '";
		var sessVar = "' . $context['session_var'] . '";
		var sessId = "' . $context['session_id'] . '";
		var errorString = "' . $txt['error_string'] . '";
		var cloneMade = "' . $txt['clone_made'] . '";
		var cloneDeleted = "' . $txt['clone_deleted'] . '";
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
		// if (is_bool(strpos($epcol_idb, 'epcol')))
			// continue;

		$epcol_id = str_replace('epcol_', '', $epcol_idb);

		if (!is_bool(strpos($epcol_idb, 'epcol')))
			foreach ($epcol_data as $position => $id_position)
				$newLayout[$epcol_id][$id_position] = $position;

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
			);
	}

	if (!empty($newLayout))
	foreach ($newLayout as $update_layout_key => $update_layout_value)
	{
		$update_query = '';
		$update_params = array();
		$current_positions = array();
		foreach ($update_layout_value as $update_key => $update_value)
		{
			$update_query .= '
					WHEN {int:current_position' . $update_key . '} THEN {int:new_position' . $update_key . '}';

			$update_params = array_merge($update_params, array(
				'current_position' . $update_key => $update_key,
				'new_position' . $update_key => $update_value,
			));
			$current_positions[] = $update_key;
		}

		if ($update_layout_key == 0)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ep_module_positions
				SET
					position = CASE id_position ' . $update_query . '
						END,
					id_layout_position = 0
				WHERE id_position IN({array_int:current_positions})',
				array_merge($update_params, array(
					'current_positions' => $current_positions,
				))
			);
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ep_module_positions AS dmp, {db_prefix}ep_layout_positions AS dlp
				SET
					dmp.position = CASE dmp.id_position ' . $update_query . '
						END,
					dmp.id_layout_position = {int:new_column}
				WHERE dmp.id_position IN({array_int:current_positions})',
				array_merge($update_params, array(
					'new_column' => $update_layout_key,
					'current_positions' => $current_positions,
				))
			);
	}

	// We need to empty the cache now, but make sure it is in the correct format, first.
	foreach ($_SESSION['layout_actions'] as $action)
		if (is_array(cache_get_data('envision_columns_' . md5(md5($action)), 3600)))
			cache_put_data('envision_columns_' . md5(md5($action)), 0, 3600);

	// Yep, that's all, folks!
	die();
}

/**
 * Modifies all the settings and optional parameters for a odule/clone.
 *
 * @since 1.0
 */
function ModifyModule()
{
	global $context, $txt, $helptxt, $sourcedir, $smcFunc, $envisionModules;

	$context[$context['admin_menu_name']]['current_subsection'] = 'epmanmodules';
	$context['page_title'] = $txt['ep_modify_mod'];
	$context['sub_template'] = 'modify_modules';

	// Used for grabbing stored variables and the file_input parameter type.
	require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');

	// We need to know if they are modifying an original module or a clone.  Clones will be a simple module=id_clone
	$context['modid'] = isset($_REQUEST['modid']) && !isset($_REQUEST['module']) ? (int) $_REQUEST['modid'] : '';
	$cloneid = isset($_REQUEST['module']) && !isset($_REQUEST['modid']) ? (int) $_REQUEST['module'] : '';

	// They aren't modifying anything, error!
	if(empty($context['modid']) && empty($cloneid))
		fatal_lang_error('ep_module_not_installed', false);

	// Differientiate between the 2 types of modules.
	$context['is_clone'] = !empty($context['modid']) ? false : true;
	$context['ep_modid'] = !empty($context['modid']) ? (int) $context['modid'] : (int) $cloneid;

	// Build the query structure accordingly....
	if (!empty($cloneid))
	{
		$query = 'SELECT dmc.id_clone AS modid, dmc.title, dmc.title_link, dmc.target, dmc.name, dmc.icon, dmc.header_display, dmc.template, dmc.groups, dmp.id_param, dmp.id_clone AS id_module, dmp.name AS parameter_name, dmp.type AS parameter_type, dmp.value AS parameter_value
			FROM {db_prefix}ep_module_clones AS dmc
			LEFT JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_clone = dmc.id_clone)
			WHERE dmc.id_clone = {int:id_module} AND dmc.id_member = {int:zero}';
	}
	else
	{
		$query = 'SELECT dm.id_module AS modid, dm.title, dm.target, dm.name, dm.icon, dm.title_link, dm.header_display, dm.template, dm.groups, dmp.id_param, dmp.id_module, dmp.name AS parameter_name, dmp.type AS parameter_type, dmp.value AS parameter_value
			FROM {db_prefix}ep_modules AS dm
			LEFT JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.id_clone = {int:zero})
			WHERE dm.id_module={int:id_module}';
	}

	// Load up the general stuff in here for showing all of the settings for each parameter.
	$request = $smcFunc['db_query']('', $query, array('zero' => 0, 'id_module' => $context['ep_modid']));

		// Can't load any settings to modify if the module doesn't exist.
		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('ep_module_not_installed', false);

		$context['config_params'] = array();
		$context['mod_info'] = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Load ep_modules or ep_module_clones columns.
			if (!isset($context['mod_info'][$row['modid']]))
				$context['mod_info'][$row['modid']] = array(
					'titlebar' => isset($txt['epmod_' . $row['name']]) ? 'epmod_' . $row['name'] : '',
					'title' => $row['title'],
					'target' => $row['target'],
					'icon' => moduleLoadIcon($row['icon']),
					'title_link' => $row['title_link'],
					'header_display' => !empty($row['header_display']) ? $row['header_display'] : 0,
					'name' => $row['name'],
					'help' => isset($helptxt['epmod_' . $row['name']]) ? 'epmod_' . $row['name'] : '',
					'info' => isset($txt['epmodinfo_' . $row['name']]) ? $txt['epmodinfo_' . $row['name']] : '',
					'template' => isset($row['template']) ? $row['template'] : 'default',
					'groups' => isset($row['groups']) && $row['groups'] != '' ? ListGroups($row['groups'] == '-2' ? array('-2') : explode(',', $row['groups'])) : array(),
				);

			// Load up all the module display styles.
			foreach (glob($context['epmod_template'].'*.php') as $template)
			{
				$template = explode("/", $template);
				$template = end($template);
				if ($template != 'index.php')
				{
					$template = explode(".", $template);
					$context['epmod_templates'][$template[0]] = $template[0];
				}
			}

			// Loading up all possible parameters
			if (!isset($context['config_params'][$row['id_param']]) && !empty($row['id_param']))
			{
				$context['config_params'][$row['id_param']] = array();

				// No CAPS and/or spaces, you filthy Envision Module Customizers ;)
				$row['parameter_type'] = trim(strtolower($row['parameter_type']));

				// Get all current files for this module if they exist.  Only looping through this 1 time, so we need to get it all!
				if ($row['parameter_type'] == 'file_input' && !isset($context['ep_file_input']))
				{
					$context['ep_file_input'] = true;

					// Need to know how many files they uploaded thus far.
					$result = @$smcFunc['db_query']('', '
						SELECT id_file, id_param, filename, file_hash
						FROM {db_prefix}ep_module_files
						WHERE id_param = {int:id_param} AND file_type = {int:not_thumb}',
						array(
							'id_param' => $row['id_param'],
							'not_thumb' => 0,
						)
					);

					$temp = array();
					while($rowData = $smcFunc['db_fetch_assoc']($result))
						$temp[$rowData['id_file']] = $rowData;

					$smcFunc['db_free_result']($result);

					// Sorts the array, much quicker than doing an ORDER BY.
					ksort($temp);

					// Get all files uploaded already.  Grabbing from the table.
					foreach($temp as $rowTemp)
					{
						$context['current_files'][$rowTemp['id_param']][] = array(
							'name' => $rowTemp['filename'],
							'id' => $rowTemp['id_file'],
							'file_hash' => $rowTemp['file_hash'],
						);
					}
				}
				// Prepare the select box values if we have any.
				if ($row['parameter_type'] == 'select')
				{
					$select_params = array();
					$options = array();
					if (!empty($row['parameter_value']))
					{
						$select_params = explode(':', $row['parameter_value']);
						$default = $select_params[0];
						$options = explode(';', $select_params[1]);
					}
					else
						$default = '';
				}

				// Prepare the db_select parameter type.
				if ($row['parameter_type'] == 'db_select')
				{
					if (!empty($row['parameter_value']))
					{
						$db_options = explode(':', $row['parameter_value']);
						$db_select_options = explode(';', $row['parameter_value']);
						$db_custom = isset($db_options[2]) && stristr(trim($db_options[2]), 'custom');

						if (isset($db_options[0], $db_options[1]))
						{
							$db_input = explode(';', $db_options[0]);
							$db_output = explode(';', $db_options[1]);

							if (isset($db_input[0], $db_input[1], $db_output[0], $db_output[1]))
							{
								$db_select = array();
								$db_select_params = '';
								$db_selected = $db_input[0];
								$db_select['select2'] = $db_input[1];

								if (isset($db_select_options[0], $db_select_options[1], $db_select_options[2]))
								{
									unset($db_select_options[0]);
									$db_select_params = implode(';', $db_select_options);
								}

								if (stristr(trim($db_output[0]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[0];
									$db_select['select1'] = $db_output[1];
								}
								elseif (stristr(trim($db_output[1]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[1];
									$db_select['select1'] = $db_output[0];
								}
								else
									unset($db_select);
							}
						}
					}
				}

				// What about any BBC selection boxes?
				if ($row['parameter_type'] == 'list_bbc')
				{
					$bbcChoice = explode(';', $row['parameter_value']);
					if (!empty($bbcChoice))
					{
						// What are the options, eh?
						$temp = parse_bbc(false);
						$bbcTags = array();
						foreach ($temp as $tag)
							$bbcTags[] = $tag['tag'];

						$bbcTags = array_unique($bbcTags);
						$totalTags = count($bbcTags);

						// The number of columns we want to show the BBC tags in.
						$numColumns = isset($context['num_bbc_columns']) ? $context['num_bbc_columns'] : 3;

						// Start working out the context stuff.
						$context['config_params'][$row['id_param']]['bbc_columns'] = array();
						$tagsPerColumn = ceil($totalTags / $numColumns);

						$col = 0; $i = 0;
						$bbc_columns = array();
						foreach ($bbcTags as $tag)
						{
							if ($i % $tagsPerColumn == 0 && $i != 0)
								$col++;

							$bbc_columns[$col][] = array(
								'tag' => $tag,
								// !!! 'tag_' . ?
								'show_help' => isset($helptxt[$tag]),
							);

							$i++;
						}

						// Now put whatever BBC options we may have into context too!
						$bbc_sections = array();
						foreach ($bbcChoice as $bbc)
						{
							$bbc_sections[$bbc] = array(
								'title' => isset($txt['bbc_title_' . $bbc]) ? $txt['bbc_title_' . $bbc] : $txt['bbcTagsToUse_select'],
								'disabled' => empty($modSettings['bbc_disabled_' . $bbc]) ? array() : $modSettings['bbc_disabled_' . $bbc],
								'all_selected' => empty($modSettings['bbc_disabled_' . $bbc]),
							);
						}

						$all_selected = $bbcChoice === $bbcTags;
					}
				}

				// Prepare any list_groups parameter types that we might have.
				if ($row['parameter_type'] == 'list_groups' || $row['parameter_type'] == 'checklist')
				{
					$list_options = array();
					$check_strings = array();
					$checked = array();
					$unallowed = array();
					$order = array();
					$allow_order = false;

					if (trim($row['parameter_value']) != '')
					{
						if ($row['parameter_type'] == 'list_groups')
							$list_options = explode(':', trim(strtolower($row['parameter_value'])));
						else
						{
							$list_options = explode(':', trim($row['parameter_value']));
							if (isset($list_options[2]) && stristr(trim($list_options[2]), 'order'))
								$list_options[2] = strtolower($list_options[2]);
						}

						if (!empty($list_options) && isset($list_options[0]) && trim($list_options[0]) != '' && !stristr(trim($list_options[0]), 'order'))
						{
							if (isset($list_options[1]) && !stristr(trim($list_options[1]), 'order') && $row['parameter_type'] == 'list_groups')
							{
								$checked = explode(',', trim($list_options[0]));
								$unallowed = explode(',', trim($list_options[1]));

								// Should probably check for valid integer types also.
								foreach($checked as $group => $id)
									if (in_array($id, $unallowed))
										unset($checked[$group]);

								if (count($checked) >= 1)
									$checked = array_values($checked);
								else
									$checked = array('-2');
							}
							elseif (isset($list_options[1]) && $row['parameter_type'] == 'checklist')
							{
								$checked = explode(',', trim($list_options[0]));
								$check_strings = explode(';', trim($list_options[1]));

								// Any checked that are not in the list?  Uncheck all.
								foreach($checked as $check)
									if (!isset($check_strings[$check]))
										$checked = array('-2');
							}
							else
								$checked = explode(',', trim($list_options[0]));

							// Let's find out if they can be ordered or not.
							if (isset($list_options[2]))
							{
								$get_order = explode(';', $list_options[2]);

								if (!empty($get_order))
									if (trim($get_order[0]) == 'order')
									{
										$allow_order = true;
										if (isset($get_order[1]))
											$order = explode(',', trim($get_order[1]));
									}
							}
							elseif (!isset($list_options[2]) && isset($list_options[1]) && $row['parameter_type'] == 'list_groups')
							{
								$get_order = explode(';', $list_options[1]);

								if (!empty($get_order))
									if (trim($get_order[0]) == 'order')
									{
										$allow_order = true;

										if (isset($get_order[1]))
											$order = explode(',', trim($get_order[1]));
									}
							}
						}
						else
							$checked = array('-2');
					}
					// Nothing checked, if no parameter value somehow is completely empty!
					else
						$checked = array('-2');
				}

				// Prepare the File input values if we have any.
				if ($row['parameter_type'] == 'file_input')
				{
					if (!empty($row['parameter_value']))
					{
						$file_input = explode(':', $row['parameter_value']);

						$file_count = isset($file_input[0]) && !empty($file_input[0]) ? (int) $file_input[0] : 0;

						$mimes = !empty($file_input[1]) ? $file_input[1] : 'image/gif;image/jpeg;image/png;image/bmp';
						$dimensions = isset($file_input[2]) ? (string) $file_input[2] : '';
					}
					// We can't have empty now can we?
					else
					{
						$file_count = 1;
						$mimes = 'image/gif;image/jpeg;image/png;image/bmp';
						$dimensions = '';
					}
				}

				// Build up the parameters array
				$context['config_params'][$row['id_param']] = array(
					'id' => $row['id_param'],
					'modid' => $context['ep_modid'],
					'label_id' => 'ep_' . $row['id_param'] . '_' . $row['parameter_name'],
					'label' => 'epmod_' . $row['name'] . '_' . $row['parameter_name'],
					'bbc_columns' => $row['parameter_type'] == 'list_bbc' ? $bbc_columns : '',
					'bbc_sections' => $row['parameter_type'] == 'list_bbc' ? $bbc_sections : '',
					'bbc_all_selected' => $row['parameter_type'] == 'list_bbc' ? $all_selected : false,
					'file_mimes' => $row['parameter_type'] == 'file_input' ? (string) $mimes : '',
					'file_count' => $row['parameter_type'] == 'file_input' ? (int) $file_count : '',
					'file_dimensions' => $row['parameter_type'] == 'file_input' ? (string) $dimensions : '',
					'help' => isset($helptxt['epmod_' . $row['name'] . '_' . $row['parameter_name']]) ? 'epmod_' . $row['name'] . '_' . $row['parameter_name'] : '',
					'db_select_options' => $row['parameter_type'] == 'db_select' ? (isset($db_select) ? ListDbSelects($db_select, $row['id_param']) : array()) : array(),
					'db_selected' => $row['parameter_type'] == 'db_select' ? $db_selected : '',
					'db_select_custom' => $row['parameter_type'] == 'db_select' ? $db_custom : false,
					'name' => $row['name'] . '_' . $row['parameter_name'],
					'size' => $row['parameter_type'] == 'int' ? '2' : ($row['parameter_type'] == 'large_text' ? '4' : '30'),
					'select_options' => $row['parameter_type'] == 'select' ? $options : ($row['parameter_type'] == 'list_boards' ? ListBoards() : ''),
					'check_order' => $row['parameter_type'] == 'list_groups' || $row['parameter_type'] == 'checklist' ? $allow_order : '',
					'options' => $row['parameter_type'] == 'select' && isset($select_params[1]) ? $select_params[1] : ($row['parameter_type'] == 'db_select' && isset($db_select_params) ? $db_select_params : ''),
					'select_value' => $row['parameter_type'] == 'select' ? (int) $default : $row['parameter_type'] == 'list_boards' ? $row['parameter_value'] : '',
					'type' => $row['parameter_type'],
					'value' => $row['parameter_type'] == 'html' ? html_entity_decode($row['parameter_value'], ENT_QUOTES) : $row['parameter_value'],
				);

					// Let's make sure each parameter gets sorted correctly!!
					ksort($context['config_params']);
					foreach($context['config_params'] as $key => $sort);
						ksort($context['config_params'][$key]);

					// Build the Group list or Checklist.
					$context['config_params'][$row['id_param']]['check_value'] = '';

					if ($row['parameter_type'] == 'list_groups')
					{
						if(!empty($unallowed))
						{
							$context['config_params'][$row['id_param']]['check_value'] = implode(',', $unallowed);
							if ($allow_order)
								$context['config_params'][$row['id_param']]['check_value'] .= ':order';
						}
						elseif(empty($unallowed))
							if ($allow_order)
								$context['config_params'][$row['id_param']]['check_value'] = ':order';
					}
					elseif ($row['parameter_type'] == 'rich_edit')
					{
						// Needed for the editor.
						require_once($sourcedir . '/ep_source/Subs-Editor.php');

						// Now create the editor.
						$editorOptions = array(
							'id' => $context['config_params'][$row['id_param']]['name'],
							'value' => $row['parameter_value'],
							'labels' => array(
							),
							'height' => '175px',
							'width' => '100%',
							'preview_type' => 2,
							'rich_active' => false,
						);

						create_control_richedit($editorOptions);
						$context['config_params'][$row['id_param']]['post_box_name'] = $editorOptions['id'];

						$context['controls']['richedit'][$context['config_params'][$row['id_param']]['name']]['rich_active'] = false;
					}
					elseif($row['parameter_type'] == 'checklist')
					{
						if (!empty($check_strings))
						{
							$context['config_params'][$row['id_param']]['check_value'] = implode(';', $check_strings);
							if ($allow_order)
								$context['config_params'][$row['id_param']]['check_value'] .= ':order';
						}
					}

					$context['config_params'][$row['id_param']]['check_options'] = array();

					if ($row['parameter_type'] == 'list_groups' && !empty($checked))
						$context['config_params'][$row['id_param']]['check_options'] = ListGroups($checked, $unallowed, $order, $row['id_param']);
					elseif($row['parameter_type'] == 'checklist' && !empty($checked))
						$context['config_params'][$row['id_param']]['check_options'] = ListChecks($checked, $check_strings, $order, $row['name'] . '_' . $row['parameter_name'], $row['id_param']);

			}
		}
		$smcFunc['db_free_result']($request);

	// Saving?
	if (isset($_REQUEST['save']))
	{
		checkSession();

		if (!isset($context['ep_modid']) || empty($context['ep_modid']))
			fatal_lang_error('ep_module_not_installed', false);

		// Get the title, target, target link, and the icon.
		$module_title = (string) html_entity_decode($_REQUEST['module_title'], ENT_QUOTES);

		// Fix the module title so it's not soooooooooo long, 30 is a good number I suppose ;)
		if($smcFunc['strlen']($module_title) > 30)
			$module_title = $smcFunc['substr']($module_title, 0, 27) . '...';

		$query_array = array(
			'module_title' => $smcFunc['htmlspecialchars'](un_htmlspecialchars($module_title)),
			'module_target' => !empty($_POST['module_link_target']) ? (int) $_POST['module_link_target'] : 0,
			'module_link' => (string) $_POST['module_link'],
			'module_icon' => isset($_POST['file']) ? (string) $_POST['file'] : (isset($_POST['cat']) ? (string) $_POST['cat'] : ''),
			'id_module' => $context['ep_modid'],
			'module_header_display' => !empty($_POST['module_header']) ? (int) $_POST['module_header'] : 0,
			'module_template' => $_POST['module_template'],
			'module_groups' => isset($_POST['groups']) && $_POST['groups'] != '' ? implode(',', $_POST['groups']) : '-2',
		);

		// Build the query.
		$query = 'UPDATE {db_prefix}' . (!empty($context['modid']) ? 'ep_modules' : 'ep_module_clones') . '
		SET ' . (!empty($module_title) ? 'title = {string:module_title}, ' : '') . 'title_link = {string:module_link},
			target = {int:module_target}, icon = {string:module_icon}, header_display = {int:module_header_display}, template = {string:module_template}, groups = {string:module_groups}
		WHERE ' . (!empty($context['modid']) ? 'id_module' : 'id_clone') . ' = {int:id_module}';

		// Update the title, link and icon.
		$smcFunc['db_query']('', $query, $query_array);

		// Need to get all values of all hidden inputs
		if (isset($_POST['moeparams_count']) && !empty($_POST['moeparams_count']))
		{
			$params_count = (int) $_POST['moeparams_count'];
			$param_names = array();
			$param_types = array();

			for($x=0; $x<=$params_count - 1; $x++)
			{
				if (isset($_POST['param_name' . ($x+1)]))
				{
					$param_names[intval($_POST['param_id' . ($x+1)])] = (string) $_POST['param_name' . ($x+1)];
					$param_types[intval($_POST['param_id' . ($x+1)])] = (string) $_POST['param_type' . ($x+1)];
				}
			}

			// Ok, check if we have a file_input type somewhere in here.
			if (in_array('file_input', $param_types))
			{
				// Set the file directory.
				$module_dir = $context['epmod_files_dir'] . $_POST['modname'];

				// Ohhh, folder.... Where are you?
				if (!is_dir($module_dir))
					if (!mkdir($module_dir, 0755, true))
						fatal_error($txt['mod_folder_missing'] . ' /envisionportal/module_files/' . $_POST['modname'], false);

				// Protect the Folder.  Safety First!
				if (!file_exists($module_dir . '/index.php'))
					copy($context['epmod_files_dir'] . 'index.php', $module_dir . '/index.php');

				// Secure it!
				if (!file_exists($module_dir . '/.htaccess'))
					copy($context['epmod_files_dir'] . '.htaccess', $module_dir . '/.htaccess');

				// Begin to build the file input array.
				if (!isset($fileOptions))
					$fileOptions = array(
						'id_member' => 0,
						'folderpath' => $module_dir,
						'mod_id' => $context['ep_modid'],
					);
			}

			foreach($param_names as $id => $value)
			{
				if (!isset($filid))
					$filid = 0;

				$filid++;

				// Do we have any file inputs?
				if ($param_types[$id] == 'file_input')
				{
					$quantity = !empty($context['current_files'][$id]) ? (int) $context['current_files'][$id] : 0;
					$files = array();

					// Store the files parameter id value
					$fileOptions['id_param'] = $id;

					if (isset($files[$id-1]))
						unset($files[$id-1]);

					$files[$id] = array();

					// Getting the mime types, need the $filid variable.
					if (isset($_POST['file_mimes' . $filid]) && !empty($_POST['file_mimes' . $filid]))
						$files[$filid]['mimetypes'] = explode(';', (string) $_POST['file_mimes' . $filid]);
					else
						$files[$filid]['mimetypes'] = array('image/gif', 'image/jpeg', 'image/png', 'image/bmp');

					// Get the file count.
					if (isset($_POST['file_count' . $filid]))
						$files[$filid]['filecount'] = !empty($_POST['file_size' . $filid]) ? (int) $_POST['file_size' . $filid] : 0;

					// Get the dimensions if exists.
					if (isset($_POST['file_dimensions' . $filid]) && !empty($_POST['file_dimensions' . $filid]))
					{
						$files[$filid]['dimensions'] = !empty($_POST['file_dimensions' . $filid]) ? $_POST['file_dimensions' . $filid] : '';
						$width_height = explode(';', $files[$filid]['dimensions']);
					}

					// Get the dimensions to resize to, if specified, otherwise, don't bother resizing.
					if (!empty($width_height))
					{
						$resize = array();

						foreach($width_height as $dimension)
						{
							if (substr(trim(strtolower($dimension)), 0, 6) == 'width=')
							{
								$resize['width'] = substr($dimension, 6);
								continue;
							}
							elseif(substr(trim(strtolower($dimension)), 0, 7) == 'height=')
							{
								$resize['height'] = substr($dimension, 7);
								continue;
							}
							elseif(substr(trim(strtolower($dimension)), 0, 6) == 'strict')
							{
								$resize['is_strict'] = true;
								continue;
							}
						}
					}

					// Handle the removal of any current files within a parameter.
					if (isset($_POST['file_del' . $filid]) && !empty($_POST['file_del' . $filid]))
					{
						$del_temp = array();
						foreach ($_POST['file_del' . $filid] as $i => $dummy)
							$del_temp[$i] = (int) $dummy;

						foreach ($context['current_files'][$id] as $k => $dummy)
						{
							if (!in_array($dummy['id'], $del_temp))
							{
								// Let's remove the file first.
								$file = getFilename($dummy['name'], $dummy['id'], $module_dir, false, $dummy['file_hash']);
								@unlink($file);

								// Remove any thumbnail associations.
								$result = $smcFunc['db_query']('', '
									SELECT dmf.id_thumb, thumb.filename, thumb.file_hash
									FROM {db_prefix}ep_module_files AS dmf
									LEFT JOIN {db_prefix}ep_module_files AS thumb ON (thumb.id_file = dmf.id_thumb)
									WHERE dmf.id_file = {int:id_file} AND dmf.id_param = {int:id_param} AND dmf.file_type = {int:is_zero}
									LIMIT 1',
									array(
										'id_param' => $id,
										'is_zero' => 0,
										'id_file' => $dummy['id'],
								));

								list ($id_thumb, $thumb_name, $thumb_hash) = $smcFunc['db_fetch_row']($result);
								$smcFunc['db_free_result']($result);

								$thumb = array('is_zero' => 0);
								$query = 'id_file = {int:id_file} AND id_param = {int:id_param} AND file_type = {int:is_zero} LIMIT 1';

								if (!empty($id_thumb))
								{
									$thumb = array('id_thumb' => $id_thumb);
									$query = 'id_param = {int:id_param} AND (id_file = {int:id_file} OR id_file = {int:id_thumb}) LIMIT 2';
									// Delete the thumbnail.
									$file_thumb = getFilename($thumb_name, $id_thumb, $module_dir, false, $thumb_hash);
									unlink($file_thumb);
								}

								// Now Remove it from the database.
								$smcFunc['db_query']('', 'DELETE FROM {db_prefix}ep_module_files
								WHERE ' . $query,
								array_merge($thumb, array('id_file' => $dummy['id'], 'id_param' => (int) $id)));
							}
						}
					}

					// Are we adding any new files?
					if (isset($_FILES[$value]) && !empty($_FILES[$value]['name']))
					{
						// Getting all files for each parameter.
						foreach ($_FILES[$value]['tmp_name'] as $n => $dummy)
						{
							// Empty? Don't bother.
							if ($_FILES[$value]['name'][$n] == '')
								continue;

							// Is the path writable?
							if (!is_writable($module_dir))
								fatal_lang_error('module_files_no_write', false);

							// Problem uploading?
							if (!is_uploaded_file($_FILES[$value]['tmp_name'][$n]) || (@ini_get('open_basedir') == '' && !file_exists($_FILES[$value]['tmp_name'][$n])))
								fatal_lang_error('module_file_timeout', false);

							// Fix for PSD files.
							if ((in_array('image/psd', $files[$filid]['mimetypes']) || strtolower($files[$filid]['mimetypes'][0]) == 'all') && ($_FILES[$value]['type'][$n] == 'application/octet-stream' || $_FILES[$value]['type'][$n] == 'application/octetstream'))
							{
								// Get the extension of the file.
								$file_extension = strtolower(substr(strrchr($_FILES[$value]['name'][$n], '.'), 1));

								if ($file_extension == 'psd')
								{
									$psd = true;

									// if no size, than it's not a valid PSD file.
									$size = @getimagesize($_FILES[$value]['tmp_name'][$n]);

									if (empty($size))
										unset($psd);
								}
							}

							// Check for PHP Files
							if ((in_array('application/x-httpd-php', $files[$filid]['mimetypes']) || strtolower($files[$filid]['mimetypes'][0]) == 'all') && ($_FILES[$value]['type'][$n] == 'application/octet-stream' || $_FILES[$value]['type'][$n] == 'application/octetstream'))
							{
								$file_extension = strtolower(substr(strrchr($_FILES[$value]['name'][$n], '.'), 1));

								if ($file_extension == 'php')
								{
									// Reading the current php file to make sure it's a PHP File.
									$fo = fopen($_FILES[$value]['tmp_name'][$n], 'rb');
									while (!feof($fo))
									{
										$fo_output = fgets($fo, 16384);

										// look for a match
										if ((substr($fo_output, 0, 5) == '<?php' || substr($fo_output, 0, 2) == '<?') && substr($fo_output, 0, 5) != '<?xml')
										{
											$php = true;
											break;
										}
									}
									fclose($fo);
								}
							}

							// Try and get the mime if you dare!
							if (!isset($psd) && !isset($php))
							{
								if (!in_array($_FILES[$value]['type'][$n], $files[$filid]['mimetypes']) && strtolower($files[$filid]['mimetypes'][0]) != 'all')
									fatal_error(sprintf($txt['module_wrong_mime_type'], $_FILES[$value]['type'][$n]), false);
								else
									// Store the mime.
									$fileOptions['file_mime'] = $_FILES[$value]['type'][$n];
							}
							elseif (isset($php))
							{
								// Do 1 more check here.
								if (isPHPFile($_FILES[$value]['tmp_name'][$n]))
									$fileOptions['file_mime'] = 'application/x-httpd-php';
								else
									$fileOptions['file_mime'] = $_FILES[$value]['type'][$n];
							}
							else
								$fileOptions['file_mime'] = 'image/psd';

							$quantity++;

							// Check the filecount quantity.
							if (!empty($files[$filid]['filecount']) && $quantity > $files[$filid]['filecount'])
								fatal_lang_error('module_file_limit', false);

							// These need to be set on a per file basis.
							$fileOptions['id_file'] = 0;
							$fileOptions['name'] = $_FILES[$value]['name'][$n];
							$fileOptions['tmp_name'] = $_FILES[$value]['tmp_name'][$n];
							$fileOptions['size'] = $_FILES[$value]['size'][$n];

							// Check if there are dimensions defined and set this accordingly.
							if (!empty($files[$filid]['dimensions']))
							{
								// All Valid image mime types.
								$image_mimes = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/tiff', 'image/psd');

								// Eureka, we have a compatible image mime.
								if (in_array($_FILES[$value]['type'][$n], $image_mimes))
								{
									// Check if it must be resized or not.
									if(!empty($resize['width']) || !empty($resize['height']))
									{
										$fileOptions['resizeWidth'] = !empty($resize['width']) ? $resize['width'] : 0;
										$fileOptions['resizeHeight'] = !empty($resize['height']) ? $resize['height'] : 0;
										$fileOptions['strict'] = !empty($resize['is_strict']) ? true : false;
									}
								}
							}

							// Get all of the allowed extensions for this mime type.
							$fileOptions['fileExtensions'] = AllowedFileExtensions($fileOptions['file_mime']);

							if (!createFile($fileOptions))
							{
								// Error Somewhere...
								if (in_array('files_no_write', $fileOptions['errors']))
									fatal_lang_error('module_folderpath_error', true);
								if (in_array('could_not_upload', $fileOptions['errors']))
									fatal_lang_error('restricted_unexists', true);
								if (in_array('file_timeout', $fileOptions['errors']))
									fatal_lang_error('file_timeout', false);
								if (in_array('bad_extension', $fileOptions['errors']))
									fatal_lang_error('file_bad_extension', false);
							}

							// Free up some unneccessary stuff!
							if (isset($fileOptions['resizeWidth']))
								unset($fileOptions['resizeWidth']);
							if (isset($fileOptions['resizeHeight']))
								unset($fileOptions['resizeHeight']);
							if (isset($fileOptions['strict']))
								unset($fileOptions['strict']);
							unset($fileOptions['file_mime']);
							unset($fileOptions['id_file']);
							unset($fileOptions['name']);
							unset($fileOptions['tmp_name']);
							unset($fileOptions['size']);
						}
					}
					// Are we done with the file_input?  And I was just getting warmed up ;)
					continue;
				}

				// Dealing with Listed Group Checkboxes or Checklists now!
				if ($param_types[$id] == 'list_groups' || $param_types[$id] == 'checklist')
				{
					$checked_groups = array();
					$unallowed_groups = array();
					$check_strings = array();
					$conval = array();
					$ordered = false;
					$group_order = '';

					$listgroup = $param_types[$id] == 'list_groups' ? true : false;
					$checkid = $param_types[$id] == 'list_groups' ? 'grp' : 'chk';
					$checkname = $param_types[$id] == 'list_groups' ? 'groups' : 'checks';

					if(isset($_POST['conval' . $checkid . '_' . $filid]))
						$conval = explode(':', $_POST['conval' . $checkid . '_' . $filid]);

					if(!empty($conval))
					{
						if (isset($conval[0]) && trim(strlen($conval[0])) >= 1 && !empty($conval[1]))
						{
							$ordered = $conval[1] == 'order' ? true : false;

							if ($listgroup)
								$unallowed_groups = explode(',', $conval[0]);
							else
								$check_strings = explode(';', $conval[0]);
						}
						else
						{
							// We are dealing with only 1 value in the array.  Could be either or.  So check it.
							if ($conval[0] == 'order' && $listgroup)
							{
								$ordered = true;
							}
							elseif (trim(strlen($conval[0])) >= 1 && $conval[0] != 'order' && $listgroup)
								$unallowed_groups = explode(',', $conval[0]);
							elseif (!$listgroup)
								$check_strings = explode(';', $conval[0]);
						}
					}

					// We should know this by now.
					if ($ordered)
					{
						if (isset($_POST['order' . $checkid . '_' . $filid]) && $_POST['order' . $checkid . '_' . $filid] != '')
							$check_order = (string) $_POST['order' . $checkid . '_' . $filid];
						else
						{
							$ordered = false;
							$check_order = '';
						}
					}

					// Build the checked list.
					$checked_list = array();

					if (!empty($_POST[$checkname . $filid]))
						foreach ($_POST[$checkname . $filid] as $checks)
						{
							if ($listgroup)
							{
								if (!in_array($checks, $unallowed_groups))
									$checked_list[] = (int) $checks;
							}
							else
								$checked_list[] = (int) $checks;
						}
					else
						$checked_list = array('-2');

					// Just in case all checked are $unallowed.  Don't ask how!
					if (empty($checked_list))
						$checked_list = array('-2');

					$checkStr = !empty($checked_list) ? implode(',', $checked_list) : '-2';

					// Build the list of either unallowed or check strings
					if ($listgroup)
						$the_list = !empty($unallowed_groups) ? ':' . implode(',', $unallowed_groups) : '';
					else
					{
						// If no first string, unset.
						if (!isset($check_strings[0]) || strlen(trim($check_strings[0])) <= 0)
							unset($check_strings);

						$the_list = !empty($check_strings) ? ':' . implode(';', $check_strings) : '';
					}

					// Put it all together now!
					$check_value = $checkStr . $the_list . ($ordered ? ':order' . ($check_order != '' ? ';' . $check_order : '') : '');
				}
				else
				{
					// Clear any previous checklists if we have none for this parameter setting.
					if (isset($check_value))
						unset($check_value);

					$daValue = isset($_POST[$value]) ? ($param_types[$id] == 'int' || $param_types[$id] == 'check' ? (empty($_POST[$value]) || intval($_POST[$value]) < 0 ? '0' : (int) $_POST[$value]) : (string) $_POST[$value]) : '';
				}
				// Handle all selects.
				if ($param_types[$id] == 'select' || $param_types[$id] == 'db_select')
				{
					if (isset($_POST['param_opts' . $id]))
						$param_opts = (string) $_POST['param_opts' . $id];

					if (isset($param_opts) && strlen($param_opts) > 0)
						$daValue = $daValue . ($param_types[$id] == 'db_select' ? ';' : ':') . $param_opts;
				}

				if ($param_types[$id] == 'db_select' && isset($_POST[$value . '_db_custom']))
				{
					$new_db_vals = array();
					foreach ($_POST[$value . '_db_custom'] as $insert_value)
					{
						$insert_value = $smcFunc['htmlspecialchars'](un_htmlspecialchars(strip_tags(trim($insert_value))));

						if (!empty($insert_value))
							if (count($_POST[$value . '_db_custom']) == 1)
								$new_db_vals[] = $insert_value;
							else
								$new_db_vals[] = array($insert_value);
					}

					// Now let's get the column and table for our insert.
					if (!empty($daValue))
					{
						$db_options = explode(':', $daValue);
						$db_select_options = explode(';', $row['parameter_value']);
						$db_custom = isset($db_options[2]) && stristr(trim($db_options[2]), 'custom');

						if (isset($db_options[0], $db_options[1]))
						{
							$db_input = explode(';', $db_options[0]);
							$db_output = explode(';', $db_options[1]);

							if (isset($db_input[0], $db_input[1], $db_output[0], $db_output[1]))
							{
								$db_select = array();
								$db_select_params = '';
								$db_selected = $db_input[0];
								$db_select['select2'] = $db_input[1];

								if (isset($db_select_options[0], $db_select_options[1], $db_select_options[2]))
								{
									unset($db_select_options[0]);
									$db_select_params = implode(';', $db_select_options);
								}

								if (stristr(trim($db_output[0]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[0];
									$db_select['select1'] = $db_output[1];
								}
								elseif (stristr(trim($db_output[1]), '{db_prefix}'))
								{
									$db_select['table'] = $db_output[1];
									$db_select['select1'] = $db_output[0];
								}
								else
									unset($db_select);
							}
						}
					}

					// Needed for db_list_indexes...
					db_extend('packages');

					$columns = array(
						$db_select['select1'] => 'string',
					);

					$values = $new_db_vals;

					$keys = array(
						$smcFunc['db_list_indexes']($db_select['table']),
					);

					$smcFunc['db_insert']('insert', $db_select['table'], $columns, $values, $keys);
				}

				// Did they request removal of one of these db_select values?
				if (isset($_POST['epDeletedDbSelects_' . $id]))
					foreach ($_POST['epDeletedDbSelects_' . $id] as $key)
					{
						$smcFunc['db_query']('', '
							DELETE FROM ' . $db_select['table'] . '
							WHERE {raw:query_select} = {string:key}',
							array(
								'key' => $key,
								'query_select' =>  $db_select['select1'],
							)
						);
					}

				// Now do the list_bbc type...
				if ($param_types[$id] == 'list_bbc')
					if (isset($_POST[$value . '_enabledTags']))
						$daValue = implode(';', $_POST[$value . '_enabledTags']);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}ep_module_parameters
					SET value = {string:value}
					WHERE ' . ($context['is_clone'] ? 'id_clone={int:id_module}' : 'id_module={int:id_module}') . ' AND id_param = {int:id_param}',
					array(
						'value' => isset($check_value) && $check_value != '' ? $check_value : $smcFunc['htmlspecialchars'](un_htmlspecialchars($daValue)),
						'id_module' => $context['ep_modid'],
						'id_param' => (int) $id,
					)
				);
			}
		}

		// All done, now that was F U N!
		$base = 'action=admin;area=epmodules;sa=modifymod;';
		$redirect = $base . (!$context['is_clone'] ? 'modid' : 'module') . '=' . $context['ep_modid'];

	// We need to empty the cache now, but make sure it is in the correct format, first.
	foreach ($_SESSION['layout_actions'] as $action)
		if (is_array(cache_get_data('envision_columns_' . md5(md5($action)), 3600)))
			cache_put_data('envision_columns_' . md5(md5($action)), 0, 3600);

		redirectexit($redirect);
	}
}

/**
 * Determines if a file is really a PHP file.
 *
 * @param string $file path to the file to check.
 * @return bool true if it was successfully checked as a PHP file; false otherwise.
 * @since 1.0
 */
function isPHPFile($file)
{
    if(!$content = file_get_contents($file))
        return false;

	$get_tokens = @token_get_all($content);

    foreach($get_tokens as $token)
        if(is_array($token) && in_array(current($token), array(T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO)))
            return true;

    return false;
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
 * @param array $checked integer list of all id_groups to be checked (have a mark in the checkbox). Default is an empty array.
 * @param array $unallowed integer list of all id_groups that are skipped. Default is an empty array.
 * @param array $order integer list specifying the order of id_groups to be displayed. Default is an empty array.
 * @param string $param_name the name of the paameter being used.
 * @param int $param_id the parameter's ID.
 * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
 * @since 1.0
 */
function ListGroups($checked = array(), $unallowed = array(), $order = array(), $param_id = 0)
{
	global $context, $smcFunc, $txt;

	// We'll need this for loading up the names of each group.
	if (!loadLanguage('ManageBoards'))
		loadLanguage('ManageBoards');

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
 * Clones a module.
 *
 * @since 1.0
 */
function CloneEnvisionMod()
{
	global $context, $sourcedir, $smcFunc, $txt, $restrictedNames;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	validateSession();

	$request = $smcFunc['db_query']('', '
		SELECT
			dlp.id_layout, dlp.id_layout_position
		FROM {db_prefix}ep_layout_positions AS dlp
			LEFT JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND dl.name = {string:layout_name} AND dl.id_layout = {int:id_layout})
		WHERE dlp.id_layout = dl.id_layout AND dlp.enabled = -1',
		array(
			'layout_name' => $_SESSION['selected_layout']['name'],
			'one' => 1,
			'zero' => 0,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
		)
	);

	list($id_layout, $id_layout_position) = $smcFunc['db_fetch_row']($request);

	// We need to know if they are modifying an original module or a clone.  Clones will be a simple id=id_clone
	$context['modid'] = isset($_REQUEST['modid']) && !isset($_REQUEST['module']) ? (int) $_REQUEST['modid'] : '';
	$cloneid = isset($_REQUEST['module']) && !isset($_REQUEST['modid']) ? (int) $_REQUEST['module'] : '';
	$not_clone = !empty($cloneid) && isset($_REQUEST['mod']);

	// They aren't modifying anything, error!
	if(empty($context['modid']) && empty($cloneid))
		fatal_lang_error('ep_module_not_installed', false);

	//Which type is it?
	$context['is_clone'] = !empty($context['modid']) ? false : true;
	$context['ep_modid'] = !empty($context['modid']) ? (int) $context['modid'] : (int) $cloneid;

	if ($not_clone)
		EnvisionClone($id_layout_position, 0, array($cloneid));
	else
	{
		if (!$context['is_clone'])
			CloneEnvisionModules($id_layout, $id_layout_position, array($context['ep_modid']));
		else
			EnvisionDeclone(array($cloneid), array(), 2);
	}

	redirectexit('action=admin;area=epmodules;sa=epmanmodules');
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
		SELECT dl.actions
		FROM {db_prefix}ep_groups AS dg
		LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group' . (isset($context['edit_layout']) ? ' AND dl.id_layout != {int:curr_layout}' : '') . ')
		WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
		$query_array
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

	$edit_layout_query = array('curr_layout' => $_SESSION['selected_layout']['id_layout']);
	$query_array = isset($context['edit_layout']) ? array_merge(array('one' => 1, 'zero' => 0), $edit_layout_query) : array('one' => 1, 'zero' => 0);

	// Need to add current action to the exceptions within this group, so we don't add them twice.
	$request = $smcFunc['db_query']('', '
		SELECT dl.actions
		FROM {db_prefix}ep_groups AS dg
		LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group' . (isset($context['edit_layout']) ? ' AND dl.id_layout != {int:curr_layout}' : '') . ')
		WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
		$query_array
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$exceptions = array_merge($exceptions, explode(',', $row['actions']));

	$smcFunc['db_free_result']($request);

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

	if (isset($_POST['layout_name']) && !empty($_POST['layout_name']))
		$layout_name = trim($_POST['layout_name']);
	else
	{
		$layout_name = '';
		$layout_errors[] = 'no_layout_name';
	}

	// We need to make sure that the layout name doesn't exist in any of the other layouts.
	if (!empty($layout_name))
	{
		$request = $smcFunc['db_query']('', '
			SELECT dl.id_layout
			FROM {db_prefix}ep_groups AS dg
			INNER JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND LOWER(dl.name) = {string:layout_name})
			WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
			array(
				'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
				'zero' => 0,
				'layout_name' => strtolower($layout_name),
			)
		);
		if ($smcFunc['db_num_rows']($request) !== 0)
		{
			$layout_errors[] = 'layout_exists';
			$layout_name = '';
		}

		$smcFunc['db_free_result']($request);
		// Now let's use html_entities on it before placing into the database.
		$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars($layout_name));
	}

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

	// Finally get the layout style they chose.
	// Should compare this to the advanced layout style selection POST, if advanced is selected, $layout_style = -1;
	$selected_layout = !empty($_POST['layout_style']) ? (int) $_POST['layout_style'] : 0;

	if (count($layout_errors) >= 1)
		return layoutPostError($layout_errors, 'add_layout', $layout_name, $layout_actions, $selected_layout);

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	$id_group = 1;

	$selected_layout = !empty($_POST['layout_style']) ? (int) $_POST['layout_style'] : 0;

	if (!empty($selected_layout))
		$insert_positions = epPredefined_Layouts($selected_layout);
	else
		fatal_lang_error('ep_layout_unknown', false);

	$ep_actions = implode(',', $layout_actions);
	$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name'])));

	// Add the module info to the database
	$columns = array(
		'name' => 'string-65',
		'actions' => 'string',
		'id_group' => 'int',
	);

	$keys = array(
		'id_layout',
		'id_group'
	);

	$data = array(
		$layout_name,
		$ep_actions,
		$id_group,
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layouts',  $columns, $data, $keys);

	// We need to tell the positions table which ID was inserted
	$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layouts', 'id_layout');

	// One more to go - insert the layout.
	$columns = array(
		'id_layout' => 'int',
		'column' => 'string',
		'row' => 'string',
		'enabled' => 'int',
	);

	$keys = array(
		'id_layout',
		'id_layout_position',
	);

	// Add the Disabled Modules section to the layout style.
	$insert_positions = array_merge($insert_positions, array(array('column' => '0:0', 'row' => '0:0', 'enabled' => -1)));

	foreach ($insert_positions as $insert_position)
	{
		$data = array(
			$iid,
			$insert_position['column'],
			$insert_position['row'],
			$insert_position['enabled'],
		);

		$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);

		// We need to get the id_layout_position of the SMF section.
		if (isset($insert_position['smf']))
			$smf_id = $smcFunc['db_insert_id']('{db_prefix}ep_layout_positions', 'id_layout_position');
	}

	$iid2 = $smcFunc['db_insert_id']('{db_prefix}ep_layout_positions', 'id_layout_position');

	$_SESSION['selected_layout'] = array(
		'id_layout' => (int) $iid,
		'name' => $layout_name,
	);

	$_SESSION['layouts'][$iid] = $layout_name;

	// Only needs 1 parameter passed to it since the rest is in the $_SESSION.
	// and don't bother with actual modules, we are cloning from an array instead!
	EnvisionClone($iid2, $smf_id);

	redirectexit('action=admin;area=epmodules;sa=epmanmodules');
}

/**
 * Clones one or more modules.
 *
 * @param int $id_layout the layout to point the clones to.
 * @param int @id_layout_position the section to point the clones to.
 * @param array $id_modules contains the IDs of the modules to clone. If it is blank, ALL module ids are assumed.
 * @since 1.0
 */
function CloneEnvisionModules($id_layout, $id_layout_position, $id_modules = array())
{
	global $context, $scripturl, $smcFunc, $txt, $options;

	 // Just a few precautionary measures.
	 if (!allowedTo('admin_forum'))
	  return;

	 // This is kinda important.
	 validateSession();

	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module, dm.name, dm.title, dm.title_link, dm.target, dm.icon, dm.files, dm.functions,
			dmp.id_param, dmp.name AS param_name, dmp.type AS param_type, dmp.value AS param_value, dl.id_layout, dmp2.position
		FROM {db_prefix}ep_modules AS dm
			INNER JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
			INNER JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND dl.name = {string:layout_name} AND dl.id_layout = {int:id_layout})
			LEFT JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_module = dm.id_module AND dmp.id_clone = {int:zero})
			LEFT JOIN {db_prefix}ep_module_positions AS dmp2 ON (dmp2.id_layout = dl.id_layout AND dmp2.id_layout_position = {int:id_layout_pos})' . (!empty($id_modules) ? '
		WHERE dm.id_module IN({array_int:id_modules})' : ''),
		array(
			'layout_name' => $_SESSION['selected_layout']['name'],
			'id_layout_pos' => $id_layout_position,
			'one' => 1,
			'zero' => 0,
			'id_layout' => $_SESSION['selected_layout']['id_layout'],
			'id_modules' => $id_modules,
		)
	);

	$params2clone = array();
	$disabled_count = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($mod2clone[$row['id_module']]))
			$mod2clone[$row['id_module']] = array(
				'name' => $row['name'],
				'title' => $row['title'],
				'title_link' => $row['title_link'],
				'target' => $row['target'],
				'icon' => $row['icon'],
				'files' => $row['files'],
				'functions' => $row['functions']
			);

		if (!isset($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]))
			if (!empty($row['id_param']))
				$mod2clone[$row['id_module']]['params2clone'][$row['id_param']] = array(
					'name' => $row['param_name'],
					'type' => $row['param_type'],
					'value' => $row['param_value']
			);

		if (!is_null($row['position']))
			$disabled_count++;


		// Wouldn't want params to be ordered differently from the other modules ( 1st Pass )!
		if (!empty($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]) && count($mod2clone[$row['id_module']]['params2clone'][$row['id_param']]) >= 1)
			ksort($mod2clone[$row['id_module']]['params2clone'][$row['id_param']], SORT_NUMERIC);
	}

	// We'll want to free this up and not waste memory.
	$smcFunc['db_free_result']($request);

	$i = 0;
	foreach ($mod2clone as $mod2clone_key => $mod2clone_value)
	{
		// Add the module info to the database
		$columns = array(
			'id_module' => 'int',
			'name' => 'string',
			'title' => 'string',
			'title_link' => 'string',
			'target' => 'int',
			'icon' => 'string',
			'files' => 'string',
			'functions' => 'string',
			'id_member' => 'int',
			'is_clone' => 'int',
		);

		$keys = array(
			'id_clone',
			'id_module',
			'id_member'
		);

		$data = array(
			$mod2clone_key,
			$mod2clone_value['name'],
			$mod2clone_value['title'],
			$mod2clone_value['title_link'],
			$mod2clone_value['target'],
			$mod2clone_value['icon'],
			$mod2clone_value['files'],
			$mod2clone_value['functions'],
			0,
			1,
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_clones',  $columns, $data, $keys);

		// We need to tell the parameters table which ID was inserted
		$iid = $smcFunc['db_insert_id']('{db_prefix}ep_module_clones', 'id_clone');

		// In case we have parameters...
		if (isset($mod2clone_value['params2clone']))
		{
			$columns = array(
				'id_clone' => 'int',
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

			// Ensure the parameters are ordered correctly ( 2nd pass )!
			ksort($mod2clone_value['params2clone']);

			foreach($mod2clone_value['params2clone'] as $key => $param)
			{
				// Insert the parameters, we'll need to insert the module id also.
				$data = array(
					$iid,
					$mod2clone_key,
					$param['name'],
					$param['type'],
					$param['value'],
				);
				$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_parameters', $columns, $data, $keys);
			}
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
			$id_layout_position,
			$id_layout,
			0,
			$iid,
			(empty($id_modules) ? $i : $disabled_count),
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_positions',  $columns, $data, $keys);

		$i++;
	}


	$diid = $smcFunc['db_insert_id']('{db_prefix}ep_module_positions', 'id_position');

	// That's all she wrote.
	if (isset($_GET['xml']))
		die('
				<div class="DragBox clonebox' . (!empty($options['ep_mod_color']) ? $options['ep_mod_color'] : '1') . ' draggable_module" id="envisionmod_' . $diid . '" style="text-align: center;">
						<p>' . $mod2clone[$context['ep_modid']]['title'] . '</p>
						<p class="inner"><a href="' . $scripturl . '?action=admin;area=epmodules;sa=modifymod;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['ep_admin_modules_manage_modify'] . '</a> | <a href="javascript:void(0)" js_link="' . $scripturl . '?action=admin;area=epmodules;sa=clonemod;xml;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . $txt['epmodule_declone'] . '</a></p>
				</div>');
	else
		return;
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

	if (!EnvisionDeleteLayout($id_layout))
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

	$request = $smcFunc['db_query']('', '
		SELECT dl.name, dl.actions, dlp.id_layout_position, dlp.row, dlp.column, dlp.enabled, dmp.id_module, dmp.id_clone, dmp.id_position
		FROM {db_prefix}ep_groups AS dg
			INNER JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND dl.id_layout = {int:id_layout})
			LEFT JOIN {db_prefix}ep_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout' . (!empty($context['layout_errors']) ? '' : ' AND dlp.enabled != {int:invisible_layout}') . ')
			LEFT JOIN {db_prefix}ep_module_positions AS dmp ON (dmp.id_layout = dl.id_layout AND dmp.id_layout_position = dlp.id_layout_position)
		WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
		array(
			'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
			'zero' => 0,
			'invisible_layout' => -2,
			'id_layout' => $selected_layout,
		)
	);

	$context['total_columns'] = 0;
	$context['total_rows'] = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['enabled'] != -1)
		{
			if (isset($_POST['remove_positions']))
			{
				$was_deleted = strstr($_POST['remove_positions'], $row['id_layout_position']);
				if ($was_deleted !== false)
					continue;
			}

			$context['layout_name'] = un_htmlspecialchars($row['name']);
			$context['current_actions'] = explode(',', $row['actions']);
			$cols = explode(':', $row['column']);
			$rows = explode(':', $row['row']);
			$cols[0] = !empty($cols[0]) ? $cols[0] : 0;
			$rows[0] = !empty($rows[0]) ? $rows[0] : 0;

			$smf = (int) $row['id_clone'] + (int) $row['id_module'];
			$smf_col = empty($smf) && !is_null($row['id_position']);

			if (!isset($context['current_sections'][$rows[0]][$cols[0]]))
				$context['total_columns']++;

			if (!isset($context['current_sections'][$rows[0]]))
				$context['total_rows']++;

			$context['current_sections'][$rows[0]][$cols[0]] = array(
				'is_smf' => $smf_col,
				// 'has_modules' => !empty($smf),
				'id_layout_position' => $row['id_layout_position'],
				'id_position' => $row['id_position'],
				'colspans' => !empty($cols[1]) ? $cols[1] : 0,
				'rowspans' => !empty($rows[1]) ? $rows[1] : 0,
				'enabled' => !empty($row['enabled']),
			);
		}
		else
			if (!isset($context['disabled_section']))
				$context['disabled_section'] = $row['id_layout_position'];
	}

	$smcFunc['db_free_result']($request);

	ksort($context['current_sections']);
	foreach ($context['current_sections'] as $key => $value)
		ksort($context['current_sections'][$key]);

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

	// 	die(var_dump($_POST));
	$layout_errors = array();
	$layout_name = '';
	$layout_actions = array();
	$selected_layout = isset($_POST['layout_picker']) && !empty($_POST['layout_picker']) ? (int) $_POST['layout_picker'] : fatal_lang_error('cant_find_layout_id', false);

	if ($_SESSION['show_smf'])
	{
		if (isset($_POST['layout_name']) && !empty($_POST['layout_name']) )
			$layout_name = trim($_POST['layout_name']);
		else
		{
			$layout_name = '';
			$layout_errors[] = 'no_layout_name';
		}

		// We need to make sure that the layout name doesn't exist in any of the other layouts.
		if (!empty($layout_name))
		{
			$request = $smcFunc['db_query']('', '
				SELECT dl.id_layout
				FROM {db_prefix}ep_groups AS dg
				INNER JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND LOWER(dl.name) = {string:layout_name} AND dl.id_layout != {int:id_layout})
				WHERE dg.id_member = {int:zero} AND dg.id_group = {int:one}',
				array(
					'one' => 1, // This needs to change to the current group that the user is working on. USE a $_SESSION variable 4 this!
					'zero' => 0,
					'layout_name' => strtolower($layout_name),
					'id_layout' => $selected_layout,
				)
			);
			if ($smcFunc['db_num_rows']($request) !== 0)
			{
				$layout_errors[] = 'layout_exists';
				$layout_name = '';
			}

			$smcFunc['db_free_result']($request);

			// Now let's use html_entities on it before placing into the database.
			$layout_name = $smcFunc['htmlspecialchars'](un_htmlspecialchars($layout_name));
		}

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

	$update_query = '';
	$update_params = array();
	$id_layout_positions = array();
	$regulatory_check = array();
	$val = 0;
	$context['row_pos_error_ids'] = array();
	$context['col_pos_error_ids'] = array();
	$context['colspans_error_ids'] = array();

	$update_query .= '
			dlp.column = CASE dlp.id_layout_position';

	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);
		if (!is_numeric($_POST['colspans'][$data[2]]))
		{
			$context['colspans_error_ids'][] = $data[2];
			$layout_errors[104] = 'colspans_invalid';
		}

		if (!isset($regulatory_check[$data[0]]))
			$val = 0;

		$val = $val + ($_POST['colspans'][$data[2]] == 0 ? 1 : $_POST['colspans'][$data[2]]);

		$regulatory_check[$data[0]] = $val;


		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:column' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'column' . $data[2] => $data[1] . ':' . $_POST['colspans'][$data[2]],
		));

		$id_layout_positions[] = $data[2];
	}

	$update_query .= '
				END,
			dlp.row = CASE dlp.id_layout_position';

	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);

		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:row' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'row' . $data[2] => $data[0] . ':0',
		));

		$id_layout_positions[] = $data[2];
	}

	$update_query .= '
				END,
			dlp.enabled = CASE dlp.id_layout_position';
	foreach ($_POST['cId'] as $value)
	{
		$data = explode('_', $value);
		if (!empty($_POST['enabled'][$data[2]]))
			$value = 1;
		else
			$value = 0;

		$update_query .= '
				WHEN {int:id_layout_position' . $data[2] . '} THEN {string:enabled' . $data[2] . '}';

		$update_params = array_merge($update_params, array(
			'id_layout_position' . $data[2] => $data[2],
			'enabled' . $data[2] => $value,
		));
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

	$id_group = 1;

	$ep_actions = ($_SESSION['show_smf'] ? implode(',', $layout_actions) : '');
	$layout_name = ($_SESSION['show_smf'] ? $smcFunc['htmlspecialchars'](un_htmlspecialchars(trim($_POST['layout_name']))) : '');

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ep_layouts AS dl, {db_prefix}ep_layout_positions AS dlp
		SET ' . ($_SESSION['show_smf'] ? 'dl.name = {string:layout_name},
			dl.actions = {string:layout_actions},' : '') . $update_query . '
				END
		WHERE dl.id_layout = {int:id_layout} AND dlp.id_layout_position IN({array_int:id_layout_positions})',
		array_merge($update_params, array(
			'layout_name' => $layout_name,
			'layout_actions' => $ep_actions,
			'id_layout' => $selected_layout,
			'id_layout_positions' => $id_layout_positions,
		))
	);

	if ($_SESSION['show_smf'] && $_POST['old_smf_pos'] != $_POST['smf_radio'])
	{
		/*
		The Admin has chosen to move SMF - this is done in a three step manner.
			First we get rid of the old position, then any modules standing in the
			way must move; and finally, we insert the new position into the database.
		*/

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_positions
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $_POST['old_smf_pos'],
			)
		);

		// Make way for the mighty SMF, O ye little modules!
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ep_module_positions AS dmp, {db_prefix}ep_layout_positions AS dlp
			SET dmp.id_layout_position = dlp.id_layout_position, dmp.position = dmp.id_position
			WHERE dmp.id_layout = {int:selected_layout}
				AND dlp.id_layout = {int:selected_layout}
				AND dlp.enabled = -1
				AND dmp.id_layout_position = {int:id_layout_position}',
			array(
				'selected_layout' => $selected_layout,
				'id_layout_position' => $_POST['smf_radio'],
			)
		);

		$columns = array(
			'id_layout_position' => 'int',
			'id_layout' => 'int',
			'id_module' => 'int',
			'id_clone' => 'int',
			'position' => 'int',
		);

		$values = array(
			$_POST['smf_radio'],
			$selected_layout,
			0,
			0,
			0,
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('insert', '{db_prefix}ep_module_positions', $columns, $values, $keys);
	}

	if (!empty($_POST['remove_positions']))
	{
		// The Admin has chosen to remove some columns.
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

		// Any modules that were in these deleted sections must be moved to the disabled section.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ep_module_positions AS dmp, {db_prefix}ep_layout_positions AS dlp
			SET dmp.id_layout_position = dlp.id_layout_position, dmp.position = dmp.id_position
			WHERE dmp.id_layout = {int:selected_layout}
				AND dlp.id_layout = {int:selected_layout}
				AND dlp.enabled = -1
				AND dmp.id_layout_position IN({array_int:remove_ids})',
			array(
				'selected_layout' => $selected_layout,
				'remove_ids' => $killdata,
			)
		);
	}

	// Cleanup...
	unset($_SESSION['show_smf']);
	unset($regulatory_check);
	unset($val);

	// We need to empty the cache now, but make sure it is in the correct format, first.
	foreach ($_POST['layout_actions'] as $action)
		if (is_array(cache_get_data('envision_columns_' . md5(md5($action)), 3600)))
			cache_put_data('envision_columns_' . md5(md5($action)), 0, 3600);

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
function epPredefined_Layouts($style)
{
	// Here's Envision's default layout:
	switch ((int) $style)
	{
		case 2:
			// OMEGA
			return array(
				// row 0
				array(
					'column' => '0:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '1:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '2:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '0:0',
					'enabled' => 1,
				),
				// row 1
				array(
					'column' => '0:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				array(
					'smf' => true,
					'column' => '1:2',
					'row' => '1:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// row 2
				array(
					'column' => '0:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '1:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '2:0',
					'row' => '2:0',
					'enabled' => 1,
				),
				array(
					'column' => '3:0',
					'row' => '2:0',
					'enabled' => 1,
				)
			);
			break;
		// Default - Envision Portal
		default:
			return array(
				// top
				array(
					'column' => '0:3',
					'row' => '0:0',
					'enabled' => 1,
				),
				// left
				array(
					'column' => '0:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// middle
				array(
					'smf' => true,
					'column' => '1:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// right
				array(
					'column' => '2:0',
					'row' => '1:0',
					'enabled' => 1,
				),
				// bottom
				array(
					'column' => '0:3',
					'row' => '2:0',
					'enabled' => 1,
				)
			);
			break;
	}
}

// Takes the clones id_clone value to clone it from.
// For when we need to clone an actual clone or create a layout!
function EnvisionClone($id_layout_position, $smf_id = 0, $id_clones = array())
{
	global $context, $user_info, $scripturl, $smcFunc, $txt, $options;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	checkSession('get');

	$disabled_count = 0;

	if (count($id_clones) >= 1)
	{
		// We are just cloning 1 Clone.
		// I placed this into a separate query since we may want to account for modules that are allowed, but only for Non-Admins.
		$query = 'SELECT dmc.id_clone, dmc.is_clone, dmc.id_module, dmc.name, dmc.title, dmc.title_link, dmc.target, dmc.icon, dmc.files, dmc.functions,
		dmp.id_param, dmp.name AS param_name, dmp.type AS param_type, dmp.value AS param_value, dl.id_layout, dmp2.position
	FROM {db_prefix}ep_module_clones AS dmc
		INNER JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
		INNER JOIN {db_prefix}ep_layouts AS dl ON (dl.id_group = dg.id_group AND dl.name = {string:layout_name} AND dl.id_layout = {int:id_layout})
		LEFT JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_clone = dmc.id_clone)
		LEFT JOIN {db_prefix}ep_module_positions AS dmp2 ON (dmp2.id_layout = dl.id_layout AND dmp2.id_layout_position = {int:id_layout_pos})
		WHERE dmc.id_clone IN ({array_int:id_clones}) AND dmc.id_member = {int:id_member} AND dmc.is_clone = {int:zero}';

		// Begin the Query.
		$request = $smcFunc['db_query']('', $query,
			array(
				'layout_name' => $_SESSION['selected_layout']['name'],
				'one' => 1,
				'zero' => 0,
				'id_layout_pos' => $id_layout_position,
				'id_member' => 0,
				'id_layout' => $_SESSION['selected_layout']['id_layout'],
				'id_clones' => $id_clones,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($clone[$row['id_clone']]))
				$clone[$row['id_clone']] = array(
					'id_module' => $row['id_module'],
					'name' => $row['name'],
					'title' => $row['title'],
					'title_link' => $row['title_link'],
					'target' => $row['target'],
					'icon' => $row['icon'],
					'files' => $row['files'],
					'functions' => $row['functions']
				);

			if (!isset($clone[$row['id_clone']]['params'][$row['id_param']]))
				if (!empty($row['id_param']))
					$clone[$row['id_clone']]['params'][$row['id_param']] = array(
						'name' => $row['param_name'],
						'type' => $row['param_type'],
						'value' => $row['param_value']
				);

			if (!is_null($row['position']))
				$disabled_count++;

			// make sure the params are ordered correctly for when we insert them.
			// wouldn't want params to be ordered differently from the others.
			if (!empty($clone[$row['id_clone']]['params'][$row['id_param']]) && count($clone[$row['id_clone']]['params'][$row['id_param']]) >= 1)
				ksort($clone[$row['id_clone']]['params'][$row['id_param']], SORT_NUMERIC);
		}
		// Uses less memory when we free the result here, since we could be grabbing an array of id_clones to clone.
		$smcFunc['db_free_result']($request);
	}
	else
	{
		global $sourcedir;

		require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');

		$modules = loadDefaultModuleConfigs(array(), true);

		// We'll want to select all modules if it's the Admin, else only the allowed modules.
		// We are grabbing here from MODULE IDS, not clone ids!
		$request = $smcFunc['db_query']('', '
			SELECT
				dm.id_module, dm.name
				FROM {db_prefix}ep_modules AS dm
				LEFT JOIN {db_prefix}ep_groups AS dg ON (id_member = {int:id_member} AND dg.id_group = {int:curr_group})
				LEFT JOIN {db_prefix}ep_layouts AS dl ON (dl.id_layout = {int:id_layout} AND dl.id_group = dg.id_group AND dl.name={string:layout_name})
				ORDER BY NULL',
			array(
				'id_member' => 0,
				'curr_group' => 1,
				'layout_name' => $_SESSION['selected_layout']['name'],
				'id_layout' => $_SESSION['selected_layout']['id_layout'],
				'one' => 1,
			)
		);

		$clone = array();
		$clone_name = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($clone[$row['id_module']]))
			{
				$clone[$row['id_module']] = $modules[$row['name']];
				$clone_name[$row['id_module']] = $row['name'];
			}
		}

		$smcFunc['db_free_result']($request);
	}

	$i = 0;
	foreach ($clone as $clone_key => $clone_value)
	{
		// Add the module info to the database
		$columns = array(
			'name' => 'string',
			'title' => 'string',
			'title_link' => 'string',
			'target' => 'int',
			'icon' => 'string',
			'files' => 'string',
			'functions' => 'string',
			'id_member' => 'int',
			'id_module' => 'int',
			'is_clone' => 'int',
		);

		$keys = array(
			'id_clone',
			'id_module',
			'id_member'
		);

		$data = array(
			empty($id_clones) ? $clone_name[$clone_key] : $clone_value['name'],
			$clone_value['title'],
			$clone_value['title_link'],
			$clone_value['target'],
			$clone_value['icon'],
			$clone_value['files'],
			$clone_value['functions'],
			0,
			empty($id_clones) ? $clone_key : $clone_value['id_module'],
			empty($id_clones) ? 0 : 1,
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_clones',  $columns, $data, $keys);

		// We need to tell the parameters table which ID was inserted
		$iid = $smcFunc['db_insert_id']('{db_prefix}ep_module_clones', 'id_clone');

		// In case we have parameters...
		if (isset($clone_value['params']))
		{
			$columns = array(
				'id_clone' => 'int',
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

			// Also clone the parameters, if any.
			foreach($clone_value['params'] as $key => $param)
			{
				// Insert the parameters, we'll need to insert the module id also.
				$data = array(
					$iid,
					empty($id_clones) ? $clone_key : $clone_value['id_module'],
					empty($id_clones) ? $key : $param['name'],
					$param['type'],
					$param['value'],
				);
				$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_parameters', $columns, $data, $keys);
			}
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
			$id_layout_position,
			(int) $_SESSION['selected_layout']['id_layout'],
			0,
			$iid,
			empty($id_clones) ? $i : $disabled_count,
		);

		$keys = array(
			'id_position',
			'id_layout_position',
			'id_layout',
			'id_module',
			'id_clone',
		);

		$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_positions',  $columns, $data, $keys);

		$i++;
	}

	$diid = $smcFunc['db_insert_id']('{db_prefix}ep_module_positions', 'id_position');

	// Lastly, throw in the SMF section.
	$smcFunc['db_insert']('ignore', '{db_prefix}ep_module_positions',
		array('id_layout_position' => 'int', 'id_layout' => 'int', 'id_module' => 'int', 'id_clone' => 'int', 'position' => 'int'), 			array((int) $smf_id, (int) $_SESSION['selected_layout']['id_layout'], 0, 0, 0),
		$keys
	);

	// That's all she wrote.
	if (isset($_GET['xml']))
		die('
				<div class="DragBox clonebox' . (!empty($options['ep_mod_color']) ? $options['ep_mod_color'] : '1') . ' draggable_module centertext" id="envisionmod_' . $diid . '">
						<p>' . $clone[$context['ep_modid']]['title'] . '</p>
						<p class="inner"><a href="' . $scripturl . '?action=admin;area=epmodules;sa=modifymod;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['ep_admin_modules_manage_modify'] . '</a> | <a href="javascript:void(0)" js_link="' . $scripturl . '?action=admin;area=epmodules;sa=clonemod' . (empty($id_clones) ? ';mod' : '') . ';xml;module=' . $iid . ';' . $context['session_var'] . '=' . $context['session_id'] . '" class="clonelink">' . $txt['epmodule_declone'] . '</a></p>
				</div>');
	else
		return;
}

/**
 * This function will remove clones and all their properties from the id_clone value OR the id_module value.
 *
 * @param int $admin values are:
 * - 0 = Remove ALL Clones, including the Admins for that module!
 * - 2 = Remove ONLY the Admins Clones for that module!
 * Default set to remove all clones, cept for the Admins clones
 * @since 1.0
 */

//!!! Removes Clones based on clone value(s), or module value(s).
function EnvisionDeclone($clones = array(), $modules = array(), $admin = 1)
{
	 global $context, $smcFunc, $user_info;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	checkSession('get');

	// Must be an Admin, heh ;)
	if ($admin != 1)
	{
		if (!allowedTo('admin_forum'))
		 	return;
	}

	 // Nothing to remove!
	 if (count($modules) + count($clones) <= 0)
	  return;

	// May need more work later, if we allow people to remove all clones only from a layout.
	// Even though, technically they are all clones anyways.
	$layout_delete = count($clones) > 1;

	 $ids = array();
	 $ids = count($clones) > 0 ? $clones : $modules;

	 $where = (count($clones) > 0 ? 'dmc.id_clone' : 'dmc.id_module') . ' IN ({array_int:ids})' . (!empty($admin) ? ' AND ' . ($admin == 1 ? 'dmc.id_member != 0' : 'dmc.id_member = 0') : '');

	 // Does it exist, eg. is it installed?
	 $request = $smcFunc['db_query']('', '
	  SELECT
		dmc.id_clone, dmc.name, dmp2.id_param, dmp.id_position' . (!$layout_delete ? ', dmp.id_layout, dmp.id_layout_position, dmp.position' : '') . '
	  FROM {db_prefix}ep_module_clones AS dmc
		LEFT JOIN {db_prefix}ep_module_parameters AS dmp2 ON (dmc.id_clone = dmp2.id_clone AND dmp2.type={string:file_input})
		LEFT JOIN {db_prefix}ep_module_positions AS dmp ON (dmc.id_clone = dmp.id_clone)
	  WHERE ' . $where,
	  array(
		'ids' => $ids,
		'file_input' => 'file_input',
	  )
	 );

	 // No clones exist, so return outta here.
	 if ($smcFunc['db_num_rows']($request) == 0)
	  return;

	 $clone_info = array();

	 while ($row = $smcFunc['db_fetch_assoc']($request))
	 {
		if (!isset($clone_info['cloneids'][$row['id_clone']]))
		{
			$clone_info['cloneids'][$row['id_clone']] = $row['id_clone'];
			$clone_info['params'] = array();

			if(!isset($clone_info['name'][$row['id_clone']]))
			$clone_info['name'][$row['id_clone']] = $row['name'];

			if(!isset($clone_info['id_position'][$row['id_clone']]))
			$clone_info['id_position'][$row['id_clone']] = $row['id_position'];

			if (!$layout_delete)
				$clone_info['position'][$row['id_layout']]['pos' . $row['id_position'] . $row['position'] . '_' . $row['id_layout_position']] = $row['position'];
		}

		// Getting all file_input param ids.
		if (!empty($row['id_param']))
		{
			$clone_info['params'][] = $row['id_param'];
			if (!isset($clone_info['folderName'][$row['id_param']]))
				$clone_info['folderName'][$row['id_param']] = $row['name'];
		}
	 }

	 $smcFunc['db_free_result']($request);

	 // Removing all clone positions from the layout!
	 $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_module_positions
		WHERE id_clone IN ({array_int:id_clones}) AND id_module={int:zero}',
		array(
			'id_clones' => $clone_info['cloneids'],
			'zero' => 0,
		)
	 );

	if (!$layout_delete)
		foreach($clone_info['position'] as $id_layout => $id_layout_pos)
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

	 // Are there any files to remove?
	 if (isset($clone_info['params'][0]))
	 {
	 	global $sourcedir;

		require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');

		$request = $smcFunc['db_query']('', '
			SELECT
			id_file, filename, id_param, file_hash
			FROM {db_prefix}ep_module_files
			WHERE id_param IN ({array_int:params})',
			array(
				'params' => $clone_info['params'],
			)
		);
		$filename = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$filename[$row['id_file']] = getFilename($row['filename'], $row['id_file'], $context['epmod_files_dir'] . $clone_info['folderName'][$row['id_param']], false, $row['file_hash']);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_files
			WHERE id_param IN ({array_int:id_params})',
			array(
				'id_params' => $clone_info['params'],
			)
		);

		// Delete the files associated with the clone(s).
		foreach($filename as $file)
			@unlink($file);
	 }

	 // Removing parameters
	 $smcFunc['db_query']('', '
		  DELETE FROM {db_prefix}ep_module_parameters
		  WHERE id_clone IN ({array_int:id_clones})',
		  array(
			'id_clones' => $clone_info['cloneids'],
		  )
	 );

	 // Last but not least!
	 $smcFunc['db_query']('', '
		  DELETE FROM {db_prefix}ep_module_clones
		  WHERE id_clone IN ({array_int:id_clones})',
		  array(
			'id_clones' => $clone_info['cloneids'],
		  )
	 );

	 // That's all she wrote.
	 if (isset($_GET['xml']))
	 	die('deleted' . $clone_info['id_position'][$clones[0]]);
	 else
		return;
}

/**
 * Removes all traces of a layout.
 *
 * @param int $id_layout the layout to delete
 * @return bool true on success; false  otherwise.
 * @since 1.0
 */
function EnvisionDeleteLayout($id_layout)
{
	global $smcFunc, $user_info;

	// Just some extra security here!
	if (!allowedTo('admin_forum'))
		return;

	checkSession('get');

	$member_opt = 2;

	$delete_modules = array();
	$delete_clones = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_clone FROM {db_prefix}ep_module_positions
		WHERE id_layout = {int:id_layout}',
		array(
			'id_layout' => $id_layout,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		return false;

	while ($row = $smcFunc['db_fetch_assoc']($request))
		if (!empty($row['id_clone']))
			$delete_clones[] = $row['id_clone'];

	EnvisionDeclone($delete_clones, array(), $member_opt);

	foreach (array('layouts', 'layout_positions', 'module_positions') as $table_name)
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

?>