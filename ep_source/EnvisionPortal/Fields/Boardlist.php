<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal\Fields;

use EnvisionPortal\UpdateFieldInterface;

class Boardlist implements UpdateFieldInterface
{
	private array $field;
	private string $key;
	private string $type;

	public function __construct(array $field, string $key, string $type)
	{
		$this->field = $field;
		$this->key = $key;
		$this->type = $type;
		global $smcFunc;

		$checked = explode(',', $field['value']);
		$request = $smcFunc['db_query']('', '
			SELECT
				id_cat, c.name, id_board, b.name, child_level
			FROM {db_prefix}boards AS b
				JOIN {db_prefix}categories AS c USING (id_cat)
			WHERE redirect = {string:empty_string}
			ORDER BY board_order',
			[
				'empty_string' => '',
			]
		);
		$this->options = [];
		while ([$id_cat, $cat_name, $id_board, $name, $child_level] = $smcFunc['db_fetch_row']($request)) {
			if (!isset($this->options[$id_cat])) {
				$this->options[$id_cat] = [
					'name' => $cat_name,
					'boards' => [],
				];
			}

			$this->options[$id_cat]['boards'][$id_board] = [
				'name' => $name,
				'child_level' => $child_level,
				'checked' => in_array($id_board, $checked) || in_array(-3, $checked),
			];
		}
		$smcFunc['db_free_result']($request);
	}

	public function beforeSave($val): string
	{
		$ret = '';
		if ($val !== null && is_array($val)) {
			$ret = implode(',', $val);
		}

		return $ret;
	}

	public function __toString(): string
	{
		global $context;

		$ret = '';

		foreach ($this->options as $category) {
			$ret .= '
							<fieldset>
								<legend>
									' . $category['name'] . '
								</legend>
						<ul class="reset">';

			foreach ($category['boards'] as $id => $board) {
				$ret .= '
							<li>
								<label style="padding-' . ($context['right_to_left'] ? 'right' : 'left' . ': ' . $board['child_level']) . 'em;">
									<input type="checkbox" class="input_check" name="' . $this->key . '[]" value="' . $id . '"' . ($board['checked'] ? ' checked' : '') . ' />
									' . $board['name'] . '
								</label>
							</li>';
			}
		}

		$ret .= '
						</ul>
							</fieldset>';

		return $ret;
	}
}
