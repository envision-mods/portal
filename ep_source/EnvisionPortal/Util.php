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

use FilesystemIterator;
use Generator;

class Util
{
	public static function decamelize(string $string): string
	{
		return strtolower(preg_replace('/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '_$0', $string));
	}

	public static function camelize(string $string): string
	{
		return preg_replace_callback(
			'/(?:^|_)([a-z])/',
			function ($m) {
				return strtoupper($m[1]);
			},
			$string
		);
	}

	public static function find_integrated_classes(string $interface): Generator
	{
		if (count($results = call_integration_hook('integrate_envisionportal_classlist')) > 0) {
			foreach ($results as $classlist) {
				foreach ($classlist as $fqcn) {
					if (class_exists($fqcn) && is_subclass_of($fqcn, $interface, true)) {
						yield null => $fqcn;
					}
				}
			}
		}
	}

	/**
	 * Similar to array_map, but maps key-value pairs (tuples).
	 *
	 * Applies the callback to the elements of the given iterable.
	 * Original values (and keys) are lost during transformation!
	 *
	 * @param callable $callback This must return a list with two
	 *                           elements; the first one becomes the key
	 *                           and the second one becomes the value.
	 * @param iterable $iterator An iterable to run through $callback.
	 *
	 * @return Generator
	 */
	public static function map(callable $callback, iterable $iterator): Generator
	{
		foreach ($iterator as $k => $v) {
			yield call_user_func($callback, $v, $k);
		}
	}

	public static function find_classes(FilesystemIterator $iterator, string $ns, string $interface): Generator
	{
		foreach ($iterator as $file_info) {
			if (class_exists($fqcn = $ns . $file_info->getBasename('.php')) && is_subclass_of(
					$fqcn,
					$interface,
					true
				)) {
				yield $file_info->getBasename('.php') => $fqcn;
			}
		}

		yield from self::find_integrated_classes($interface);
	}
}