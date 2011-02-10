<?php
/**************************************************************************************
* Subs-EnvisionPortal.php                                                             *
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

/*	This file contains functions vital for the performance of Envision
	Portal. It provides the following functions:

	void loadDefaultModuleConfigs(array installed_mods = array(), boolean new_layout = false)
		- Initializes the default Module settings.
		- installed_mods get passed an array of the name, files, functions.  It is important
		  to note that these are modules that get installed via the Add Modules section.
		- new_layout is used to determine if this information is to be used for a newly created layout.

	void loadParameter(array file_input = array(), string param_type, string param_value)
		- Reads the information for each parameter individually and returns a clean string based on the
		  parameter type sent to it.
		- returns the new value to be used for that parameter within the module's function.

	void parseString(string str = '', string type = 'filepath', boolean replace = true)
		- Reads the input string (str) and returns either a new string or an integer value.
		- when replace = false, returns 1 if invalid characters are found within str, else 0.
		- when replace = true, replaces all invalid characters with ''.
		- Note:  Their are a few types that don't accept replace = false, in those types, if
		  replace is set to true, it will simply return str without doing anything to it.

	void module_error(string type = 'error', string error_type = 'general', boolean log_error = false, boolean echo = true)
		- Echoes an error message within a module if echo = true.  Note:  This module doesn't do any error handling
		  for you, you must do this yourself for your modules.  This just provides an error message of some sort for
		  you to use within your module.  Make sure you return after calling this function in your modules, or it will
		  continue running your code.
		- possible string types are:  error, mod_not_installed, not_allowed, no_language, query_error, empty.  You can
		  also define you own string to be passed in here that will output that message instead of any of the pre-defined
		  messages listed above.
		- possible error_type strings are: general, critical, database, undefined_vars, user, template, and debug.  If
		  critical is defined for the error_type, than the error message will output red colored text.
		- log_error = whether or not to log the error into the Admin -> Error Log.
		- If echo = true will output it directly, if false, returns the information to be used within a variable.

	void loadFiles(array file_input = array())
		- Loads up all files for any given id_param via the file_input parameter type.

	void createFile(array fileOptions)
		- Handles uploaded files via the file_input parameter type.
		- Places the information for each file uploaded into the ep_module_files database table.

	void AllowedFileExtensions(string file_mime)
		- Returns all possible extensions for any given mime type supplied within the file_mime separated by commas.

	void getFilename(string filename, string file_id, string path, boolean new = false, string file_hash = '')
		- Gets/Sets a files encrypted filename via the file_input parameter type.

	void getLegacyFilename(string filename, string file_id, string path, boolean new = false)
		- Returns a clean, encrypted path and file hash.

	void GetEnvisionModuleInfo(string scripts, string mod_functions, string dirname, string file, string name = '', boolean install = false)
		- Gets all of the data from the info.xml file for a module.
		- Returns an array of data, or false if an error occurred such as mandatory fields are missing, etc..

	void GetEnvisionAddedModules()
		- Gets all Uploaded Module information for output into the Add Modules section of the Envision Admin.
		- Determines whether or not a module is installed.

	void GetEnvisionInstalledModules(array installed_mods = array())
		- Gets all installed modules and output it into an array that we can use.
		- If installed_mods is an empty array, than it will query the database to get the
		  information needed from installed modules.

	void loadLayout(string $url)
		- Loads the layout for the action specified by $url
		- Called from ep_main() in DrweamPortal.php
		- If $url = [home] (square backets included), the request is from the home page
		- Calls ProcessModule() to prepare the module for use within the template
		- Sets $context['envision_columns'] with the layout data

	array ProcessModule(array $data)
		- Calls loadDefaultModuleConfigs() to get the function to call for the default modules
		- Prepares the raw module data generated by loadLayout() for use in the template
		- Returns an array of the data

	array load_envision_menu()
		- Prepares all the user added buttons for the menu
		- Returns an array of the data

	array add($index, $position, $array, $add, $add_key)
		- adds something to an array
*/

function loadDefaultModuleConfigs($installed_mods = array(), $new_layout = false)
{
	global $txt;

	// Default Module Configurations.
	$envisionModules = array(
		'announce' => array(
			'title' => 'Announcement',
			'files' => '',
			'target' => 1,
			'icon' => 'world.png',
			'title_link' => '',
			'functions' => 'module_announce',
			'params' => array(
				'msg' => array(
					'type' => 'large_text',
					'value' => 'Welcome to Envision Portal!',
				),
			),
		),
		'usercp' => array(
			'title' => $txt['ep_module_usercp'],
			'files' => '',
			'target' => 1,
			'icon' => 'heart.png',
			'title_link' => 'action=profile',
			'functions' => 'module_usercp',
			'params' => array(),
		),
		'stats' => array(
			'title' => $txt['ep_module_stats'],
			'files' => '',
			'target' => 1,
			'icon' => 'stats.png',
			'title_link' => 'action=stats',
			'functions' => 'module_stats',
			'params' => array(
				'stat_choices' => array(
					'type' => 'checklist',
					'value' => '0,1,2,5,6:members;posts;topics;categories;boards;ontoday;onever:order',
				),
			),
		),
		'online' => array(
			'title' => $txt['ep_module_online'],
			'files' => '',
			'target' => 1,
			'icon' => 'user.png',
			'title_link' => 'action=who',
			'functions' => 'module_online',
			'params' => array(
				'online_pos' => array(
					'type' => 'select',
					'value' => '0:top;bottom',
				),
				'show_online' => array(
					'type' => 'checklist',
					'value' => '0,1,2:users;buddies;guests;hidden;spiders:order',
				),
				'online_groups' => array(
					'type' => 'list_groups',
					'value' => '-3:-1,0,3:order',
				),
			),
		),
		'news' => array(
			'title' => $txt['ep_module_news'],
			'files' => '',
			'target' => 1,
			'icon' => 'cog.png',
			'title_link' => '',
			'functions' => 'module_news',
			'params' => array(
				'board' => array(
					'type' => 'list_boards',
					'value' => '1',
				),
				'limit' => array(
					'type' => 'int',
					'value' => '5',
				),
			),
		),
		'recent' => array(
			'title' => $txt['ep_module_topics'],
			'files' => '',
			'target' => 1,
			'icon' => 'pencil.png',
			'title_link' => 'action=recent',
			'functions' => 'module_recent',
			'params' => array(
				'post_topic' => array(
					'type' => 'select',
					'value' => '1:posts;topics',
				),
				'show_avatars' => array(
					'type' => 'check',
					'value' => '1',
				),
				'num_recent' => array(
					'type' => 'int',
					'value' => '10',
				),
			),
		),
		'search' => array(
			'title' => $txt['ep_module_search'],
			'files' => '',
			'target' => 1,
			'icon' => 'magnifier.png',
			'title_link' => 'action=search',
			'functions' => 'module_search',
			'params' => array(),
		),
		'calendar' => array(
			'title' => $txt['ep_module_calendar'],
			'files' => '',
			'target' => 1,
			'icon' => 'cal.png',
			'title_link' => '',
			'functions' => 'module_calendar',
			'params' => array(
				'display' => array(
					'type' => 'select',
					'value' => '0:month;info',
				),
				'show_months' => array(
					'type' => 'select',
					'value' => '1:year;asdefined',
				),
				'previous' => array(
					'type' => 'int',
					'value' => '1',
				),
				'next' => array(
					'type' => 'int',
					'value' => '1',
				),
				'show_options' => array(
					'type' => 'checklist',
					'value' => '0,1,2:events;holidays;birthdays:order',
				),
			),
		),
		'poll' => array(
			'title' => $txt['ep_module_poll'],
			'files' => '',
			'target' => 1,
			'icon' => 'comments.png',
			'title_link' => '',
			'functions' => 'module_poll',
			'params' => array(
				'options' => array(
					'type' => 'select',
					'value' => '0:showPoll;topPoll;recentPoll',
				),
				'topic' => array(
					'type' => 'int',
					'value' => '0',
				),
			),
		),
		'top_posters' => array(
			'title' => $txt['ep_module_topPosters'],
			'files' => '',
			'target' => 1,
			'icon' => 'rosette.png',
			'title_link' => '',
			'functions' => 'module_topPosters',
			'params' => array(
				'show_avatar' => array(
					'type' => 'check',
					'value' => '1',
				),
				'show_postcount' => array(
					'type' => 'check',
					'value' => '1',
				),
				'num_posters' => array(
					'type' => 'int',
					'value' => '10',
				),
			),
		),
		'theme_select' => array(
			'title' => $txt['ep_module_theme_select'],
			'files' => '',
			'target' => 1,
			'icon' => 'palette.png',
			'title_link' => 'action=theme;sa=pick',
			'functions' => 'module_theme_selector',
			'params' => array(),
		),
		'new_members' => array(
			'title' => $txt['ep_module_new_members'],
			'files' => '',
			'target' => 1,
			'icon' => 'overlays.png',
			'title_link' => 'action=stats',
			'functions' => 'module_new_members',
			'params' => array(
				'limit' => array(
					'type' => 'int',
					'value' => '3',
				),
				'list_type' => array(
					'type' => 'select',
					'value' => '0:0;1;2',
				),
			),
		),
		'staff' => array(
			'title' => $txt['ep_module_staff'],
			'files' => '',
			'target' => 1,
			'icon' => '',
			'title_link' => '',
			'functions' => 'module_staff',
			'params' => array(
				'list_type' => array(
					'type' => 'select',
					'value' => '1:0;1;2',
				),
				'name_type' => array(
					'type' => 'select',
					'value' => '0:0;1;2',
				),
				'groups' => array(
					'type' => 'list_groups',
					'value' => '1,2:-1,0:order',
				),
			),
		),
		'sitemenu' => array(
			'title' => $txt['ep_module_sitemenu'],
			'files' => '',
			'target' => 1,
			'icon' => 'star.png',
			'title_link' => '',
			'functions' => 'module_sitemenu',
			'params' => array(
				'onesm' => array(
					'type' => 'check',
					'value' => '0',
				),
			),
		),
		'shoutbox' => array(
			'title' => $txt['ep_module_shoutbox'],
			'files' => '',
			'target' => 1,
			'icon' => 'comments.png',
			'title_link' => '',
			'functions' => 'module_shoutbox',
			'params' => array(
				'id' => array(
					'type' => 'db_select',
					'value' => '1;id_shoutbox:{db_prefix}ep_shoutboxes;name:custom',
				),
				'refresh_rate' => array(
					'type' => 'int',
					'value' => '1',
				),
				'max_count' => array(
					'type' => 'int',
					'value' => '15',
				),
				'max_chars' => array(
					'type' => 'int',
					'value' => '128',
				),
				'text_size' => array(
					'type' => 'select',
					'value' => '1:small;medium',
				),
				'member_color' => array(
					'type' => 'check',
					'value' => '1',
				),
				'message' => array(
					'type' => 'text',
					'value' => '',
				),
				'message_position' => array(
					'type' => 'select',
					'value' => '1:top;after;bottom',
				),
				'message_groups' => array(
					'type' => 'list_groups',
					'value' => '-3:3',
				),
				'mod_groups' => array(
					'type' => 'list_groups',
					'value' => '1:-1,0,3',
				),
				'mod_own' => array(
					'type' => 'list_groups',
					'value' => '0,1,2:-1,3',
				),
				'bbc' => array(
					'type' => 'list_bbc',
					'value' => 'b;i;u;s;pre;left;center;right;url;sup;sub;php;nobbc;me',
				),
			),
		),
		'custom' => array(
			'title' => $txt['ep_module_custom'],
			'files' => '',
			'target' => 1,
			'icon' => 'comments.png',
			'title_link' => '',
			'functions' => 'module_custom',
			'params' => array(
				'code_type' => array(
					'type' => 'select',
					'value' => '1:0;1;2',
				),
				'code' => array(
					'type' => 'rich_edit',
					'value' => '',
				),
			),
		),
	);

	// Any modules installed?
	if (count($installed_mods) >= 1 || $new_layout)
		return array_merge($envisionModules, GetEnvisionInstalledModules($installed_mods));
	else
		return $envisionModules;
}

