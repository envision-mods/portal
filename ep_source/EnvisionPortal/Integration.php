<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal;

/**
 * @internal
 */
class Integration
{
	private static bool $isPortalPage = false;
	private static bool $isActive = false;
	private static array $actions = [];

	/**
	 * @return array
	 */
	public static function getActions(): array
	{
		return self::$actions;
	}

	/**
	 * @param array $actions
	 */
	public static function setActions(array $actions): void
	{
		self::$actions = $actions;
	}

	public static function actions(array &$action_array)
	{
		$action_array['envision'] = ['ep_source/EnvisionPortal.php', 'envisionActions'];
		$action_array['forum'] = ['BoardIndex.php', 'BoardIndex'];
	}

	/**
	 * Map namespaces to directories
	 *
	 * @param array $classMap
	 */
	public static function autoload(array &$classMap)
	{
		$classMap['EnvisionPortal\\'] = 'ep_source/EnvisionPortal/';
	}

	public static function redirect(string &$setLocation)
	{
		global $context, $modSettings, $scripturl;

		if (self::$isActive && $setLocation == $scripturl) {
			$setLocation .= '?action=forum';
		}

		if (!empty($modSettings['queryless_urls']) && (empty($context['server']['is_cgi']) || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && (!empty($context['server']['is_apache']) || !empty($context['server']['is_lighttpd']) || !empty($context['server']['is_litespeed']))) {
			if (defined('SID') && SID != '') {
				$setLocation = preg_replace_callback(
					'|^\E' . $scripturl . '\Q\?(?:' . SID . '(?:;|&|&amp;))(page=[^#]+?)(#[^"]*?)?$|',
					fn($m) => $scripturl . '/' . strtr($m[1], '&;=', '//,') . '.html?' . SID . ($m[2] ?? ''),
					$setLocation
				);
			} else {
				$setLocation = preg_replace_callback(
					'|^\E' . $scripturl . '\Q\?(page=[^#"]+?)(#[^"]*?)?$|',
					fn($m) => $scripturl . '/' . strtr($m[1], '&;=', '//,') . '.html' . ($m[2] ?? ''),
					$setLocation
				);
			}
		}
	}

	/**
	 * Set the default action
	 */
	public static function default_action()
	{
		global $context, $modSettings, $sourcedir, $txt;

		if (!empty($_REQUEST['page']) && !empty($modSettings['ep_pages_mode'])) {
			call_user_func(['EnvisionPortal\Pages', 'main']);
		} else {
			if (!self::$isActive) {
				require_once $sourcedir . '/BoardIndex.php';

				call_user_func('BoardIndex');
			} else {
				$context['sub_template'] = 'portal';
				$context['page_title'] = $context['forum_name'] . ' - ' . $txt['home'];
				self::$isPortalPage = true;
			}
		}
	}

	/**
	 * Set the fallback action
	 */
	public static function fallback_action()
	{
		global $context, $scripturl, $sourcedir;

		if (self::$isActive) {
			Portal::fromAction('forum');
			$context['canonical_url'] = $scripturl . '?action=forum';
		}
		require_once $sourcedir . '/BoardIndex.php';

		call_user_func('BoardIndex');
	}

	/**
	 * Add our AJAX action to the list
	 *
	 * @param array &$no_stat_actions Array of all actions which may not be logged.
	 */
	public static function pre_log_stats(array &$no_stat_actions)
	{
		$no_stat_actions['epjs'] = true;
	}

	public static function buttons(array &$menu_buttons)
	{
		global $scripturl, $txt;

		if (isset($_REQUEST['xml'])) {
			return;
		}

		$envisionportal = [
			'title' => $txt['forum'] ?? 'Forum',
			'href' => $scripturl . '?action=forum',
			'show' => self::$isActive,
			'action_hook' => true,
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

	/**
	 * Standard method to tweak the current action when using a custom
	 * action as forum index.
	 *
	 * @param string $current_action
	 */
	public static function fixCurrentAction(string &$current_action)
	{
		if (!self::$isActive) {
			return;
		}

		if ($current_action == 'home' && !self::$isPortalPage) {
			$current_action = 'forum';
		}
	}

	public static function pre_load()
	{
		global $sourcedir;

		spl_autoload_register(function ($class) use ($sourcedir) {
			$prefix = 'EnvisionPortal\\';
			// does the class use the namespace prefix?
			$len = strlen($prefix);
			if (strncmp($prefix, $class, $len) === 0) {
				include $sourcedir . '/ep_source/' . strtr($class, '\\', '/') . '.php';
			}
		});
	}

	public static function load_theme()
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

		self::$isActive = !empty($modSettings['ep_portal_mode']) && allowedTo('ep_view');
		if (($maintenance && !allowedTo('admin_forum')) || !self::$isActive) {
			return;
		}

		// BOOGIE TIME!!
		loadLanguage('ep_languages/EnvisionPortal+ep_languages/ManageEnvisionModules');
		loadTemplate('ep_template/EnvisionPortal', 'ep_css/envisionportal');

		if ($context['current_action'] == 'helpadmin') {
			loadLanguage('ep_languages/EnvisionHelp');
		}

		$eta = -hrtime(true);
		$qc = -$GLOBALS['db_count'];
		Portal::fromAction($context['current_action']);
		$context['ep_time'] = $eta + hrtime(true);
		$context['ep_qc'] = $qc + $GLOBALS['db_count'];
	}

	/**
	 * Global permissions used by this mod per user group
	 *
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList   An associative array with all the possible permissions.
	 */
	public static function load_permissions(array &$permissionGroups, array &$permissionList)
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

	public static function load_permission_levels(array &$groupLevels)
	{
		$groupLevels['global']['restrict'][] = 'ep_view';
	}

	public static function reports_groupperm(array &$disabled_permissions)
	{
		global $modSettings;

		if (empty($modSettings['ep_portal_mode'])) {
			$disabled_permissions[] = 'ep_view';
		}
	}

	public static function modifylanguages(array &$themes, array &$lang_dirs)
	{
		global $settings, $txt;

		$themes['ep'] = [
			'name' => $txt['ep_'],
			'theme_dir' => $settings['default_theme_dir'] . '/languages/ep_languages',
		];
		$lang_dirs['ep'] = $settings['default_theme_dir'] . '/languages/ep_languages';
	}

	public static function who_allowed(array &$allowedActions)
	{
		global $txt;

		if (self::$isActive) {
			$txt['who_index'] = Util::replaceVars(
				$txt['ep_who_portal'],
				['scripturl' => '%s', 'forum_name' => '%s']
			);
			$txt['whoall_forum'] = Util::replaceVars(
				$txt['ep_who_forum'],
				['scripturl' => '%s', 'forum_name' => '%s']
			);
		}
	}

	public static function admin_areas(array &$admin_areas)
	{
		global $context, $scripturl, $txt;

		if (!self::$isActive) {
			loadLanguage('ep_languages/EnvisionPortal');
		}

		$envisionportal = [
			'title' => $txt['ep_'],
			'areas' => [
				'epconfig' => [
					'label' => $txt['ep_admin_config'],
					'function' => 'EnvisionPortal\ManageEnvisionSettings::call',
					'icon' => 'epconfiguration',
					'subsections' => [
						'epinfo' => [$txt['ep_admin_information'], ''],
						'epgeneral' => [$txt['ep_admin_general'], ''],
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
						loadTemplate('ep_template/ManageEnvisionPages', 'ep_css/admin');
						new ManageEnvisionPages($_GET['sa'] ?? '');
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
						loadTemplate('ep_template/ManageEnvisionMenu', 'ep_css/admin');
						new ManageEnvisionMenu($_GET['sa'] ?? '');
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

	public static function whos_online($actions)
	{
		global $scripturl, $txt;

		$data = [];

		if (isset($actions['page'])) {
			$data = $txt['who_hidden'];

			$row = Pages::fetch($actions['page']);

			// Invalid page? Bail.
			if ($row === null) {
				return $data;
			}

			if ($row->isAllowed()) {
				$data = Util::replaceVars(
					$txt['ep_who_page'],
					['scripturl' => $scripturl, 'page' => $actions['page'], 'page_name' => censorText($row->getName())]
				);
			}
		}

		return $data;
	}

	public static function fix_url(&$val)
	{
		global $context, $modSettings, $scripturl;

		if (!empty($modSettings['queryless_urls']) && (!$context['server']['is_cgi'] || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && ($context['server']['is_apache'] || $context['server']['is_lighttpd'] || $context['server']['is_litespeed'])) {
			$val = preg_replace_callback(
				'|\b\E' . $scripturl . '\Q\?(page=[^#"]+)(#[^"]*)?$|',
				fn($m) => $scripturl . '/' . strtr($m[1], '&;=', '//,') . '.html' . ($m[2] ?? ''),
				$val
			);
		}
	}

	public static function buffer($buffer)
	{
		global $context, $db_show_debug, $modSettings, $scripturl, $txt;

		/*
		 * Fix the category links across the board, even in mods and themes
		 * that use their own.  In order for this to work, the category
		 * item should be immediately after $scripturl like how SMF does
		 * it.  Thus, index.php#c1 gets converted, while $sess_id#c1 does not.
		 */
		if (defined('WIRELESS') && !WIRELESS) {
			$buffer = preg_replace('/index.php#c([0-9]+)/', 'index.php?action=forum#c$1', $buffer);
		}

		// Add our copyright.  Please have a thought for the developers and keep it in place.
		$search_array = [
			'/\/\/www.simplemachines.org" title="Simple Machines" target="_blank" (:?rel="noopener|class="new_win)">Simple Machines<\/a>/',
		];
		$replace_array = [
			'$0 | <a class="new_win" href="https://portal.live627.com/" target="_blank">Envision Portal ' . Portal::VERSION . ' &copy; ' . Portal::COPYRIGHT_YEAR . ' Envision Portal Team</a>',
		];

		if (isset($db_show_debug) && $db_show_debug === true && isset($context['ep_cols'])) {
			$ret = '
	<div id="ep_debug">';

			foreach ($context['ep_cols'] as $col) {
				$ret .= sprintf(
					'
		<div style="--area: %d / %d / span %d / span %d;">',
					$col['x'],
					$col['y'],
					$col['rowspan'],
					$col['colspan'],
				);

				if ($col['modules'] != []) {
					foreach ($col['modules'] as $module) {
						$ret .= '
			<div class="' . (defined('SMF_VERSION') ? 'descbox' : 'plainbox') . ' centertext">
				<b>' . $module['module_title'] . '</b><br>' . $module['time'] . ' ms
			</div>';
					}
				}

				$ret .= '
		</div>';
			}

			$ret .= '
	</div>';

			$search_array[] = '/	<a href="[^?]+\?action=viewquery" target="_blank" rel="noopener">/';
			$replace_array[] = $ret . '$0';
		}

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

		// This should work even in 4.2.x, just not CGI without cgi.fix_pathinfo.
		if (!empty($modSettings['queryless_urls']) && (!$context['server']['is_cgi'] || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && ($context['server']['is_apache'] || $context['server']['is_lighttpd'] || $context['server']['is_litespeed'])) {
			// Let's do something special for session ids!
			if (defined('SID') && SID != '') {
				$buffer = preg_replace_callback(
					'|"\E' . $scripturl . '\Q\?(?:' . SID . '(?:;|&|&amp;))(page=[^#"]+?)(#[^"]*?)?"|',
					fn($m) => '"' . $scripturl . '/' . strtr($m[1], '&;=', '//,') . '.html?' . SID . ($m[2] ?? '') . '"',
					$buffer
				);
			} else {
				$buffer = preg_replace_callback(
					'|"\E' . $scripturl . '\Q\?(page=[^#"]+?)(#[^"]*?)?"|',
					fn($m) => '"' . $scripturl . '/' . strtr($m[1], '&;=', '//,') . '.html' . ($m[2] ?? '') . '"',
					$buffer
				);
			}
		}

		return isset($_REQUEST['xml']) ? $buffer : preg_replace($search_array, $replace_array, $buffer);
	}

	public static function admin_search(array &$language_files, array $include_files, array &$settings_search): void
	{
		$language_files[] = 'ep_languages/ManageEnvisionSettings';
		$settings_search[] = [__NAMESPACE__ . '\\ManageEnvisionSettings::getConfigVars', 'area=epconfig;sa=epgeneral'];
	}
}
