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

class Check implements UpdateFieldInterface
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

	public function beforeSave($val): string
	{
		return $val !== null ? '1' : '0';
	}

	public function __toString(): string
	{
		return '
					<input type="checkbox" name="' . $this->key . '" id="' . $this->key . '"' . (!empty($this->field['value']) ? ' checked' : '') . ' value="1" class="input_check" />';
	}
}