//!!! Loads up the modules parameters. Does some editing to the parameter value based on the type and outputs its new value.
function loadParameter($file_input = array(), $param_type, $param_value)
{
	global $context;

	// Loading up all files are we?
	if (count($file_input) >= 1)
	{
		$mod_param = loadFiles($file_input);
		return $mod_param;
	}

	// Need to handle all selects here.
	if (trim(strtolower($param_type)) == 'select')
	{
		$select_params = array();
		$values = array();

		$select_params = explode(':', $param_value);
		if (!empty($select_params))
		{
			$opt_value = (int) $select_params[0];
			if (isset($select_params[1]))
				$values = explode(';', $select_params[1]);
		}

		// Need to make sure its fine before setting this.
		if (count($values) >= 1 && $opt_value < count($values))
			$mod_param = $values[$opt_value];
		else
			// Error, set to empty and let the module function handle this instead.
			$mod_param = '';
	}
	elseif(trim(strtolower($param_type)) == 'db_select')
	{
		// Only returning the selected value for this parameter.
		$db_select = explode(':', $param_value);
		if (isset($db_select[0]))
		{
			$db_select_value = explode(';', $db_select[0]);

			if (isset($db_select_value[0]))
				$mod_param = (string) $db_select_value[0];
			else
				$mod_param = '';
		}
		else
			$mod_param = '';
	}
	elseif(trim(strtolower($param_type)) == 'checklist')
	{
		$list_params = explode(':', $param_value);

		// Set a few booleans here.
		$has_checks = !empty($list_params) && isset($list_params[0]) && trim($list_params[0]) != '' && !stristr(trim($list_params[0]), 'order');
		$has_strings = isset($list_params[1]) && trim($list_params[1]) != '';
		$has_order = !empty($list_params[2]) && isset($list_params[2]) && strlen(stristr(trim($list_params[2]), 'order')) > 0;

		if ($has_order)
		{
			$order = array();
			$order = explode(';', $list_params[2]);
				if (!isset($order[1]) || trim($order[1]) == '')
					unset($order);
		}

		if ($has_checks)
		{
			$mod_param = array();

			// Grab the checked values.
			$mod_param['checked'] = $list_params[0];

			// Order me timbers...
			if ($has_order && isset($order))
				$mod_param['order'] = $order[1];
			else
			{
				if ($has_order && !isset($order))
				{
					if ($has_strings)
						$mod_param['order'] = implode(',', array_keys(explode(';', $list_params[1])));
					else
						$mod_param['order'] = '';
				}
			}
		}
		else
			// Error, set checked to empty and let the module function handle this instead.
			$mod_param = '';

		// We're done with this now.
		unset($list_params);
	}
	elseif(trim(strtolower($param_type)) == 'list_groups')
	{
		$group_params = explode(':', $param_value);

		if (!empty($group_params) && isset($group_params[0]) && trim($group_params[0]) != '' && !stristr(trim($group_params[0]), 'order'))
		{
			// Are there any group ids that are not allowed?
			if (isset($group_params[1]) && !stristr(trim($group_params[1]), 'order'))
			{
				$checked = explode(',', $group_params[0]);
				$unallowed = explode(',', $group_params[1]);

				// We have values not allowed, let's filter them out now.
				$checked = array_diff($checked, $unallowed);

				// Note:  If (value < -1), than nothing is checked for this in the Admin.
				// 		  But we will let the Customizer choose what to do about it and keep it's value as is!
				if (count($checked) >= 1)
				{
					// Rebuild the array keys.
					$checked = array_values($checked);

					// Put it back together and return it.
					$mod_param = implode(',', $checked);

					// No longer needed.
					unset($checked, $unallowed);
				}
				// Opps, no group ids are being used.  Let the module function handle this instead.
				else
					$mod_param = '';
			}
			else
				// All groups are enabled, return the values.
				$mod_param = $group_params[0];
		}
		else
			// Error, set to empty and let the module function handle this instead.
			$mod_param = '';

		// We're done with this now.
		unset($group_params);
	}
	elseif(trim(strtolower($param_type)) == 'list_bbc')
		$mod_param = $param_value;
	else
		$mod_param = trim(strtolower($param_type)) == 'html' ? html_entity_decode($param_value, ENT_QUOTES, $context['character_set']) : $param_value;

	return $mod_param;
}

function parseString($str = '', $type = 'filepath', $replace = true)
{
	if ($str == '')
		return '';

	switch ((string) $type)
	{
		// Only accepts replace.
		case 'module_name':
			$find = array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/');
			$replace_str = array('_', '_', '');
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// trims away the first and last slashes, or matches against it.
		case 'folderpath':
			$valid_str = $replace ? 0 : (strpos($str, ' ') !== false ? 1 : 0);
			$find = $replace ? '#^/|/$|[^A-Za-z0-9_\/s/\-/]#' : '#^(\w+/){0,2}\w+-$#';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : (!empty($valid_str) ? $valid_str : preg_match($find, $str));
			break;
		case 'function_name':
			$find = '~[^A-Za-z0-9_]~';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : preg_match($find, $str);
			break;
		// Only accepts replace.
		case 'uploaded_file':
			$find = array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/');
			$replace_str = array('_', '.', '');
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// Only accepts replace.
		case 'phptags':
			$find = array('/<\?php/s', '/\?>/s', '/<\?/s');
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : $str;
			break;
		// Example: THIS STRING:  /my%root/p:a;t h/my file-english.php/  BECOMES THIS: myroot/path/myfile-english.php
		default:
			$valid_str = $replace ? 0 : (strpos($str, ' ') !== false ? 1 : 0);
			$find = $replace ? '#^/|/$|[^A-Za-z0-9_.\/s/\-/]#' : '#^(\w+/){0,2}\w+-\.\w+$#';
			$replace_str = '';
			$valid_str = $replace ? preg_replace($find, $replace_str, $str) : (!empty($valid_str) ? $valid_str : preg_match($find, $str));
			break;
	}
	return $valid_str;
}

function module_error($type = 'error', $error_type = 'general', $log_error = false, $echo = true)
{
	global $txt;

	// All possible pre-defined types.
	$valid_types = array(
		'mod_not_installed' => $type == 'mod_not_installed' ? 1 : 0,
		'not_allowed' => $type == 'not_allowed' ? 1 : 0,
		'no_language' => $type == 'no_language' ? 1 : 0,
		'query_error' => $type == 'query_error' ? 1 : 0,
		'empty' => $type == 'empty' ? 1 : 0,
		'error' => $type == 'error' ? 1 : 0,
	);

	$error_string = !empty($valid_types[$type]) ? $txt['ep_module_' . $type] : $type;
	$error_html = $error_type == 'critical' ? array('<p class="error">', '</p>') : array('', '');

	// Don't need this anymore!
	unset($valid_types);

	// Should it be logged?
	if ($log_error)
		log_error($error_string, $error_type);

	$return = implode($error_string, $error_html);

	// Echo...? Echo...?
	if ($echo)
		echo $return;
	else
		return $return;
}

function loadFiles($file_input = array())
{
	global $txt, $scripturl, $topic, $sourcedir, $smcFunc;

	if(count($file_input) <= 0)
		return '';

	// Calling all files for that module/clone!
	$request = $smcFunc['db_query']('', '
		SELECT id_file, id_thumb, file_type, filename, file_hash, fileext, size, downloads, width, height, mime_type
		FROM {db_prefix}ep_module_files
		WHERE id_param = {int:id_param}',
		array(
			'id_param' => $file_input['id_param'],
		)
	);

	$mod_type = $file_input['is_clone'] ? 'clone' : 'mod';

	$fileData = array();

	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Getting all info.
		if (empty($row['file_type']))
			$fileData[$row['id_file']] = array(
				'name' => preg_replace('~&amp;#(\\d{1,7}|x[0-9a-fA-F]{1,6});~', '&#\\1;', htmlspecialchars($row['filename'])),
				'extension' => $row['fileext'],
				'size' => round($row['size'] / 1024, 2) . ' ' . $txt['kilobyte'],
				'byte_size' => $row['size'],
				'width' => $row['width'],
				'height' => $row['height'],
				'downloads' => $row['downloads'],
				'mime_type' => $row['mime_type'],
				'href' => $scripturl . '?action=envisionFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_file'],
				'link' => '<a href="' . $scripturl . '?action=envisionFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_file'] . '">' . htmlspecialchars($row['filename']) . '</a>',
				'is_image' => !empty($row['width']) && !empty($row['height']),
				'has_thumb' => !empty($row['id_thumb']),
				'thumb_href' => !empty($row['id_thumb']) ? $scripturl . '?action=envisionFiles;' . $mod_type . '=' . $file_input['id'] . ';id=' . $row['id_thumb'] . ';image' : '',
			);
	}

	// Order it correctly.
	ksort($fileData, SORT_NUMERIC);

	// Rebuild it.
	$fileData = array_values($fileData);

	return $fileData;
}

