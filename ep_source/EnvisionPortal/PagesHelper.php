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

class PagesHelper
{
	/**
	 * Gets all membergroups and filters them according to the parameters.
	 *
	 * @param int[] $checked    list of all id_groups to be checked (have a mark in the checkbox).
	 *                          Default is an empty array.
	 * @param bool  $inherited  whether or not to filter out the inherited groups. Default is false.
	 *
	 * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
	 */
	public function listGroups(array $checked = [], $inherited = false)
	{
		global $modSettings, $smcFunc, $sourcedir, $txt;

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
	 * Loads all pages from the db
	 *
	 * @return string[]
	 */
	public function total_getMenu()
	{
		global $smcFunc;

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT
				id_page, name, type, body, status, permissions, description
			FROM {db_prefix}envision_pages'
		);
		$pages = [];

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = $row;
		}

		return $pages;
	}

	/**
	 * Createlist callback, used to display um entries
	 *
	 * @param int    $start
	 * @param int    $items_per_page
	 * @param string $sort
	 *
	 * @return string[]
	 */
	public function list_getMenu($start, $items_per_page, $sort)
	{
		global $smcFunc;

		$pages = [];
		$request = $smcFunc['db_query']('', '
			SELECT
				id_page, name, type, slug, status, description
			FROM {db_prefix}envision_pages
			ORDER BY {raw:sort}
			LIMIT {int:offset}, {int:limit}',
			[
				'sort' => $sort,
				'offset' => $start,
				'limit' => $items_per_page,
			]
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = $row;
		}

		return $pages;
	}

	/**
	 * Createlist callback to determine the number of pages
	 *
	 * @return int
	 */
	public function list_getNumPages()
	{
		global $smcFunc;

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT COUNT(*)
			FROM {db_prefix}envision_pages'
		);
		[$numPages] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $numPages;
	}

	/**
	 * Removes menu item(s) from the um system
	 *
	 * @param int[] $ids
	 */
	public function deletePage(array $ids): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}envision_pages
			WHERE id_page IN ({array_int:page_list})',
			[
				'page_list' => $ids,
			]
		);
	}

	/**
	 * Changes the status of a page from active to inactive
	 */
	public function updatePage(array $updates): void
	{
		global $smcFunc;

		foreach ($this->total_getMenu() as $item) {
			$status = !empty($updates['status'][$item['id_page']]) ? 'active' : 'inactive';

			if ($status != $item['status']) {
				$smcFunc['db_query'](
					'',
					'
					UPDATE {db_prefix}envision_pages
					SET status = {string:status}
					WHERE id_page = {int:item}',
					[
						'status' => $status,
						'item' => $item['id_page'],
					]
				);
			}
		}
	}

	/**
	 * Checks if there is an existing um id with the same slug before saving
	 *
	 * @param int    $id
	 * @param string $slug
	 *
	 * @return int
	 */
	public function checkPage($id, $slug): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_page
			FROM {db_prefix}envision_pages
			WHERE slug = {string:slug}
				AND id_page != {int:id}',
			[
				'slug' => $slug,
				'id' => $id ?: 0,
			]
		);
		$check = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		return $check;
	}

	/**
	 * Saves a new or updates an existing page
	 */
	public function savePage(array $data): void
	{
		global $smcFunc;

		/*
		 * Check specifically for four-byte characters.
		 *
		 * UTF-8 has single bytes (0-127), leading bytes (192-254) and continuation
		 * bytes (128-191).  The leading byte preceeds up to three continuation bytes.
		 * The algorithm for UTF-8 always consumes bytes in the same order no matter
		 * what CPU it is running on.
		 *
		 * Unicode code points  Encoding  Binary value
		 * -------------------  --------  ------------
		 *  U+000000-U+00007f   0xxxxxxx  0xxxxxxx
		 *
		 *  U+000080-U+0007ff   110yyyxx  00000yyy xxxxxxxx
		 *                      10xxxxxx
		 *
		 *  U+000800-U+00ffff   1110yyyy  yyyyyyyy xxxxxxxx
		 *                      10yyyyxx
		 *                      10xxxxxx
		 *
		 *  U+010000-U+10ffff   11110zzz  000zzzzz yyyyyyyy xxxxxxxx
		 *                      10zzyyyy
		 *                      10yyyyxx
		 *                      10xxxxxx
		 */
		foreach (['name', 'description', 'body'] as $el) {
			$data[$el] = preg_replace_callback(
				'/[\xF0-\xF4][\x80-\xbf]{3}/',
				function ($m) {
					$val = (ord($m[0][0]) & 0x07) << 18;
					$val += (ord($m[0][1]) & 0x3F) << 12;
					$val += (ord($m[0][2]) & 0x3F) << 6;
					$val += ord($m[0][3]) & 0x3F;

					return '&#' . $val . ';';
				},
				$data[$el]
			);
		}

		if (!empty($data['in'])) {
			$smcFunc['db_query'](
				'',
				'
				UPDATE {db_prefix}envision_pages
				SET
					name = {string:name},
					slug = {string:slug},
					type = {string:type},
					body = {string:body},
					status = {string:status},
					permissions = {string:permissions},
					description = {string:description}
				WHERE id_page = {int:id}',
				[
					'id' => $data['in'],
					'name' => $data['name'],
					'slug' => $data['slug'],
					'type' => $data['type'],
					'body' => $data['body'],
					'status' => $data['status'],
					'permissions' => implode(',', array_filter($data['permissions'], 'strlen')),
					'description' => $data['description'],
				]
			);
		} else {
			$smcFunc['db_insert'](
				'insert',
				'{db_prefix}envision_pages',
				[
					'name' => 'string',
					'slug' => 'string',
					'type' => 'string',
					'body' => 'string',
					'status' => 'string',
					'permissions' => 'string',
					'description' => 'string',
				],
				[
					$data['name'],
					$data['slug'],
					$data['type'],
					$data['body'],
					$data['status'],
					implode(',', array_filter($data['permissions'], 'strlen')),
					$data['description'],
				],
				['id_page']
			);
		}
	}

	/**
	 * Fetch a specific page
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function fetchPage($id): array
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				id_page, name, slug, type, body, status, permissions, description
			FROM {db_prefix}envision_pages
			WHERE id_page = {int:page}',
			[
				'page' => $id,
			]
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		return [
			'id' => $row['id_page'],
			'name' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['name']),
			'slug' => $row['slug'],
			'type' => $row['type'],
			'permissions' => explode(',', $row['permissions']),
			'body' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['body']),
			'status' => $row['status'],
			'description' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['description']),
		];
	}

	/**
	 * Removes all pages
	 */
	public function deleteallPages(): void
	{
		global $smcFunc;

		$smcFunc['db_query'](
			'',
			'
			TRUNCATE {db_prefix}envision_pages'
		);
	}
}