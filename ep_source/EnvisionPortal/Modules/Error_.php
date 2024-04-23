<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleTrait;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Error_// implements ModuleInterface
{
	use ModuleTrait;

	private $fields;

	public function __invoke(array $fields)
	{
		$this->fields = $fields;
	}

	public function __toString()
	{
		return $this->error('function_error', 'critical');
	}

	public function getDefaultProperties(): array
	{
		return [];
	}
}