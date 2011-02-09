<?php
/**************************************************************************************
* EnvisionPortal.php                                                                  *
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

/*	This is the main script Envision Portal uses to generate the portal
	and page content. It provides the following functions:

	void ep_init()
		- !!!
	void envisionPortal()
		- !!!
	void envisionActions()
		- !!!
	void envisionPages()
		- !!!
	void envisionFiles()
		- !!!
*/

function ep_init($init_action = '')
{
	global $context, $txt, $settings, $board, $topic, $sourcedir, $scripturl, $boarddir, $boardurl;
	global $modSettings, $modules, $layout, $portal_ver, $maintenance, $forum_version, $user_info;

	// Software Version.
	// !!!Revise this on each commit!
	$portal_ver = '1.0 DEV r10';

	// Unallowed Envision names.
	$envision_names = array('announce', 'usercp', 'stats', 'online', 'news', 'topics', 'posts', 'search', 'calendar', 'poll', 'top_posters', 'theme_select', 'new_members', 'staff', 'sitemenu', 'shoutbox', 'custom');
	$reserved_names = array('.', '..', '.htaccess', '.core', '.htpasswd');

	$context['ep_restricted_names'] = array_merge($envision_names, $reserved_names);

	// XML mode? Save time (cut it in half) and CPU cycles by bailing out.
	if (isset($_REQUEST['xml']))
	{
		// !!! TODO: Put all XML functions into Subs-EnvisionXml.php
		require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');
		require_once($sourcedir . '/ep_source/Subs-EnvisionModules.php');

		// Avert a SMF bug with the menu...
		if (!loadLanguage('ep_languages/EnvisionPortal'))
			loadLanguage('ep_languages/EnvisionPortal');

		return;
	}

	// No need to load this function in this case.
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach' && (!empty($modSettings['allow_guestAccess']) && $user_info['is_guest']))
		return;

	// This is important to be loaded first.
	if (!loadLanguage('ep_languages/EnvisionPortal'))
		loadLanguage('ep_languages/EnvisionPortal');

	// Images. :D
	$context['ep_icon_url'] = $boardurl . '/ep_extra/module_icons/';
	$context['epmod_image_url'] = $boardurl . '/ep_extra/module_images/';
	$context['epadmin_image_url'] = $boardurl . '/ep_extra/images/admin';

	// Files and Modules
	$context['epmod_files_url'] = $boardurl . '/ep_extra/module_files/';
	$context['epmod_files_dir'] = $boarddir . '/ep_extra/module_files/';
	$context['epmod_modules_dir'] = $boarddir . '/ep_extra/modules';

	// This is changeable from the Envision Admin -> Configuration -> Module Settings area, so we need to load up the correct filepath that is in there.
	$context['epmod_icon_url'] = $boardurl . '/' . $modSettings['ep_icon_directory'] . '/';
	$context['epmod_icon_dir'] = $boarddir . '/' . $modSettings['ep_icon_directory'] . '/';

	// Templates
	$context['epmod_template'] = $boarddir . '/ep_extra/module_templates/';

	// Is Envision Portal disabled? Can you view it?
	if (empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
		return;

	// Load the sub-functions needed for Envision Portal, and the Envision Modules.
	require_once($sourcedir . '/ep_source/Subs-EnvisionPortal.php');
	require_once($sourcedir . '/ep_source/Subs-EnvisionModules.php');
	require_once($sourcedir . '/ep_source/EnvisionModules.php');

	// Load the EnvisionModules Language File for all you Module Customizers out there :)
	if (!loadLanguage('ep_languages/EnvisionModules'))
		loadLanguage('ep_languages/EnvisionModules');

	// These puppies are evil >:D
	unset($_GET['PHPSESSID'], $_GET['theme']);

	// We want the first item in the requested URI
	reset($_GET);
	$uri = key($_GET);

	// If a registered SMF action was called, use that instead
	$da_action = !empty($uri) ? !empty($context['current_action']) ? $context['current_action'] : '[' . $uri . ']' : '';
	$da_action = !empty($init_action) ? $init_action : $da_action;

	$skipped_actions = array(
		'jsoption' => 0,
		'.xml' => 0,
		'xmlhttp' => 0,
		'dlattach' => 0,
		'helpadmin' => 0,
		'keepalive' => 0,
	);

	// Don't continue if we're wireless or on certain actions....
	if (WIRELESS || isset($skipped_actions[$da_action]))
		return;

	// Add Forum to the linktree.
	if ((!empty($modSettings['ep_portal_mode']) && allowedTo('ep_view')) && (!empty($board) || !empty($topic) || $da_action == 'forum' || $da_action == 'collapse'))
	{
		// The forum is always the second item in the linktree right?
		if (count($context['linktree']) > 2)
		{
			// This is basically going to push everything one offset forward, duplicating the first item.
			foreach ($context['linktree'] as $offset => $link)
				$context['linktree'][$offset + 1] = array(
					'name' => $link['name'],
					'url' => $link['url'],
				);

			// And thus the forum is the second item in the linktree.
			$context['linktree'][1] = array(
				'name' => $txt['forum'],
				'url' => $scripturl . '?action=forum',
			);
		}
		else
			$context['linktree'][] = array(
				'name' => $txt['forum'],
				'url' => $scripturl . '?action=forum',
			);

		// Fix the linktree if a category was requested.
		foreach ($context['linktree'] as $key => $tree)
			if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
				$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
	}

	// Default Exception actions.
	$context['exceptions'] = array(
		'print' => 0,
		'clock' => 0,
		'about:unknown' => 0,
		'about:mozilla' => 0,
		'modifycat' => 0,
		'.xml' => 0,
		'xmlhttp' => 0,
		'dlattach' => 0,
		'envisionFiles' => 0,
		'printpage' => 0,
		'keepalive' => 0,
		'jseditor' => 0,
		'jsmodify' => 0,
		'jsoption' => 0,
		'suggest' => 0,
		'verificationcode' => 0,
		'viewsmfile' => 0,
		'viewquery' => 0,
		// Removing some known 2's here
		'editpoll2' => 0,
		'login2' => 0,
		'movetopic2' => 0,
		'post2' => 0,
		'quickmod2' => 0,
		'register2' => 0,
		'removetopic2' => 0
	);

	if (isset($context['exceptions'][$da_action]))
		return;

	// Don't continue if they're a guest and guest access is off.
	if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'])
		return;

	$curr_action = !empty($da_action) ? $da_action : '[home]';
	$context['ep_home'] = $curr_action == '[home]';

	loadLayout($curr_action);

	// Load the portal layer, making sure we didn't arleady add it.
	if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers']))
		// Checks if the forum is in maintenance, and if the portal is disabled.
		if (($maintenance && !allowedTo('admin_forum')) || empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
			$context['template_layers'] = array('html', 'body');
		else
			$context['template_layers'][] = 'portal';

		// Include the JS file for everything.
		if (!empty($context['has_ep_layout']))
			$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/envisionportal.js"></script>
	<style type="text/css">
		#envision_container
		{
			display: table;
			width: 100%;
		}
	</style>';
}

function ep_main()
{
	global $context, $txt;

	// A mobile device doesn't require a portal...
	if (WIRELESS)
		redirectexit('action=forum');

	// Load the Envision Portal template file
	$context['sub_template'] = 'portal';

	// Set the page title
	$context['page_title'] = $context['forum_name'] . ' - ' . $txt['home'];
	$context['page_title_html_safe'] = $context['forum_name'] . ' - ' . $txt['home'];
}

//!!! Handles all Sub-actions for index.php?action=envision
function envisionActions()
{
	// Get the subaction
	$sa = !empty($_GET['sa']) && isset($_GET['sa']) ? $_GET['sa'] : '';

	if (empty($sa))
		return;

	$xml = isset($_GET['xml']);

	switch ($sa)
	{
		// We can add in more sub-actions if we need to ;)
		case 'shoutbox':
			$request = $xml ? (isset($_GET['send_shout']) ? 'send_shout' : (isset($_GET['delete_shout']) ? 'delete_shout' : 'get_shouts')) : '';
			$get_value = $xml ? (isset($_GET['send_shout']) ? $_GET['send_shout'] : (isset($_GET['get_shouts']) ? $_GET['get_shouts'] : '')) : '';
			ep_shoutbox($request, $get_value);
			break;
		case 'shout_history':
			ep_shoutbox_history($_GET);
			break;
		case 'insertcolumn':
			ep_insert_column();
			break;
		case 'dbSelect':
			ep_edit_db_select();
			break;
		default:
			// Perhaps a different default here?
			$request = $xml ? (isset($_GET['send_shout']) ? 'send_shout' : (isset($_GET['delete_shout']) ? 'delete_shout' : 'get_shouts')) : '';
			$get_value = $xml ? (isset($_GET['send_shout']) ? $_GET['send_shout'] : (isset($_GET['get_shouts']) ? $_GET['get_shouts'] : '')) : '';
			ep_shoutbox($request, $get_value);
			break;
	}
}

function envisionPages()
{
	global $context, $modSettings, $smcFunc, $txt, $user_info;

	// Let's make it plain and simple: we don't want mobile devices!
	if (WIRELESS)
		redirectexit('action=forum');

	// If Envision is inactive, we can still use Envision Pages thanks to this.
	if (!$modSettings['ep_portal_mode'])
		loadTemplate('ep_template/EnvisionPortal');

	// Let's see what page name or id they put in, if blank, send em to the home page.
	$call = isset($_GET['page']) ? $_GET['page'] : redirectexit();

	// Put it in the session to prevent it from being logged when they refresh the page.
	$_SESSION['last_page_id'] = $call;

	// We need to make sure we don't confuse page ids with page names.
	if (!is_numeric($call) || stristr($call, '.') || stristr($call, 'e'))
		$query = 'page_name = {string:page}';
	else
		$query = 'id_page = {int:page}';

	// Let's grab the content from the DB.
	$request = $smcFunc['db_query']('', '
		SELECT title, type, body, permissions, status, page_views
		FROM {db_prefix}ep_envision_pages
		WHERE ' . $query . '
		LIMIT 1',
		array(
			'page' => $smcFunc['htmlspecialchars']($call),
		)
	);

	// If nothing gets returned, exit and prevent any errors.
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('ep_pages_not_exist', false);

	$row = $smcFunc['db_fetch_assoc']($request);
	$context['page_data'] = array(
		'title' => htmlentities(trim($row['title']), ENT_QUOTES, $context['character_set']),
		'body' => $row['body'],
		'permissions' => explode(', ', $row['permissions']),
		'status' => $row['status'],
		'type' => $row['type'],
		'page_views' => $row['page_views']
	);

	// Are you allowed to see the page?
	if ((array_intersect($user_info['groups'], $context['page_data']['permissions']) || allowedTo('admin_forum')) && ($context['page_data']['status'] == 1 || allowedTo('admin_forum')))
	{
		// Modify the body according to the page type
		switch ($context['page_data']['type'])
		{
			// BBC
			case 2:
				$content = parse_bbc(strip_tags($context['page_data']['body']));
				$context['page_data']['body'] = trim($content);
				break;

			// HTML
			case 1:
				$content = html_entity_decode($context['page_data']['body'], ENT_QUOTES, $context['character_set']);
				$context['page_data']['body'] = trim($content);
				break;

			// PHP...
			case 0:
				$content = trim(html_entity_decode($context['page_data']['body'], ENT_QUOTES, $context['character_set']));
				$content = trim($content, '<?php');
				$content = trim($content, '?>');

				function envision_error_handler($output)
				{
					$error = error_get_last();
					$output = "";
					if (!empty($error))
						foreach ($error as $info => $string)
							if ($info == 'message')
								$output .= $string;

					return $output;
				}

				ob_start('envision_error_handler');
				eval($content);
				$code = ob_get_contents();
				ob_end_clean();

				$context['page_data']['body'] = $code;
				break;
		}

		$context['page_title'] = $context['page_data']['title'];
		$context['page_title_html_safe'] = $context['page_data']['title'];

		if (!isset($_SESSION['viewed_page_' . $call]))
		{
			$smcFunc['db_query']('','
				UPDATE {db_prefix}ep_envision_pages
				SET page_views = page_views + 1
				WHERE ' . $query,
				array(
					'page' => $smcFunc['htmlspecialchars']($call),
				)
			);

			$_SESSION['viewed_page_' . $call] = '1=1';
		}

		// Finally, display the content.
		$context['sub_template'] = 'envision_pages';
	}
	else
	{
		// Can use log_error if we want and grab the users ip address and send it with the error for security reasons.
		// But for now, we use FALSE for logging into SMF error log.
		fatal_lang_error('ep_pages_no_access', false);
	}
}

//!!! accessed via the file_input parameter type ( index.php?action=envisionFiles )
function envisionFiles()
{
	global $smcFunc, $txt, $modSettings, $user_info, $context;

	if (empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view'))
		fatal_lang_error('ep_unable_to_view_file', false);

	$_REQUEST['id'] = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : fatal_lang_error('no_access', false);

	// Which type are we dealing with, hmmm.
	$mod = isset($_REQUEST['mod']) ? (int) $_REQUEST['mod'] : 0;
	$clone = isset($_REQUEST['clone']) ? (int) $_REQUEST['clone'] : 0;

	// can't have both, must have 1 or the other.
	if ((!empty($mod) && !empty($clone)) || (empty($mod) && empty($clone)))
		fatal_lang_error('no_access', false);

	$is_clone = !empty($clone) ? true : false;

	$name = $is_clone ? 'dmc.' : 'dm.';

	// Build a partial query
	$query = ' AND ' . ($is_clone ? 'dmp.id_clone = {int:id_clone})' : 'dmp.id_module = {int:id_module} AND dmp.id_clone = {int:zero})');
	$query .= $is_clone ? ' INNER JOIN {db_prefix}ep_module_clones AS dmc ON (dmc.id_clone = dmp.id_clone AND dmc.id_clone = {int:id_clone} AND dmc.id_member = {int:id_member})' : ' INNER JOIN {db_prefix}ep_modules AS dm ON (dm.id_module = dmp.id_module AND  dm.id_module = {int:id_module})';

	// Getting the files.
	$request = $smcFunc['db_query']('', '
		SELECT dmf.filename, dmf.file_hash, dmf.fileext, dmf.id_file, dmf.file_type, dmf.mime_type, ' . $name . 'name
		FROM {db_prefix}ep_module_files AS dmf
		INNER JOIN {db_prefix}ep_module_parameters AS dmp ON (dmp.id_param = dmf.id_param' . $query . '
		WHERE dmf.id_member = {int:id_member} AND dmf.id_file = {int:file}',
		array(
			'zero' => 0,
			'id_clone' => $clone,
			'id_module' => $mod,
			'file' => $_REQUEST['id'],
			'id_member' => 0,
		)
	);

	// Not allowed or doesn't exist, exit!
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access', false);

	list ($real_filename, $file_hash, $file_ext, $id_file, $file_type, $mime_type, $mod_name) = $smcFunc['db_fetch_row']($request);

	$smcFunc['db_free_result']($request);

	// Get the module directory.
	$module_dir = $context['epmod_files_dir'] . $mod_name;

	// Update the download counters (unless it's a thumbnail).
	if ($file_type != 1)
		$smcFunc['db_query']('', '
			UPDATE LOW_PRIORITY {db_prefix}ep_module_files
			SET downloads = downloads + 1
			WHERE id_file = {int:id_file}',
			array(
				'id_file' => $id_file,
			)
		);

	$filename = getFilename($real_filename, $_REQUEST['id'], $module_dir, false, $file_hash);

	// Clear any output that was made before now!
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// No point in a nicer message, because this is supposed to be a file anyways...
	if (!file_exists($filename))
	{
		loadLanguage('ep_languages/Errors');

		header('HTTP/1.0 404 ' . $txt['file_not_found']);
		header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['file_not_found']);
	}

	// If it hasn't been modified since the last time this file was retrieved, there's no need to display it again.
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (strtotime($modified_since) >= filemtime($filename))
		{
			ob_end_clean();

			// No it hasn't been modified.
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}

	// Check whether the ETag was sent back, and cache based on that...
	$file_md5 = '"' . md5_file($filename) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $file_md5) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');
	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Connection: close');
	header('ETag: ' . $file_md5);

	// Does this have a mime type?
	if ($mime_type && (isset($_REQUEST['image']) || !in_array($file_ext, array('jpg', 'gif', 'jpeg', 'x-ms-bmp', 'png', 'psd', 'tiff', 'iff'))))
		header('Content-Type: ' . $mime_type);
	else
	{
		header('Content-Type: ' . ($context['browser']['is_ie'] || $context['browser']['is_opera'] ? 'application/octetstream' : 'application/octet-stream'));
		if (isset($_REQUEST['image']))
			unset($_REQUEST['image']);
	}

	if (!isset($_REQUEST['image']))
	{
		// Convert the file to UTF-8, cuz most browsers dig that.
		$utf8name = !$context['utf8'] && function_exists('iconv') ? iconv($context['character_set'], 'UTF-8', $real_filename) : (!$context['utf8'] && function_exists('mb_convert_encoding') ? mb_convert_encoding($real_filename, 'UTF-8', $context['character_set']) : $real_filename);
		$fixchar = create_function('$n', '
			if ($n < 32)
				return \'\';
			elseif ($n < 128)
				return chr($n);
			elseif ($n < 2048)
				return chr(192 | $n >> 6) . chr(128 | $n & 63);
			elseif ($n < 65536)
				return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
			else
				return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

		// Different browsers like different standards...
		if ($context['browser']['is_firefox'])
			header('Content-Disposition: attachment; filename*="UTF-8\'\'' . preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name) . '"');

		elseif ($context['browser']['is_opera'])
			header('Content-Disposition: attachment; filename="' . preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name) . '"');

		elseif ($context['browser']['is_ie'])
			header('Content-Disposition: attachment; filename="' . urlencode(preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $utf8name)) . '"');

		else
			header('Content-Disposition: attachment; filename="' . $utf8name . '"');
	}

	// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
	if (!isset($_REQUEST['image']) && in_array($file_ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg', 'tiff')))
		header('Cache-Control: no-cache');
	else
		header('Cache-Control: max-age=' . (525600 * 60) . ', private');

	if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		header('Content-Length: ' . filesize($filename));

	// Try to buy some time...
	@set_time_limit(0);

	// For text files.....
	if (!isset($_REQUEST['image']) && in_array($file_ext, array('txt', 'css', 'htm', 'html', 'php', 'xml')))
	{
		// We need to check this isn't unicode!
		$fp = fopen($filename, 'rb');
		$header = fread($fp, 2);
		fclose($fp);

		if ($header != chr(255).chr(254) && $header != chr(254).chr(255))
		{
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
				$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r\n", $buffer);');
			elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
				$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r", $buffer);');
			else
				$callback = create_function('$buffer', 'return preg_replace(\'~\r~\', "\r\n", $buffer);');
		}
	}

	// We don't do output compression for files this large...
	if (filesize($filename) > 4194304)
	{
		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);
	}
	// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
	elseif (isset($callback) || @readfile($filename) == null)
		echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

	obExit(false);
}

?>
