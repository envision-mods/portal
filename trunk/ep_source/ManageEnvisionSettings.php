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

	loadLanguage('ep_languages/EnvisionHelp');
	loadLanguage('ep_languages/ManageSettings');
	loadLanguage('ep_languages/ManageEnvisionSettings');
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
	require_once($sourcedir . '/ep_source/Subs-Membergroups.php');

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
					'title' => $txt['ep_credits_groups_pm'],
					'members' => array(
						'<span onclick="alert(\'THE KING\');">Solomon &quot;SoLoGHoST&quot; Closson</span>',
						'Chris &quot;ccbtimewiz&quot; Batista',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_dev'],
					'members' => array(
						'<span onclick="alert(\'THE SHERIFF\');">John &quot;live627&quot; Rayes</span>',
						'Russell &quot;nend&quot; Najar',
						'Alexander &quot;Bugo&quot; Kordjukov',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_support'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['ep_credits_groups_custom'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['ep_credits_groups_qa'],
					'members' => array(
						'MC73',
						'Ruediger',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_pr'],
					'members' => array(
						'Matt Westlake-Toms',
						'Tumbleweed',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_docs'],
					'members' => array(
						'Xarcell',
					),
				),
				array(
					'title' => $txt['ep_credits_groups_lang'],
					'members' => array(
						'Micha &quot;chilly&quot; Heiderich',
					),
				),
				array(
					'title' => $txt['ep_credits_special'],
					'members' => array(
						'Robert &quot;xero&quot; Stamm',
						'<span onclick="alert(\'WALUIGI TIME\');">Tyler &quot;tyty1234&quot; Asuncion</span>',
						'Aldo &quot;hadesflames&quot; Barreras',
						'Steve &quot;Bluto&quot; Fox',
						'<span onclick="alert(\'Not known as Gaz or Gazman.\');">Gary M. Gadsdon</span>',
						'Jerry Osborne',
						'Marcus &quot;Nas&quot; Forsberg',
						'Hugo &quot;Costa&quot; Costa',
						'Relyana',
						'Dzonny',
						'Adriana &quot;adribetty394&quot; Medina',
						'Colin &quot;Shadow82x&quot; Blaber',
						'Bryan &quot;Runic&quot; Deakin',
						'xTmDarren',
						'Fox',
						'Deezel',
						'bigguy',
						'Eliana Tamerin',
						'JBlaze',
						'Jeff',
						'Marcel',
						'margarett',
						'metallica48423',
						'Nathaniel',
						'necrit',
						'Relyana',
						'Steven &quot;Fustrate&quot; Hoffman',
						'Shortie',
						'Trekkie101',
						'[n3rve]',
					),
				),
				array(
					'title' => $txt['ep_credits_fam_fam'],
					'members' => array(
						$txt['ep_credits_fam_fam_message'],
					),
				),
			),
			'translators' => $txt['ep_credits_translators_thanks'],
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

?>
