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

class DatabaseHelper
{
	/**
	 * Generator that runs queries about attachment data and yields the result rows.
	 *
	 * @param array  $selects Table columns to select.
	 * @param array  $params  Parameters to substitute into query text.
	 * @param string $from    FROM clause.
	 * @param array  $joins   Zero or more *complete* JOIN clauses.
	 *                        E.g.: 'LEFT JOIN messages AS m ON (a.id_msg = m.id_msg)'
	 * @param array  $where   Zero or more conditions for the WHERE clause.
	 *                        Conditions will be placed in parentheses and concatenated with AND.
	 *                        If this is left empty, no WHERE clause will be used.
	 * @param array  $order   Zero or more conditions for the ORDER BY clause.
	 *                        If this is left empty, no ORDER BY clause will be used.
	 * @param int    $limit   Maximum number of results to retrieve.
	 *                        If this is left empty, all results will be retrieved.
	 *
	 * @return array The result as associative array of database rows.
	 */
	public static function fetchBy(
		array $selects,
		string $from,
		array $params = [],
		array $joins = [],
		array $where = [],
		array $order = [],
		array $group = [],
		int $limit = null,
		int $offset = null
	): array {
		global $smcFunc;

		$pages = [];
		$request = $smcFunc['db_query'](
			'',
			'
			SELECT ' . implode(', ', $selects) . '
			FROM ' . implode("\n\t\t\t\t", array_merge([$from], $joins)) . ($where === [] ? '' : '
			WHERE (' . implode(') AND (', $where) . ')') . ($order === [] ? '' : '
			ORDER BY ' . implode(', ', $order)) . ($limit !== null ? '
			LIMIT ' . $limit : '') . ($offset !== null ? '
			OFFSET ' . $offset : ''),
			$params,
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = $row;
		}

		return $pages;
	}

	public static function insert(string $table_name, array $columns): void
	{
		global $smcFunc;

		$sql = '';
		$where_params = [];
		$coluumn_params = [];

		foreach ($columns as $column => [$type, $data]) {
			$column_params[$column] = $type;
			$where_params[] = $data;
		}

		$smcFunc['db_insert']('insert', $table_name, $coluumn_params, $where_params, []);
	}

	public static function update(string $table_name, array $columns, string $col, int $id): void
	{
		global $smcFunc;

		$sql = '';
		$where_params = ['id' => $id, 'col' => $col];

		foreach ($columns as $column => [$type, $data]) {
		// Are we restricting the length?
		if (strpos($type, 'string-') !== false)
			$sql .= $column . ' = ' . sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . ') ', $column);
		else
			$sql .= $column . ' = {' . $type . ':' . $column . '} ';
			$where_params[$column] = $data;
		}

		$smcFunc['db_query'](
			'',
			'
			UPDATE ' . $table_name . '
			SET ' . $sql . '
			WHERE {identifier:col} = {int:id}',
			$where_params
		);
	}

	public static function delete(string $table_name, string $col, int $id): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM ' . $table_name . '
			WHERE {identifier:col} = {int:id}',
			[
				'id' => $id,
				'col' => $col,
			]
		);
	}

	public static function deleteMany(string $table_name, string $col, array $ids): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM ' . $table_name . '
			WHERE {identifier:col} IN ({array_int:ids})',
			[
				'ids' => $ids,
				'col' => $col,
			]
		);
	}

	public static function deleteAll(string $table_name): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', 'TRUNCATE ' . $table_name);
	}

	public static function increment(string $table_name, string $increment_col, string $where_col, int $id): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			UPDATE ' . $table_name . '
			SET {identifier:col} = {identifier:col} + 1
			WHERE {identifier:where_col} = {int:id}',
			[
				'id' => $id,
				'where_col' => $where_col,
				'col' => $increment_col,
			]
		);
	}
}