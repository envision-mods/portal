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

function ep_load_module_context($installed_mods = array(), $new_layout = false)
{
	global $context, $txt;

	// Default module configurations.
	$ep_module_context = array(
		'announce' => array(
			'module_title' => array(
				'value' => $txt['ep_module_announce'],
			),
			'module_icon' => array(
				'value' => 'world.png',
			),
			'msg' => array(
				'type' => 'large_text',
				'value' => 'Welcome to Envision Portal!',
			),
		),
		'usercp' => array(
			'module_title' => array(
				'value' => $txt['ep_module_usercp'],
			),
			'module_icon' => array(
				'value' => 'heart.png',
			),
			'module_link' => array(
				'value' => 'action=profile',
			),
		),
		'stats' => array(
			'module_title' => array(
				'value' => $txt['ep_module_stats'],
			),
			'module_icon' => array(
				'value' => 'stats.png',
			),
			'module_link' => array(
				'value' => 'action=stats',
			),
			'stat_choices' => array(
				'type' => 'callback',
				'callback_func' => 'checklist',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_checks($field[\'value\'], array(\'members\', \'posts\', \'topics\', \'categories\', \'boards\', \'ontoday\', \'onever\'), array(), $field[\'label\'], 0);

					return $field;'),
				'value' => '0,1,2,5,6',
				'order' => true,
			),
		),
		'online' => array(
			'module_title' => array(
				'value' => $txt['ep_module_online'],
			),
			'module_icon' => array(
				'value' => 'user.png',
			),
			'module_link' => array(
				'value' => 'action=who',
			),
			'online_pos' => array(
				'type' => 'select',
				'value' => '0',
				'options' => 'top;bottom',
			),
			'show_online' => array(
				'type' => 'callback',
				'callback_func' => 'checklist',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_checks($field[\'value\'], array(\'users\', \'buddies\', \'guests\', \'hidden\', \'spiders\'), array(), $field[\'label\'], 0);

					return $field;'),
				'value' => '0,1,2',
				'order' => true,
			),
			'online_groups' => array(
				'type' => 'callback',
				'callback_func' => 'list_groups',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_groups($field[\'value\'], \'-1,0,3\');

					return $field;'),
				'value' => '-3',
			),
		),
		'news' => array(
			'module_title' => array(
				'value' => $txt['ep_module_news'],
			),
			'module_icon' => array(
				'value' => 'cog.png',
			),
			'board' => array(
				'type' => 'select',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_boards();

					return $field;'),
					'value' => '1',
				),
			'limit' => array(
				'type' => 'int',
				'value' => '5',
			),
		),
		'recent' => array(
			'module_title' => array(
				'value' => $txt['ep_module_topics'],
			),
			'module_icon' => array(
				'value' => 'pencil.png',
			),
			'module_link' => array(
				'value' => 'action=recent',
			),
			'post_topic' => array(
				'type' => 'select',
				'value' => 'topics',
				'options' => 'posts;topics',
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
		'search' => array(
			'module_title' => array(
				'value' => $txt['ep_module_search'],
			),
			'module_icon' => array(
				'value' => 'magnifier.png',
			),
			'module_link' => array(
				'value' => 'action=search',
			),
		),
		'calendar' => array(
			'module_title' => array(
				'value' => $txt['ep_module_calendar'],
			),
			'module_icon' => array(
				'value' => 'cal.png',
			),
			'display' => array(
				'type' => 'select',
				'value' => '0',
				'options' => 'month;info',
			),
			'show_months' => array(
				'type' => 'select',
				'value' => '1',
				'options' => 'year;asdefined',
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
				'type' => 'callback',
				'callback_func' => 'checklist',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_checks($field[\'value\'], array(\'events\', \'holidays\', \'birthdays\'), array(), $field[\'label\'], 0);

					return $field;'),
				'value' => '0,1,2',
				'order' => true,
			),
		),
		'poll' => array(
			'module_title' => array(
				'value' => $txt['ep_module_poll'],
			),
			'module_icon' => array(
				'value' => 'comments.png',
			),
			'options' => array(
				'type' => 'select',
				'value' => '0',
				'options' => 'showPoll;topPoll;recentPoll',
			),
			'topic' => array(
				'type' => 'int',
				'value' => '0',
			),
		),
		'top_posters' => array(
			'module_title' => array(
				'value' => $txt['ep_module_topPosters'],
			),
			'module_icon' => array(
				'value' => 'rosette.png',
			),
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
		'theme_select' => array(
			'module_title' => array(
				'value' => $txt['ep_module_theme_select'],
			),
			'module_icon' => array(
				'value' => 'palette.png',
			),
			'module_link' => array(
				'value' => 'action=theme;sa=pick',
			),
		),
		'new_members' => array(
			'module_title' => array(
				'value' => $txt['ep_module_new_members'],
			),
			'module_icon' => array(
				'value' => 'overlays.png',
			),
			'module_link' => array(
				'value' => 'action=stats',
			),
			'limit' => array(
				'type' => 'int',
				'value' => '3',
			),
			'list_type' => array(
				'type' => 'select',
				'value' => '0',
				'options' => '0;1;2',
			),
		),
		'staff' => array(
			'module_title' => array(
				'value' => $txt['ep_module_staff'],
			),
			'module_icon' => array(
				'value' => 'rainbow.png',
			),
			'list_type' => array(
				'type' => 'select',
				'value' => '1',
				'options' => '0;1;2',
			),
			'name_type' => array(
				'type' => 'select',
				'value' => '0',
				'options' => '0;1;2',
			),
			'groups' => array(
				'type' => 'callback',
				'callback_func' => 'checklist',
				'preload' => create_function('&$field', '
					$field[\'options\'] = ep_list_groups($field[\'value\'], \'-1,0\');

					return $field;'),
				'value' => '1,2',
				'order' => true,
			),
		),
		'sitemenu' => array(
			'module_title' => array(
				'value' => $txt['ep_module_sitemenu'],
			),
			'module_icon' => array(
				'value' => 'rainbow.png',
			),
			'module_icon' => array(
				'value' => 'star.png',
			),
			'onesm' => array(
				'type' => 'check',
				'value' => '0',
			),
		),
		'custom' => array(
			'module_title' => array(
				'value' => $txt['ep_module_custom'],
			),
			'module_icon' => array(
				'value' => 'comments.png',
			),
			'code_type' => array(
				'type' => 'select',
				'value' => '1',
				'options' => '0;1;2',
			),
			'code' => array(
				'type' => 'rich_edit',
				'value' => '',
			),
		),
	);

	// Let other modules hook in to the system.
	ep_include_hook('load_module_files', $context['ep_module_modules_dir']);
	ep_include_language_hook('load_module_language_files', $context['ep_module_modules_dir']);
	ep_call_hook('load_module_fields', array(&$ep_module_context));

	return $ep_module_context;
}

function ep_parse_string($str = '', $type = 'filepath', $replace = true)
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

	ep_call_hook('module_error', array(&$type));

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

/**
 * Logs an item into the database.
 *
 * This function gets called when a specific action is executed by a member. It then saves a short log that can be viewed in the admin section.
 *
 * @param string $action the action to log. Common values include:
 * - add_layout for when a layout gets added;
 * - edit_layout for when a layout is edited; note that module addition or removal isn't logged;
 * - delete_layout for when a layout is deleted;
 * - export_layout for when a layout is exported to XML;
 * - import_layout for when a layout is imported from XML.
 * $param int $id_member the affected member's ID. Use a zero to ignore this. Note this becomes a key for the extra column; the currently logged member's ID gets inserted into the relevant column.
 * @param array $extra associative array of extra info that goes with the log. For instance, 'id_member' would  be a key with the affected member's ID as its value.
 */
function logEpAction($action, $id_member, $extra)
{
	global $smcFunc, $user_info;

	$columns = array(
		'action' => 'string',
		'id_member' => 'int',
		'time' => 'int',
		'extra' => 'string',
	);

	if (!empty($id_member))
		$extra['id_member'] = $id_member;

	$data = array(
		$action, $user_info['id'], time(), serialize($extra),
	);

	$keys = array(
		'id_action', 'id_member',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_log_actions',  $columns, $data, $keys);
}

/**
 * Loads the list of actions from the log for createList().
 *
 * @param int $start determines where to start getting actions. Used in SQL's LIMIT clause.
 * @param int $items_per_page determines how many actions are returned. Used in SQL's LIMIT clause.
 * @param string $sort determines which column to sort by. Used in SQL's ORDER BY clause.
 * @return array the associative array returned by $smcFunc['db_fetch_assoc']().
 * @since 1.0
 */
function list_getLogs($start, $items_per_page, $sort)
{
	global $context, $scripturl, $sourcedir, $smcFunc, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT
			ela.id_action, ela.action, ela.extra, ela.time, mem.id_member, mem.real_name, mg.group_name
		FROM {db_prefix}ep_log_actions AS ela
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = ela.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$entries = array();
	$member_list = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['extra'] = @unserialize($row['extra']);

		// Corrupt?
		$row['extra'] = is_array($row['extra']) ? $row['extra'] : array();

		// Does something else want to extend the logs?
		ep_include_hook('load_log_files');
		ep_include_language_hook('load_log_language_files', $sourcedir . '/ep_plugin_language');
		ep_call_hook('load_log_fields', array(&$row));

		if (strstr($row['action'], 'layout') !== false)
			$row['action_text'] = sprintf($txt['ep_log_' . $row['action']], $row['extra']['layout_name']);

		if (!empty($row['extra']['id_member']))
			$member_list[$row['id_action']] = $row['extra']['id_member'];

		$entries[$row['id_action']] = array(
			'id' => $row['id_action'],
			'time' => timeformat($row['time']),
			'timestamp' => forum_time(true, $row['time']),
			'editable' => time() > $row['time'] + $context['hoursdisable'] * 3600,
			'extra' => $row['extra'],
			'action' => $row['action'],
			'action_text' => $row['action_text'],
			'position' => empty($row['real_name']) && empty($row['group_name']) ? $txt['guest'] : $row['group_name'],
			'moderator_link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : (empty($row['real_name']) ? ($txt['guest'] . (!empty($row['extra']['member_acted']) ? ' (' . $row['extra']['member_acted'] . ')' : '')) : $row['real_name']),
		);
	}

	$smcFunc['db_free_result']($request);

	if (!empty($member_list))
	{
		$members_request = $smcFunc['db_query']('', '
			SELECT real_name, id_member
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:member_list})',
			array(
				'member_list' => $member_list,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($members_request))
			foreach ($member_list as $action => $member)
				$entries[$action]['member'] = array(
					'id' => $row['id_member'],
					'name' => $row['real_name'],
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
				);

		$smcFunc['db_free_result']($members_request);
	}

	return $entries;
}

/**
 * Gets the total number of Envision Pages for createList().
 *
 * @return int the total number of actions in the log
 * @since 1.0
 */
function list_getNumLogs()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ep_log_actions',
		array(
		)
	);

	list ($num_actions) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $num_actions;
}

function envisionBuffer($buffer)
{
	global $portal_ver, $context, $modSettings;

	/*
	Fix the category links across the board, even in mods and themes
		that use their own. In ordeer for this to work, the category
		item should be immediately after $scripturl like how SMF does
		it. Thus, index.php#c1 gets converted, while $sess_id#c1 does not.
		*/
	if (!WIRELESS)
		$buffer = preg_replace('/index.php#c([\d]+)/', 'index.php?action=forum#c$1', $buffer);

	// Add our copyright. Please have a thought for the developers and keep it in place.
	$search_array = array(
		', Simple Machines LLC</a>',
	);

	if (!empty($modSettings['ep_inline_copyright']))
		$replace_array = array(
			', Simple Machines LLC</a> | <a class="new_win" href="http://envisionportal.net/" target="_blank">Envision Portal ' . $portal_ver . ' &copy; 2011 Envision Portal Team</a>',
		);
	else
		$replace_array = array(
			', Simple Machines LLC</a></span></li><li class="copyright"><span><a class="new_win" href="http://envisionportal.net/" target="_blank">Envision Portal ' . $portal_ver . ' &copy; 2011 Envision Portal Team</a>',
		);

	if (!empty($context['has_ep_layout']))
	{
		// Prevent the Envision table from overflowing the SMF theme
		$search_array[] = '<body>';
		$search_array[] = '</body>';

		$replace_array[] = '<body><div id="envision_container">';
		$replace_array[] = '</div></body>';
	}

	return (isset($_REQUEST['xml']) ? $buffer : str_replace($search_array, $replace_array, $buffer));
}

function ep_get_module_info($scripts, $mod_functions, $dirname, $file, $name = '', $install = false)
{
	global $boarddir, $context, $modSettings, $scripturl, $smcFunc;

	// Are we allowed to use this name?
	if (in_array($file, $context['ep_restricted_names']))
		return false;

	// Optional check: does it exist? (Mainly for installation)
	if (!empty($name) && $name != $file)
		return false;

	// If the required info file does not exist let's silently die...
	if (!file_exists($dirname . '/' . $file . '/module.xml'))
		return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($dirname . '/' . $file . '/module.xml');

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$module_info1 = new xmlArray($file_info);
	$module_info1 = $module_info1->path('module[0]');

	// Required XML elements and attributes. Quit if any one is missing.
	if (!$module_info1->exists('title')) return false;
	if (!$module_info1->exists('description')) return false;

	return array(
		'title' => $module_info1->fetch('title'),
		'description' => ($module_info1->exists('description/@parsebbc')) ? ($module_info1->fetch('description/@parsebbc') ? parse_bbc($module_info1->fetch('description')) : $module_info1->fetch('description')) : $module_info1->fetch('description'),
		'desc_parse_bbc' => ($module_info1->exists('description/@parsebbc') ? $module_info1->fetch('description/@parsebbc') : false),
		'delete_link' => $scripturl . '?action=admin;area=epmodules;sa=epdeletemodule;name=' . $file . ';' . $context['session_var'] . '=' . $context['session_id'],
		'install_link' => $scripturl . '?action=admin;area=epmodules;sa=epinstallmodule;name=' . $file . ';' . $context['session_var'] . '=' . $context['session_id'],
	);
}

function listModules()
{
	global $boarddir, $context, $modSettings, $sourcedir, $scripturl, $smcFunc, $txt;

	// We want to define our variables now...
	$AvailableModules = array();
	$added_mods = array();

	// Let's loop throuugh each folder and get their module data. If anything goes wrong we shall skip it.
	$files = scandir($context['ep_module_modules_dir']);

	foreach ($files as $file)
	{
		$retVal = ep_get_module_info('', '', $context['ep_module_modules_dir'], $file, false);
		if ($retVal === false)
			continue;
		else
		{
			$added_mods[] = $file;
			$module_info[$file] = $retVal;
		}
	}

	if (isset($module_info))
	{
		// Find out if any of these are installed.
		$request = $smcFunc['db_query']('', '
			SELECT id_module, type
			FROM {db_prefix}ep_modules
			WHERE type IN ({array_string:module_names})',
			array(
				'module_names' => $added_mods,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($info[$row['type']]))
			{
				// It's installed, so remove the install link, and add uninstall and settings links.
				unset($module_info[$row['type']]['install_link']);
				$module_info[$row['type']] += array(
					'uninstall_link' => $scripturl . '?action=admin;area=epmodules;sa=epuninstallmodule;name=' . $row['type'] . ';' . $context['session_var'] . '=' . $context['session_id'],
					'settings_link' => $scripturl . '?action=admin;area=epmodules;sa=modify;in=' . $row['id_module'] . ';' . $context['session_var'] . '=' . $context['session_id'],
				);
			}
		}

		return $module_info;
	}
	else
		return array();
}

function ep_get_plugin_info($dirname, $file, $installing = false)
{
	global $boarddir, $context, $modSettings, $scripturl, $smcFunc;

	// If the required info file does not exist let's silently die...
	if (!file_exists($dirname . '/' . $file . '/plugin.xml'))
		return false;

	// And finally, get the file's contents
	$file_info = file_get_contents($dirname . '/' . $file . '/plugin.xml');
	$file_setting = $dirname . '/' . $file . '/scripts/script.php';
	$func_name = 'plugin_' . $file . '_info';

	// Parse info.xml into an xmlArray.
	loadClassFile('Class-Package.php');
	$plugin_info1 = new xmlArray($file_info);
	$plugin_info1 = $plugin_info1->path('plugin[0]');

	// Required XML elements and attributes. Quit if any one is missing.
	if (!$plugin_info1->exists('title')) return false;
	if (!$plugin_info1->exists('description')) return false;
	if (!file_exists($file_setting)) return false;
	require_once($file_setting);

	// If installing, run the script.
	if ($installing && is_callable($func_name))
		$results = $func_name();
	else
		$results = array();

	return array_merge(array(
		'title' => $plugin_info1->fetch('title'),
		'description' => ($plugin_info1->exists('description/@parsebbc')) ? ($plugin_info1->fetch('description/@parsebbc') ? parse_bbc($plugin_info1->fetch('description')) : $plugin_info1->fetch('description')) : $plugin_info1->fetch('description'),
		'enabled' => false,
	), $results);
}

function listPlugins($installing = false)
{
	global $context, $modSettings, $sourcedir, $scripturl, $smcFunc, $txt;
	$plugin_names = array();

	// Let's loop throuugh each folder and get their plugin data. If anything goes wrong we shall skip it.
	$files = scandir($context['ep_plugins_dir']);

	foreach ($files as $file)
	{
		$retVal = ep_get_plugin_info($context['ep_plugins_dir'], $file, $installing);
		if ($retVal === false)
			continue;
		else
		{
			$plugin_names[] = $file;
			$plugin_info[$file] = $retVal;
		}
	}

	if (isset($plugin_info))
	{
		// Find out if any of these are installed.
		$request = $smcFunc['db_query']('', '
			SELECT type
			FROM {db_prefix}ep_plugins
			WHERE type IN ({array_string:plugin_names})',
			array(
				'plugin_names' => $plugin_names,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$plugin_info[$row['type']]['enabled'] = true;

		return $plugin_info;
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

	foreach ($installed_mods as $installed)
	{
		$retVal = GetEnvisionModuleInfo($installed['files'], $installed['functions'], $context['ep_module_modules_dir'], $installed['name'], $installed['name']);
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
			emp.value
		FROM {db_prefix}ep_module_parameters AS emp
		WHERE emp.id_param = {int:config_id} AND emp.type = {string:type}',
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

function loadLayout($url, $return = false)
{
	global $smcFunc, $context, $scripturl, $txt, $user_info;

	if (is_int($url))
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				*, elp.id_layout_position
			FROM {db_prefix}ep_layouts AS el
				LEFT JOIN {db_prefix}ep_layout_positions AS elp ON (elp.id_layout = el.id_layout)
				LEFT JOIN {db_prefix}ep_module_positions AS emp ON (emp.id_layout_position = elp.id_layout_position)
				LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			WHERE el.id_layout = {int:id_layout}',
			array(
				'zero' => 0,
				'id_layout' => $url,
			)
		);
	}
	else
	{
		$match = (!empty($_REQUEST['board']) ? '[board]=' . $_REQUEST['board'] : (!empty($_REQUEST['topic']) ? '[topic]=' . (int) $_REQUEST['topic'] : (!empty($_REQUEST['page']) ? '[page]=' . $_REQUEST['page'] : $url)));
		$general_match = (!empty($_REQUEST['board']) ? '[board]' : (!empty($_REQUEST['topic']) ? '[topic]' : (!empty($_REQUEST['page']) ? '[page]' : (!empty($_REQUEST['action']) ? '[all_actions]' : ''))));
		$mmatch = $match;
		$mgeneral_match = $general_match;

		$request = $smcFunc['db_query']('', '
			SELECT
				el.id_layout
			FROM {db_prefix}ep_layouts AS el
				INNER JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout AND ela.action = {string:current_action})
			WHERE el.id_member = {int:current_member}',
			array(
				'current_action' => $mmatch,
				'current_member' => $user_info['id'],
			)
		);

		$num2 = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		if (empty($num2))
			$mmatch = $mgeneral_match;

		$request = $smcFunc['db_query']('', '
			SELECT
				el.id_member
			FROM {db_prefix}ep_layouts AS el
				INNER JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout AND ela.action = {string:current_action})
			WHERE el.id_member = {int:current_member}',
			array(
				'current_action' => $mmatch,
				'current_member' => $user_info['id'],
			)
		);

		list ($current_member) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (empty($current_member))
			$current_member = 0;

		$request = $smcFunc['db_query']('', '
			SELECT
				el.id_layout
			FROM {db_prefix}ep_layouts AS el
				INNER JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout AND ela.action = {string:current_action})
			WHERE el.id_member = {int:zero}',
			array(
				'current_action' => $match,
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
				*, elp.id_layout_position
			FROM {db_prefix}ep_layouts AS el
				JOIN {db_prefix}ep_layout_actions AS ela ON (ela.action = {string:current_action} AND ela.id_layout = el.id_layout)
				LEFT JOIN {db_prefix}ep_layout_positions AS elp ON (elp.id_layout = el.id_layout)
				LEFT JOIN {db_prefix}ep_module_positions AS emp ON (emp.id_layout_position = elp.id_layout_position)
				LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			WHERE el.id_member = {int:current_member}',
			array(
				'current_member' => $current_member,
				'current_action' => empty($current_member) ? $match : $mmatch,
			)
		);

		$num = $smcFunc['db_num_rows']($request);
		if (empty($num))
			return;

		$old_row = 0;
		$view_groups = array();

		// Let the theme know we have a layout.
		$context['has_ep_layout'] = true;
	}

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$smf_col = !empty($row['is_smf']);

		if (!is_int($url) && !$smf_col && $row['status'] == 'inactive')
			continue;

		if (is_int($url))
			$context['layout_name'] = $row['name'];

		if (!isset($ep_modules[$row['x_pos']][$row['y_pos']]) && !empty($row['id_layout_position']))
			$ep_modules[$row['x_pos']][$row['y_pos']] = array(
				'is_smf' => $smf_col,
				'id_layout_position' => $row['id_layout_position'],
				'html' => ($row['colspan'] >= 2 ? ' colspan="' . $row['colspan'] . '"' : '') . (!empty($context['ep_home']) && in_array($row['y_pos'], array(0, 2)) || empty($context['ep_home']) && $row['y_pos'] <= 1 && !$smf_col ? ' style="width: 200px;"' : ''),
				'extra' => $row,
			);

		if (!is_null($row['id_position']) && !empty($row['id_layout_position']))
		{
			// Store $context variables for each module.  Mod Authors can use these for unique ID values, function names, etc.
			// !!! Is this really needed?
			if (!isset($ep_modules[$row['x_pos']][$row['y_pos']]['modules'][$row['position']]))
				if (empty($context['ep_mod_' . $row['type']]))
					$context['ep_mod_' . $row['type']] = $row['type'] .  '_' . $row['id_position'];

			$ep_modules[$row['x_pos']][$row['y_pos']]['modules'][$row['position']] = array(
				'is_smf' => $smf_col,
				'modify_link' => $user_info['is_admin'] ? ' [<a href="' . $scripturl . '?action=admin;area=epmodules;sa=modify;in=' . $row['id_position'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['modify'] . '</a>]' : '',
				'type' => $row['type'],
				'id' => $row['id_position'],
			);
		}
	}

	if ($return)
		return $ep_modules;

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

	$module_context = ep_load_module_context();

	foreach ($ep_modules as $row_id => $row_data)
		foreach ($row_data as $column_id => $column_data)
			if (isset($column_data['modules']))
					foreach ($column_data['modules'] as $module => $id)
						if (!empty($id['type']))
							$ep_modules[$row_id][$column_id]['modules'][$module] = ep_process_module($module_context, $id, !is_int($url));

	ep_call_hook('load_layout', array(&$ep_modules, $url));

	if (is_int($url))
		$context['ep_columns'] = $ep_modules;
	else
		$context['envision_columns'] = $ep_modules;

	// We are done with the modules' Javascript, sir!
	$context['insert_after_template'] .= '
	// ]]></script>';
}

function ep_process_module($module_context, $data, $full_layout)
{
	global $context, $modSettings, $settings, $options, $txt, $user_info, $scripturl, $smcFunc;

	// Load user-defined module configurations.
	$request = $smcFunc['db_query']('', '
		SELECT
			name, em.type AS module_type, value
		FROM {db_prefix}ep_module_positions AS emp
			LEFT JOIN {db_prefix}ep_modules AS em ON (em.id_module = emp.id_module)
			LEFT JOIN {db_prefix}ep_module_field_data AS emd ON (emd.id_module_position = emp.id_position)
		WHERE emp.id_position = {int:id_position}',
		array(
			'id_position' => $data['id'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$module_type = $row['module_type'];

		if (!empty($row['name']))
			$fields[$row['name']] = array(
				'value' => $row['value'],
		);
	}

	// Merge the default and custom configs together.
	$info = $module_context[$module_type];

	if (!empty($fields))
		$fields = array_replace_recursive($info, $fields);
	else
		$fields = $info;

	$data['module_title'] = $fields['module_title']['value'];

	if ($full_layout === false)
		return $data;

	if (file_exists($context['ep_module_modules_dir'] . '/' . $data['type'] . '/main.php'))
		require_once($context['ep_module_modules_dir'] . '/' . $data['type'] . '/main.php');

	// Load the module template.
	if (empty($fields['module_template']['value']) || !empty($fields['module_template']['value']) && !file_exists($context['ep_module_template'] . $fields['module_template']['value']))
		$fields['module_template']['value'] = 'default.php';

	require_once($context['ep_module_template'] . $fields['module_template']['value']);
	$data['module_template'] = str_replace('.php', '', $fields['module_template']['value']);

	// Correct the title target...
	if (!isset($fields['module_target']['value']))
		$data['module_target'] = '_self';

	if (!empty($fields['module_icon']['value']));
		$data['module_icon'] = '<img src="' . $context['ep_module_icon_url'] . $fields['module_icon']['value'] . '" alt="" title="' . $data['module_title'] . '" class="icon" style="margin-left: 0px;" />&nbsp;';

	if (isset($fields['module_link']))
	{
		$http = stristr($fields['module_link']['value'], 'http://') !== false || stristr($fields['module_link']['value'], 'www.') !== false;

		if ($http)
			$data['module_title'] = '<a href="' . $fields['module_link']['value'] . '" target="' . $data['module_target'] . '">' . $data['module_title'] . '</a>';
		else
			$data['module_title'] = '<a href="' . $scripturl . '?' . $fields['module_link']['value'] . '" target="' . $data['module_target'] . '">' . $data['module_title'] . '</a>';
	}

	if (!empty($fields))
	{
		$fields2 = $fields;
		$fields = array();

		foreach ($fields2 as $key => $field)
			if (isset($field['type']))
				$data['fields'][$key] = $field['value'];//loadParameter(array(), $field['type'], $field['value']);
	}

	$data['function'] = 'module_' . $data['type'];

	$data['is_collapsed'] = $user_info['is_guest'] ? !empty($_COOKIE[$data['type'] . 'module_' . $data['id']]) : !empty($options[$data['type'] . 'module_' . $data['id']]);

	if (isset($data['header_display']) && $data['header_display'] == 2)
	{
		$data['is_collapsed'] = false;
		$data['hide_upshrink'] = true;
	}
	else
		$data['hide_upshrink'] = false;

	if (!isset($data['header_display']))
		$data['header_display'] = 1;

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

	ep_call_hook('ep_process_module', array(&$data));

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

	if (empty($request) || $smcFunc['db_num_rows']($request) == 0)
		return $menu_buttons;

	$new_menu_buttons = array();
	$db_buttons = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$db_buttons[$row['id_button']] = $row;

	$smcFunc['db_free_result']($request);

	reset($db_buttons);
	while (list($key, $row) = each($db_buttons))
	{
		$permissions = explode(',', $row['permissions']);

		if((!array_intersect($user_info['groups'], $permissions) || $row['status'] != '1') && !allowedTo('admin_forum'))
			continue;

		$ep_temp_menu = array(
			'title' => $row['name'],
			'href' => ($row['target'] == 'forum' ? $scripturl : '') . $row['link'],
			'target' => $row['target'],
			'active_button' => false,
		);

		$new_menu_buttons = array();
		$is_added = false;
		foreach ($menu_buttons as $area => $info)
		{
			if ($area == $row['parent'] && $row['position'] == 'before')
			{
				$new_menu_buttons[$row['id_button']] = $ep_temp_menu;
				$is_added = true;
			}

			$new_menu_buttons[$area] = $info;

			if ($area == $row['parent'] && $row['position'] == 'after')
			{
				$new_menu_buttons[$row['id_button']] = $ep_temp_menu;
				$is_added = true;
			}

			if ($area == $row['parent'] && $row['position'] == 'child_of')
			{
				$new_menu_buttons[$row['parent']]['sub_buttons'][$row['id_button']] = $ep_temp_menu;
				$is_added = true;
			}

			if ($row['position'] == 'child_of' && isset($info['sub_buttons']) && array_key_exists($row['parent'], $info['sub_buttons']))
			{
				$new_menu_buttons[$area]['sub_buttons'][$row['parent']]['sub_buttons'][$row['id_button']] = $ep_temp_menu;
				$is_added = true;
			}
		}
		$menu_buttons = $new_menu_buttons;
		unset($db_buttons[$key]);
		if(!$is_added && array_key_exists($row['parent'], $db_buttons))
			$db_buttons[$key] = $row;
	}

	ep_call_hook('load_envision_menu', array(&$menu_buttons));
	return $menu_buttons;
}

function add_ep_menu_buttons($menu_buttons)
{
	global $txt, $context, $scripturl, $modSettings;

	if (empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
		return $menu_buttons;

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
	global $txt, $modSettings;

	if (empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
		return $admin_areas;

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
					'logs' => array($txt['ep_admin_log'], ''),
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
			'epplugins' => array(
				'label' => $txt['ep_admin_plugins'],
				'file' => 'ep_source/ManageEnvisionPlugins.php',
				'function' => 'plugins',
				'icon' => 'epplugins.png',
				'subsections' => array(
					'epmanplugins' => array($txt['ep_admin_manage_plugins'], ''),
					'epaddplugins' => array($txt['ep_admin_add_plugins'], ''),
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

function recurse($array, $array1)
{
	foreach ($array1 as $key => $value)
	{
		// Create new key in $array, if it is empty or not an array.
		if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
			$array[$key] = array();

		// Overwrite the value in the base array.
		if (is_array($value))
			$value = recurse($array[$key], $value);

		$array[$key] = $value;
	}
	return $array;
}

function envision_integrate_pre_load()
{
	global $modSettings, $sourcedir;

	// Is Envision Portal enabled in the Core Features?
	$modSettings['ep_portal_mode'] = isset($modSettings['admin_features']) ? in_array('ep', explode(',', $modSettings['admin_features'])) : false;

	// Unserialize our permanent hooks here.
	if (!empty($modSettings['ep_permanented_hooks']))
		$modSettings['ep_permanented_hooks'] = unserialize($modSettings['ep_permanented_hooks']);
	else
		$modSettings['ep_permanented_hooks'] = array();

	require_once($sourcedir . '/ep_source/EnvisionPortal.php');
	require_once($sourcedir . '/ep_source/Subs-EnvisionModules.php');
	require_once($sourcedir . '/ep_source/EnvisionModules.php');
	require_once($sourcedir . '/ep_source/Subs-EnvisionPlugins.php');

	// Compatibility for PHP < 5.3.0 - http://www.php.net/manual/en/function.array-replace-recursive.php#92574
	if (!function_exists('array_replace_recursive'))
	{
		function array_replace_recursive($array, $array1)
		{
			// Handle the arguments, merging them one by one.
			$args = func_get_args();
			$array = $args[0];
			if (!is_array($array))
				return $array;

			for ($i = 1; $i < count($args); $i++)
				if (is_array($args[$i]))
					$array = recurse($array, $args[$i]);

			return $array;
		}
	}
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

function envision_integrate_core_features(&$core_features)
{
	global $modSettings;

	if (empty($modSettings['ep_portal_mode']))
		loadLanguage('ep_languages/EnvisionPortal');

	$ep_core_feature = array(
		'url' => 'action=admin;area=epmodules',
		'save_callback' => create_function('$value', '
			clean_cache();
		'),
	);

	$new_core_features = array();
	foreach ($core_features as $area => $info)
	{
		$new_core_features[$area] = $info;
		if ($area == 'cp')
			$new_core_features['ep'] = $ep_core_feature;
	}
	$core_features = $new_core_features;

}

function envision_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context;

	loadLanguage('ep_languages/EnvisionPermissions');

	// If this is a guest limit the available permissions.
	if (isset($context['group']['id']) && $context['group']['id'] == -1)
		$permissionList['membergroup'] += array(
			'ep_view' => array(false, 'ep', 'ep'),
		);
	else
		$permissionList['membergroup'] += array(
			'ep_view' => array(false, 'ep', 'ep'),
			'ep_create_layouts' => array(false, 'ep', 'ep'),
			'ep_create_unapproved_layouts' => array(false, 'ep', 'ep'),
			'ep_modify_layouts' => array(true, 'ep', 'ep'),
			'ep_delete_layouts' => array(true, 'ep', 'ep'),
			'ep_import_layouts' => array(true, 'ep', 'ep'),
			'ep_export_layouts' => array(true, 'ep', 'ep'),
		);
}

function envision_integrate_profile_areas(&$profile_areas)
{
	global $txt, $sourcedir;

	loadTemplate('ep_template/EnvisionProfile');
	loadLanguage('ep_languages/EnvisionProfile');

	$profile_areas += array(
		'ep' => array(
			'title' => $txt['ep_'],
			'areas' => array(
				'overview' => array(
					'label' => $txt['summary'],
					'file' => 'ep_source/EnvisionProfile.php',
					'function' => 'overview',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				),
				'layouts' => array(
					'label' => $txt['ep_my_layouts'],
					'file' => 'ep_source/EnvisionProfile.php',
					'function' => 'Layouts',
					'subsections' => array(
						'manage' => array($txt['ep_manage_layouts'], array('ep_modify_layouts_own', 'ep_modify_layouts_any', 'ep_delete_layouts_own', 'ep_delete_layouts_any')),
						'add' => array($txt['ep_add_layout'], array('ep_create_layouts')),
						'managemodules' => array($txt['ep_admin_manage_modules'], ''),
					),
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				),
			),
		),
	);

	// Time to fish for hookers? Yeah, yeah, correct me. I don't care. :P
	ep_include_hook('load_profile_files');
	ep_include_language_hook('load_profile_language_files', $sourcedir . '/ep_plugin_language');
	ep_call_hook('load_profile_areas', array(&$profile_areas));
}

function integrate_envision_attachments()
{
	global $smcFunc, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT id_folder, filename, file_hash, fileext, id_attach, attachment_type, mime_type, approved, id_member
		FROM {db_prefix}attachments
		WHERE id_attach = {int:id_attach}
		LIMIT 1',
		array(
			'id_attach' => $_REQUEST['attach'],
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);

	if ($user_info['id'] == $row['id_member'])
		isAllowedTo('ep_' . $types[$type][3] . '_own');
	else
		isAllowedTo('ep_' . $types[$type][3] . '_any');

	$smcFunc['db_data_seek']($request, 0);
	return $request;
}

function renderAttachmentCallBack($buffer)
{
	global $context, $modSettings, $sourcedir, $smcFunc, $user_info;

	// check that something was actually written to the buffer
	if (strlen($buffer) > 0)
	{
		// We need to know where this thing is going.
		if (!empty($modSettings['currentAttachmentUploadDir']))
		{
			if (!is_array($modSettings['attachmentUploadDir']))
				$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);

			// Just use the current path for temp files.
			$attach_dir = $modSettings['attachmentUploadDir'][$modSettings['currentAttachmentUploadDir']];
			$id_folder = $modSettings['currentAttachmentUploadDir'];
		}
		else
		{
			$attach_dir = $modSettings['attachmentUploadDir'];
			$id_folder = 1;
		}

		$name = 'xml_layout_' . time() . '.xml';
		$filename = 'post_tmp_' . $user_info['id'] . '_0';
		$file = $attach_dir . '/' . $filename;
		if (!touch($file))
			return $buffer;
		$fh = fopen($file, 'w');
		fwrite($fh, $buffer);
		fclose($fh);

		// Make it an attachment.
		$attachment_options = array(
			'post' => 0,
			'poster' => $user_info['id'],
			'name' => $name,
			'tmp_name' => $filename,
			'size' => filesize($file),
			'approved' => true,
			'skip_thumbnail' => true,
		);

		require_once($sourcedir . '/Subs-Post.php');
		createAttachment($attachment_options);
		$context['attach_id'] = $attachment_options['id'];
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}attachments
			SET id_member = {int:id_member}
			WHERE id_attach = {int:id_attach}',
			array(
				'id_member' => $user_info['id'],
				'id_attach' => $attachment_options['id'],
			)
		);
	}
	return $buffer;
}

// End the buffer and offer to download the attachment.
function saveRenderedAttachment()
{
	global $context, $scripturl;
	$buffer = ob_get_clean();

	if (isset($context['attach_id']))
		redirectexit($scripturl . '?action=dlattach;attach=' . $context['attach_id'] . ';type=envision');
	else
		// Saving as attachment failed. Just print the data to the browser...
		echo $buffer;
}

?>