<?php
/**************************************************************************************
 * EnvisionPortal                                                                      *
 * Community Portal Application for SMF                                                *
 * =================================================================================== *
 * Software by:                  EnvisionPortal (http://envisionportal.net/)           *
 * Software for:                 Simple Machines Forum                                 *
 * Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
 * Support, News, Updates at:    http://envisionportal.net/                            *
 **************************************************************************************/

if (!defined('SMF')) {
	die('Hacking attempt...');
}

function add_ep_menu_buttons(&$menu_buttons)
{
	global $txt, $context, $scripturl, $modSettings;

	if (empty($modSettings['ep_portal_mode']) || !allowedTo('ep_view')) {
		return $menu_buttons;
	}
	if (isset($_REQUEST['xml'])) {
		return;
	}

	$envisionportal = [
		'title' => $txt['forum'] ?? 'Forum',
		'href' => $scripturl . '?action=forum',
		'show' => !empty($modSettings['ep_portal_mode']) && allowedTo('ep_view'),
		'active_button' => false,
	];

	$new_menu_buttons = [];
	foreach ($menu_buttons as $area => $info) {
		$new_menu_buttons[$area] = $info;
		if ($area == 'home') {
			$new_menu_buttons['forum'] = $envisionportal;
		}
	}

	$menu_buttons = $new_menu_buttons;

	// Adding the Envision Portal submenu to the Admin button.
	if (isset($menu_buttons['admin'])) {
		$envisionportal = [
			'envisionportal' => [
				'title' => $txt['ep_'],
				'href' => $scripturl . '?action=admin;area=epmodules;sa=epmanmodules',
				'show' => allowedTo('admin_forum'),
				'is_last' => true,
			],
		];

		$i = 0;
		$new_subs = [];
		$count = count($menu_buttons['admin']['sub_buttons']);
		foreach ($menu_buttons['admin']['sub_buttons'] as $subs => $admin) {
			$i++;
			$new_subs[$subs] = $admin;
			if ($subs == 'permissions') {
				$permissions = true;
				// Remove is_last if set.
				if (isset($buttons['admin']['sub_buttons']['permissions']['is_last'])) {
					unset($buttons['admin']['sub_buttons']['permissions']['is_last']);
				}

				$new_subs['envisionportal'] = $envisionportal['envisionportal'];

				// set is_last to envisionportal if it's the last.
				if ($i != $count) {
					unset($new_subs['envisionportal']['is_last']);
				}
			}
		}

		// If permissions doesn't exist for some reason, we'll put it at the end.
		if (!isset($permissions)) {
			$menu_buttons['admin']['sub_buttons'] += $envisionportal;
		} else {
			$menu_buttons['admin']['sub_buttons'] = $new_subs;
		}
	}
}

