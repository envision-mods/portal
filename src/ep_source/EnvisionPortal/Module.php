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

use EnvisionPortal\ModuleInterface;

class Module implements \ArrayAccess
{
	use ArrayAccessTrait;

	public string $module_title;
	public ModuleInterface $class;
	public string $module_target;
	public string $module_icon;
	public bool $is_collapsed;
	public string $header_display = '';
	public float $time;

	public string $type;
	public int $id;

	public function __construct(string $type, int $id)
	{
		$this->type = $type;
		$this->id = $id;
	}
}
