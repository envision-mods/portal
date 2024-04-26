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

class Page implements EntityInterface, \ArrayAccess
{
	use ArrayAccessTrait;

	public int $id;
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
	 * @param int $id
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
		int $id,
		?string $slug,
		?string $name,
		?string $type,
		?string $body,
		?array $permissions,
		?string $status,
		?string $description,
		int $views
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
	public function getName(): ?string
	{
		return $this->name;
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
	 * @return ?string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}
}