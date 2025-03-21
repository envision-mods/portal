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

class Button implements \ArrayAccess
{
	use ArrayAccessTrait;

	public int $id;
	public string $name;
	public string $target;
	public string $type;
	public string $position;
	public array $permissions;
	public string $link;
	public string $status;
	public string $parent;

	public function __construct(
		int $id,
		string $name,
		string $target,
		string $type,
		string $position,
		array $permissions,
		string $link,
		string $status,
		string $parent
	) {
		$this->id = $id;
		$this->name = $name;
		$this->target = $target;
		$this->type = $type;
		$this->position = $position;
		$this->permissions = $permissions;
		$this->link = $link;
		$this->status = $status;
		$this->parent = $parent;
	}

	/**
	 * @uses DatabaseHelper::fetchBy
	 */
	public static function fetchBy(
		array $selects,
		array $params = [],
		array $joins = [],
		array $where = [],
		array $order = [],
		array $group = [],
		int $limit = null,
		int $offset = null
	): array {
		$entries = DatabaseHelper::fetchBy($selects, '{db_prefix}ep_menu', $params, $joins, $where, $order, $group, $limit, $offset);
		$buttons = [];

		foreach ($entries as $entry) {
			$buttons[] = new Button(
				(int) $entry['id_button'],
				$entry['name'] ?? '',
				$entry['target'] ?? '',
				$entry['type'] ?? '',
				$entry['position'] ?? '',
				isset($entry['permissions']) ? explode(',', $entry['permissions']) : [],
				$entry['link'] ?? '',
				$entry['status'] ?? '',
				$entry['parent'] ?? '',
			);
		}

		return $buttons;
	}

	/**
	 * @uses DatabaseHelper::insert
	 */
	public function insert(): void
	{
		DatabaseHelper::insert(
			'{db_prefix}ep_menu',
			[
				'name' => ['string', $this->name],
				'type' => ['string', $this->type],
				'target' => ['string', $this->target],
				'position' => ['string', $this->position],
				'link' => ['string', $this->link],
				'status' => ['string', $this->status],
				'permissions' => ['string', implode(',', array_filter($this->permissions, 'strlen'))],
				'parent' => ['string', $this->parent],
			]
		);
	}

	/**
	 * @uses DatabaseHelper::update
	 */
	public function update(): void
	{
		DatabaseHelper::update(
			'{db_prefix}ep_menu',
			[
				'name' => ['string', $this->name],
				'type' => ['string', $this->type],
				'target' => ['string', $this->target],
				'position' => ['string', $this->position],
				'link' => ['string', $this->link],
				'status' => ['string', $this->status],
				'permissions' => ['string', implode(',', array_filter($this->permissions, 'strlen'))],
				'parent' => ['string', $this->parent],
			],
			'id_button',
			$this->id
		);
	}

	public function delete(): void
	{
		DatabaseHelper::delete('{db_prefix}ep_menu', 'id_button', $this->id);
	}

	/**
	 * @uses DatabaseHelper::deleteMany
	 */
	public static function deleteMany(array $ids): void
	{
		DatabaseHelper::deleteMany('{db_prefix}ep_menu', 'id_button', $ids);
	}

	/**
	 * @uses DatabaseHelper::deleteAll()
	 */
	public static function deleteAll(): void
	{
		DatabaseHelper::deleteAll('{db_prefix}ep_menu');
	}

}
