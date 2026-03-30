<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal;

/**
 * Generic interface for sharing ANY type of data between modules
 * 
 * Modules declare what data keys they need during initialization,
 * then Portal loads that data once and injects it to all modules.
 * 
 * Example usage:
 * - fetchSharedDataKeys() returns ['articles', 'comments']
 * - Portal batches all requests: articles, comments
 * - setSharedData() receives ['articles' => [...], 'comments' => [...]]
 */
interface SharedDataInterface
{
	/**
	 * Returns data keys and their loading requirements
	 *
	 * @return array Keyed by data type, containing array of identifiers
	 *               Example: ['articles' => [1, 2, 3], 'comments' => [5, 6, 7]]
	 */
	public function fetchSharedDataKeys(): array;

	/**
	 * Receive the loaded shared data
	 *
	 * @param array $shared_data Keyed by data type, contains loaded data
	 *                           Example: ['articles' => [...], 'comments' => [...]]
	 */
	public function setSharedData(array $shared_data): void;
}