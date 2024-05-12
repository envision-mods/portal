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

class DataMapper implements DataMapperInterface
{
	/**
	 * @uses DatabaseHelper::fetchBy
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
	): array {
		$entries = DatabaseHelper::fetchBy($selects, '{db_prefix}envision_pages', $params, $joins, $where, $order, $group, $limit, $offset);
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

	/**
	 * @uses DatabaseHelper::insert
	 */
	public function insert(EntityInterface $entity): void
	{
		global $user_info;

		DatabaseHelper::insert('{db_prefix}envision_pages', [
			'name' => ['string-255', $entity->name],
			'slug' => ['string-65', $entity->slug],
			'type' => ['string-65', $entity->type],
			'description' => ['string-255', $entity->description],
			'status' => ['string-255', $entity->status],
			'body' => ['string', $entity->body],
			'permissions' => ['string-255', implode(',', $entity->permissions)],
			'poster_name' => ['string', $user_info['name']],
			'id_member' => ['int', $user_info['id']],
			'created_at' => ['raw', 'NOW()'],
		]);
	}

	/**
	 * @uses DatabaseHelper::update
	 */
	public function update(EntityInterface $entity): void
	{
		DatabaseHelper::update('{db_prefix}envision_pages', [
			'name' => ['string-255', $entity->name],
			'slug' => ['string-65', $entity->slug],
			'type' => ['string-65', $entity->type],
			'description' => ['string-255', $entity->description],
			'status' => ['string-255', $entity->status],
			'body' => ['string', $entity->body],
			'permissions' => ['string-255', implode(',', $entity->permissions)],
			'updated_at' => ['raw', 'NOW()'],
		],'id_page', $entity->getId());
	}

	public function delete(EntityInterface $entity): void
	{
		$this->deleteMany([$entity->getId()]);
	}

	/**
	 * @uses DatabaseHelper::deleteMany
	 */
	public function deleteMany(array $ids): void
	{
		DatabaseHelper::deleteMany('{db_prefix}envision_pages', 'id_page', $ids);
	}

	/**
	 * @uses DatabaseHelper::deleteAll()
	 */
	public function deleteAll(EntityInterface $entity): void
	{
		DatabaseHelper::deleteAll('{db_prefix}envision_pages');
	}

	/**
	 * Increment the view count for a page if not already viewed in the current session.
	 *
	 * @uses DatabaseHelper::increment
	 *
	 * @param EntityInterface $entity
	 */
	public function incrementViews(EntityInterface $entity): void
	{
		if (!isset($_SESSION['viewed_page_' . $entity->getId()])) {
			DatabaseHelper::increment('{db_prefix}envision_pages', 'views', 'id_page', $entity->getId());

			$_SESSION['viewed_page_' . $entity->getId()] = '1';
		}
	}
}
