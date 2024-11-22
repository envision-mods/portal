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
 * Interface for representing a field in a form.
 */
interface FieldInterface
{
	/**
	 * FieldInterface constructor.
	 *
	 * @param array  $field An array representing the field properties.
	 * @param string $value The current value of the field.
	 * @param string $type  The type of the field.
	 */
	public function __construct(array $field, string $value, string $type);

	/**
	 * Convert the field to its HTML representation.
	 *
	 * @return string The HTML representation of the field.
	 */
	public function __toString(): string;
}

/**
 * Interface for cacheable fields, allowing fetching and storing data.
 */
interface CacheableFieldInterface extends FieldInterface
{
	/**
	 * Fetch data from the database to "cache", or store in memory.
	 *
	 * This is useful when multiple fields of the same type are loaded
	 * and a static (unchanging) query is used to fetch data.
	 *
	 * @return array The fetched data.
	 */
	public function fetchData(): array;

	/**
	 * Store shared data from another field of the same type.
	 *
	 * @param array $data Data from another field of the same type.
	 */
	public function setData(array $data): void;
}

/**
 * Interface for fields that need to transform data before saving.es/MIT
 */
interface UpdateFieldInterface extends FieldInterface
{
	/**
	 * Transform data before it is saved.
	 *
	 * @param string|null $val The value from POST.
	 *
	 * @return string The value to save to the database.
	 */
	public function beforeSave(?string $val): string;
}