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

class Portal
{
	const VERSION = '1.0.0';
	const COPYRIGHT_YEAR = '2024';

	/**
	 * @return bool whether this is an attachment, avatar, toggle of editor buttons, theme option, XML feed, popup, etc.
	 */
	private static function canSkipAction($da_action)
	{
		$skipped_actions = [
			'about:unknown' => true,
			'clock' => true,
			'dlattach' => true,
			'findmember' => true,
			'helpadmin' => true,
			'jsoption' => true,
			'likes' => true,
			'loadeditorlocale' => true,
			'modifycat' => true,
			'pm' => ['sa' => ['popup']],
			'profile' => ['area' => ['popup', 'alerts_popup']],
			'requestmembers' => true,
			'smstats' => true,
			'suggest' => true,
			'verificationcode' => true,
			'viewquery' => true,
			'viewsmfile' => true,
			'xmlhttp' => true,
			'.xml' => true,
		];
		call_integration_hook('integrate_skipped_actions', [&$skipped_actions]);
		$skip_this = false;
		if (isset($skipped_actions[$da_action])) {
			if (is_array($skipped_actions[$da_action])) {
				foreach ($skipped_actions[$da_action] as $subtype => $subnames) {
					$skip_this |= isset($_REQUEST[$subtype]) && in_array($_REQUEST[$subtype], $subnames);
				}
			} else {
				$skip_this = isset($skipped_actions[$da_action]);
			}
		}

		return $skip_this;
	}

	private function getMatch($da_action)
	{
		if (isset($_REQUEST['board'])) {
			return '[board]=' . $_REQUEST['board'];
		} elseif (isset($_REQUEST['topic'])) {
			return '[topic]=' . $_REQUEST['topic'];
		} elseif (isset($_REQUEST['page'])) {
			return '[page]=' . $_REQUEST['page'];
		}

		return $da_action;
	}

	private function getGeneralMatch(): string
	{
		if (isset($_REQUEST['board'])) {
			return '[boards]';
		} elseif (isset($_REQUEST['topic'])) {
			return '[topics]';
		} elseif (isset($_REQUEST['page'])) {
			return '[pages]';
		} elseif (isset($_REQUEST['action'])) {
			return '[actions]';
		}

		return '[home]';
	}

	private function getMatchedLayout($da_action): ?int
	{
		global $smcFunc;

		$match = $this->getMatch($da_action);
		$general_match = $this->getGeneralMatch();

		$request = $smcFunc['db_query']('', '
			SELECT
				id_layout, action
			FROM {db_prefix}ep_layout_actions
				WHERE action IN ({string:match}, {string:general_match})',
			[
				'match' => $match,
				'general_match' => $general_match,
			]
		);

		while (list ($id_layout, $action) = $smcFunc['db_fetch_row']($request)) {
			if ($action == $match || $action == $general_match) {
				return (int)$id_layout;
			}
		}

		return null;
	}

	/**
	 * Load a layout that is assigned to the current SMF action.
	 *
	 * @param string|null $init_action
	 */
	public static function fromAction(?string $init_action = null)
	{
		global $context, $boarddir, $boardurl;
		global $board, $topic, $scripturl, $txt;

		// Oh no, the fights out / gonna punch your lights out
		unset($_GET['PHPSESSID'], $_GET['theme']);

		// We want the first item in the requested URI
		reset($_GET);
		$uri = key($_GET);

		// If a registered SMF action was called, use that instead
		$da_action = $init_action ?? '[' . $uri . ']';

		if (self::canSkipAction($da_action)) {
			return;
		}

		if (!empty($board) || !empty($topic) || $context['current_action'] == 'forum') {
			array_splice($context['linktree'], 1, 0, [
				[
					'name' => $txt['forum'],
					'url' => $scripturl . '?action=forum',
				],
			]);
		}

		$context['ep_icon_url'] = $boardurl . '/ep_extra/module_icons/';
		$context['module_image_url'] = $boardurl . '/ep_extra/module_images/';
		$context['epadmin_image_url'] = $boardurl . '/ep_extra/images/admin';
		$context['module_modules_dir'] = $boarddir . '/ep_extra/modules';
		$context['ep_plugins_dir'] = $boarddir . '/ep_extra/plugins';
		$context['ep_plugins_url'] = $boardurl . '/ep_extra/plugins';
		$context['module_icon_url'] = $boardurl . '/ep_extra/module_icons';
		$context['module_icon_dir'] = $boarddir . '/ep_extra/module_icons';
		$context['module_template'] = $boarddir . '/ep_extra/module_templates';

		$layout = self::getLoadedLayoutFromName($da_action);

		if ($layout !== null) {
			$context['ep_cols'] = $layout;

			if ($context['template_layers'] != []) {
				$context['template_layers'][] = 'portal';
			}
		}
	}

