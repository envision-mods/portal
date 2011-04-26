<?php
/**************************************************************************************
* ManageEnvisionSettings.php                                                          *
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
 * This file handles Envision Portal's general settings.
 *
 * @package source
 * @copyright 2009-2010 Envision Portal
 * @license http://envisionportal.net/index.php?action=about;sa=legal Envision Portal License (Based on BSD)
 * @link http://envisionportal.net Support, news, and updates
 * @see ManageEnvisionSettings.template.php
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
 * @todo condense this function with {@link Configuration()}; why this avoids repetition I have no ides =/
 */
function loadGeneralSettingParameters($subActions = array(), $defaultAction = '')
{
	global $context, $txt, $sourcedir;

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	loadLanguage('ep_languages/EnvisionHelp+ManageSettings+ep_languages/ManageEnvisionSettings');
	loadTemplate('ep_template/ManageEnvisionSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	$context['sub_template'] = 'show_settings';

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(array_keys($subActions)));
	$context['sub_action'] = $_REQUEST['sa'];
}

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function Configuration()
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$subActions = array(
		'epinfo' => 'EnvisionPortalInfo',
		'epgeneral' => 'ModifyEnvisionGeneral',
		'epmodulesettings' => 'ModifyEnvisionModuleSettings',
		'logs' => 'EnvisionLogs',
	);

	loadGeneralSettingParameters($subActions, 'epinfo');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['ep_admin_config'],
		'help' => $txt['ep_admin_config_help'],
		'description' => $txt['ep_admin_config_desc'],
		'tabs' => array(
			'epinfo' => array(
				'description' => $txt['ep_admin_config_info_desc'],
			),
			'epgeneral' => array(
				'description' => $txt['ep_admin_config_general_desc'],
			),
			'epmodulesettings' => array(
				'description' => $txt['ep_admin_config_modulesettings_desc'],
			),
		),
	);

	$context['page_title'] = $txt['ep_admin_config_title'];

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

/**
 * Handles the Envision Portal information tab.
 *
 * @since 1.0
 */
