<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace EnvisionPortal;

class Module implements \ArrayAccess
{
	use ArrayAccessTrait;

	public string $type;
	public int $id;

	public function __construct(string $type, int $id)
	{
		$this->type = $type;
		$this->id = $id;
	}
}