function createFile(&$fileOptions)
{
	global $sourcedir, $smcFunc;

	$file_dir = $fileOptions['folderpath'];

	$fileOptions['errors'] = array();

	if (!isset($fileOptions['id_file']))
		$fileOptions['id_file'] = 0;

	$file_restricted = @ini_get('open_basedir') != '';

	// Make sure the file actually exists...
	if (!$file_restricted && !file_exists($fileOptions['tmp_name']))
	{
		$fileOptions['errors'] = array('could_not_upload');
		return false;
	}

	// These are the only valid image types.
	$validImageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpeg', 14 => 'iff');

 	if (!$file_restricted)
	{
		$size = @getimagesize($fileOptions['tmp_name']);
		list ($fileOptions['width'], $fileOptions['height']) = $size;

		// If it's an image get the mime type right.
		if (empty($fileOptions['mime_type']) && $fileOptions['width'])
		{
			// Got a proper mime type?
			if (!empty($size['mime']))
				$fileOptions['mime_type'] = $size['mime'];
			// Otherwise a valid one?
			elseif (isset($validImageTypes[$size[2]]))
				$fileOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
		}
	}

	// Get the hash if no hash has been given yet.
	if (empty($fileOptions['file_hash']))
		$fileOptions['file_hash'] = getFilename($fileOptions['name'], false, null, true);

	// Check the extension, it must be valid.
	$allowed = explode(',', $fileOptions['fileExtensions']);
	foreach ($allowed as $k => $dummy)
		$allowed[$k] = trim($dummy);

	if (!in_array(strtolower(substr(strrchr($fileOptions['name'], '.'), 1)), $allowed))
		$fileOptions['errors'] = array('bad_extension');

	if (!is_writable($file_dir))
		$fileOptions['errors'] = array('files_no_write');

	// Return if errors detected somewhere.
	if (!empty($fileOptions['errors']))
		return false;

	// Assuming no-one set the extension let's take a look at it.
	if (empty($fileOptions['fileext']))
	{
		$fileOptions['fileext'] = strtolower(strrpos($fileOptions['name'], '.') !== false ? substr($fileOptions['name'], strrpos($fileOptions['name'], '.') + 1) : '');
		if (strlen($fileOptions['fileext']) > 8 || '.' . $fileOptions['fileext'] == $fileOptions['name'])
			$fileOptions['fileext'] = '';
	}

	// If strict, skip this and go directly to a thumbnail, ONLY if it is bigger than the dimensions specified.
	if (isset($fileOptions['resizeWidth']) && isset($fileOptions['resizeHeight']))
	{
		$resize = ($fileOptions['width'] > $fileOptions['resizeWidth'] && !empty($fileOptions['resizeWidth'])) || ($fileOptions['height'] > $fileOptions['resizeHeight'] && !empty($fileOptions['resizeHeight'])) ? true : false;
		$not_strict = (!empty($fileOptions['strict']) && !$resize) || empty($fileOptions['strict']) ? true : false;
	}
	else
	{
		$resize = false;
		$not_strict = true;
	}

	$smcFunc['db_insert']('',
		'{db_prefix}ep_module_files',
		array(
			'id_param' => 'int', 'id_member' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
			'size' => 'int', 'width' => 'int', 'height' => 'int',
			'mime_type' => 'string-255',
		),
		array(
			(int) $fileOptions['id_param'], $fileOptions['id_member'], $fileOptions['name'], $fileOptions['file_hash'], $fileOptions['fileext'],
			(int) $fileOptions['size'], (empty($fileOptions['width']) ? 0 : (int) $fileOptions['width']), (empty($fileOptions['height']) ? '0' : (int) $fileOptions['height']),
			(!empty($fileOptions['mime_type']) ? $fileOptions['mime_type'] : (!empty($fileOptions['file_mime']) ? $fileOptions['file_mime'] : '')),
		),
		array('id_file')
	);

	$fileOptions['id'] = $smcFunc['db_insert_id']('{db_prefix}ep_module_files', 'id_file');

	$fileOptions['destination'] = getFilename(basename($fileOptions['name']), $fileOptions['id'], $fileOptions['folderpath'], false, $fileOptions['file_hash']);

	if ($not_strict)
	{
		// Move the file to where it needs to go.
		if (!move_uploaded_file($fileOptions['tmp_name'], $fileOptions['destination']))
		{
			$fileOptions['error'] = array('file_timeout');
			return false;
		}
		// We couldn't access the file before...
		elseif ($file_restricted)
		{
			$size = @getimagesize($fileOptions['destination']);
			list ($fileOptions['width'], $fileOptions['height']) = $size;

			// Have a go at getting the right mime type.
			if (empty($fileOptions['mime_type']) && $fileOptions['width'])
			{
				if (!empty($size['mime']))
					$fileOptions['mime_type'] = $size['mime'];
				elseif (isset($validImageTypes[$size[2]]))
					$fileOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
			}

			if (!empty($fileOptions['width']) && !empty($fileOptions['height']))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}ep_module_files
					SET
						width = {int:width},
						height = {int:height},
						mime_type = {string:mime_type}
					WHERE id_file = {int:id_file}',
					array(
						'width' => (int) $fileOptions['width'],
						'height' => (int) $fileOptions['height'],
						'id_file' => $fileOptions['id'],
						'mime_type' => empty($fileOptions['mime_type']) ? '' : $fileOptions['mime_type'],
					)
				);
		}
		// Attempt to chmod it.
		@chmod($fileOptions['destination'], 0644);

		// No Thumbnails to create!
		if (!$resize)
			return true;
	}

	// Ready to create the thumbnails
	if (!$not_strict || $resize)
	{
		if (!empty($fileOptions['strict']))
			move_uploaded_file($fileOptions['tmp_name'], $fileOptions['destination']);

		require_once($sourcedir . '/ep_source/Subs-Graphics.php');
		if (createThumbnail($fileOptions['destination'], $fileOptions['resizeWidth'], $fileOptions['resizeHeight']))
		{
			// Strict?
			if (!empty($fileOptions['strict']))
			{
				if (@rename($fileOptions['destination'] . '_thumb', $fileOptions['destination']))
				{
					$destination = $fileOptions['destination'];
					$filename = $fileOptions['name'];
				}
				else
				// Just in case we have trouble renaming the file.
				{
					$destination = $fileOptions['destination'] . '_thumb';
					$filename = $fileOptions['name'] . '_thumb';
				}
			}
			else
			{
				$destination = $fileOptions['destination'] . '_thumb';
				$filename = $fileOptions['name'] . '_thumb';
			}

			// Figure out how big we actually made it.
			$size = @getimagesize($destination);
			list ($thumb_width, $thumb_height) = $size;

			if (!empty($size['mime']))
				$thumb_mime = $size['mime'];
			elseif (isset($validImageTypes[$size[2]]))
				$thumb_mime = 'image/' . $validImageTypes[$size[2]];
			// Lord only knows how this happened...
			else
				$thumb_mime = '';

			$thumb_filename = $filename;
			$thumb_size = filesize($destination);
			$thumb_file_hash = getFilename($thumb_filename, false, null, true);

			if (empty($fileOptions['strict']))
			{
				// To the database we go!
				$smcFunc['db_insert']('',
					'{db_prefix}ep_module_files',
					array(
						'id_param' => 'int', 'id_member' => 'int', 'file_type' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
						'size' => 'int', 'width' => 'int', 'height' => 'int', 'mime_type' => 'string-255',
					),
					array(
						(int) $fileOptions['id_param'], $fileOptions['id_member'], 1, $thumb_filename, $thumb_file_hash, $fileOptions['fileext'],
						$thumb_size, $thumb_width, $thumb_height, $thumb_mime,
					),
					array('id_file')
				);
				$fileOptions['thumb'] = $smcFunc['db_insert_id']('{db_prefix}ep_module_files', 'id_file');

				if (!empty($fileOptions['thumb']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}ep_module_files
						SET id_thumb = {int:id_thumb}
						WHERE id_file = {int:id_file}',
						array(
							'id_thumb' => $fileOptions['thumb'],
							'id_file' => $fileOptions['id'],
						)
					);

					rename($fileOptions['destination'] . '_thumb', getFilename($thumb_filename, $fileOptions['thumb'], $fileOptions['folderpath'], false, $thumb_file_hash));
				}
			}
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}ep_module_files
					SET
						width = {int:width},
						height = {int:height},
						size = {int:size},
						mime_type = {string:mime_type}
					WHERE id_file = {int:id_file}',
					array(
						'width' => (int) $thumb_width,
						'height' => (int) $thumb_height,
						'id_file' => $fileOptions['id'],
						'size' => (int) $thumb_size,
						'mime_type' => $thumb_mime,
					)
				);

				// Attempt to chmod it.
				@chmod($fileOptions['destination'], 0644);
			}
		}
	}
	return true;
}

function envisionBuffer($buffer)
{
	global $portal_ver, $context;

	// Comeon now, does it really make that much of a difference?  Show some Respect please...
	$search_array = array(
		', Simple Machines LLC</a>',
		'class="copywrite"',
	);
	$replace_array = array(
		', Simple Machines LLC</a><br /><a class="new_win" href="http://envisionportal.net/" target="_blank">Envision Portal v' . $portal_ver . ' &copy; 2011 Envision Portal Team</a>',
		'class="copywrite" style="line-height: 1;"',
	);

	if (!empty($context['has_ep_layout']))
	{
		// Prevent the Envision table from overrflowing the SMF theme
		$search_array[] = '<body>';
		$search_array[] = '</body>';

		$replace_array[] = '<body><div id="envision_container">';
		$replace_array[] = '</div></body>';
	}

	return (isset($_REQUEST['xml']) ? $buffer : str_replace($search_array, $replace_array, $buffer));
}