function EnvisionPortalInfo()
{
	global $context, $txt, $scripturl, $sourcedir, $modSettings, $settings, $portal_ver;

	// Needed to get forum admins. (temporary placeholder)
	require_once($sourcedir . '/Subs-Membergroups.php');

	if (listMembergroupMembers_Href($context['administrators'], 1, 32) && allowedTo('manage_membergroups'))
	{
		// Add a 'more'-link if there are more than 32.
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=moderate;area=viewgroups;sa=members;group=1">' . $txt['more'] . '</a>';
	}

	// Some much needed scripting ;)
	$context['html_headers'] .=  '
	<script type="text/javascript"><!-- // --><![CDATA[

		function setEnvisionNews()
		{
			if (!epNews || epNews.length <= 0)
				return;

			var str = "<dl>";

			for (var i = 0; i < epNews.length; i++)
			{
				str += "\n	<dt><a href=\"" + epNews[i].url + "\" target=\"_blank\">" + epNews[i].subject + "</a> ' . $txt['on'] . ' " + epNews[i].time + "</dt>";
				str += "\n	<dd>"
				str += "\n		" + epNews[i].message;
				str += "\n	</dd>";
			}

			setInnerHTML(document.getElementById("epAnnouncements"), str + "</dl>");
		}

		function setEnvisionVersion()
		{
			var installed_version = "' . $portal_ver . '";

			if (typeof(window.epCurrentVersion) === "undefined" || !window.epCurrentVersion)
				return;

			if (installed_version != window.epCurrentVersion)
			{
				setInnerHTML(document.getElementById("ep_installed_version"), \'<span class="alert">' . $portal_ver . '</span>\');
				setInnerHTML(document.getElementById("ep_update_section"), ' . JavaScriptEscape('
					<span class="upperframe"><span><!-- // --></span></span>
						<div class="roundframe smalltext">
						<span class="error">' . $txt['ep_outdated'] . '</span>
						</div>
					<span class="lowerframe"><span><!-- // --></span></span>
				') . ');
			}

			setInnerHTML(document.getElementById("ep_latest_version"), window.epCurrentVersion);
		}
	// ]]></script>';

	// Our credits info. =D
	$context['credits'] = array(
		array(
			'pretext' => $txt['ep_credits_info'],
			'groups' => array(
				array(
					'title' => $txt['ep_credits_groups_dev'],
					'members' => array(
						'<a href="http://envisionportal.net/index.php?action=profile;u=9" target="_blank">John &quot;live627&quot; Rayes</a>',
						'<a href="http://envisionportal.net/index.php?action=profile;u=1" target="_blank">Aldo &quot;hadesflames&quot; Barreras</a>',
						'<a href="http://envisionportal.net/index.php?action=profile;u=13" target="_blank">Marcus &quot;cookiemonster&quot; Forsberg</a>',
						'<a href="http://envisionportal.net/index.php?action=profile;u=5" target="_blank">Jay &quot;JBlaze&quot; Clemmons</a>',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_custom'],
					'members' => array(
						'<a href="http://envisionportal.net/index.php?action=profile;u=3" target="_blank">Gary M. Gadsdon</a>',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_mar'],
					'members' => array(
						'<a href="http://envisionportal.net/index.php?action=profile;u=2" target="_blank">Bryan &quot;Runic&quot; Deakin</a>',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_docs'],
					'members' => array(
						'<a href="http://envisionportal.net/index.php?action=profile;u=4" target="_blank">Bigguy</a>',
						'<a href="http://envisionportal.net/index.php?action=profile;u=6" target="_blank">Drunken Clam</a>',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_global'],
					'members' => array(
						'<a href="http://envisionportal.net/index.php?action=profile;u=11" target="_blank">Micha &quot;chilly&quot; Heiderich</a>',
					),
				),
				array(
					'title' => $txt['ep_credits_special'],
					'members' => array(
						$txt['ep_credits_all_friends'],
					),
				),
				array(
					'title' => $txt['ep_credits_fugue'],
					'members' => array(
						$txt['ep_credits_fugue_message'],
					),
				),
			),
			'posttext' => $txt['ep_credits_anyone'],
		),
	);

	$context['sub_template'] = 'portal_info';
}

/**
 * Loads the general settings for Envision Portal so the admin can change them. uUses the sub template show_settings in Admin.template.php to display them.
 *
 * @param bool $return_config Determines whether or not to return the config array.
 * @return void|array The $config_vars if $return_config is true.
 * @since 1.0
 */
function ModifyEnvisionGeneral($return_config = false)
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$config_vars = array(
		array('check', 'ep_collapse_modules', 'help' => 'ep_collapse_modules_help'),
		array('check', 'ep_color_members', 'help' => 'ep_color_members_help'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=epconfig;sa=epgeneral');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=epconfig;save;sa=epgeneral';
	$context['settings_title'] = $txt['ep_admin_config_general'];

	prepareDBSettingContext($config_vars);

}

/**
 * Loads the master module settings for Envision Portal so the admin can change them. uUses the sub template show_settings in Admin.template.php to display them.
 *
 * @param bool $return_config Determines whether or not to return the config array.
 * @return void|array The $config_vars if $return_config is true.
 * @since 1.0
 */
function ModifyEnvisionModuleSettings($return_config = false)
{
	global $txt, $boarddir, $scripturl, $context, $modSettings, $smcFunc, $settings, $sc;

	$config_vars = array(
			array('select', 'ep_module_display_style', array(&$txt['ep_module_display_style_blocks'], &$txt['ep_module_display_style_modular']), 'help' => 'ep_module_display_style_help'),
		'',
			array('check', 'ep_module_enable_animations', 'help' => 'ep_module_enable_animationshelp'),
			array('select', 'ep_module_animation_speed', array(&$txt['ep_animation_speed_veryslow'], &$txt['ep_animation_speed_slow'], &$txt['ep_animation_speed_normal'], &$txt['ep_animation_speed_fast'], &$txt['ep_animation_speed_veryfast']), 'help' => 'ep_module_animation_speed_help'),
			array('check', 'ep_disable_custommod_icons', 'help' => 'ep_disable_custommod_icons_help'),
			array('check', 'ep_enable_custommod_icons', 'help' => 'ep_enable_custommod_icons_help'),
			array('text', 'ep_icon_directory', 'size' => 40, 'help' => 'ep_icon_directory_help'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();

		// No slashes to the left.
		if ($smcFunc['substr']($_POST['ep_icon_directory'], 0, 1) == '/')
			$_POST['ep_icon_directory'] = $smcFunc['substr']($_POST['ep_icon_directory'], 1, $smcFunc['strlen']($_POST['ep_icon_directory']) - 1);

		// No slashes to the right.
		if ($smcFunc['substr']($_POST['ep_icon_directory'], -1, 1) == '/')
			$_POST['ep_icon_directory'] = $smcFunc['substr']($_POST['ep_icon_directory'], 0, $smcFunc['strlen']($_POST['ep_icon_directory']) - 2);

		// If not a valid directory, load up the previous directory they had defined!
		if (!is_dir($boarddir . '/' . $_POST['ep_icon_directory']))
			$_POST['ep_icon_directory'] = $modSettings['ep_icon_directory'];


		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=epconfig;sa=epmodulesettings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=epconfig;save;sa=epmodulesettings';
	$context['settings_title'] = $txt['ep_admin_config_modulesettings'];

	prepareDBSettingContext($config_vars);
}

function EnvisionLogs()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	loadLanguage('Modlog');

	// The number of entries to show per page of log file.
	$context['items_per_page'] = 30;

	// Amount of hours that must pass before allowed to delete file.
	$context['hoursdisable'] = 24;

	// Handle deletion...
	if (isset($_POST['removeall']) && $context['can_delete'])
	{
		checkSession();

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_log_actions
			WHERE time < {int:twenty_four_hours_wait}',
			array(
				'twenty_four_hours_wait' => time() - $context['hoursdisable'] * 3600,
			)
		);
	}
	elseif (!empty($_POST['remove']) && isset($_POST['delete']) && $context['can_delete'])
	{
		checkSession();
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_log_actions
			WHERE id_action IN ({array_string:delete_actions})
				AND time < {int:twenty_four_hours_wait}',
			array(
				'twenty_four_hours_wait' => time() - $context['hoursdisable'] * 3600,
				'delete_actions' => array_unique($_POST['delete']),
			)
		);
	}

	// Our options for our list.
	$listOptions = array(
		'id' => 'ep_list_logs',
		'items_per_page' => $context['items_per_page'],
		'base_href' => $scripturl . '?action=admin;area=epconfig;sa=logs',
		'default_sort_col' => 'time',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'file' => $sourcedir . '/ep_source/Subs-EnvisionPortal.php',
			'function' => 'list_getLogs',
		),
		'get_count' => array(
			'file' => $sourcedir . '/ep_source/Subs-EnvisionPortal.php',
			'function' => 'list_getNumLogs',
		),
		'no_items_label' => $txt['ep_no_logs'],
		'columns' => array(
			'action' => array(
				'header' => array(
					'value' => $txt['modlog_action'],
					'class' => 'lefttext first_th',
				),
				'data' => array(
					'db' => 'action_text',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'ela.action',
					'reverse' => 'ela.action DESC',
				),
			),
			'time' => array(
				'header' => array(
					'value' => $txt['modlog_date'],
					'class' => 'lefttext',
				),
				'data' => array(
					'db' => 'time',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'ela.time DESC',
					'reverse' => 'ela.time',
				),
			),
			'moderator' => array(
				'header' => array(
					'value' => $txt['modlog_member'],
					'class' => 'lefttext',
				),
				'data' => array(
					'db' => 'moderator_link',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'mem.real_name',
					'reverse' => 'mem.real_name DESC',
				),
			),
			'position' => array(
				'header' => array(
					'value' => $txt['modlog_position'],
					'class' => 'lefttext',
				),
				'data' => array(
					'db_htmlsafe' => 'position',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'mg.group_name',
					'reverse' => 'mg.group_name DESC',
				),
			),
			'delete' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$entry', '
						return \'<input type="checkbox" class="input_check" name="delete[]" value="\' . $entry[\'id\'] . \'"\' . ($entry[\'editable\'] ? \'\' : \' disabled="disabled"\') . \' />\';
					'),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=epconfig;sa=logs',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				$context['session_var'] => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
						<input type="submit" name="remove" value="' . $txt['modlog_remove'] . '" class="button_submit" />
						<input type="submit" name="removeall" value="' . $txt['modlog_removeall'] . '" class="button_submit" />',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'ep_list_logs';
}

?>
