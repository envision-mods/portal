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
	// !!! Revise this on each commit!
	$portal_ver = '1.0 DEV r29';

	// Unallowed Envision names.
	$envision_names = array('announce', 'usercp', 'stats', 'online', 'news', 'topics', 'posts', 'search', 'calendar', 'poll', 'top_posters', 'theme_select', 'new_members', 'staff', 'sitemenu', 'shoutbox', 'custom');
	$reserved_names = array('.', '..', '.htaccess', '.core', '.htpasswd');

	$context['ep_restricted_names'] = array_merge($envision_names, $reserved_names);

	// XML mode? Save time (cut it in half) and CPU cycles by bailing out.
	if (isset($_REQUEST['xml']))
	{
		// !!! TODO: Put all XML functions into Subs-EnvisionXml.php
		require_once($sourcedir . '/ep_source/Subs-EnvisionModules.php');

		return;
	}

	// No need to load this function in this case.
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach' && (!empty($modSettings['allow_guestAccess']) && $user_info['is_guest']))
		return;

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
		SELECT title, type, body, permissions, status, page_views, header
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
		'page_views' => $row['page_views'],
		'header' => $row['header'],
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

		if (!empty($context['page_data']['header']))
			$context['html_headers'] .= '
' . $context['page_data']['header'];

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
		fatal_lang_error('ep_pages_no_access', false);
}

?>
