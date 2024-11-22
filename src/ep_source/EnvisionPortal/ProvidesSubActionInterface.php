<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * This file contains code covered by:
 * Simple Machines (https://www.simplemachines.org)
 */

declare(strict_types=1);

namespace EnvisionPortal;

/**
 * Interface for all action classes to register their sub-actions.
 *
 * Any action class that delegates tasks to different sub-actions should
 * implement this interface and use  ProvidesSubActionTrait.  Use the public
 * methods defined in this interface to aad sub-actions.
 */
interface ProvidesSubActionInterface
{
	/****************
	 * Public methods
	 ****************/

	/**
	 * Set a default sub-action.
	 *
	 * The default sub-action is the one executed if no sub-action was requested.
	 *
	 * @param string $sa
	 */
	public function setDefaultSubAction(string $sa): void;

	/**
	 * Add a sub-action.
	 *
	 * A sub-action is another term for a route where a URL parameter, usually "sa", has
	 * its value mapped to some function.
	 *
	 * @param string $sa
	 * @param callable $cb
	 */
	public function addSubAction(string $sa, callable $cb): void;

	/**
	 * Determines whether the provided sub-action exists.
	 *
	 * @param string $sa
	 *
	 * @return bool
	 */
	public function hasSubAction(string $sa): bool;

	/**
	 * Finds a sub-action that is associated with the given keyword.
	 *
	 * If no sub-actions match or nothing was provided, the internal variable is set to
	 * either the previously specified default or the first registered sub-action.
	 *
	 * @param string|null $sa
	 */
	public function findRequestedSubAction(?string $sa = null): void;

	/**
	 * @return string|null
	 */
	public function getSubAction(): ?string;

	/**
	 * Executes the sub-action.
	 *
	 * @uses findRequestedSubAction() if $sa is null.
	 *
	 * @param string|null $sa
	 *
	 * @return mixed
	 */
	public function callSubAction(?string $sa = null): mixed;
}

?>