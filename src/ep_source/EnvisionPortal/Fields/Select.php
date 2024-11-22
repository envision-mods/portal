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

use EnvisionPortal\FieldInterface;

class Select implements FieldInterface
{
	private array $field;
	private string $key;
	private string $type;

	public function __construct(array $field, string $key, string $type)
	{
		$this->field = $field;
		$this->key = $key;
		$this->type = $type;
	}

	public function __toString(): string
	{
		global $txt;

		$ret = '
					<select name="' . $this->key . '" id="' . $this->key . '">';

		foreach ($this->field['options'] as $option) {
			if (is_array($option)) {
				$ret .= '
						<optgroup label="' . $option['name'] . '">';
				foreach ($option['boards'] as $board) {
					$ret .= '
							<option value="' . $board['id'] . '"' . ($board['id'] == $this->field['value'] ? ' selected' : '') . '>' . $board['name'] . '</option>';
				}
				$ret .= '
						</optgroup>';
			} else {
				$ret .= '
						<option value="' . $option . '"' . ($option == $this->field['value'] ? ' selected' : '') . '>' . $txt['ep_modules'][$this->type][$this->key][$option] . '</option>';
			}
		}

		$ret .= '
					</select>';

		return $ret;
	}
}
