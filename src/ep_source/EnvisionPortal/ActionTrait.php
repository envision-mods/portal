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

trait ActionTrait
{
	/****************************
	 * Internal static properties
	 ****************************/

	/**
	 * @var static
	 *
	 * An instance of this class.
	 * This is used by the load() method to prevent multiple instantiations.
	 */
	protected static self $obj;

	/***********************
	 * Public static methods
	 ***********************/

	/**
	 * Static wrapper for constructor.
	 *
	 * @return static An instance of this class.
	 */
	public static function load(): static
	{
		if (!isset(static::$obj)) {
			static::$obj = new static();
		}

		return static::$obj;
	}

	/**
	 * Convenience method to load() and execute() an instance of this class.
	 */
	public static function call(): void
	{
		self::load()->execute();
	}

	/******************
	 * Internal methods
	 ******************/

	/**
	 * Constructor. Protected to force instantiation via self::load().
	 */
	protected function __construct()
	{
	}
}

?>