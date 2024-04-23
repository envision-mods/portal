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
	public static function map(callable $callback, iterable $iterator): \Generator
	{
		foreach ($iterator as $k => $v) {
			yield call_user_func($callback, $v, $k);
		}
	}

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
	 * Replaces placeholders from a string with the provided variables.
	 *
	 * Example:
	 * "The book {title} was written by {author_name}" becomes "The book Harry Potter was written by J.K. Rowling"
	 *
	 * @param string $template A template with variables placeholders {$variable}.
	 * @param array $variables A key => value store of variable names and values.
	 *
	 * @return string
	 */
	public static function replaceVars(string $template, array $array): string
	{
		return preg_replace_callback(
			'~{{1,2}\s*?([a-zA-Z0-9\-_.]+)\s*?}{1,2}~',
			fn($matches) => $variables[$matches[1]] ?? $matches[1],
			$template
		);
	}
	/**
	 * Gets all membergroups and filters them according to the parameters.
	 *
	 * @param int[] $checked    list of all id_groups to be checked (have a mark in the checkbox).
	 *                          Default is an empty array.
	 * @param bool  $inherited  Whether to filter out the inherited groups. Default is false.
	 *
	 * @return array All the membergroups filtered according to the parameters; empty array if something went wrong.
	 */
	public function listGroups(array $checked = [], bool $inherited = false): array
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
	 * @param int    $start          The item to start with.
	 * @param int    $items_per_page How many items to show per page.
	 * @param string $sort           A string indicating how to sort results.
	 * @param array  $list           Array of arrays or objects implementing
	 *                               ArrayAccess to sort and slice.
	 *
	 * @return array An array of info.
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