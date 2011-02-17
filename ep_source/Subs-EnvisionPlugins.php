<?php
/**************************************************************************************
* Subs-EnvisionPlugins.php                                                            *
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

/**
 *	Loads all plugin files
 *
 *	@since 1.0.0
*/
function ep_plugin_load_files($hook = '')
{
	global $sourcedir, $themedir, $boarddir, $modSettings;

	/* Empty hook, or not set? Return silly goose! */
	if (empty($hook) || empty($modSettings['ep_plugin_include_' . $hook]))
		return;

	/* Plugin directories */
	$plugin_dirs = array(
		$themedir . '/ep_plugin_template',
		$sourcedir . '/ep_plugin_source',
		$boarddir . '/ep_plugin_extra'
	);

	/* File list */
	$filelist = explode(',', $modSettings['ep_plugin_include_' . $hook]);

	/* Loop through, making sure files exist before including them! */
	foreach ($filelist as $file)
	{
		foreach ($plugin_dirs as $dir)
		{
			if (file_exists($dir . '/' . $file))
				require_once($dir . '/' . $file);
		}
	}
}

/**
 *	Loads language files for each plugin
 *
 *	@since 1.0.0
*/
function ep_plugin_load_langfiles($hook = '')
{
	global $modSettings;

	if (empty($hook) || empty($modSettings['ep_plugin_include_lang_' . $hook]))
		return;

	$filelist = explode(',', $modSettings['ep_plugin_include_lang_' . $hook]);
	foreach ($filelist as $file)
		loadLanguage($file);
}

?>