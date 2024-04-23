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

trait ArrayAccessTrait
{
	/**
	 * Whether the given offset exists.
	 *
	 * @param  mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->$offset);
	}

	/**
	 * Fetch the offset if it exists othwerwise return NULL.
	 *
	 * @param  mixed $offset
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->$offset ?? null;
	}

	/**
	 * Assign the offset.
	 *
	 * @param  mixed $offset
	 */
	public function offsetSet($offset, $value): void
	{
		$this->$offset = $value;
	}

	/**
	 * Unset the offset.
	 *
	 * @param  mixed $offset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->$offset);
	}
}