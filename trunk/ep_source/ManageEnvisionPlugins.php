<?php
/**************************************************************************************
* ManageEnvisionPlugins.php                                                             *
***************************************************************************************
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

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function Plugins()
{
	global $context, $txt;

	loadTemplate('ep_template/ManageEnvisionPlugins');
	loadLanguage('ep_languages/ManageEnvisionPlugins');

	$subActions = array(
		'manage' => array('ManageEnvisionPlugins', 'fadmin_orum'),
		'manage2' => array('SavePlugin', 'admin_forum'),
	);

	// Default to sub action 'manage'
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'manage';

	// Have you got the proper permissions?
	if (!empty($subActions[$_GET['sa']][1]))
		isAllowedTo($subActions[$_GET['sa']][1]);

	$context['page_title'] = $txt['ep_plugins_title'];

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ep_plugins_title'],
		'description' => $txt['ep_plugins_desc'],
	);

	// Call the right function for this sub-acton.
	$subActions[$_GET['sa']][0]();

}

/**
 * Gets a list of the existing plugins from the filesystem.
 *
 * @since 1.0
 */
function ManageEnvisionPlugins()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	$context['ep_plugins'] = listPlugins();
}
/**
 * Saves a Envision Plugin. Handles creating a new plugin or modilying an existing one.
 *
 * @since 1.0
 */
function SavePlugin()
{
	global $context, $modSettings;

	$plugins_list = listPlugins(true);
	if (isset($_POST['ep_plugins_c']))
		foreach ($_POST['ep_plugins_c'] as $which => $install_state)
		{
			// Enabling?
			if (!$plugins_list[$which]['enabled'] && !empty($install_state))
			{
				if (isset($plugins_list[$which]['code']['enable']) && is_callable($plugins_list[$which]['code']['enable']))
					$plugins_list[$which]['code']['enable']();

				if (!isset($modSettings['ep_plugins']))
					$modSettings['ep_plugins'] = array();

				$plugins = unserialize($modSettings['ep_plugins']);
				$plugins[] = $which;
				updateSettings(array('ep_plugins' => serialize($plugins)));

				logEpAction('enable_plugin', 0, array($which));
			}

			// Disabling?
			if ($plugins_list[$which]['enabled'] && empty($install_state))
			{
				if (isset($plugins_list[$which]['code']['disable']) && is_callable($plugins_list[$which]['code']['disable']))
					$plugins_list[$which]['code']['disable']();

				$plugins = unserialize($modSettings['ep_plugins']);
				$plugins = array_diff($plugins, array($which));
				updateSettings(array('ep_plugins' => serialize($plugins)));

				logEpAction('disable_plugin', 0, array($which));
			}
		}

	redirectexit('action=admin;area=epplugins');
}

?>