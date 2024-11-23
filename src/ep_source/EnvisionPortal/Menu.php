<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @license   http://opensource.org/licenses/MIT MIT
 * @author    John Rayes <live627@gmail.com>
 */

namespace EnvisionPortal;

/**
 * Handles the dynamic addition and manipulation of menu buttons in the Envision Portal.
 */

class Menu
{
	/**
	 * Main method for injecting dynamic buttons into the menu.
	 *
	 * Ensures that this method is always executed last by managing its integration priority.
	 *
	 * @param array $menu_buttons Reference to the existing menu button structure.
	 */
	public static function main(array &$menu_buttons): void
	{
		global $user_info, $scripturl, $modSettings;

		// Ensure this function is always integrated at the highest priority.
		$integration = 'EnvisionPortal\Menu::main';
		if (substr($modSettings['integrate_menu_buttons'], -strlen($integration)) !== $integration) {
			remove_integration_function('integrate_menu_buttons', $integration);
			add_integration_function('integrate_menu_buttons', $integration);
		}

		// Process each dynamically configured button.
		$buttonCount = (int) ($modSettings['ep_button_count'] ?? 0);
		for ($i = 1; $i <= $buttonCount; $i++) {
			$key = 'ep_button_' . $i;

			if (empty($modSettings[$key])) {
				continue;
			}

			$row = json_decode($modSettings[$key], true);
			$tempMenu = [
				'title' => $row['name'],
				'href' => ($row['type'] === 'forum' ? $scripturl . '?' : '') . $row['link'],
				'target' => $row['target'],
				'show' => (allowedTo('admin_forum') || !empty(array_intersect($user_info['groups'], $row['groups'])))
					&& $row['active'],
			];

			self::addMenuButton($tempMenu, $menu_buttons, $row['parent'], $row['position'], $key);
		}
	}

	/**
	 * Recursively finds the correct position to insert a button into the menu structure.
	 *
	 * @param array  $button        The button to add.
	 * @param array  $menuStructure Reference to the current menu structure.
	 * @param string $parentKey     The parent button under which this button is placed.
	 * @param string $position      The position relative to the parent (e.g., before, after, child_of).
	 * @param string $buttonKey     The unique key for this button.
	 */
	public static function addMenuButton(array $button, array &$menuStructure, string $parentKey, string $position, string $buttonKey): void
	{
		foreach ($menuStructure as $key => &$menu) {
			if ($key === $parentKey) {
				switch ($position) {
					case 'before':
					case 'after':
						self::insertButton([$buttonKey => $button], $menuStructure, $parentKey, $position);
						break 2;

					case 'child_of':
						$menu['sub_buttons'][$buttonKey] = $button;
						break 2;
				}
			} elseif (!empty($menu['sub_buttons'])) {
				self::addMenuButton($button, $menu['sub_buttons'], $parentKey, $position, $buttonKey);
			}
		}
	}

	/**
	 * Replay the current state of menu buttons for debugging purposes.
	 *
	 * @param array $menu_buttons The current menu button structure.
	 */
	public static function replay(array &$menu_buttons): void
	{
		global $context;
		$context['replayed_menu_buttons'] = $menu_buttons;
	}

	/**
	 * Inserts a button into a specific position within the menu structure.
	 *
	 * @param array  $button        The button to insert.
	 * @param array  $menuStructure Reference to the current menu structure.
	 * @param string $referenceKey  The key to position relative to.
	 * @param string $position      The position relative to the reference (before or after).
	 */
	private static function insertButton(array $button, array &$menuStructure, string $referenceKey, string $position = 'after'): void
	{
		$offset = 0;
		foreach ($menuStructure as $key => $value) {
			if (++$offset && $key === $referenceKey) {
				break;
			}
		}

		if ($position === 'before') {
			$offset--;
		}

		$menuStructure = array_slice($menuStructure, 0, $offset, true)
			+ $button
			+ array_slice($menuStructure, $offset, null, true);
	}
}