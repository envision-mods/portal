<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal;

/**
 * Interface for defining modules in Envision Portal.
 *
 * Modules are callable objects that provide properties which can be
 * configured by the admin to change the module's behavior.
 */
interface ModuleInterface
{
	/**
	 * Invoke the module with an array of fields.
	 *
	 * @param array $fields An array of fields to process.
	 */
	public function __invoke(array $fields);

	/**
	 * Get the default properties of the module.
	 *
	 * @return array The default properties of the module.
	 */
	public function getDefaultProperties(): array;

	/**
	 * Convert the module to its HTML representation.
	 *
	 * @return string The HTML representation of the module.
	 */
	public function __toString();
}
