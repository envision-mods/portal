<?php

namespace EnvisionPortal;

class DatabaseHelper
{
	/**
	 * Generator that runs queries about attachment data and yields the result rows.
	 *
	 * @param array  $selects Table columns to select.
	 * @param array  $params  Parameters to substitute into query text.
	 * @param string $from    FROM clause. Default: '{db_prefix}attachments AS a'
	 * @param array  $joins   Zero or more *complete* JOIN clauses.
	 *                        E.g.: 'LEFT JOIN {db_prefix}messages AS m ON (a.id_msg = m.id_msg)'
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
			FROM ' . implode("\n\t\t\t\t\t", array_merge([$from], $joins)) . ($where === [] ? '' : '
			WHERE (' . implode(') AND (', $where) . ')') . ($order === [] ? '' : '
			ORDER BY ' . implode(', ', $order)) . ($limit !== null ? '
			LIMIT ' . $limit : '') . ($offset !== null ? '
			offset ' . $offset : ''),
			$params,
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = $row;
		}

		return $pages;
	}

	public static function insert(DataMapperInterface $dataMapper): void
	{
		global $smcFunc;

		$sql = '';
		$where_params = [];
		$coluumn_params = [];
		$columns = $dataMapper->getColumnsToInsert();
		foreach ($dataMapper->getColumnInfo() as $column => [$type, $data]) {
			if (isset($columns[$column])) {
				$column_params[$column] = $type;
				$where_params[] = $data;
			}
		}

		$smcFunc['db_insert'](
			'insert',
			'{db_prefix}' . $dataMapper->getTableName(),
			$coluumn_params,
			$where_params,
			[]
		);
	}

	public static function update(DataMapperInterface $dataMapper): void
	{
		global $smcFunc;

		$sql = '';
		$where_params = [];
		$columns = $dataMapper->getColumnsToUpdate();
		foreach ($dataMapper->getColumnInfo() as $column => [$type, $data]) {
			if (isset($columns[$column])) {
				$sql .= $column . ' = {' . $type . ':' . $column . '}';
				$where_params[$column] = $data;
			}
		}

		$smcFunc['db_query'](
			'',
			'
				UPDATE {db_prefix}' . $dataMapper->getTableName() . '
				SET ' . $sql . '
				WHERE {identifier:col} = {int:id}',
			$where_params + [
				'id' => $dataMapper->getId(),
				'col' => $dataMapper->getIdInfo(),
			]
		);
	}

	public static function deleteMany(DataMapperInterface $dataMapper, array $ids): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . $dataMapper->getTableName() . '
			WHERE {identifier:col} IN ({array_int:ids})',
			[
				'ids' => $ids,
				'col' => $dataMapper->getIdInfo(),
			]
		);
	}

	public static function deleteAll(DataMapperInterface $dataMapper): void
	{
		global $smcFunc;

		$smcFunc['db_query'](
			'',
			'TRUNCATE {db_prefix}' . $dataMapper->getTableName()
		);
	}

	public static function increment(DataMapperInterface $dataMapper, string $col, int $id): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}}' . $dataMapper->getTableName() . '
			SET {identifier:col} = {identifier:col} + 1
			WHERE {identifier:where_col} = {int:id}',
			[
				'ids' => $id,
				'where_col' => $dataMapper->getIdInfo(),
				'col' => $col,
			]
		);
	}
}