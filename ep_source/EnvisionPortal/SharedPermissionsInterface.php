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
 * Interface for modules that share permissions in Envision Portal.
 *
 * Modules implementing this interface can specify permissions to fetch,
 * enabling Envision Portal to combine queries when possible.
 */
interface SharedPermissionsInterface
{
	/**
	 * Returns an array of permission names to be fetched.
	 *
	 * @return array An array of permission names.
	 */
	public function fetchPermissionNames(): array;

	/**
	 * Sets shared permissions for the module.
	 *
	 * @param array $boards_can An array of shared permissions.
	 */
	public function setSharedPermissions(array $boards_can): void;
}
