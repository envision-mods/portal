<?php

namespace EnvisionPortal;

interface EntityInterface
{
	/**
	 * @return bool
	 */
	public function isAllowed(): bool;

	/**
	 * @return array
	 */
	public function getColumnInfo(): array;
}