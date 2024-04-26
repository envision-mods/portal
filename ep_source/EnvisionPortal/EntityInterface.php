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

interface EntityInterface
{
	/**
	 * @return bool
	 */
	public function isAllowed(): bool;

	/**
	 * @return int
	 */
	public function getId(): int;
}