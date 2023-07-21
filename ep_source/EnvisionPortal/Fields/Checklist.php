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

	public function __construct(array $field, string $key, string $type)
	{
		$this->field = $field;
		$this->key = $key;
		$this->type = $type;
		global $txt;

		$this->options = [];
		$order = [];

		if (strpos($this->field['value'], ';')) {
			$checked = explode(';', $this->field['value']);
			$order = explode(',', $checked[0]);
			$checked = explode(',', $checked[1]);
		} else {
			$checked = explode(',', $this->field['value']);
		}

		$can_order = count($order) == count($this->options);
		for ($i = 0, $n = count($field['options']); $i < $n; $i++) {
			if ($order != []) {
				$this->options[$order[$i]] = [
					'name' => $txt['ep_modules'][$this->type][$this->key][$field['options'][$order[$i]]],
					'checked' => in_array($order[$i], $checked),
				];
			} else {
				$this->options[] = [
					'name' => $txt['ep_modules'][$this->type][$this->key][$field['options'][$i]],
					'checked' => in_array($i, $checked),
				];
			}
		}
	}

	public function beforeSave($val): string
	{
		$ret = '';
		if ($val !== null && is_array($val)) {
			if (!empty($this->field['order']) && isset($_POST[$this->key . 'order']) && is_array(
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

		if (!empty($this->field['order'])) {
			$ret .= ' class="ordered-checklist" data-up="' . $txt['checks_order_up'] . '" data-down="' . $txt['checks_order_down'] . '"';
		}

		$ret .= '><ul class="reset">';

		foreach ($this->options as $i => $group) {
			$ret .= '
							<li><label>
								<input type="checkbox" class="input_check" name="' . $this->key . '[]" value="' . $i . '" ' . ($group['checked'] ? 'checked' : '') . ' />
								' . (isset($this->field['option_names'], $this->field['option_names'][$i]) ? $this->field['option_names'][$i] : $txt['ep_modules'][$this->type][$this->key][$this->field['options'][$i]]);

			if (!empty($this->field['order'])) {
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
