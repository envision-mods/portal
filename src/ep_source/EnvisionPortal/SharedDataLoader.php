<?php

declare(strict_types=1);

namespace EnvisionPortal;

/**
 * Handles loading of custom shared data
 * Modules register loaders for custom data types
 */
class SharedDataLoader
{
	/**
	 * Registry of data loaders
	 * Format: ['data_type' => callable]
	 */
	private static array $loaders = [];

	/**
	 * Register a data loader callback
	 * 
	 * @param string $data_type Name of data type (e.g., 'articles', 'comments')
	 * @param callable $loader Function that loads the data
	 *                          Signature: function(array $ids): array
	 */
	public static function registerLoader(string $data_type, callable $loader): void
	{
		self::$loaders[$data_type] = $loader;
	}

	/**
	 * Load data for multiple types
	 * 
	 * @param array $requests Keyed by data type: ['articles' => [1,2,3], 'comments' => [5,6]]
	 * @return array Loaded data keyed by type
	 */
	public static function loadData(array $requests): array
	{
		$loaded_data = [];

		foreach ($requests as $data_type => $ids) {
			if (empty($ids)) {
				continue;
			}

			if (!isset(self::$loaders[$data_type])) {
				continue; // Skip if no loader registered
			}

			// Remove duplicates
			$ids = array_unique($ids);

			// Call the loader
			$loader = self::$loaders[$data_type];
			$loaded_data[$data_type] = $loader($ids);
		}

		return $loaded_data;
	}

	/**
	 * Clear all registered loaders
	 */
	public static function clearLoaders(): void
	{
		self::$loaders = [];
	}
}