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
	public static function fetch(string $call): ?Page
	{
		global $smcFunc;

		if (!is_numeric($call)) {
			$query = 'slug = {string:page}';
		} else {
			$query = 'id_page = {int:page}';
		}

		$entries = Page::fetchBy(['*'], ['page' => $call], [], [$query]);

		return $entries[0] ?? null;
	}

	public static function main(): void
	{
		global $context, $modSettings, $smcFunc;

		if (empty($modSettings['ep_portal_mode'])) {
			loadTemplate('ep_template/EnvisionPortal');
		}

		$call = $_GET['page'] ?? redirectexit();

		// Put it in the session to prevent it from being logged when they refresh the page.
		$_SESSION['last_page_id'] = $call;

		$row = self::fetch($call);

		if ($row === null) {
			fatal_lang_error('ep_pages_not_exist', false);
		}
		if ($row->isAllowed()) {
			$context['page_title'] = $row->name;

			$context['page_data'] = [
				'body' => $row->getBody(),
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
	<meta property="og:description" content="' . $smcFunc['htmlspecialchars']($row->getDescription()) . '">';
			} else {
				$context['meta_description'] = $smcFunc['htmlspecialchars']($row->getDescription());
				self::setMetaProperty('type', 'website');
			}
		} else {
			fatal_lang_error('ep_pages_no_access', false);
		}
	}

	public static function setMetaTag(string $key, string $value): void
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

	public static function setMetaProperty(string $key, string $value): void
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