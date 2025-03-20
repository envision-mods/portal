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

class Page implements \ArrayAccess
{
	use ArrayAccessTrait;

	public ?int $id;
	public ?string $slug;
	public ?string $name;
	public ?string $type;
	public ?string $body;
	public ?array $permissions;
	public ?string $status;
	public ?string $description;
	private PageModeInterface $mode;
	public int $views;

	/**
	 * @param ?int $id
	 * @param ?string $slug
	 * @param ?string $name
	 * @param ?string $type
	 * @param ?string $body
	 * @param ?array $permissions
	 * @param ?string $status
	 * @param ?string $description
	 * @param int $views
	 */
	public function __construct(
		?int $id,
		?string $slug,
		?string $name,
		?string $type,
		?string $body,
		?array $permissions,
		?string $status,
		?string $description,
		int $views = 0,
	) {
		$this->id = $id;
		$this->slug = $slug;
		$this->name = $name;
		$this->type = $type;
		$this->body = $body;
		$this->permissions = $permissions;
		$this->status = $status;
		$this->description = $description;
		$this->views = $views;
	}

	/**
	 * @return ?string
	 */
	public function getBody(): ?string
	{
		$cn = strpos($this->type, '\\') !== false ? $this->type : 'EnvisionPortal\PageModes\\' . $this->type;
		$this->mode = new $cn;

		return $this->mode->parse($this->body);
	}

	/**
	 * @return bool
	 */
	public function isAllowed(): bool
	{
		global $user_info;

		return allowedTo('admin_forum') || array_intersect(
				$user_info['groups'],
				$this->permissions
			) != [] && $this->status == 'active';
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
	public function insert(): void
	{
		global $user_info;

		DatabaseHelper::insert('{db_prefix}envision_pages', [
			'name' => ['string-255', $this->name],
			'slug' => ['string-65', $this->slug],
			'type' => ['string-65', $this->type],
			'description' => ['string-255', $this->description],
			'status' => ['string-255', $this->status],
			'body' => ['string', $this->body],
			'permissions' => ['string-255', implode(',', $this->permissions)],
			'poster_name' => ['string', $user_info['name']],
			'id_member' => ['int', $user_info['id']],
			'created_at' => ['raw', 'NOW()'],
		]);
	}

	/**
	 * @uses DatabaseHelper::update
	 */
	public function update(): void
	{
		DatabaseHelper::update('{db_prefix}envision_pages', [
			'name' => ['string-255', $this->name],
			'slug' => ['string-65', $this->slug],
			'type' => ['string-65', $this->type],
			'description' => ['string-255', $this->description],
			'status' => ['string-255', $this->status],
			'body' => ['string', $this->body],
			'permissions' => ['string-255', implode(',', $this->permissions)],
			'updated_at' => ['raw', 'NOW()'],
		],'id_page', $this->id);
	}

	public function delete(): void
	{
		DatabaseHelper::deleteMany('{db_prefix}envision_pages', 'id_page', $this->id);
	}

	/**
	 * @uses DatabaseHelper::deleteMany
	 */
	public static function deleteMany(array $ids): void
	{
		DatabaseHelper::deleteMany('{db_prefix}envision_pages', 'id_page', $ids);
	}

	/**
	 * @uses DatabaseHelper::deleteAll()
	 */
	public static function deleteAll(): void
	{
		DatabaseHelper::deleteAll('{db_prefix}envision_pages');
	}

	/**
	 * Increment the view count for a page if not already viewed in the current session.
	 *
	 * @uses DatabaseHelper::increment()
	 */
	public function incrementViews(): void
	{
		if (!isset($_SESSION['viewed_page_' . $this->id])) {
			DatabaseHelper::increment('{db_prefix}envision_pages', 'views', 'id_page', $this->id);

			$_SESSION['viewed_page_' . $this->id] = '1';
			$this->views++;
		}
	}
}