	/**
	 * @param string|null $init_action
	 *
	 * @return array|null
	 */
	public static function getLoadedLayoutFromName(?string $init_action = null): ?array
	{
		$obj = new self;
		$cache_key = 'envisionportal_' . $obj->getMatch($init_action) . '_' . $obj->getGeneralMatch();

		if (($data = cache_get_data($cache_key, 3600)) === null) {
			$id_layout = $obj->getMatchedLayout($init_action);

			if ($id_layout !== null) {
				$data = $obj->loadLayoutData($id_layout);
				cache_put_data($cache_key, $data, 3600);
			}
		}

		if ($data !== null) {
			return $obj->loadLayoutContext($data);
		}

		return null;
	}

	public static function loadModule(int $id_position): ?array
	{
		global $smcFunc, $txt;

		$request = $smcFunc['db_query']('', '
			SELECT
				type
			FROM {db_prefix}ep_module_positions
			WHERE id_position = {int:id_position}',
			[
				'id_position' => $id_position,
			]
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			return null;
		}

		[$type] = $smcFunc['db_fetch_row']($request);

		$module_fields = [];
		$request = $smcFunc['db_query']('', '
			SELECT
				name, id_module_position, value
			FROM {db_prefix}ep_module_field_data
			WHERE id_module_position = {int:id_position}',
			[
				'id_position' => $id_position,
			]
		);

		while (list ($name, $id, $value) = $smcFunc['db_fetch_row']($request)) {
			$module_fields[$id][$name] = $value;
		}

		$module = 'EnvisionPortal\Modules\\' . Util::camelize($type);
		$obj = class_exists($module) ? new $module : new Modules\Error_;
		$data = $obj->getDefaultProperties();

		if (isset($module_fields[$id_position])) {
			foreach ($module_fields[$id_position] as $key => $field) {
				$data[$key]['value'] = $field;
			}
		}

		if (!isset($data['module_title'])) {
			$data['module_title']['value'] = isset($txt['ep_modules'][$type], $txt['ep_modules'][$type]['title']) ? $txt['ep_modules'][$type]['title'] : $type;
		}

		return [$data, $type];
	}

	/**
	 * Fetch a layout from the database based on ID.
	 *
	 * The returned data depends on the mode:
	 *
	 * - `0`: Only return layout positions;
	 * - `1`: Sane as `0`, but also load all module data;
	 * - `2`: Same as `0`, but also load module titles.
	 *
	 * @todo Make $module_mode an enum. Requisites bumping the minimum supported PHP version fromm 7.4 too 8.1.
	 *
	 * @param int $id_layout   Exact layout id as is from the database.
	 * @param int $module_mode Mode. Returns `null` if out of bounds.
	 *
	 * @return array|null Layout data or `null` if not found.
	 */
	public static function getLoadedLayoutFromId(int $id_layout, int $module_mode = 1): ?array
	{
		global $txt;

		$obj = new self;
		$data = $obj->loadLayoutData($id_layout, $module_mode);

		if ($data !== null) {
			switch ($module_mode) {
				case 1:
					return $obj->loadLayoutContext($data);
				case 0:
					return $data['layout'];
				case 2:
					foreach ($data['layout'] as $id_layout_position => $row) {
						foreach ($row['modules'] as $module_position => $module) {
							$data['layout'][$id_layout_position]['modules'][$module_position]['module_title'] = $module['module_title'] ?? isset($txt['ep_modules'][$module['type']], $txt['ep_modules'][$module['type']]['title']) ? $txt['ep_modules'][$module['type']]['title'] : $module['type'];
						}
					}

					return $data['layout'];
			}
		}

		return null;
	}

	public static function main()
	{
		global $context, $txt;

		// A mobile device doesn't require a portal...
		if (defined('WIRELESS') && WIRELESS) {
			redirectexit('action=forum');
		}

		$context['sub_template'] = 'portal';
		$context['page_title'] = $context['forum_name'] . ' - ' . $txt['home'];
	}

