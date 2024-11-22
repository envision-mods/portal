<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;

class ThemeSelect implements ModuleInterface
{
	use ModuleTrait;

	private array $available_themes = [];
	private int $current_theme;

	/**
	 * Resolves the ID of a theme.
	 *
	 * The identifier can be specified in:
	 * - a GET variable
	 * - the session
	 * - user's preferences
	 * - board
	 * - forum default
	 *
	 * @return int Theme ID to load
	 */
	private function getThemeId(): int
	{
		global $modSettings, $user_info, $board_info;

		// The theme is the forum's default.
		$themeID = $modSettings['theme_guests'];

		if (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')) {
			// The theme was specified by REQUEST.
			if (!empty($_REQUEST['theme'])) {
				$themeID = (int)$_REQUEST['theme'];
				$_SESSION['theme'] = $themeID;
			} // The theme was specified by REQUEST... previously.
			elseif (!empty($_SESSION['theme'])) {
				$themeID = (int)$_SESSION['theme'];
			} // The theme is just the user's choice. (might use ?board=1;theme=0 to force board theme.)
			elseif (!empty($user_info['theme'])) {
				$themeID = $user_info['theme'];
			}
		} // The theme was specified by the board.
		elseif (!empty($board_info['theme'])) {
			$themeID = $board_info['theme'];
		}

		return $themeID;
	}

	public function __invoke(array $fields)
	{
		global $language, $smcFunc, $txt, $modSettings, $user_info, $settings;

		if (empty($modSettings['knownThemes'])) {
			return;
		}

		$this->current_theme = $this->getThemeId();

		$request = $smcFunc['db_query']('', '
			SELECT id_theme, variable, value
			FROM {db_prefix}themes
			WHERE variable IN (\'name\', \'theme_url\', \'theme_dir\', \'images_url\')
				AND id_theme IN ({array_string:known_themes})
				AND id_member = 0',
			[
				'known_themes' => explode(',', $modSettings['knownThemes']),
			]
		);
		while ([$id_theme, $variable, $value] = $smcFunc['db_fetch_row']($request)) {
			if (!isset($this->available_themes[$id_theme])) {
				$this->available_themes[$id_theme] = [
					'id' => $id_theme,
					'selected' => $this->current_theme == $id_theme,
				];
			}
			$this->available_themes[$id_theme][$variable] = $value;
		}
		$smcFunc['db_free_result']($request);

		foreach ($this->available_themes as $id_theme => $theme_data) {
			if (file_exists($theme_data['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php')) {
				include($theme_data['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php');
			} elseif (file_exists($theme_data['theme_dir'] . '/languages/Settings.' . $language . '.php')) {
				include($theme_data['theme_dir'] . '/languages/Settings.' . $language . '.php');
			}

			if (!defined('SMF_VERSION')) {
				$this->available_themes[$id_theme]['thumbnail_href'] = strtr(
					$txt['theme_thumbnail_href'] ?? $theme_data['images_url'] . '/thumbnail.gif',
					[$settings['images_url'] => $theme_data['images_url']]
				);
			} else {
				$this->available_themes[$id_theme]['thumbnail_href'] = sprintf(
					$txt['theme_thumbnail_href'] ?? '%s/thumbnail.png',
					$theme_data['images_url']
				);
			}
			$this->available_themes[$id_theme]['description'] = $txt['theme_description'] ?? '';

			if ($smcFunc['strlen']($theme_data['name']) > 18) {
				$this->available_themes[$id_theme]['name'] = $smcFunc['substr']($theme_data['name'], 0, 18) . '...';
			}
		}

		ksort($this->available_themes);
	}

	function __toString()
	{
		global $txt;

		if ($this->available_themes == []) {
			$ret = $this->error('empty');
		} else {
			$ret = '
							<div style="text-align: center;">
								<form action="" method="get" class="centertext">
									<p><img src="' . $this->available_themes[$this->current_theme]['thumbnail_href'] . '" alt="*" width=120 height=120/></p>
									<p class="smalltext">
										<select name="theme" onchange="ep_change_theme(this)">';

			foreach ($this->available_themes as $theme) {
				$ret .= '
											<option value="' . $theme['id'] . '"' . ($theme['selected'] ? ' selected' : '') . ' data-url="' . $theme['thumbnail_href'] . '">' . $theme['name'] . '</option>';
			}

			$ret .= '
										</select>
									</p>
									<p><input type="submit" value="' . $txt['ep_update'] . '" class="' . (defined('SMF_VERSION') ? 'button' :  'button_submit') . '" /></p>
								</form>
							</div>';
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'palette',
			],
			'module_link' => [
				'value' => 'action=profile',
			],
		];
	}
}