function AllowedFileExtensions($file_mime)
{
	switch ((string) $file_mime)
	{
		case 'x-world/x-3dmf':
			return '3dm, 3dmf, qd3, qd3d';
			break;
		case 'application/octet-stream':
		case 'application/octetstream':
			return 'a, arc, arj, bin, com, dump, exe, lha, lhx, lzh, lzx, o, saveme, uu, zip, zoo';
			break;
		case 'image/psd':
			return 'psd';
			break;
		case 'application/x-authorware-bin':
			return 'aab';
			break;
		case 'application/x-authorware-map':
			return 'aam';
			break;
		case 'application/x-authorware-seg':
			return 'aas';
			break;
		case 'text/vnd.abc':
			return 'abc';
			break;
		case 'text/html':
			return 'acgi, htm, html, htmls, htx, shtml';
			break;
		case 'video/animaflex':
			return 'afl';
			break;
		case 'application/postscript':
			return 'ai, eps, ps';
			break;
		case 'audio/aiff':
		case 'audio/x-aiff':
			return 'aif, aifc, aiff';
			break;
		case 'application/x-aim':
			return 'aim';
			break;
		case 'text/x-audiosoft-intra':
			return 'aip';
			break;
		case 'application/x-navi-animation':
			return 'ani';
			break;
		case 'application/x-nokia-9000-communicator-add-on-software':
			return 'aos';
			break;
		case 'application/mime':
			return 'aps';
			break;
		case 'application/arj':
			return 'arj';
			break;
		case 'image/x-jg':
			return 'art';
			break;
		case 'video/x-ms-asf':
			return 'asf, asx';
			break;
		case 'video/x-ms-asf-plugin':
		case 'application/x-mplayer2':
			return 'asx';
			break;
		case 'text/x-asm':
			return 'asm, s';
			break;
		case 'text/asp':
			return 'asp';
			break;
		case 'audio/basic':
			return 'au, snd';
			break;
		case 'audio/x-au':
			return 'au';
			break;
		case 'application/x-troff-msvideo':
		case 'video/avi':
		case 'video/msvideo':
		case 'video/x-msvideo':
			return 'avi';
			break;
		case 'video/avs-video':
			return 'avs';
			break;
		case 'application/x-bcpio':
			return 'bcpio';
			break;
		case 'application/mac-binary':
		case 'application/macbinary':
		case 'application/x-binary':
		case 'application/x-macbinary':
			return 'bin';
			break;
		case 'image/bmp':
			return 'bm, bmp';
			break;
		case 'image/x-windows-bmp':
			return 'bmp';
			break;
		case 'application/book':
			return 'boo, book';
			break;
		case 'application/x-bzip2':
			return 'boz, bz2';
			break;
		case 'application/x-bsh':
			return 'bsh, sh, shar';
			break;
		case 'application/x-bzip':
			return 'bz';
			break;
		case 'text/plain':
			return 'c, c++, cc, com, conf, cxx, def, f, f90, for, g, h, hh, idc, jav, java, list, log, lst, m, mar, pl, sdml, text, txt';
			break;
		case 'text/x-c':
			return 'c, cc, cpp';
			break;
		case 'application/vnd.ms-pki.seccat':
			return 'cat';
			break;
		case 'application/clariscad':
			return 'ccad';
			break;
		case 'application/x-cocoa':
			return 'cco';
			break;
		case 'application/cdf':
		case 'application/x-cdf':
			return 'cdf';
			break;
		case 'application/x-netcdf':
			return 'cdf, nc';
			break;
		case 'application/x-x509-user-cert':
			return 'crt';
			break;
		case 'application/pkix-cert':
			return 'cer, crt';
			break;
		case 'application/x-x509-ca-cert':
			return 'cer, crt, der';
			break;
		case 'application/x-chat':
			return 'cha, chat';
			break;
		case 'application/java':
		case 'application/java-byte-code':
		case 'application/x-java-class':
			return 'class';
			break;
		case 'application/x-cpio':
			return 'cpio';
			break;
		case 'application/mac-compactpro':
		case 'application/x-compactpro':
		case 'application/x-cpt':
			return 'cpt';
			break;
		case 'application/pkcs-crl':
		case 'application/pkix-crl':
			return 'crl';
			break;
		case 'application/x-csh':
		case 'text/x-script.csh':
			return 'csh';
			break;
		case 'application/x-pointplus':
		case 'text/css':
			return 'css';
			break;
		case 'application/x-director':
			return 'dcr, dir, dxr';
			break;
		case 'application/x-deepv':
			return 'deepv';
			break;
		case 'video/x-dv':
			return 'dif, dv';
			break;
		case 'video/dl':
		case 'video/x-dl':
			return 'dl';
			break;
		case 'application/msword':
			return 'doc, dot, w6w, wiz, word';
			break;
		case 'application/commonground':
			return 'ep';
			break;
		case 'application/drafting':
			return 'drw';
			break;
		case 'application/x-dvi':
			return 'dvi';
			break;
		case 'drawing/x-dwf':
		case 'model/vnd.dwf':
			return 'dwf';
			break;
		case 'application/acad':
			return 'dwg';
			break;
		case 'image/vnd.dwg':
		case 'image/x-dwg':
			return 'dwg, dxf, svf';
			break;
		case 'application/dxf':
			return 'dxf';
			break;
		case 'text/x-script.elisp':
			return 'el';
			break;
		case 'application/x-bytecode.elisp':
		case 'application/x-elc':
			return 'elc';
			break;
		case 'application/x-envoy':
			return 'env, evy';
			break;
		case 'application/x-esrehber':
			return 'es';
			break;
		case 'text/x-setext':
			return 'etx';
			break;
		case 'application/envoy':
			return 'evy';
			break;
		case 'text/x-fortran':
			return 'f77, f90, for, f';
			break;
		case 'application/vnd.fdf':
			return 'fdf';
			break;
		case 'application/fractals':
			return 'fif';
			break;
		case 'image/fif':
			return 'fif';
			break;
		case 'video/fli':
		case 'video/x-fli':
			return 'fli';
			break;
		case 'image/florian':
			return 'flo, turbot';
			break;
		case 'text/vnd.fmi.flexstor':
			return 'flx';
			break;
		case 'video/x-atomic3d-feature':
			return 'fmf';
			break;
		case 'image/vnd.fpx':
		case 'image/vnd.net-fpx':
			return 'fpx';
			break;
		case 'application/freeloader':
			return 'frl';
			break;
		case 'audio/make':
			return 'funk, my, pfunk';
			break;
		case 'image/g3fax':
			return 'g3';
			break;
		case 'image/gif':
			return 'gif';
			break;
		case 'video/gl':
		case 'video/x-gl':
			return 'gl';
			break;
		case 'audio/x-gsm':
			return 'gsd, gsm';
			break;
		case 'application/x-gsp':
			return 'gsp';
			break;
		case 'application/x-gss':
			return 'gss';
			break;
		case 'application/x-gtar':
			return 'gtar';
			break;
		case 'application/x-compressed':
			return 'gz, tgz, z, zip';
			break;
		case 'application/x-gzip':
			return 'gz, gzip';
			break;
		case 'multipart/x-gzip':
			return 'gzip';
			break;
		case 'text/x-h':
			return 'h, hh';
			break;
		case 'application/x-hdf':
			return 'hdf';
			break;
		case 'application/x-helpfile':
			return 'help, hlp';
			break;
		case 'application/vnd.hp-hpgl':
			return 'hgl, hpg, hpgl';
			break;
		case 'text/x-script':
			return 'hlb';
			break;
		case 'application/hlp':
		case 'application/x-winhelp':
			return 'hlp';
			break;
		case 'application/binhex':
		case 'application/binhex4':
		case 'application/mac-binhex':
		case 'application/mac-binhex40':
		case 'application/x-binhex40':
		case 'application/x-mac-binhex40':
			return 'hqx';
			break;
		case 'application/hta':
			return 'hta';
			break;
		case 'text/x-component':
			return 'htc';
			break;
		case 'text/webviewhtml':
			return 'htt';
			break;
		case 'x-conference/x-cooltalk':
			return 'ice';
			break;
		case 'image/x-icon':
			return 'ico';
			break;
		case 'image/ief':
			return 'ief, iefs';
			break;
		case 'application/iges':
		case 'model/iges':
			return 'iges, igs';
			break;
		case 'application/x-ima':
			return 'ima';
			break;
		case 'application/x-httpd-imap':
			return 'imap';
			break;
		case 'application/inf':
			return 'inf';
			break;
		case 'application/x-internett-signup':
			return 'ins';
			break;
		case 'application/x-ip2':
			return 'ip';
			break;
		case 'video/x-isvideo':
			return 'isu';
			break;
		case 'audio/it':
			return 'it';
			break;
		case 'application/x-inventor':
			return 'iv';
			break;
		case 'i-world/i-vrml':
			return 'ivr';
			break;
		case 'application/x-livescreen':
			return 'ivy';
			break;
		case 'audio/x-jam':
			return 'jam';
			break;
		case 'text/x-java-source':
			return 'jav, java';
			break;
		case 'application/x-java-commerce':
			return 'jcm';
			break;
		case 'image/jpeg':
			return 'jfif, jfif-tbnl, jpe, jpeg, jpg';
			break;
		case 'image/pjpeg':
			return 'jfif, jpe, jpeg, jpg';
			break;
		case 'image/x-jps':
			return 'jps';
			break;
		case 'application/x-javascript':
			return 'js';
			break;
		case 'image/jutvision':
			return 'jut';
			break;
		case 'audio/midi':
			return 'kar, mid, midi';
			break;
		case 'music/x-karaoke':
			return 'kar';
			break;
		case 'application/x-ksh':
		case 'text/x-script.ksh':
			return 'ksh';
			break;
		case 'audio/nspaudio':
		case 'audio/x-nspaudio':
			return 'la, lma';
			break;
		case 'audio/x-liveaudio':
			return 'lam';
			break;
		case 'application/x-latex':
			return 'latex, ltx';
			break;
		case 'application/lha':
		case 'application/x-lha':
			return 'lha';
			break;
		case 'application/x-lisp':
		case 'text/x-script.lisp':
			return 'lsp';
			break;
		case 'text/x-la-asf':
			return 'lsx';
			break;
		case 'application/x-lzh':
			return 'lzh';
			break;
		case 'application/lzx':
		case 'application/x-lzx':
			return 'lzx';
			break;
		case 'text/x-m':
			return 'm';
			break;
		case 'video/mpeg':
			return 'm1v, m2v, mp2, mp3, mpa, mpe, mpeg, mpg';
			break;
		case 'audio/mpeg':
			return 'm2a, mp2, mp3, mpa, mpg, mpga';
			break;
		case 'audio/x-mpequrl':
			return 'm3u';
			break;
		case 'application/x-troff-man':
			return 'man';
			break;
		case 'application/x-navimap':
			return 'map';
			break;
		case 'application/mbedlet':
			return 'mbd';
			break;
		case 'application/x-magic-cap-package-1.0':
			return 'mc$';
			break;
		case 'application/mcad':
		case 'application/x-mathcad':
			return 'mcd';
			break;
		case 'image/vasa':
		case 'text/mcf':
			return 'mcf';
			break;
		case 'application/netmc':
			return 'mcp';
			break;
		case 'application/x-troff-me':
			return 'me';
			break;
		case 'message/rfc822':
			return 'mht, mhtml, mime';
			break;
		case 'application/x-midi':
		case 'audio/x-mid':
		case 'audio/x-midi':
		case 'music/crescendo':
		case 'x-music/x-midi':
			return 'mid, midi';
			break;
		case 'application/x-frame':
		case 'application/x-mif':
			return 'mif';
			break;
		case 'www/mime':
			return 'mime';
			break;
		case 'audio/x-vnd.audioexplosion.mjuicemediafile':
			return 'mjf';
			break;
		case 'video/x-motion-jpeg':
			return 'mjpg';
			break;
		case 'application/base64':
			return 'mm, mme';
			break;
		case 'application/x-meme':
			return 'mm';
			break;
		case 'audio/mod':
		case 'audio/x-mod':
			return 'mod';
			break;
		case 'video/quicktime':
			return 'moov, mov, qt';
			break;
		case 'video/x-sgi-movie':
			return 'movie, mv';
			break;
		case 'audio/x-mpeg':
		case 'video/x-mpeq2a':
			return 'mp2';
			break;
		case 'video/x-mpeg':
			return 'mp2, mp3';
			break;
		case 'audio/mpeg3':
		case 'audio/x-mpeg-3':
			return 'mp3';
			break;
		case 'application/x-project':
			return 'mpc, mpt, mpv, mpx';
			break;
		case 'application/vnd.ms-project':
			return 'mpp';
			break;
		case 'application/marc':
			return 'mrc';
			break;
		case 'application/x-troff-ms':
			return 'ms';
			break;
		case 'application/x-vnd.audioexplosion.mzz':
			return 'mzz';
			break;
		case 'image/naplps':
			return 'nap, naplps';
			break;
		case 'application/vnd.nokia.configuration-message':
			return 'ncm';
			break;
		case 'image/x-niff':
			return 'nif, niff';
			break;
		case 'application/x-mix-transfer':
			return 'nix';
			break;
		case 'application/x-conference':
			return 'nsc';
			break;
		case 'application/x-navidoc':
			return 'nvd';
			break;
		case 'application/oda':
			return 'oda';
			break;
		case 'application/x-omc':
			return 'omc';
			break;
		case 'application/x-omcdatamaker':
			return 'omcd';
			break;
		case 'application/x-omcregerator':
			return 'omcr';
			break;
		case 'text/x-pascal':
			return 'p';
			break;
		case 'application/pkcs10':
		case 'application/x-pkcs10':
			return 'p10';
			break;
		case 'application/pkcs-12':
		case 'application/x-pkcs12':
			return 'p12';
			break;
		case 'application/x-pkcs7-signature':
			return 'p7a';
			break;
		case 'application/pkcs7-mime':
		case 'application/x-pkcs7-mime':
			return 'p7c, p7m';
			break;
		case 'application/x-pkcs7-certreqresp':
			return 'p7r';
			break;
		case 'application/pkcs7-signature':
			return 'p7s';
			break;
		case 'application/pro_eng':
			return 'part, prt';
			break;
		case 'text/pascal':
			return 'pas';
			break;
		case 'image/x-portable-bitmap':
			return 'pbm';
			break;
		case 'application/vnd.hp-pcl':
		case 'application/x-pcl':
			return 'pcl';
			break;
		case 'image/x-pict':
			return 'pct';
			break;
		case 'image/x-pcx':
			return 'pcx';
			break;
		case 'chemical/x-pdb':
			return 'pdb, xyz';
			break;
		case 'application/pdf':
			return 'pdf';
			break;
		case 'audio/make.my.funk':
			return 'pfunk';
			break;
		case 'image/x-portable-graymap':
		case 'image/x-portable-greymap':
			return 'pgm';
			break;
		case 'application/x-httpd-php':
			return 'php';
			break;
		case 'image/pict':
			return 'pic, pict';
			break;
		case 'application/x-newton-compatible-pkg':
			return 'pkg';
			break;
		case 'application/vnd.ms-pki.pko':
			return 'pko';
			break;
		case 'text/x-script.perl':
			return 'pl';
			break;
		case 'application/x-pixclscript':
			return 'plx';
			break;
		case 'image/x-xpixmap':
			return 'pm, xpm';
			break;
		case 'text/x-script.perl-module':
			return 'pm';
			break;
		case 'application/x-pagemaker':
			return 'pm4, pm5';
			break;
		case 'image/png':
			return 'png, x-png';
			break;
		case 'application/x-portable-anymap':
		case 'image/x-portable-anymap':
			return 'pnm';
			break;
		case 'application/mspowerpoint':
			return 'pot, pps, ppt, ppz';
			break;
		case 'application/vnd.ms-powerpoint':
			return 'pot, ppa, pps, ppt, pwz';
			break;
		case 'model/x-pov':
			return 'pov';
			break;
		case 'image/x-portable-pixmap':
			return 'ppm';
			break;
		case 'application/powerpoint':
		case 'application/x-mspowerpoint':
			return 'ppt';
			break;
		case 'application/x-freelance':
			return 'pre';
			break;
		case 'paleovu/x-pv':
			return 'pvu';
			break;
		case 'text/x-script.phyton':
			return 'py';
			break;
		case 'application/x-bytecode.python':
			return 'pyc';
			break;
		case 'audio/vnd.qcelp':
			return 'qcp';
			break;
		case 'image/x-quicktime':
			return 'qif, qti, qtif';
			break;
		case 'video/x-qtc':
			return 'qtc';
			break;
		case 'audio/x-pn-realaudio':
			return 'ra, ram, rm, rmm, rmp';
			break;
		case 'audio/x-pn-realaudio-plugin':
			return 'ra, rmp, rpm';
			break;
		case 'audio/x-realaudio':
			return 'ra';
			break;
		case 'application/x-cmu-raster':
		case 'image/x-cmu-raster':
			return 'ras';
			break;
		case 'image/cmu-raster':
			return 'ras, rast';
			break;
		case 'text/x-script.rexx':
			return 'rexx';
			break;
		case 'image/vnd.rn-realflash':
			return 'rf';
			break;
		case 'image/x-rgb':
			return 'rgb';
			break;
		case 'application/vnd.rn-realmedia':
			return 'rm';
			break;
		case 'audio/mid':
			return 'rmi';
			break;
		case 'application/ringing-tones':
		case 'application/vnd.nokia.ringing-tone':
			return 'rng';
			break;
		case 'application/vnd.rn-realplayer':
			return 'rnx';
			break;
		case 'application/x-troff':
			return 'roff, t, tr';
			break;
		case 'image/vnd.rn-realpix':
			return 'rp';
			break;
		case 'text/richtext':
			return 'rt, rtf, rtx';
			break;
		case 'text/vnd.rn-realtext':
			return 'rt';
			break;
		case 'application/rtf':
			return 'rtf, rtx';
			break;
		case 'application/x-rtf':
			return 'rtf';
			break;
		case 'video/vnd.rn-realvideo':
			return 'rv';
			break;
		case 'audio/s3m':
			return 's3m';
			break;
		case 'application/x-tbook':
			return 'sbk, tbk';
			break;
		case 'application/x-lotusscreencam':
		case 'text/x-script.guile':
		case 'text/x-script.scheme':
		case 'video/x-scm':
			return 'scm';
			break;
		case 'application/sep':
		case 'application/x-sep':
			return 'sep';
			break;
		case 'application/sounder':
			return 'sdr';
			break;
		case 'application/sea':
		case 'application/x-sea':
			return 'sea';
			break;
		case 'application/set':
			return 'set';
			break;
		case 'text/sgml':
		case 'text/x-sgml':
			return 'sgm, sgml';
			break;
		case 'application/x-sh':
		case 'text/x-script.sh':
			return 'sh';
			break;
		case 'application/x-shar':
			return 'sh, shar';
			break;
		case 'text/x-server-parsed-html':
			return 'shtml, ssi';
			break;
		case 'audio/x-psid':
			return 'sid';
			break;
		case 'application/x-sit':
		case 'application/x-stuffit':
			return 'sit';
			break;
		case 'application/x-koan':
			return 'skd, skm, skp, skt';
			break;
		case 'application/x-seelogo':
			return 'sl';
			break;
		case 'application/smil':
			return 'smi, smil';
			break;
		case 'audio/x-aepcm':
			return 'snd';
			break;
		case 'application/solids':
			return 'sol';
			break;
		case 'application/x-pkcs7-certificates':
			return 'spc';
			break;
		case 'text/x-speech':
			return 'spc, talk';
			break;
		case 'application/futuresplash':
			return 'spl';
			break;
		case 'application/x-sprite':
			return 'spr, sprite';
			break;
		case 'application/x-wais-source':
			return 'src, wsrc';
			break;
		case 'application/streamingmedia':
			return 'ssm';
			break;
		case 'application/vnd.ms-pki.certstore':
			return 'sst';
			break;
		case 'application/step':
			return 'step, stp';
			break;
		case 'application/sla':
		case 'application/vnd.ms-pki.stl':
		case 'application/x-navistyle':
			return 'stl';
			break;
		case 'application/x-sv4cpio':
			return 'sv4cpio';
			break;
		case 'application/x-sv4crc':
			return 'sv4crc';
			break;
		case 'application/x-world':
			return 'svr, wrl';
			break;
		case 'x-world/x-svr':
			return 'svr';
			break;
		case 'application/x-shockwave-flash':
			return 'swf';
			break;
		case 'application/x-tar':
			return 'tar';
			break;
		case 'application/toolbook':
			return 'tbk';
			break;
		case 'application/x-tcl':
		case 'text/x-script.tcl':
			return 'tcl';
			break;
		case 'text/x-script.tcsh':
			return 'tcsh';
			break;
		case 'application/x-tex':
			return 'tex';
			break;
		case 'application/x-texinfo':
			return 'texi, texinfo';
			break;
		case 'application/plain':
			return 'text';
			break;
		case 'application/gnutar':
			return 'tgz';
			break;
		case 'image/tiff':
		case 'image/x-tiff':
			return 'tif, tiff';
			break;
		case 'audio/tsp-audio':
			return 'tsi';
			break;
		case 'application/dsptype':
		case 'audio/tsplayer':
			return 'tsp';
			break;
		case 'text/tab-separated-values':
			return 'tsv';
			break;
		case 'text/x-uil':
			return 'uil';
			break;
		case 'text/uri-list':
			return 'uni, unis, uri, uris';
			break;
		case 'application/i-deas':
			return 'unv';
			break;
		case 'application/x-ustar':
		case 'multipart/x-ustar':
			return 'ustar';
			break;
		case 'text/x-uuencode':
			return 'uu, uue';
			break;
		case 'application/x-cdlink':
			return 'vcd';
			break;
		case 'text/x-vcalendar':
			return 'vcs';
			break;
		case 'application/vda':
			return 'vda';
			break;
		case 'video/vdo':
			return 'vdo';
			break;
		case 'application/groupwise':
			return 'vew';
			break;
		case 'video/vivo':
		case 'video/vnd.vivo':
			return 'viv, vivo';
			break;
		case 'application/vocaltec-media-desc':
			return 'vmd';
			break;
		case 'application/vocaltec-media-file':
			return 'vmf';
			break;
		case 'audio/voc':
		case 'audio/x-voc':
			return 'voc';
			break;
		case 'video/vosaic':
			return 'vos';
			break;
		case 'audio/voxware':
			return 'vox';
			break;
		case 'audio/x-twinvq-plugin':
			return 'vqe, vql';
			break;
		case 'audio/x-twinvq':
			return 'vqf';
			break;
		case 'application/x-vrml':
			return 'vrml';
			break;
		case 'model/vrml':
		case 'x-world/x-vrml':
			return 'vrml, wrl, wrz';
			break;
		case 'x-world/x-vrt':
			return 'vrt';
			break;
		case 'application/x-visio':
			return 'vsd, vst, vsw';
			break;
		case 'application/woreperfect6.0':
			return 'w60, wp5';
			break;
		case 'application/woreperfect6.1':
			return 'w61';
			break;
		case 'audio/wav':
		case 'audio/x-wav':
			return 'wav';
			break;
		case 'application/x-qpro':
			return 'wb1';
			break;
		case 'image/vnd.wap.wbmp':
			return 'wbmp';
			break;
		case 'application/vnd.xara':
			return 'web';
			break;
		case 'application/x-123':
			return 'wk1';
			break;
		case 'windows/metafile':
			return 'wmf';
			break;
		case 'text/vnd.wap.wml':
			return 'wml';
			break;
		case 'application/vnd.wap.wmlc':
			return 'wmlc';
			break;
		case 'text/vnd.wap.wmlscript':
			return 'wmls';
			break;
		case 'application/vnd.wap.wmlscriptc':
			return 'wmlsc';
			break;
		case 'application/woreperfect':
			return 'wp, wp5, wp6, wpd';
			break;
		case 'application/x-wpwin':
			return 'wpd';
			break;
		case 'application/x-lotus':
			return 'wq1';
			break;
		case 'application/mswrite':
		case 'application/x-wri':
			return 'wri';
			break;
		case 'text/scriplet':
			return 'wsc';
			break;
		case 'application/x-wintalk':
			return 'wtk';
			break;
		case 'image/x-xbitmap':
		case 'image/x-xbm':
		case 'image/xbm':
			return 'xbm';
			break;
		case 'video/x-amt-demorun':
			return 'xdr';
			break;
		case 'xgl/drawing':
			return 'xgz';
			break;
		case 'image/vnd.xiff':
			return 'xif';
			break;
		case 'application/excel':
			return 'xl, xla, xlb, xlc, xld, xlk, xll, xlm, xls, xlt, xlv, xlw';
			break;
		case 'application/x-excel':
			return 'xla, xlb, xlc, xld, xlk, xll, xlm, xls, xlt, xlv, xlw';
			break;
		case 'application/x-msexcel':
			return 'xla, xls, xlw';
			break;
		case 'application/vnd.ms-excel':
			return 'xlb, xlc, xll, xlm, xls, xlw';
			break;
		case 'audio/xm':
			return 'xm';
			break;
		case 'application/xml':
		case 'text/xml':
			return 'xml';
			break;
		case 'xgl/movie':
			return 'xmz';
			break;
		case 'application/x-vnd.ls-xpix':
			return 'xpix';
			break;
		case 'image/xpm':
			return 'xpm';
			break;
		case 'video/x-amt-showrun':
			return 'xsr';
			break;
		case 'image/x-xwd':
		case 'image/x-xwindowdump':
			return 'xwd';
			break;
		case 'application/x-compress':
			return 'z';
			break;
		case 'application/x-zip-compressed':
		case 'application/zip':
		case 'multipart/x-zip':
			return 'zip';
			break;
		case 'text/x-script.zsh':
			return 'zsh';
			break;
		default:
			return '';
			break;
	}
}

