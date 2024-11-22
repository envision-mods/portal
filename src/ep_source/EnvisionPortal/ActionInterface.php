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
 * Interface for all action classes.
 *
 * In general, constructors for classes implementing this interface should
 * be protected in order to force instantiation via load(). This is because
 * there should normally only ever be one instance of an action.
 */
interface ActionInterface
{
	/****************
	 * Public methods
	 ****************/

	/**
	 * This method should function as the dispatcher to whatever sub-action
	 * methods are necessary. It is also the place to do any heavy lifting
	 * needed to finalize setup before dispatching to a sub-action method.
	 */
	public function execute(): void;

	/***********************
	 * Public static methods
	 ***********************/

	/**
	 * Static wrapper for constructor.
	 *
	 * @return self An instance of the class.
	 */
	public static function load(): self;

	/**
	 * Convenience method to load() and execute() an instance of the class.
	 */
	public static function call(): void;
}

?>