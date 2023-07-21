<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace EnvisionPortal;

class Menu
{
	public static function main(&$menu_buttons): void
	{
		global $smcFunc, $user_info, $scripturl, $modSettings;

		// Make damn sure we ALWAYS load last. Priority: 100!
		if (substr($modSettings['integrate_menu_buttons'], -25) !== 'EnvisionPortal\Menu::main') {
			remove_integration_function('integrate_menu_buttons', 'EnvisionPortal\Menu::main');
			add_integration_function('integrate_menu_buttons', 'EnvisionPortal\Menu::main');
		}

		for ($i = 1; $i <= ($modSettings['ep_button_count'] ?? 0); $i++) {
			$key = 'ep_button_' . $i;

			if (!isset($modSettings[$key])) {
				continue;
			}
			$row = json_decode($modSettings[$key], true);
			$temp_menu = [
				'title' => $row['name'],
				'href' => ($row['type'] == 'forum' ? $scripturl . '?' : '') . $row['link'],
				'target' => $row['target'],
				'show' => (allowedTo('admin_forum') || array_intersect(
							$user_info['groups'],
							$row['groups']
						) != []) && $row['active'],
			];

			self::recursive_button($temp_menu, $menu_buttons, $row['parent'], $row['position'], $key);
		}
	}

	public static function replay(&$menu_buttons)
	{
		global $context;

		$context['replayed_menu_buttons'] = $menu_buttons;
	}

	private static function recursive_button(array $needle, array &$haystack, $insertion_point, $where, $key): void
	{
		foreach ($haystack as $area => &$info) {
			if ($area == $insertion_point)
				switch ($where) {
					case 'before':
					case 'after':
						self::insert_button([$key => $needle], $haystack, $insertion_point, $where);
						break 2;

					case 'child_of':
						$info['sub_buttons'][$key] = $needle;
						break 2;
				}
			elseif (!empty($info['sub_buttons'])) {
				self::recursive_button($needle, $info['sub_buttons'], $insertion_point, $where, $key);
			}
		}
	}

	private static function insert_button(array $needle, array &$haystack, $insertion_point, $where = 'after'): void
	{
		$offset = 0;

		foreach ($haystack as $area => $dummy) {
			if (++$offset && $area == $insertion_point) {
				break;
			}
		}

		if ($where == 'before') {
			$offset--;
		}

		$haystack = array_slice($haystack, 0, $offset, true) + $needle + array_slice($haystack, $offset, null, true);
	}
}
