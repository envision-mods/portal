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

class Pages
{
	public static function main(): void
	{
		global $context, $modSettings, $smcFunc, $txt, $user_info;

		if (!$modSettings['ep_portal_mode']) {
			loadTemplate('ep_template/EnvisionPortal');
		}

		$call = isset($_GET['page']) ? $_GET['page'] : redirectexit();

		// Put it in the session to prevent it from being logged when they refresh the page.
		$_SESSION['last_page_id'] = $call;

		if (!is_numeric($call)) {
			$query = 'slug = {string:page}';
		} else {
			$query = 'id_page = {int:page}';
		}

		$request = $smcFunc['db_query']('', '
			SELECT name, type, body, permissions, status, description
			FROM {db_prefix}envision_pages
			WHERE ' . $query . '
			LIMIT 1',
			[
				'page' => $call,
			]
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			fatal_lang_error('ep_pages_not_exist', false);
		}

		$row = $smcFunc['db_fetch_assoc']($request);

		if (allowedTo('admin_forum') || array_intersect(
				$user_info['groups'],
				explode(',', $row['permissions']) != []
			) && $row['status'] == 'active') {
			$context['page_title'] = $row['name'];

			if (!isset($_SESSION['viewed_page_' . $call])) {
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}envision_pages
					SET views = views + 1
					WHERE ' . $query,
					[
						'page' => $call,
					]
				);

				$_SESSION['viewed_page_' . $call] = '1';
			}

			$cn = strpos($row['type'], '\\') !== false ? $row['type'] : 'EnvisionPortal\PageModes\\' . $row['type'];
			$obj = new $cn;

			$context['page_data'] = [
				'body' => $obj->parse($row['body']),
			];
			$context['sub_template'] = 'envision_pages';
			$context['linktree'][] = [
				'name' => $context['page_title'],
			];
			if (!defined('SMF_VERSION')) {
				$context['html_headers'] .= '
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="' . $context['forum_name'] . '">
	<meta property="og:title" content="' . $context['page_title'] . '">
	<meta property="og:description" content="' . $smcFunc['htmlspecialchars']($row['description']) . '">';
			} else {
				$context['meta_description'] = $smcFunc['htmlspecialchars']($row['description']);
				$this->setMetaProperty('type', 'website');
			}
		} else {
			fatal_lang_error('ep_pages_no_access', false);
		}
	}

	public function setMetaTag(string $key, string $value): void
	{
		global $context;

		$found = false;

		foreach ($context['meta_tags'] as $i => $m) {
			if (isset($m['name']) && $m['name'] == $key) {
				$context['meta_tags'][$i]['content'] = $value;
				$found = true;
			}
		}

		if (!$found) {
			$context['meta_tags'][] = ['name' => $key, 'content' => $value];
		}
	}

	public function setMetaProperty(string $key, string $value): void
	{
		global $context;

		$found = false;

		foreach ($context['meta_tags'] as $i => $m) {
			if (isset($m['property']) && $m['property'] == 'og:' . $key) {
				$context['meta_tags'][$i]['content'] = $value;
				$found = true;
			}
		}

		if (!$found) {
			$context['meta_tags'][] = ['property' => 'og:' . $key, 'content' => $value];
		}
	}
}
