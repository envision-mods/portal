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
 * Utility class providing various helper functions.
 */
class Util
{
	/**
	 * Convert a camelCase string to snake_case.
	 *
	 * @param string $string The input string in camelCase format.
	 *
	 * @return string The converted string in snake_case format.
	 */
	public static function decamelize(string $string): string
	{
		return strtolower(preg_replace('/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '_$0', $string));
	}

	/**
	 * Convert a snake_case string to camelCase.
	 *
	 * @param string $string The input string in snake_case format.
	 *
	 * @return string The converted string in camelCase format.
	 */
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

	/**
	 * Find classes implementing a specified interface.
	 *
	 * @param string $interface The fully qualified interface name.
	 *
	 * @return \Generator A generator yielding the found class names.
	 */
	public static function find_integrated_classes(string $interface): \Generator
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
	 * Apply a callback to each element of the iterable.
	 *
	 * Similar to array_map, but maps key-value pairs (tuples).
	 *
	 * Applies the callback to the elements of the given iterable.
	 * Original values (and keys) are lost during transformation!
	 *
	 * The callback must return a list (array) with two elements; the
	 * first one becomes the key and the second one becomes the value.
	 *
	 * @param callable $callback The callback function to apply.
	 * @param iterable $iterator The iterable to apply the callback to.
	 *
	 * @return \Generator A generator yielding the results of applying the callback.
	 */
	public static function map(callable $callback, iterable $iterator): \Generator
	{
		foreach ($iterator as $k => $v) {
			yield call_user_func($callback, $v, $k);
		}
	}

	/**
	 * Find classes in a specified namespace implementing a specified interface.
	 *
	 * @param \FilesystemIterator $iterator  The iterator for the namespace.
	 * @param string              $ns        The namespace of the classes to search.
	 * @param string              $interface The fully qualified interface name.
	 *
	 * @return \Generator A generator yielding the found class names.
	 */
	public static function find_classes(\FilesystemIterator $iterator, string $ns, string $interface): \Generator
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

	/**
	 * Replace placeholders in a string with provided variables.
	 *
	 * Example:
	 * "The book {title} was written by {author_name}" becomes
	 * "The book Harry Potter was written by J.K. Rowling"
	 *
	 * @param string $template  The template string with placeholders.
	 * @param array  $variables The key-value store of variables and values.
	 *
	 * @return string The processed template string.
	 */
	public static function replaceVars(string $template, array $variables): string
	{
		return preg_replace_callback(
			'~{{1,2}\s*?([a-zA-Z0-9\-_.]+)\s*?}{1,2}~',
			fn($matches) => $variables[$matches[1]] ?? $matches[1],
			$template
		);
	}

	/**
	 * Get a list of membergroups based on specified criteria.
	 *
	 * @param int[] $checked   List of group IDs to be marked.
	 * @param bool  $inherited Whether to filter out inherited groups.
	 *
	 * @return array The list of membergroups filtered according to the criteria.
	 */
	public static function listGroups(array $checked = [], bool $inherited = false): array
	{
		global $modSettings, $smcFunc, $txt;

		loadLanguage('ManageBoards');
		$groups = [
			-1 => [
				'name' => $txt['parent_guests_only'],
				'checked' => in_array(-1, $checked) || in_array(-3, $checked),
				'is_post_group' => false,
			],
			0 => [
				'name' => $txt['parent_members_only'],
				'checked' => in_array(0, $checked) || in_array(-3, $checked),
				'is_post_group' => false,
			],
		];
		$where = ['id_group NOT IN (1, 3)'];

		if (!$inherited) {
			$where[] = 'id_parent = {int:not_inherited}';

			if (empty($modSettings['permission_enable_postgroups'])) {
				$where[] = 'min_posts = {int:min_posts}';
			}
		}
		$request = $smcFunc['db_query']('', '
			SELECT
				id_group, group_name, min_posts
			FROM {db_prefix}membergroups
			WHERE ' . implode("\n\t\t\t\tAND ", $where),
			[
				'not_inherited' => -2,
				'min_posts' => -1,
			]
		);

		while ([$id, $name, $min_posts] = $smcFunc['db_fetch_row']($request)) {
			$groups[$id] = [
				'name' => trim($name),
				'checked' => in_array($id, $checked) || in_array(-3, $checked),
				'is_post_group' => $min_posts != -1,
			];
		}
		$smcFunc['db_free_result']($request);

		return $groups;
	}

	/**
	 * Process a list of items, sorting and slicing as needed.
	 *
	 * @param int    $start          The index to start slicing from.
	 * @param int    $items_per_page The number of items per page.
	 * @param string $sort           The sorting criteria.
	 * @param array  $list           The list of items to process.
	 *
	 * @return array The processed list of items.
	 */
	public static function process(int $start, int $items_per_page, string $sort, array $list): array
	{
		$tmp = [];
		$sort = strtok($sort, ' ');
		$desc = strtok(' ') !== false;
		foreach ($list as $key => $row) {
			$tmp[$key] = $row[$sort];
		}
		array_multisort($tmp, $desc ? SORT_DESC : SORT_ASC, $list);

		if ($items_per_page) {
			$list = array_slice($list, $start, $items_per_page);
		}

		return $list;
	}
}