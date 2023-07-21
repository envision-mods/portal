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

class Radio implements FieldInterface
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

		$ret = '';

		foreach ($this->field['options'] as $option) {
			$ret .= '
						<label><input type="radio" name="' . $this->key . '" value="' . $option . '"' . ($option == $this->field['value'] ? ' checked' : '') . '>' . $txt['ep_modules'][$this->type][$this->key][$option] . '</label>';
		}

		return $ret;
	}
}
