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

class BitwiseChecklist implements UpdateFieldInterface
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
		$ret = 0;
		if ($val !== null && is_array($val)) {
			foreach ($this->field['options'] as $i) {
				if (isset($val[$i])) {
					$ret |= $i;
				} else {
					$ret &= ~$i;
				}
			}
		}

		return (string)$ret;
	}

	public function __toString(): string
	{
		global $txt;

		$ret = '
							<fieldset data-c=" ' . $txt['check_all'] . '"><ul class="reset">';

		foreach ($this->field['options'] as $i) {
			$ret .= '
							<li><label>
								<input type="checkbox" class="input_check" name="' . $this->key . '[' . $i . ']"' . (($this->field['value'] & $i) == $i ? ' checked' : '') . ' />
								' . $txt['ep_modules'][$this->type][$this->key][$i];

			$ret .= '
							</label></li>';
		}

		$ret .= '
						</ul></fieldset>';

		return $ret;
	}
}
