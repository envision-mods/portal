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
 * @package   source
 * @copyright 2009-2010 Envision Portal
 * @license   http://envisionportal.net/index.php?action=about;sa=legal Envision Portal License (Based on BSD)
 * @link      http://envisionportal.net Support, news, and updates
 * @see       ManageEnvisionSettings.template.php
 * @since     1.0
 * @version   1.1
 */

if (!defined('SMF')) {
	die('Hacking attempt...');
}

/**
 * Loads some general settings parameters to help minimize code duplication.
 *
 * @param array $subActions Array of all the subactions. The format of this array is 'mySubAction' =>
 *                              'functionToCall'. Default is an empty array.
 * @param string $defaultAction A string which holds the default sub action. Default is an empty string.
 *
 * @since 1.0
 * @todo  condense this function with {@link Configuration()}; why this avoids repetition I have no ides =/
 */
function loadGeneralSettingParameters($subActions = [], $defaultAction = '')
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
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (!empty($defaultAction) ? $defaultAction : array_pop(
		array_keys($subActions)
	));
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

	$context['insert_after_template'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/jquery-ui-1.7.3.custom.min.js"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_man_mods.js"></script>';

	if (isset($_REQUEST['xml'])) {
		$context['template_layers'] = [];
	}

	$subActions = [
		'epinfo' => 'EnvisionPortalInfo',
		'epgeneral' => 'ModifyEnvisionGeneral',
	];

	loadGeneralSettingParameters($subActions, 'epinfo');

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = [
		'title' => &$txt['ep_admin_config'],
		'help' => $txt['ep_admin_config_help'],
		'description' => $txt['ep_admin_config_desc'],
		'tabs' => [
			'epinfo' => [
				'description' => $txt['ep_admin_config_info_desc'],
			],
			'epgeneral' => [
				'description' => $txt['ep_admin_config_general_desc'],
			],
		],
	];

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

	if (listMembergroupMembers_Href($context['administrators'], 1, 32) && allowedTo('manage_membergroups')) {
		// Add a 'more'-link if there are more than 32.
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=moderate;area=viewgroups;sa=members;group=1">' . $txt['more'] . '</a>';
	}

	// Some much needed scripting ;)
	$context['html_headers'] .= '
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
				setInnerHTML(document.getElementById("ep_update_section"), ' . JavaScriptEscape(
			'
					<span class="upperframe"><span><!-- // --></span></span>
						<div class="roundframe smalltext">
						<span class="error">' . $txt['ep_outdated'] . '</span>
						</div>
					<span class="lowerframe"><span><!-- // --></span></span>
				'
		) . ');
			}

			setInnerHTML(document.getElementById("ep_latest_version"), window.epCurrentVersion);
		}
	// ]]></script>';

	// Our credits info. =D
	$context['credits'] = [
		[
			'pretext' => $txt['ep_credits_info'],
			'groups' => [
				[
					'title' => $txt['ep_credits_groups_dev'],
					'members' => [
						'John &quot;live627&quot; Rayes',
					],
				],
				[
					'title' => $txt['ep_credits_special'],
					'members' => [
						$txt['ep_credits_all_friends'],
					],
				],
				[
					'title' => $txt['ep_credits_fugue'],
					'members' => [
						$txt['ep_credits_fugue_message'],
					],
				],
			],
			'posttext' => $txt['ep_credits_anyone'],
		],
	];

	$context['sub_template'] = 'portal_info';
}

/**
 * Loads the general settings for Envision Portal so the admin can change them. uUses the sub template show_settings in
 * Admin.template.php to display them.
 *
 * @param bool $return_config Determines whether or not to return the config array.
 *
 * @return void|array The $config_vars if $return_config is true.
 * @since 1.0
 */
function ModifyEnvisionGeneral($return_config = false)
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$config_vars = [
		['check', 'ep_portal_mode', 'subtext' => $txt['ep_portal_mode_subtext']],
		$txt['ep_pages_title'],
		['check', 'ep_pages_mode', 'subtext' => $txt['ep_pages_subtext']],
		['callback', 'ep_admin_config'],
		[
			'permissions',
			'ep_view',
			'text_label' => $txt['ep_view_permissions'],
			'subtext' => $txt['ep_view_permissions_subtext']
		],
		$txt['ep_admin_config_modules'],
		['check', 'ep_collapse_modules'],
	];

	if ($return_config) {
		return $config_vars;
	}

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=epconfig;sa=epgeneral');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=epconfig;save;sa=epgeneral';
	$context['settings_title'] = $txt['ep_admin_config_general'];

	prepareDBSettingContext($config_vars);

	$context['force_form_onsubmit'] = 'epc_FormSendingHandler.send(); return false;';

	$context['insert_after_template'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_modify_modules.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var epc_FormSendingHandler = new epc_Form({
			sUrl: \'' . $context['post_url'] . ';xml\' .
			sSessionVar: ' . JavaScriptEscape($context['session_var']) . ' .
			sSessionId: ' . JavaScriptEscape($context['session_id']) . '
		});
	// ]]></script>';
}

function template_callback_ep_admin_config(): void
{
	global $txt;

	echo '
									</dl>
									<div class=descbox>', $txt['ep_admin_config_general_optional'], '</div>
									<dl class=settings>';
}