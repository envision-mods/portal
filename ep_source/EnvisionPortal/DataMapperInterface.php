<?php

declare(strict_types=1);

/**
 * @package EnvisionPortal
 * @version 2.0.2
 * @author John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace EnvisionPortal;

interface DataMapperInterface
{
	/**
	 * Fetches data from the database based on specified criteria.
	 *
	 * @param array  $selects Table columns to select.
	 * @param array  $params  Parameters to substitute into query text.
	 * @param array  $joins   Zero or more *complete* JOIN clauses.
	 *                        E.g.: 'LEFT JOIN messages AS m ON (a.id_msg = m.id_msg)'
	 * @param array  $where   Zero or more conditions for the WHERE clause.
	 *                        Conditions will be placed in parentheses and concatenated with AND.
	 *                        If this is left empty, no WHERE clause will be used.
	 * @param array  $order   Zero or more conditions for the ORDER BY clause.
	 *                        If this is left empty, no ORDER BY clause will be used.
	 * @param array  $group   Zero or more conditions for the GROUP BY clause.
	 *                        If this is left empty, no GROUP BY clause will be used.
	 * @param int    $limit   Maximum number of results to retrieve.
	 *                        If this is left empty, all results will be retrieved.
	 * @param int    $offset  Offset for LIMIT clause.
	 *
	 * @return array The result as associative array of database rows.
	 */
	public function fetchBy(
		array $selects,
		array $params = [],
		array $joins = [],
		array $where = [],
		array $order = [],
		array $group = [],
		int $limit = null,
		int $offset = null
	): array;

	/**
	 * Inserts a new record into the database.
	 *
	 * @param EntityInterface $entity The entity to insert.
	 */
	public function insert(EntityInterface $entity): void;

	/**
	 * Updates an existing record in the database.
	 *
	 * @param EntityInterface $entity The entity to update.
	 */
	public function update(EntityInterface $entity): void;

	/**
	 * Deletes a record from the database.
	 *
	 * @param EntityInterface $entity The entity to delete.
	 */
	public function delete(EntityInterface $entity): void;

	/**
	 * Deletes multiple records from the database.
	 *
	 * @param array $ids Array of IDs of the records to delete.
	 */
	public function deleteMany(array $ids): void;

	/**
	 * Deletes all records from the database.
	 */
	public function deleteAll(): void;

	/**
	 * Increments the views counter for a specific entity in the database.
	 *
	 * @param EntityInterface $entity The entity for which to increment the views counter.
	 */
	public function incrementViews(EntityInterface $entity): void;
}
