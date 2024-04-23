<?php

namespace EnvisionPortal;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
interface ModuleInterface
{
	public function __invoke(array $fields);

	public function getDefaultProperties(): array;

	public function __toString();
}