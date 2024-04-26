<?php

namespace EnvisionPortal;

interface DataMapperInterface
{
	/**
	 * Generator that runs queries about attachment data and yields the result rows.
	 *
	 * @param array    $selects Table columns to select.
	 * @param array    $params  Parameters to substitute into query text.
	 * @param array    $joins   or more *complete* JOIN clauses.
	 *                          E.g.: 'LEFT JOIN {db_prefix}messages AS m ON (a.id_msg = m.id_msg)'
	 * @param array    $where   Zero or more conditions for the WHERE clause.
	 *                          Conditions will be placed in parentheses and concatenated with AND.
	 *                          If this is left empty, no WHERE clause will be used.
	 * @param array    $order   Zero or more conditions for the ORDER BY clause.
	 *                          If this is left empty, no ORDER BY clause will be used.
	 * @param array    $group
	 * @param int|null $limit   Maximum number of results to retrieve.
	 *                          If this is left empty, all results will be retrieved.
	 * @param int|null $offset
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

	public function insert(EntityInterface $entity): void;

	public function update(EntityInterface $entity): void;

	public function delete(EntityInterface $entity): void;

	public function deleteMany(array $ids): void;

	public function deleteAll(EntityInterface $entity): void;
}