function add_ep_admin_areas(&$admin_areas)
{
	global $context, $scripturl, $txt;

	loadLanguage('ep_languages/EnvisionPortal');
	$envisionportal = [
		'title' => $txt['ep_'],
		'areas' => [
			'epconfig' => [
				'label' => $txt['ep_admin_config'],
				'file' => 'ep_source/ManageEnvisionSettings.php',
				'function' => 'Configuration',
				'icon' => 'epconfiguration',
				'subsections' => [
					'epinfo' => [$txt['ep_admin_information'], ''],
					'epgeneral' => [$txt['ep_admin_general'], ''],
					'epmodulesettings' => [$txt['ep_admin_module_settings'], ''],
					'logs' => [$txt['ep_admin_log'], ''],
				],
			],
			'epmodules' => [
				'label' => $txt['ep_admin_modules'],
				'file' => 'ep_source/ManageEnvisionModules.php',
				'function' => 'Modules',
				'icon' => 'epmodules',
				'subsections' => [
					'epmanmodules' => [
						$txt['ep_admin_manage_modules'],
						'',
						'url' => $scripturl . '?action=admin;area=epmodules' . (isset($_GET['in']) ? ';in=' . $_GET['in'] : ''),
					],
					'epaddlayout' => [
						$txt['add_layout'],
						'',
						$context['current_subaction'] == 'epaddlayout' || $context['current_subaction'] == 'epaddlayout2',
					],
					'epeditlayout' => [
						$txt['edit_layout'],
						'',
						$context['current_subaction'] == 'epeditlayout' || $context['current_subaction'] == 'epeditlayout2',
						'enabled' => $context['current_subaction'] != 'epaddlayout' && $context['current_subaction'] != 'epaddlayout2',
						'url' => $scripturl . '?action=admin;area=epmodules;sa=epeditlayout' . (isset($_GET['in']) ? ';in=' . $_GET['in'] : ''),
					],
					'epdeletelayout' => [
						$txt['delete_layout'],
						'',
						$context['current_subaction'] == 'epdeletelayout' || $context['current_subaction'] == 'epdeletelayout2',
						'enabled' => $context['current_subaction'] != 'epaddlayout' && $context['current_subaction'] != 'epaddlayout2',
						'url' => $scripturl . '?action=admin;area=epmodules;sa=epdeletelayout' . (isset($_GET['in']) ? ';in=' . $_GET['in'] : ''),
					],
				],
			],
			'eppages' => [
				'label' => $txt['ep_admin_pages'],
				'function' => function (): void {
					loadLanguage('ep_languages/ManageEnvisionPages');
					loadTemplate('ep_template/ManageEnvisionPages');
					(new EnvisionPortal\ManageEnvisionPages($_GET['sa'] ?? ''));
				},
				'icon' => 'eppages',
				'subsections' => [
					'manpages' => [$txt['ep_admin_manage_pages'], ''],
					'addpage' => [$txt['ep_admin_add_page'], ''],
				],
			],
			'epmenu' => [
				'label' => $txt['ep_admin_menu'],
				'function' => function (): void {
					loadLanguage('ep_languages/ManageEnvisionMenu');
					loadTemplate('ep_template/ManageEnvisionMenu');
					(new EnvisionPortal\ManageEnvisionMenu($_GET['sa'] ?? ''));
				},
				'icon' => 'epmenu',
				'subsections' => [
					'manmenu' => [$txt['ep_admin_manage_menu'], ''],
					'addbutton' => [$txt['ep_admin_add_button'], ''],
				],
			],
		],
	];

	$new_admin_areas = [];
	foreach ($admin_areas as $area => $info) {
		$new_admin_areas[$area] = $info;
		if ($area == 'config') {
			$new_admin_areas['portal'] = $envisionportal;
		}
	}

	$admin_areas = $new_admin_areas;
}

function envision_whos_online($actions)
{
	global $txt, $smcFunc, $user_info;

	$data = [];

	if (isset($actions['page'])) {
		$data = $txt['who_hidden'];

		if (is_numeric($actions['page'])) {
			$where = 'id_page = {int:numeric_id}';
		} else {
			$where = 'page_name = {string:name}';
		}

		$result = $smcFunc['db_query']('', '
			SELECT id_page, page_name, title, permissions, status
			FROM {db_prefix}ep_envision_pages
			WHERE ' . $where,
			[
				'numeric_id' => $actions['page'],
				'name' => $actions['page'],
			]
		);
		$row = $smcFunc['db_fetch_assoc']($result);

		// Invalid page? Bail.
		if (empty($row)) {
			return $data;
		}

		// Skip this turn if they cannot view this...
		if ((!array_intersect($user_info['groups'], explode(',', $row['permissions'])) || !allowedTo(
					'admin_forum'
				)) && ($row['status'] != 1 || !allowedTo('admin_forum'))) {
			return $data;
		}

		$page_data = [
			'id' => $row['id_page'],
			'page_name' => $row['page_name'],
			'title' => $row['title'],
		];

		// Good. They are allowed to see this page, so let's list it!
		if (is_numeric($actions['page'])) {
			$data = sprintf($txt['ep_who_page'], $page_data['id'], censorText($page_data['title']));
		} else {
			$data = sprintf($txt['ep_who_page'], $page_data['page_name'], censorText($page_data['title']));
		}
	}

	return $data;
}

function envision_integrate_actions(&$action_array)
{
	$action_array['forum'] = ['BoardIndex.php', 'BoardIndex'];
}

