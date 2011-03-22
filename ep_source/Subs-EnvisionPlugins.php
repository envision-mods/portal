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

/**
 * Calls a given integration hook at the related point in the code.
 *
 * Each of the hooks is an array of functions within $modSettings['hooks'], to be called at relevant points in the code Do note that the file-inclusion hooks may not be called with this.
 *
 * The contents of the $modSettings['hooks'] value is a comma separated list of function names to be called at the relevant point. These are either procedural functions or static class methods (classname::method).
 *
 * @param string $hook the name of the hook as given in $modSettings.
 * @param array $parameters parameters to be passed to the hooked functions. The list of parameters each method is exposed to is dependent on the calling code, and parameters passed by reference will be passed to hook functions as such.
 * @return array an array of results, one element per hooked function. This will be solely dependent on the hooked function.
 */
function ep_call_hook($hook, $parameters = array())
{
	global $modSettings;

	if (empty($modSettings['ep_hooks'][$hook]))
		return array();

	$results = array();

	// Loop through each function.
	foreach ($modSettings['ep_hooks'][$hook] as $function)
	{
		$function = trim($function);
		$call = strpos($fun, '::') !== false ? explode('::', $fun) : $fun;

		// If it isn't valid, remove it from our list.
		if (is_callable($call))
			$results[$function] = call_user_func_array($call, $parameters);
		else
			ep_remove_hook($call, $function);
	}

	return $results;
}

/**
 * Add a function to one of the integration hook stacks.
 *
 * This function adds a function to be called (or file to be loaded, for the pre_include hook). This function also prevents the same function being added to the same hook twice.
 *
 * @param string $hook The name of the hook that has zero or more functions attached, that the function will be added to.
 * @param string $function the name of the function whose name should be added to the named hook.
 * @param bool $permanent whether the named function will be added to the hook registry permanently (default), or simply for the current page load only.
 * @return bool true on success; false otherwise.
*/
function ep_add_hook($hook, $function, $permanent = true)
{
	global $modSettings;

	// Do nothing if it's already there, except if we're asking for registration and it isn't permanented yet.
	if ((!$permanent || in_array($function, $modSettings['ep_permanented_hooks'][$hook])) && ($in_hook = in_array($function, $modSettings['ep_hooks'][$hook])))
		return false;

	// Add it!
	if (!$in_hook)
		$modSettings['ep_hooks'][$hook][] = $function;

	if (!$permanent)
		return true;

	// Add it to the permanent hooks list.
	$modSettings['ep_permanented_hooks'][$hook][] = $function;
	$hooks = $modSettings['ep_permanented_hooks'];
	updateSettings(array('ep_permanented_hooks' => serialize($hooks)));
	$modSettings['ep_permanented_hooks'] = $hooks;

	return true;
}

/**
 * Remove a function from one of the integration hook stacks.
 *
 * This function not only removes the hook from the local registry, but also from the master registry. Note that it does not check whether the named function is callable, simply that it is part of the stack - it can be used on the file-inclusion hooks as well. If the function is not attached to the named hook, this function will simply return false for failure.
 *
 * @param string $hook the name of the hook that has one or more functions attached.
 * @param string $function the name of the function whose name should be removed from the named hook.
 * @return bool true on success; false otherwise.
 */
function ep_remove_hook($hook, $function)
{
	global $modSettings;

	// You can only remove it if it's available.
	if (empty($modSettings['ep_hooks'][$hook]) || !in_array($function, $modSettings['ep_hooks'][$hook]))
		return false;

	$modSettings['ep_hooks'][$hook] = array_diff($modSettings['ep_hooks'][$hook], (array) $function);

	if (empty($modSettings['ep_permanented_hooks'][$hook]) || !in_array($function, $modSettings['ep_permanented_hooks'][$hook]))
		return false;

	// Also remove it from the permanented hooks.
	$modSettings['ep_permanented_hooks'][$hook] = array_diff($modSettings['ep_permanented_hooks'][$hook], (array) $function);
	$hooks = $modSettings['ep_permanented_hooks'];
	updateSettings(array('ep_permanented_hooks' => serialize($hooks)));
	$modSettings['ep_permanented_hooks'] = $hooks;

	return true;
}

?>