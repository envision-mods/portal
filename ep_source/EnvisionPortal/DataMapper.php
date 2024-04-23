<?php

namespace EnvisionPortal;

class DataMapper implements DataMapperInterface
{
	private array $column_info;

	public function getIdInfo(): string
	{
		return 'id_page';
	}

	/**
	 * @return array
	 */
	public function getColumnsToInsert(): array
	{
		return [
			'name',
			'slug',
			'type',
			'body',
			'status',
			'permissions',
			'poster_name',
			'id_member',
			'created_at',
			'description',
		];
	}

	/**
	 * @return array
	 */
	public function getColumnsToUpdate(): array
	{
		return [
			'name',
			'slug',
			'type',
			'body',
			'status',
			'permissions',
			'description',
			'updated_at',
		];
	}

	/**
	 * @return string
	 */
	public function getTableName(): string
	{
		return '{db_prefix}envision_pages';
	}

	public function fetchBy(
		array $selects,
		array $params = [],
		array $joins = [],
		array $where = [],
		array $order = [],
		array $group = [],
		int $limit = null,
		int $offset = null
	): array {
		$entries = DatabaseHelper::fetchBy($selects, $this->getTableName(), $params, $joins, $where, $order, $group, $limit, $offset);
		$pages = [];

		foreach ($entries as $entry) {
			$pages[] = new Page(
				(int) $entry['id_page'],
				$entry['slug'] ?? null,
				$entry['name'] ?? null,
				$entry['type'] ?? null,
				$entry['body'] ?? null,
				isset($entry['permissions']) ? explode(',', $entry['permissions']) : null,
				$entry['status'] ?? null,
				$entry['description'] ?? null,
				(int) ($entry['views'] ?? null)
			);
		}

		return $pages;
	}

	public function insert(EntityInterface $entity): void
	{
		$this->column_info = $entity->getColumnInfo();
		DatabaseHelper::insert($this);
	}

	public function update(EntityInterface $entity): void
	{
		$this->column_info = $entity->getColumnInfo();
		DatabaseHelper::update($this);
	}

	public function delete(EntityInterface $entity): void
	{
		$this->deleteMany([$entity->getId()]);
	}

	public function deleteMany(array $ids): void
	{
		DatabaseHelper::deleteMany($this, $ids);
	}

	public function deleteAll(EntityInterface $entity): void
	{
		DatabaseHelper::deleteAll($this);
	}

	public function incrementViews(): void
	{
		global $smcFunc;

		if (!isset($_SESSION['viewed_page_' . $this->id])) {
			DatabaseHelper::increment($this, 'views', $entity->getId());

			$_SESSION['viewed_page_' . $this->id] = '1';
		}
	}

	/**
	 * @see EntityInterface::getColumnInfo()
	 */
	public function getColumnInfo(): array
	{
		return $this->column_info;
	}
}