function envisionBuffer($buffer)
{
	global $txt, $context;

	/*
	 * Fix the category links across the board, even in mods and themes
	 * that use their own.  In order for this to work, the category
	 * item should be immediately after $scripturl like how SMF does
	 * it.  Thus, index.php#c1 gets converted, while $sess_id#c1 does not.
	 */
	if (!WIRELESS) {
		$buffer = preg_replace('/index.php#c([0-9]+)/', 'index.php?action=forum#c$1', $buffer);
	}

	// Add our copyright.  Please have a thought for the developers and keep it in place.
	$search_array = [
		'/\/\/www.simplemachines.org" title="Simple Machines" target="_blank" (:?rel="noopener|class="new_win)">Simple Machines<\/a>/',
	];
	$replace_array = [
		'$0 | <a class="new_win" href="http://envisionportal.net/" target="_blank">Envision Portal 1.0.0 &copy; 2011&ndash;2023 Envision Portal Team</a>',
	];

	if ($context['show_load_time'] && isset($context['ep_time'], $context['ep_qc'])) {
		if (isset($txt['page_created_full'])) {
			$search_array[] = '/\Q' . sprintf(
					$txt['page_created_full'],
					$context['load_time'],
					$context['load_queries']
				) . '\E/';
		} else {
			$search_array[] = '/\Q' . $txt['page_created'] . $context['load_time'] . $txt['seconds_with'] . $context['load_queries'] . $txt['queries'] . '\E/';
		}
		$replace_array[] = '$0<br>' . sprintf(
				$txt['portal_created_full'],
				$context['ep_time'] / 1e9,
				$context['ep_qc']
			);
	}

	return isset($_REQUEST['xml']) ? $buffer : preg_replace($search_array, $replace_array, $buffer);
}

function envision_integrate_pre_load()
{
	global $modSettings, $sourcedir;

	// Is Envision Portal enabled in the Core Features?
	$modSettings['ep_portal_mode'] = isset($modSettings['admin_features']) && in_array(
			'ep',
			explode(',', $modSettings['admin_features'])
		);

	spl_autoload_register(function ($class) use ($sourcedir) {
		$prefix = 'EnvisionPortal\\';
		// does the class use the namespace prefix?
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) === 0) {
			include $sourcedir . '/ep_source/' . strtr($class, '\\', '/') . '.php';
		}
	});
}

function envision_integrate_load_theme()
{
	global $context, $maintenance, $modSettings, $user_info;
	static $level = 0;

	// Attempt to prevent a recursive loop.
	++$level;
	if ($level > 1) {
		return;
	}

	// Don't continue if they're a guest and guest access is off.
	if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest']) {
		return;
	}

	// XML mode? Save precious wriggling time / CPU cycles by bailing out.
	if (isset($_REQUEST['xml']) || defined('WIRELESS') && WIRELESS) {
		return;
	}

	$isActive = !empty($modSettings['ep_portal_mode']) && allowedTo('ep_view');
	if (($maintenance && !allowedTo('admin_forum')) || !$isActive) {
		return;
	}

	loadLanguage('ep_languages/EnvisionPortal+ep_languages/ManageEnvisionModules');
	loadTemplate('ep_template/EnvisionPortal', 'ep_css/envisionportal');

	if ($context['current_action'] == 'helpadmin') {
		loadLanguage('ep_languages/EnvisionHelp');
	}

	$eta = -hrtime(true);
	$qc = -$GLOBALS['db_count'];
	EnvisionPortal\Portal::fromAction($context['current_action']);
	$context['ep_time'] = $eta + hrtime(true);
	$context['ep_qc'] = $qc + $GLOBALS['db_count'];
}

function envision_integrate_core_features(&$core_features)
{
	global $modSettings;

	if (empty($modSettings['ep_portal_mode'])) {
		loadLanguage('ep_languages/EnvisionPortal');
	}

	$ep_core_feature = [
		'url' => 'action=admin;area=epmodules',
		'save_callback' => 'clean_cache',
	];

	$new_core_features = [];
	foreach ($core_features as $area => $info) {
		$new_core_features[$area] = $info;
		if ($area == 'cp') {
			$new_core_features['ep'] = $ep_core_feature;
		}
	}
	$core_features = $new_core_features;
}

function envision_integrate_load_permissions(&$permissionGroups, &$permissionList)
{
	global $context;

	loadLanguage('ep_languages/EnvisionPermissions');

	// If this is a guest limit the available permissions.
	if (isset($context['group']['id']) && $context['group']['id'] == -1) {
		$permissionList['membergroup'] += [
			'ep_view' => [false, 'ep', 'ep'],
		];
	} else {
		$permissionList['membergroup'] += [
			'ep_view' => [false, 'ep', 'ep'],
			'ep_manage_layouts' => [false, 'ep', 'ep'],
		];
	}
}