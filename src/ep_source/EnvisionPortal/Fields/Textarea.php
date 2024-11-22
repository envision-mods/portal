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

class Textarea implements FieldInterface
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
		return '
					<textarea class="w100" name="' . $this->key . '" id="' . $this->key . '">' . $this->field['value'] . '</textarea>';
	}
}
