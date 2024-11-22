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

class Checklist implements UpdateFieldInterface
{
	private array $field;
	private string $key;
	private string $type;
	private bool $has_order;

	public function __construct(array $field, string $key, string $type)
	{
		global $txt;

		$this->field = $field;
		$this->key = $key;
		$this->type = $type;
		$this->has_order = !empty($field['order']);
		$this->options = [];
		$order = [];

		if (strpos($this->field['value'], ';')) {
			$checked = explode(';', $this->field['value']);
			$order = explode(',', $checked[0]);
			$checked = explode(',', $checked[1]);
		} else {
			$checked = explode(',', $this->field['value']);
		}
		if (!isset($field['options_names'])) {
			$field['options_names'] = [];
		}

		foreach ($field['options'] as $i => $name) {
			if (isset($order[$i])) {
				$this->options[$order[$i]] = [
					'name' => $field['options_names'][$order[$i]] ?? $txt['ep_modules'][$this->type][$this->key][$field['options'][$order[$i]]],
					'checked' => in_array($order[$i], $checked),
				];
			} else {
				$this->options[$i] = [
					'name' => $field['options_names'][$i] ?? $txt['ep_modules'][$this->type][$this->key][$field['options'][$i]],
					'checked' => in_array($i, $checked),
				];
			}
		}
	}

	public function beforeSave($val): string
	{
		$ret = '';
		if ($val !== null && is_array($val)) {
			if ($this->has_order && isset($_POST[$this->key . 'order']) && is_array(
					$_POST[$this->key . 'order']
				)) {
				$ret = implode(',', $_POST[$this->key . 'order']) . ';';
			}

			$ret .= implode(',', $val);
		}

		return $ret;
	}

	public function __toString(): string
	{
		global $txt;

		$ret = '
							<fieldset data-c=" ' . $txt['check_all'] . '"';

		if ($this->has_order) {
			$ret .= ' class="ordered-checklist" data-up="' . $txt['checks_order_up'] . '" data-down="' . $txt['checks_order_down'] . '"';
		}

		$ret .= '><ul class="reset">';

		foreach ($this->options as $i => $group) {
			$ret .= '
							<li><label>
								<input type="checkbox" class="input_check" name="' . $this->key . '[]" value="' . $i . '" ' . ($group['checked'] ? 'checked' : '') . ' />
								' . $group['name'];

			if ($this->has_order) {
				$ret .= '
								<input type="hidden" name="' . $this->key . 'order[]" value="' . $i . '" />';
			}

			$ret .= '
							</label></li>';
		}

		$ret .= '
						</ul></fieldset>';

		return $ret;
	}
}
