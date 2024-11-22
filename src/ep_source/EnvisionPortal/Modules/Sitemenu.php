<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;

class Sitemenu implements ModuleInterface
{
	private array $menu;

	public function __invoke(array $fields)
	{
		global $context;

		switch ($fields['menu'] ?? '') {
			default:
				if (empty($context['menu_buttons'])) {
					setupMenuContext();
				}
				$this->menu = $context['menu_buttons'];
		}
	}

	function __toString()
	{
		$ret = '
			<ul>';

		foreach ($this->menu as $button) {
			$ret .= sprintf(
				!empty($button['sub_buttons'])
					? '
				<li><details%4$s>
					<summary><a href="%s"%s>%s</a></summary>'
					: '
				<li><a href="%s"%s>%s</a>',
				$button['href'],
				isset($button['target']) ? ' target="' . $button['target'] . '"' : '',
				$button['title'],
				$button['active_button'] ? ' open' : '',
			);

			if (!empty($button['sub_buttons'])) {
				$ret .= '
					<ul>';

				foreach ($button['sub_buttons'] as $childbutton) {
					$ret .= sprintf(
						!empty($childbutton['sub_childbuttons'])
							? '
						<li><details>
							<summary><a href="%s"%s>%s</a></summary>'
							: '
						<li><a href="%s"%s>%s</a>',
						$childbutton['href'],
						isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '',
						$childbutton['title'],
					);

					if (!empty($childbutton['sub_buttons'])) {
						$ret .= '
							<ul>';

						foreach ($childbutton['sub_buttons'] as $grandchildbutton) {
							$ret .= '
								<li><a href="' . $grandchildbutton['href'] . '"' . (isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '') . '>' . $grandchildbutton['title'] . '</a></li>';
						}

						$ret .= '
							</ul>
							</details>';
					}

					$ret .= '</li>';
				}
				$ret .= '
					</ul>
					</details>';
			}
			$ret .= '</li>';
		}

		$ret .= '
			</ul>';

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'heart',
			],
			'module_link' => [
				'value' => 'action=profile',
			],
		];
	}
}