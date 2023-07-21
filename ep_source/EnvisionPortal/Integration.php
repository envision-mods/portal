<?php

namespace EnvisionPortal;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Integration
{
	private static $epHome = false;
	private static $isActive = false;

	public static function pre_load()
	{
		global $modSettings;

		$modSettings['ep_portal_mode'] = true;

		loadLanguage('EnvisionPortal');
	}

	/**
	 * Insert the actions needed by this mod
	 *
	 * @param array $actions An array containing all possible SMF actions.
	 *
	 * @return void
	 */
	public static function actions(&$actions)
	{
		$actions['ep'] = ['ep_source/EnvisionPortal.php', 'envisionActions'];
		$actions['epjs'] = ['ep_source/EnvisionPortal.php', 'envisionFiles'];
		$actions['forum'] = ['BoardIndex.php', 'BoardIndex'];
	}

	/**
	 * Map namespaces to directories
	 *
	 * @param array $classMap
	 */
	public static function autoload(&$classMap)
	{
		$classMap['EnvisionPortal\\'] = 'EnvisionPortal/';
	}

	/**
	 * Set the default action
	 */
	public static function default_action()
	{
		global $context, $sourcedir, $txt;

		if (!self::$isActive) {
			require_once($sourcedir . '/BoardIndex.php');

			call_user_func('BoardIndex');
		} else {
			$context['sub_template'] = 'portal';
			$context['page_title'] = $context['forum_name'] . ' - ' . $txt['home'];
			self::$epHome = true;
		}
	}

	/**
	 * Add our AJAX action to the list
	 *
	 * @param array &$no_stat_actions Array of all actions which may not be logged.
	 */
	public static function pre_log_stats(&$no_stat_actions)
	{
		$no_stat_actions['epjs'] = true;
	}

	public static function buttons(&$buttons)
	{
		global $scripturl, $txt;

		if (!self::$isActive) {
			return;
		}

		self::array_insert($buttons, 'home', [
			'forum' => [
				'title' => (!empty($txt['forum']) ? $txt['forum'] : 'Forum'),
				'href' => $scripturl . '?action=forum',
				'show' => self::$isActive,
				'action_hook' => true,
			],
		], 'after');

		// Adding the Envision Portal submenu to the Admin button.
		if (isset($buttons['admin'])) {
			$buttons['admin']['sub_buttons'] += [
				'ep' => [
					'title' => $txt['ep'],
					'href' => $scripturl . '?action=admin;area=epmodules;sa=epmanmodules',
					'show' => allowedTo('admin_forum'),
					'is_last' => true,
				],
			];
		}
	}

	/**
	 * Standard method to tweak the current action when using a custom
	 * action as forum index.
	 *
	 * @param string $current_action
	 */
	public static function fixCurrentAction(&$current_action)
	{
		if (!self::$isActive) {
			return;
		}

		if ($current_action == 'home' && empty(self::$epHome)) {
			$current_action = 'forum';
		}
	}

	public static function admin_areas2(&$admin_areas)
	{
		global $txt;

		if (!self::$isActive) {
			return $admin_areas;
		}

		$ep = [
			'title' => $txt['ep'],
			'areas' => [
				'epconfig' => [
					'label' => $txt['ep_admin_config'],
					'file' => 'ep_source/ManageEnvisionSettings.php',
					'function' => 'Configuration',
					'icon' => 'epconfiguration.png',
					'subsections' => [
						'epinfo' => [$txt['ep_admin_information'], ''],
						'epgeneral' => [$txt['ep_admin_general'], ''],
					],
				],
				'epmodules' => [
					'label' => $txt['ep_admin_modules'],
					'file' => 'ep_source/ManageEnvisionModules.php',
					'function' => 'Modules',
					'icon' => 'epmodules.png',
					'subsections' => [
						'epmanmodules' => [$txt['ep_admin_manage_modules'], ''],
						'epaddmodules' => [$txt['ep_admin_add_modules'], ''],
					],
				],
			],
		];

		$new_admin_areas = [];
		foreach ($admin_areas as $area => $info) {
			$new_admin_areas[$area] = $info;
			if ($area == 'config') {
				$new_admin_areas['ep'] = $ep;
			}
		}

		$admin_areas = $new_admin_areas;
	}

	/**
	 * @param array      $array
	 * @param int|string $position
	 * @param mixed      $insert the data to add before or after the above key
	 * @param string     $where  adding before or after
	 */
	public static function array_insert(&$array, $position, $insert, $where = 'before')
	{
		if (!is_int($position)) {
			$position = array_search($position, array_keys($array));

			// If the key is not found, just insert it at the end
			if ($position === false) {
				$position = count($array) - 2;
			}
		}
		if ($where === 'after') {
			$position += 1;
		}
		$first = array_splice($array, 0, $position);
		$array = array_merge($first, $insert, $array);
	}

	public static function load_theme()
	{
		global $context, $maintenance, $modSettings, $user_info;

		// Don't continue if they're a guest and guest access is off.
		if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest']) {
			return;
		}

		// XML mode? Nothing more is required of us...
		if (isset($_REQUEST['xml'])) {
			return;
		}

		self::$isActive = !empty($modSettings['ep_portal_mode']) && allowedTo('ep_view');
		if (($maintenance && !allowedTo('admin_forum')) || !self::$isActive) {
			return;
		}

		// Load the portal layer, making sure we didn't aleady add it.
		if (!empty($context['template_layers']) && !in_array('portal', $context['template_layers'])) {
			$context['template_layers'][] = 'portal';
		}

		loadLanguage('EnvisionPortal');
		loadTemplate('EnvisionPortal');
		loadCSSFile('EnvisionPortal.css', ['default_theme' => true], 'ep');

		// Kick off time!
		$ep = new EnvisionPortal();
		$ep->load();
	}

	/**
	 * Global permissions used by this mod per user group
	 *
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList   An associative array with all the possible permissions.
	 */
	public static function load_permissions(
		&$permissionGroups,
		&$permissionList,
		&$leftPermissionGroups,
		&$hiddenPermissions,
		&$relabelPermissions
	) {
		global $modSettings;

		loadLanguage('EnvisionPortalPermissions');
		$permissionList['membergroup'] += [
			'ep_view' => [false, 'ep', 'ep'],
		];
		if (empty($modSettings['ep_portal_mode'])) {
			$hiddenPermissions[] = 'ep_view';
		}
	}

	public static function load_permission_levels(&$groupLevels)
	{
		$groupLevels['global']['restrict'][] = 'ep_view';
	}

	public static function illegal_guest_permissions()
	{
		global $context;

		$context['non_guest_permissions'][] = 'ep_view';
	}

	public static function reports_groupperm(&$disabled_permissions)
	{
		global $modSettings;

		if (empty($modSettings['ep_portal_mode'])) {
			$disabled_permissions[] = 'ep_view';
		}
	}
}