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

interface FieldInterface
{
	/**
	 * @param array  $field
	 * @param string $value
	 * @param string $type
	 */
	public function __construct(array $field, string $value, string $type);

	/**
	 * Output the HTML control for this field.
	 *
	 * @return string
	 */
	public function __toString(): string;
}

interface CacheableFieldInterface extends FieldInterface
{
	/**
	 * Fetch data from the database to "cache", or store in memory.  This is
	 * useful when multiple fields of the same type are loaded and a static
	 * (unchanging) query is used to fetch data.
	 *
	 * @return array
	 */
	public function fetchData(): array;

	/**
	 * Grab shared data from another field of the same type.
	 *
	 * @param array $data Data from another field of the same type.
	 */
	public function setData(array $data): void;
}

interface UpdateFieldInterface extends FieldInterface
{
	/**
	 * Transform data right before it is saved.
	 *
	 * @param $val ?string Value from POST.
	 *
	 * @return string Value to save to the database.
	 */
	public function beforeSave(?string $val): string;
}