function getFilename($filename, $file_id, $path, $new = false, $file_hash = '')
{
	global $modSettings, $smcFunc;

	// Just make up a nice hash...
	if ($new)
		return sha1(md5($filename . time()) . mt_rand());

	// Grab the file hash if it wasn't added.
	if ($file_hash === '')
	{
		$request = $smcFunc['db_query']('', '
			SELECT file_hash
			FROM {db_prefix}ep_module_files
			WHERE id_file = {int:id_file}',
			array(
				'id_file' => (int) $file_id,
		));

		if ($smcFunc['db_num_rows']($request) === 0)
			return false;

		list ($file_hash) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	// Files from the old system, do a legacy call.
	if (empty($file_hash))
		return getLegacyFilename($filename, $file_id, $path, $new);

	return $path . '/' . $file_id . '_' . $file_hash;
}

function getLegacyFilename($filename, $file_id, $path, $new = false)
{
	global $modSettings;

	// Remove special accented characters - ie. sí.
	$clean_name = strtr($filename, 'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ', 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy');
	$clean_name = strtr($clean_name, array('Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u'));

	// Sorry, no spaces, dots, or anything else but letters allowed.
	$clean_name = preg_replace(array('/\s/', '/[^\w_\.\-]/'), array('_', ''), $clean_name);

	$enc_name = $file_id . '_' . strtr($clean_name, '.', '_') . md5($clean_name);
	$clean_name = preg_replace('~\.[\.]+~', '.', $clean_name);

	if ($new)
		return $enc_name;

	if (file_exists($path . '/' . $enc_name))
		$filename = $path . '/' . $enc_name;
	else
		$filename = $path . '/' . $clean_name;

	return $filename;
}

function GetEnvisionModuleInfo($scripts, $mod_functions, $dirname, $file, $name = '', $install = false)
{
	global $boarddir, $context, $modSettings, $scripturl, $smcFunc;

	// Are we allowed to use this name?
	if (in_array($file, $context['ep_restricted_names'])) return false;

	// Optional check: does it exist? (Mainly for installation)
	if (!empty($name) && $name != $file) return false;

	// If the required info file does not exist let's silently die...
	if (!file_exists($dirname . '/' . $file . '/info.xml')) return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($dirname . '/' . $file . '/info.xml');

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$module_info1 = new xmlArray($file_info);
	$module_info1 = $module_info1->path('module[0]');

	// Required XML elements and attributes. Quit if any one is missing.
	if (!$module_info1->exists('title')) return false;
	if (!$module_info1->exists('description')) return false;

	if ($module_info1->exists('target'))
	{
		switch ($module_info1->fetch('target'))
		{
			case '_self':
				$module_info2 = 1;
			case '_parent':
				$module_info2 = 2;
			case '_top':
				$module_info2 = 3;
			case '_blank':
				$module_info2 = 0;
			default:
				$module_info2 = 0;
		}
	}
	else
		$module_info2 = 0;

	$other_functions = array();
	$all_files = array();
	$all_functions = array();
	$main_function = array();

	// Getting all functions and files.
	if ($module_info1->exists('file'))
	{
		$filetag = $module_info1->set('file');

		foreach ($filetag as $modfiles => $filepath)
		{
			if ($filepath->exists('function'))
			{
				$functag = $filepath->set('function');

				foreach($functag as $func => $function)
				{
					if ($function->exists('main'))
						$main_function[] = $function->fetch('main');
					else
						$other_functions[] = $function->fetch('');
				}
			}
			else
				return false;

			// Now grabbing all filepaths for each file.
			if ($filepath->exists('@path'))
				$all_files[] = $filepath->fetch('@path');
			else
				return false;
		}

		$all_functions = array_merge($main_function, $other_functions);
	}

	// And now for the parameters. Remember, they are optional!
	$param_array = array();
	if ($module_info1->exists('param'))
	{
		$params = $module_info1->set('param');
		foreach ($params as $name => $param)
		{
			if ($param->exists('@name') && $param->exists('@type'))
				$param_array[$param->fetch('@name')] = array(
					'type' => $param->fetch('@type'),
					'value' => $param->fetch('.'),
				);
		}
	}

	// Grabbing it from the database here.
	if (!empty($scripts) && !empty($mod_functions))
	{
		return array(
			'title' => $module_info1->fetch('title'),
			'files' => $scripts,
			'target' => $module_info2,
			'icon' => ($module_info1->exists('icon') ? $name . '/' . $module_info1->fetch('icon') : ''),
			'title_link' => ($module_info1->exists('url') ? $module_info1->fetch('url') : ''),
			'functions' => $mod_functions,
			'params' => $param_array,
		);
	}
	else
	{
		return array(
			'title' => $module_info1->fetch('title'),
			'description' => ($module_info1->exists('description/@parsebbc')) ? ($module_info1->fetch('description/@parsebbc') ? parse_bbc($module_info1->fetch('description')) : $module_info1->fetch('description')) : $module_info1->fetch('description'),
			'desc_parse_bbc' => ($module_info1->exists('description/@parsebbc') ? $module_info1->fetch('description/@parsebbc') : false),
			'delete_link' => $scripturl . '?action=admin;area=epmodules;sa=epdeletemodule;name=' . $file . ';' . $context['session_var'] . '=' . $context['session_id'],
			'install_link' => $scripturl . '?action=admin;area=epmodules;sa=epinstallmodule;name=' . $file . ';' . $context['session_var'] . '=' . $context['session_id'],
			'icon_link' => ($module_info1->exists('icon') ? $boarddir . '/' . $modSettings['ep_icon_directory'] . '/' . $module_info1->fetch('icon') : ''),
			'icon' => ($module_info1->exists('icon') ? $module_info1->fetch('icon') : ''),
			'target' => $module_info2,
			'target_english' => ($module_info1->exists('target') ? $module_info1->fetch('target') : ''),
			'files' => count($all_files) == 1 ? $all_files[0] : implode('+', $all_files),
			'functions' => implode('+', $all_functions),
			'title_link' => ($module_info1->exists('url') ? $module_info1->fetch('url') : ''),
			'version' => ($module_info1->exists('version') ? $module_info1->fetch('version') : ''),
			'author' => ($module_info1->exists('author') ? $module_info1->fetch('author') : ''),
			'author_link' => ($module_info1->exists('author/@url') ? $module_info1->fetch('author/@url') : ''),
			'params' => $param_array,
		);
	}
}

function GetEnvisionAddedModules()
{
	global $boarddir, $context, $modSettings, $sourcedir, $scripturl, $smcFunc, $txt;

	// We want to define our variables now...
	$AvailableModules = array();

	$added_mods = array();

	// Let's loop throuugh each folder and get their module data. If anything goes wrong we shall skip it.
	// !!! Use scandir()... don't tell me we're supporting PHP4!
	if ($dir = @opendir($context['epmod_modules_dir']))
	{
		$dirs = array();
		while ($file = readdir($dir))
		{
			$retVal = GetEnvisionModuleInfo('', '', $context['epmod_modules_dir'], $file, false);
			if ($retVal === false)
				continue;
			else
			{
				$added_mods[] = $file;
				$module_info[$file] = $retVal;
			}
		}
	}

	if (isset($module_info))
	{
		// Find out if any of these are installed.
		$request = $smcFunc['db_query']('', '
			SELECT id_module, name
			FROM {db_prefix}ep_modules
			WHERE name IN ({array_string:module_names})',
			array(
				'module_names' => $added_mods,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($info[$row['name']]))
			{
				// It's installed, so remove the install link, and add uninstall and settings links.
				unset($module_info[$row['name']]['install_link']);
				$module_info[$row['name']] += array(
					'uninstall_link' => $scripturl . '?action=admin;area=epmodules;sa=epuninstallmodule;name=' . $row['name'] . ';' . $context['session_var'] . '=' . $context['session_id'],
					'settings_link' => $scripturl . '?action=admin;area=epmodules;sa=modifymod;modid=' . $row['id_module'] . ';' . $context['session_var'] . '=' . $context['session_id'],
				);
			}
		}

		return $module_info;
	}
	else
		return array();
}

function GetEnvisionInstalledModules($installed_mods = array())
{
	global $smcFunc, $user_info, $context, $txt;

	// We'll need to build up a list of modules that are installed.
	if (count($installed_mods) < 1)
	{
		$installed_mods = array();
		// Let's collect all installed modules...
		$request = $smcFunc['db_query']('', '
			SELECT name, files, functions
			FROM {db_prefix}ep_modules
			WHERE files != {string:empty_string} AND functions != {string:empty_string}',
			array(
				'empty_string' => '',
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$installed_mods[] = array(
				'name' => $row['name'],
				'files' => $row['files'],
				'functions' => $row['functions'],
			);
		}

		$smcFunc['db_free_result']($request);
	}

	foreach($installed_mods as $installed)
	{
		$retVal = GetEnvisionModuleInfo($installed['files'], $installed['functions'], $context['epmod_modules_dir'], $installed['name'], $installed['name']);
		if ($retVal === false)
			continue;

		$module_info[$installed['name']] = $retVal;
	}

	return isset($module_info) ? $module_info : array();
}

function ep_insert_column()
{
	global $smcFunc, $context;

	isAllowedTo('admin_forum');

	$sdata = explode('_', $_GET['insert']);

	$columns = array(
		'id_layout' => 'int',
		'column' => 'string',
		'row' => 'string',
		'enabled' => 'int',
	);

	$data = array(
		$_GET['layout'],
		$sdata[1] . ':0',
		$sdata[0] . ':0',
		-2,
	);

	$keys = array(
		'id_layout_position',
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);

	$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layout_positions', 'id_layout_position');

	loadTemplate('ep_template/Xml');
	$context['sub_template'] = 'generic_xml';
	$xml_data = array(
		'items' => array(
			'identifier' => 'item',
			'children' => array(
				array(
					'attributes' => array(
						'insertid' => $iid,
					),
					'value' => $_GET['insert'] . '_' . $iid,
				),
			),
		),
	);
	$context['xml_data'] = $xml_data;
}

function ep_edit_db_select()
{
	global $smcFunc, $context;

	isAllowedTo('admin_forum');

	// Make sure we have a valid parameter ID of the right type.
	$request = $smcFunc['db_query']('', '
		SELECT
			dmp.value
		FROM {db_prefix}ep_module_parameters AS dmp
		WHERE dmp.id_param = {int:config_id} AND dmp.type = {string:type}',
		array(
			'config_id' => $_POST['config_id'],
			'type' => 'db_select',
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);

	$db_options = explode(':', $row['value']);
	$db_select_options = explode(';', $row['value']);
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

	if (isset($_POST['data']))
	{
		$key = explode('_', $_POST['key']);

		$smcFunc['db_query']('', '
			UPDATE ' . $db_select['table'] . '
			SET {raw:query_select} = {string:data}
			WHERE {raw:key_select} = {string:key}',
			array(
				'data' => $_POST['data'],
				'key' => $key[count($key) - 1],
				'query_select' =>  $db_select['select1'],
				'key_select' =>  $db_select['select2'],
			)
		);

		die();
	}
	else
	{
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

		$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layout_positions', 'id_layout_position');

		loadTemplate('ep_template/Xml');
		$context['sub_template'] = 'generic_xml';
		$xml_data = array(
			'items' => array(
				'identifier' => 'item',
				'children' => array(
					array(
						'value' => $_GET['insert'] . '_' . $iid,
					),
				),
			),
		);
		$context['xml_data'] = $xml_data;
	}

}

function loadLayout($url)
{
	global $smcFunc, $context, $scripturl, $txt, $user_info;

	$match = (!empty($_REQUEST['board']) ? '[board]=' . $_REQUEST['board'] : (!empty($_REQUEST['topic']) ? '[topic]=' . (int)$_REQUEST['topic'] : (!empty($_REQUEST['page']) ? '[page]=' . $_REQUEST['page'] : $url)));
	$general_match = (!empty($_REQUEST['board']) ? '[board]' : (!empty($_REQUEST['topic']) ? '[topic]' : (!empty($_REQUEST['page']) ? '[page]' : (!empty($_REQUEST['action']) ? '[all_actions]' : ''))));

	$request = $smcFunc['db_query']('', '
		SELECT
			dl.id_layout
		FROM {db_prefix}ep_layouts AS dl
			LEFT JOIN {db_prefix}ep_groups AS dg ON (dg.active = {int:one} AND dg.id_member = {int:zero})
		WHERE dl.id_group = dg.id_group AND FIND_IN_SET({string:current_action}, dl.actions)',
		array(
			'current_action' => $match,
			'one' => 1,
			'zero' => 0,
		)
	);

	$num2 = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	if (empty($num2))
		$match = $general_match;

	// If this is empty, e.g. index.php?action or index.php?action=
	if (empty($match))
	{
		$match = '[home]';
		$context['ep_home'] = true;
	}

	// Let's grab the data necessary to show the correct layout!
	$request = $smcFunc['db_query']('', '
		SELECT
			dm.id_module AS id_mod, dm.name AS mod_name, dm.title AS mod_title, dlp.column, dlp.row,
			dm.title_link AS mod_title_link, dm.target AS mod_target, dm.icon AS mod_icon, dm.files AS mod_files, dm.functions AS mod_functions, dm.header_display AS mod_header_display, dm.template AS mod_template, dm.groups AS mod_groups,
			dmp.position, dlp.enabled, dl.actions, dmp.id_position, dlp.id_layout_position, dmc.id_clone,
			dmc.title_link AS clone_title_link, dmc.target AS clone_target, dmc.icon AS clone_icon, dmc.files AS clone_files, dmc.functions AS clone_functions, dmc.header_display AS clone_header_display, dmc.template AS clone_template, dmc.groups AS clone_groups,
			dmc.name AS clone_name, dmc.title AS clone_title, dmc.is_clone, dmp2.id_param, dmp2.name AS pName, dmp2.type, dmp2.value
			FROM {db_prefix}ep_groups AS dg, {db_prefix}ep_layouts AS dl
				JOIN {db_prefix}ep_layout_positions AS dlp ON (dlp.id_layout = dl.id_layout AND dlp.enabled NOT IN({array_int:invisible_layouts}))
				JOIN {db_prefix}ep_module_positions AS dmp ON (dmp.id_layout_position = dlp.id_layout_position)
				LEFT JOIN {db_prefix}ep_module_clones AS dmc ON (dmc.id_member = {int:zero} AND dmc.id_clone = dmp.id_clone)
				LEFT JOIN {db_prefix}ep_modules AS dm ON (dm.id_module = dmp.id_module)
				LEFT JOIN {db_prefix}ep_module_parameters AS dmp2 ON ((dmp2.id_module = dm.id_module AND dmp2.id_clone = {int:zero}) OR dmp2.id_clone = dmc.id_clone)
			WHERE
				dg.id_member = {int:zero} AND dg.active = {int:one} AND dl.id_group = dg.id_group AND FIND_IN_SET({string:current_action}, dl.actions)',
		array(
			'one' => 1,
			'zero' => 0,
			'invisible_layouts' => array(-1, -2),
			'current_action' => $match,
		)
	);

	$num = $smcFunc['db_num_rows']($request);
	if (empty($num))
		return;

	$old_row = 0;
	$view_groups = array();

	// Let the theme know we have a layout.
	$context['has_ep_layout'] = true;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$type = !empty($row['id_clone']) ? 'clone' : 'mod';
		$is_clone = !empty($row['is_clone']) && !empty($row['id_clone']);

		$smf = (int) $row['id_clone'] + (int) $row['id_mod'];
		$smf_col = empty($smf) && !is_null($row['id_position']);

		if (!$smf_col && $row['enabled'] == 0)
			continue;

		// Who can view it?
		$view_groups = isset($row[$type.'_groups']) && $row[$type.'_groups'] != '' ? explode(',', $row[$type.'_groups']) : array();

		// -3 is for everybody...
		if (in_array('-3', $view_groups))
			$view_groups = $user_info['groups'];

		// Match the current group(s) with the parameter to determine if the user may access this, Admins can select not to view Modules also here.
		$view_groups = array_intersect($user_info['groups'], $view_groups);

		// Shucks, you can't view it
		if (!$view_groups && !$smf_col)
			continue;

		$current_row = explode(':', $row['row']);
		$current_column = explode(':', $row['column']);
		if (!isset($ep_modules[$current_row[0]][$current_column[0]]) && !empty($row['id_layout_position']))
			$ep_modules[$current_row[0]][$current_column[0]] = array(
				'is_smf' => $smf_col,
				'id_layout_position' => $row['id_layout_position'],
				'html' => ($current_column[1] >= 2 ? ' colspan="' . $current_column[1] . '"' : '') . ($current_row[1] >= 2 ? ' rowspan="' . $current_row[1] . '"' : '') . ($current_column[1] <= 1 && $context['ep_home'] && $current_column[0] != 1 || !$context['ep_home'] && $current_column[1] <= 1 && !$smf_col ? ' style="width: 200px;"' : ''),
				'enabled' => $row['enabled'],
				'disabled_module_container' => $row['enabled'] == -1,
			);

		if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
		{
			// Store $context variables for each module.  Mod Authors can use these for unique ID values, function names, etc.
			if (!isset($ep_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]))
			{
				if (empty($context['ep_mod_' . $row[$type . '_name']]))
					$context['ep_mod_' . $row[$type . '_name']] = array();

				$context['ep_mod_' . $row[$type . '_name']][] = $row[$type . '_name'] . '_' . $type . '_' . $row['id_' . $type];
			}

			$ep_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']] = array(
				'is_smf' => empty($smf),							// Returns true or false; Is this the mighty SMF that we should bow down to? :P
				'is_clone' => $is_clone,							// Returns true or false; determines if it really is a clone or not.
				'modify_link' => $user_info['is_admin'] ? ' [<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modifymod;' . (isset($row['id_clone']) ? 'module=' . $row['id_clone'] : 'modid=' . $row['id_mod']) . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['modify'] . '</a>]' : '',
				'type' => $type,									// Returns either 'mod' or 'clone'.
				'id' => $row['id_position'],						// The unique position ID of the clone/module.
				'id_position' => $row['id_position'],				// The unique position ID of the clone/module.
				'name' => $row[$type.'_name'],						// Name of clone or module.
				'title' => $row[$type.'_title'],					// Title of clone/module on titlebar.
				'title_link' => $row[$type.'_title_link'],			// Link associated with the title.
				'target' => $row[$type.'_target'],					// Target of clone/module (int value).
				'icon' => $row[$type.'_icon'],						// Icon associated with the module/clone.
				'files' => $row[$type.'_files'],					// File, if any, for the function of that module/clone.
				'functions' => $row[$type.'_functions'],			// Any functions for that module/clone.
				'header_display' => $row[$type.'_header_display'],	// Shall we show the title?
				'template' => $row[$type.'_template'],				// Which template to use?
				'groups' => !empty($row[$type.'_groups']) ? explode(',', $row[$type.'_groups']) : array(),		// The membergroups that can view this.
			);

			$params[$row['id_position']][] = array(
				'id' => $row['id_' . $type],
				'file_input' => (strtolower($row['type']) == 'file_input' ? array('id_param' => $row['id_param'], 'id' => $row['id_' . $type], 'is_clone' => !empty($row['id_clone'])) : array()),
				'name' => $row['pName'],
				'type' => $row['type'],
				'value' => $row['value'],
			);
		}

		$ep_modules[$current_row[0]][$current_column[0]]['modules'][$row['position']]['params'] = $params[$row['id_position']];
	}

	// Shouldn't be empty, but we check anyways!
	if (!empty($ep_modules))
	{
		// Open a script tag because some Javascript is coming... I wish I had no need to do this.
		$context['insert_after_template'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[';

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
		foreach ($ep_modules as $row_id => $row_data)
			foreach ($row_data as $column_id => $column_data)
				if (isset($column_data['modules']))
						foreach($column_data['modules'] as $module => $id)
							if (!empty($id['name']))
								$ep_modules[$row_id][$column_id]['modules'][$module] = processModule($id);

		$context['envision_columns'] = $ep_modules;

		// We are done with the modules' Javascript, sir!
		$context['insert_after_template'] .= '
		// ]]></script>';
	}
}

function processModule($data = array())
{
	global $context, $modSettings, $settings, $options, $txt, $user_info;

	$ep_modules = loadDefaultModuleConfigs(array(), !empty($data['functions']));

	$error_name = array();
	foreach ($ep_modules as $key => $info)
	{
		if (isset($data['name']) && $key != $data['name'])
			continue;

		if (!empty($data['files']))
		{
			// We don't want any warnings to show, so we'll check if the file exists first.
			// We already know the function doesn't exist.
			$mod_files = explode('+', $data['files']);
			foreach ($mod_files as $mFile)
				if (file_exists($context['epmod_modules_dir'] . '/' . $data['name'] . '/' . $mFile))
					require_once($context['epmod_modules_dir'] . '/' . $data['name'] . '/' . $mFile);
				else
				{
					// Log it into the error log...
					module_error(sprintf($txt['ep_modfile_not_exist'], $data['name'], $context['epmod_modules_dir'] . '/' . $data['name'] . '/' . $mFile), 'critical', true, false);
					$error_name[$data['name']] = $data['name'];
				}
		}

		// Load the module template.
		if (empty($data['template']) || !empty($data['template']) && !file_exists($context['epmod_template'].$data['template'].'.php'))
			$data['template'] = 'default';

		require_once($context['epmod_template'] . $data['template'] . '.php');

		// Correct the title target...
		switch ((int) $data['target'])
		{
			case 1:
				$data['target'] = '_self';
				break;
			case 2:
				$data['target'] = '_parent';
				break;
			case 3:
				$data['target'] = '_top';
				break;
			default:
				$data['target'] = '_blank';
				break;
		}

		// Load up the icon if there is one to load.
		$data['icon'] = !empty($data['icon']) ? $context['epmod_icon_url'] . $data['icon'] : '';

		// Load up the link for the title.
		// Checking for either an 'action' or a 'url'.
		if (isset($data['title_link']))
		{
			$http = (strpos(strtolower($data['title_link']), 'http://') === 0 ? true : (strpos(strtolower($data['title_link']), 'www.') === 0 ? true : false));

			if ($http)
			{
				$data = array_merge($data, array(
					'url' => !empty($data['url']) ? $data['url'] : '<a href="' . $data['title_link'] . '" target="' . $data['target'] . '" onfocus="if(this.blur)this.blur();">',
					'action' => '',
				));
			}
			else
			{
				$data = array_merge($data, array(
					'url' => '',
					'action' => $data['title_link'],
				));
			}
		}

		// Check for any parameters...
		if (!empty($data['params']))
		{
			$params = $data['params'];
			$data['params'] = array();

			// Just a tad faster than foreach.
			$countParams = count($params);
			for ($i = 0; $i < $countParams; $i++)
				$data['params'][$params[$i]['name']] = loadParameter($params[$i]['file_input'], $params[$i]['type'], $params[$i]['value']);
		}

		// Main module function will always be the first function in the list of functions.
		if (empty($data['functions']))
			$main_function = explode('+', $info['functions']);
		else
			$main_function = explode('+', $data['functions']);

		$data['function'] = $main_function[0];
	}

	$data['is_collapsed'] = $user_info['is_guest'] ? !empty($_COOKIE[$data['type'] . 'module_' . $data['id']]) : !empty($options[$data['type'] . 'module_' . $data['id']]);

	if ($data['header_display'] == 2)
	{
		$data['is_collapsed'] = false;
		$data['hide_upshrink'] = true;
	}
	else
		$data['hide_upshrink'] = false;

	// Which function to call?
	$toggleModule = !empty($modSettings['ep_module_enable_animations']) ? 'Anim('  : '(';
	$toggleModule .= '\'' . $data['type'] . '\', \'' . $data['id'] . '\'';

	if (!empty($modSettings['ep_module_enable_animations']))
	{
		$toggleModule .= ', \'' . $data['type'] . 'module_' . $data['id'] . '\'';
		$toggleModule .= ', \'' . (intval($modSettings['ep_module_animation_speed']) + 1) . '\');';
	}
	else
		$toggleModule .= ');';

	if (!$data['hide_upshrink'])
		$context['insert_after_template'] .= '
		var ' . $data['type'] . 'toggle_' . $data['id'] . ' = new smc_Toggle({
			bToggleEnabled:  ' . (!$data['hide_upshrink'] ? 'true' : 'false') . ',
			bCurrentlyCollapsed: ' . ($data['is_collapsed'] ? 'true' : 'false') . ',
			funcOnBeforeCollapse: function () {
				collapseModule' . $toggleModule . '
			},
			funcOnBeforeExpand: function () {
				expandModule' . $toggleModule . '
			},
			aSwappableContainers: [' . (empty($modSettings['ep_module_enable_animations']) ? '
				\'' . $data['type'] . 'module_' . $data['id'] . '\'' : '') . '
			],
			aSwapImages: [
				{
					sId: \'' . $data['type'] . 'collapse_' . $data['id'] . '\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ' . ($user_info['is_guest'] ? 'false' : 'true') . ',
				sOptionName: \'' . $data['type'] . 'collapse_' . $data['id'] . '\',
				sSessionVar: ' . JavaScriptEscape($context['session_var']) . ',
				sSessionId: ' . JavaScriptEscape($context['session_id']) . '
			},
			oCookieOptions: {
				bUseCookie: ' . ($user_info['is_guest'] ? 'true' : 'false') . ',
				sCookieName: \'' . $data['type'] . 'collapse_' . $data['id'] . '\'
			}
		});';

	return $data;
}

function load_envision_menu($menu_buttons)
{
	global $smcFunc, $user_info, $scripturl, $context;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ep_envision_menu
		ORDER BY id_button ASC',
		array(
			'db_error_skip' => true,
		)
	);

	if (!empty($smcFunc['db_error']))
		return $menu_buttons;

	$new_menu_buttons = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$permissions = explode(',', $row['permissions']);

		$ep_temp_menu = array(
			'title' => $row['name'],
			'href' => ($row['target'] == 'forum' ? $scripturl : '') . $row['link'],
			'show' => (array_intersect($user_info['groups'], $permissions)) && ($row['status'] == 'active' || (allowedTo('admin_forum') && $row['status'] == 'inactive')),
			'target' => $row['target'],
			'active_button' => false,
		);

		foreach ($menu_buttons as $area => $info)
		{
			if ($area == $row['parent'] && $row['position'] == 'before')
				$new_menu_buttons[$row['slug']] = $ep_temp_menu;

			$new_menu_buttons[$area] = $info;

			if ($area == $row['parent'] && $row['position'] == 'after')
				$new_menu_buttons[$row['slug']] = $ep_temp_menu;

			if ($area == $row['parent'] && $row['position'] == 'child_of')
				$new_menu_buttons[$row['parent']]['sub_buttons'][$row['slug']] = $ep_temp_menu;

			if ($row['position'] == 'child_of' && isset($info['sub_buttons']) && array_key_exists($row['parent'], $info['sub_buttons']))
				$new_menu_buttons[$area]['sub_buttons'][$row['parent']]['sub_buttons'][$row['slug']] = $ep_temp_menu;
		}
	}

	if (!empty($new_menu_buttons))
		$menu_buttons = $new_menu_buttons;

	return $menu_buttons;
}

function add_ep_menu_buttons($menu_buttons)
{
	global $txt, $context, $scripturl;

	// Adding the Forum button to the main menu.
	$envisionportal = array(
		'title' => (!empty($txt['forum']) ? $txt['forum'] : 'Forum'),
		'href' => $scripturl . '?action=forum',
		'show' => (!empty($modSettings['ep_portal_mode']) && allowedTo('ep_view') ? true : false),
		'active_button' => false,
	);

	$new_menu_buttons = array();
	foreach ($menu_buttons as $area => $info)
	{
		$new_menu_buttons[$area] = $info;
		if ($area == 'home')
			$new_menu_buttons['forum'] = $envisionportal;
	}

	$menu_buttons = $new_menu_buttons;

	// Adding the Envision Portal submenu to the Admin button.
	if (isset($menu_buttons['admin']))
	{
		$envisionportal = array(
			'envisionportal' => array(
				'title' => $txt['ep_'],
				'href' => $scripturl . '?action=admin;area=epmodules;sa=epmanmodules',
				'show' => allowedTo('admin_forum'),
				'is_last' => true,
			),
		);

		$i = 0;
		$new_subs = array();
		$count = count($menu_buttons['admin']['sub_buttons']);
		foreach($menu_buttons['admin']['sub_buttons'] as $subs => $admin)
		{
			$i++;
			$new_subs[$subs] = $admin;
			if($subs == 'permissions')
			{
				$permissions = true;
				// Remove is_last if set.
				if (isset($buttons['admin']['sub_buttons']['permissions']['is_last']))
					unset($buttons['admin']['sub_buttons']['permissions']['is_last']);

					$new_subs['envisionportal'] = $envisionportal['envisionportal'];

				// set is_last to envisionportal if it's the last.
				if ($i != $count)
					unset($new_subs['envisionportal']['is_last']);
			}
		}

		// If permissions doesn't exist for some reason, we'll put it at the end.
		if (!isset($permissions))
			$menu_buttons['admin']['sub_buttons'] += $envisionportal;
		else
			$menu_buttons['admin']['sub_buttons'] = $new_subs;
	}
}

function add_ep_admin_areas($admin_areas)
{
	global $txt;

	// Building the Envision Portal admin areas
	$envisionportal = array(
		'title' => $txt['ep_'],
		'areas' => array(
			'epconfig' => array(
				'label' => $txt['ep_admin_config'],
				'file' => 'ep_source/ManageEnvisionSettings.php',
				'function' => 'Configuration',
				'icon' => 'epconfiguration.png',
				'subsections' => array(
					'epinfo' => array($txt['ep_admin_information'], ''),
					'epgeneral' => array($txt['ep_admin_general'], ''),
					'epmodulesettings' => array($txt['ep_admin_module_settings'], ''),
				),
			),
			'epmodules' => array(
				'label' => $txt['ep_admin_modules'],
				'file' => 'ep_source/ManageEnvisionModules.php',
				'function' => 'Modules',
				'icon' => 'epmodules.png',
				'subsections' => array(
					'epmanmodules' => array($txt['ep_admin_manage_modules'], ''),
					'epaddmodules' => array($txt['ep_admin_add_modules'], ''),
				),
			),
			'eppages' => array(
				'label' => $txt['ep_admin_pages'],
				'file' => 'ep_source/ManageEnvisionPages.php',
				'function' => 'Pages',
				'icon' => 'eppages.png',
				'subsections' => array(
					'epmanpages' => array($txt['ep_admin_manage_pages'], ''),
					'epadepage' => array($txt['ep_admin_add_page'], ''),
				),
			),
			'epmenu' => array(
				'label' => $txt['ep_admin_menu'],
				'file' => 'ep_source/ManageEnvisionMenu.php',
				'function' => 'Menu',
				'icon' => 'epmenu.png',
				'subsections' => array(
					'epmanmenu' => array($txt['ep_admin_manage_menu'], ''),
					'epaddbutton' => array($txt['ep_admin_add_button'], ''),
				),
			),
		),
	);

	$new_admin_areas = array();
	foreach ($admin_areas as $area => $info)
	{
		$new_admin_areas[$area] = $info;
		if ($area == 'config')
			$new_admin_areas['portal'] = $envisionportal;
	}

	$admin_areas = $new_admin_areas;
}

function envision_whos_online($actions)
{
	global $txt, $smcFunc, $user_info;

	$data = array();

	if (isset($actions['page']))
	{
		$data = $txt['who_hidden'];

		if (is_numeric($actions['page']))
			$where = 'id_page = {int:numeric_id}';
		else
			$where = 'page_name = {string:name}';

		$result = $smcFunc['db_query']('', '
			SELECT id_page, page_name, title, permissions, status
			FROM {db_prefix}ep_envision_pages
			WHERE ' . $where,
			array(
				'numeric_id' => $actions['page'],
				'name' => $actions['page'],
			)
		);
		$row = $smcFunc['db_fetch_assoc']($result);

		// Invalid page? Bail.
		if (empty($row))
			return $data;

		// Skip this turn if they cannot view this...
		if ((!array_intersect($user_info['groups'], explode(',', $row['permissions'])) || !allowedTo('admin_forum')) && ($row['status'] != 1 || !allowedTo('admin_forum')))
			return $data;

		$page_data = array(
			'id' => $row['id_page'],
			'page_name' => $row['page_name'],
			'title' => $row['title'],
		);

		// Good. They are allowed to see this page, so let's list it!
		if (is_numeric($actions['page']))
			$data = sprintf($txt['ep_who_page'], $page_data['id'], censorText($page_data['title']));
		else
			$data = sprintf($txt['ep_who_page'], $page_data['page_name'], censorText($page_data['title']));
	}

	return $data;
}

function envision_integrate_actions(&$action_array)
{
	$action_array['envision'] = array('ep_source/EnvisionPortal.php', 'envisionActions');
	$action_array['envisionFiles'] = array('ep_source/EnvisionPortal.php', 'envisionFiles');
	$action_array['forum'] = array('BoardIndex.php', 'BoardIndex');
}

function envision_integrate_pre_load()
{
	global $modSettings, $sourcedir;

	// Is Envision Portal enabled in the Core Features?
	$modSettings['ep_portal_mode'] = isset($modSettings['admin_features']) ? in_array('ep', explode(',', $modSettings['admin_features'])) : false;

	// Include the main file.
	require_once($sourcedir . '/ep_source/EnvisionPortal.php');
}

function envision_integrate_load_theme()
{
	global $context, $maintenance, $modSettings;

	// Load the portal layer, making sure we didn't arleady add it.
	if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers']))
		// Checks if the forum is in maintenance, and if the portal is disabled.
		if (($maintenance && !allowedTo('admin_forum')) || empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
			$context['template_layers'] = array('html', 'body');
		else
			$context['template_layers'][] = 'portal';

	if (!empty($modSettings['ep_portal_mode']) && allowedTo('ep_view'))
	{
		if (!loadLanguage('ep_languages/EnvisionPortal'))
			loadLanguage('ep_languages/EnvisionPortal');

		loadTemplate('ep_template/EnvisionPortal', 'ep_css/envisionportal');
	}

	// Kick off time!
	ep_init();
}

?>