	/**
	 * Fetch a layout from the database based on ID.
	 *
	 * The data to fetch from the depends on the mode:
	 *
	 * - `0`: Only fetch layout positions;
	 * - `1`: Also fetch module data from the database.
	 *
	 * @param int $id_layout   Exact layout id as is from the database.
	 * @param int $module_mode Mode.
	 *
	 * @return array|null Layout data or `null` if not found.
	 */
	protected function loadLayoutData(int $id_layout, int $module_mode = 1): ?array
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				id_layout_position, id_layout, x_pos, rowspan, y_pos,
				colspan, status, is_smf, id_position, position, type
			FROM {db_prefix}ep_layout_positions
				LEFT JOIN {db_prefix}ep_module_positions USING (id_layout_position)
			WHERE id_layout = {int:id_layout}',
			[
				'id_layout' => $id_layout,
			]
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			return null;
		}

		$rows = [];
		$cols = [];
		$loaded_ids = [];
		$data = ['modules' => []];
		$dbrows = [];

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if ($row['id_position'] !== null) {
				$loaded_ids[] = $row['id_position'];
			}

			$dbrows[] = $row;
		}

		if ($module_mode != 0) {
			$request = $smcFunc['db_query']('', '
				SELECT
					name, id_module_position, value
				FROM {db_prefix}ep_module_field_data
				WHERE id_module_position IN ({array_int:loaded_ids})',
				[
					'loaded_ids' => $loaded_ids,
				]
			);

			while (list ($name, $id, $value) = $smcFunc['db_fetch_row']($request)) {
				$data['modules'][$id][$name] = $value;
			}
		}

		foreach ($dbrows as $row) {
			if (!isset($data['layout'][$row['id_layout_position']])) {
				$rows[$row['id_layout_position']] = $row['x_pos'];
				$cols[$row['id_layout_position']] = $row['y_pos'];

				$data['layout'][$row['id_layout_position']] = new Layout(
					$row['id_layout_position'],
					$row['x_pos'],
					$row['rowspan'],
					$row['y_pos'],
					$row['colspan'],
					$row['is_smf'] != 0,
					$row['status'] == 'active'
				);
			}

			if ($row['id_position'] !== null) {
				$data['layout'][$row['id_layout_position']]->modules[$row['position']] = new Module(
					$row['type'],
					$row['id_position']
				);
			}
		}

		array_multisort($rows, $cols, $data['layout']);

		foreach ($data['layout'] as &$col) {
			ksort($col['modules']);
		}

		return $data;
	}

	protected function loadLayoutContext(array $data): array
	{
		foreach ($data['layout'] as $id_layout_position => $row) {
			foreach ($row['modules'] as $module_position => $module) {
				$time = hrtime(true);
				$data['layout'][$id_layout_position]['modules'][$module_position] = $this->process_module(
					$data['modules'],
					$module
				);
				$data['layout'][$id_layout_position]['modules'][$module_position]['time'] = (hrtime(true) - $time) / 1e6;
			}
		}

		if ($this->sharedModuleData['permissions'] != []) {
			if (!defined('SMF_VERSION')) {
				$boards_can = [];
				foreach ($this->sharedModuleData['permissions'] as $permission_name) {
					$boards_can[$permission_name] = boardsAllowedTo($permission_name);
				}
			} else {
				$boards_can = boardsAllowedTo($this->sharedModuleData['permissions'], true, false);
			}
		}

		if ($this->sharedModuleData['member_ids'] != []) {
			loadMemberData($this->sharedModuleData['member_ids']);
		}

		foreach ($this->sharedModuleData['member_ids'] as $id_member) {
			loadMemberContext($id_member);
		}

		foreach ($data['layout'] as $id_layout_position => $row) {
			foreach ($row['modules'] as $module_position => $module) {
				$time = hrtime(true);
				if ($module['class'] instanceof SharedPermissionsInterface) {
					$module['class']->setSharedPermissions($boards_can);
				}
				$data['layout'][$id_layout_position]['modules'][$module_position]['time'] += (hrtime(true) - $time) / 1e6;
			}
		}

		return $data['layout'];
	}

	private array $sharedModuleData = [
		'member_ids' => [],
		'permissions' => [],
	];

	private function process_module(array $module_fields, array $data)
	{
		global $options, $txt, $user_info, $scripturl;

		$data['module_title'] = $data['module_title'] ?? isset($txt['ep_modules'][$data['type']], $txt['ep_modules'][$data['type']]['title']) ? $txt['ep_modules'][$data['type']]['title'] : $data['type'];
		$module = 'EnvisionPortal\Modules\\' . Util::camelize($data['type']);

		$data['class'] = class_exists($module) ? new $module : new Modules\Error_;
		$fields = [];

		foreach ($data['class']->getDefaultProperties() as $key => $field) {
			$fields[$key] = $module_fields[$data['id']][$key] ?? $field['value'];
		}

		if ($data['class'] instanceof SharedPermissionsInterface) {
			$this->sharedModuleData['permissions'] = array_merge(
				$this->sharedModuleData['permissions'],
				$data['class']->fetchPermissionNames()
			);
		}

		$data['class']($fields);

		if ($data['class'] instanceof SharedMemberDataInterface) {
			$this->sharedModuleData['member_ids'] = array_merge(
				$this->sharedModuleData['member_ids'],
				$data['class']->fetchMemberIds()
			);
		}

		if (!isset($fields['module_target'])) {
			$data['module_target'] = '_self';
		}

		if (!empty($fields['module_icon'])) {
			$data['module_icon'] = sprintf(
				'<span class="fugue fugue-%s" aria-hidden="true"></span>&nbsp;',
				$fields['module_icon']
			);
		}

		if (isset($fields['module_link'])) {
			if (empty(parse_url($fields['module_link'], PHP_URL_SCHEME))) {
				$fields['module_link'] = $scripturl . '?' . $fields['module_link'];
			}

			$data['module_title'] = sprintf(
				'<a href="%s" target="%s">%s</a>',
				$fields['module_link'],
				$data['module_target'],
				$data['module_title']
			);
		}

		$collapsed_key = 'ep_hide_module_' . $data['id'];
		$data['is_collapsed'] = $user_info['is_guest'] ? !empty($_COOKIE[$collapsed_key]) : !empty($options[$collapsed_key]);

		if (!isset($data['header_display'])) {
			$data['header_display'] = 0;
		}

		call_integration_hook('integrate_ep_process_module', [&$data]);

		return $data;
	}
}