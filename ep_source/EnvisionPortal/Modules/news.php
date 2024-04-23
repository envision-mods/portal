<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class News implements ModuleInterface
{
	use ModuleTrait;

	/**
	 * Cuts a string up until a given number of words.
	 *
	 * - Doesn't slice words. It CAN interrupt a sentence, however...
	 * - Preserves all whitespace characters.
	 *
	 * @access private
	 *
	 * @param string $str The text string to split
	 * @param int    $limit Maximum number of words to show. Default is 70.
	 * @param string $rep What to append if $string contains more words than specified by 4max. Default is three dots.
	 *
	 * @return string The truncated string.
	 * @since  1.0
	 */
	private function truncate($str, $n = 300, $delim = '…')
	{
		if (strlen($str) > $n) {
			preg_match('/^([\s\S]{1,' . $n . '})[\s]+?[\s\S]+/', $str, $matches);

			return rtrim($matches[1]) . $delim;
		} else {
			return $str;
		}
	}

	private $posts = [];

	public function __invoke(array $fields)
	{
		global $context, $scripturl, $settings, $smcFunc, $modSettings, $user_info;

		$board = $fields['board'] ?? 1;
		$limit = $fields['limit'] ?? 5;

		// Adjust the query. This isn't defined for SMF 2.0.
		if (!defined('SMF_VERSION')) {
			$request = $smcFunc['db_query']('', '
				SELECT
					t.id_topic, t.id_board, t.num_replies, t.num_views
				FROM {db_prefix}topics AS t
					JOIN {db_prefix}boards AS b USING (id_board)
				WHERE
					id_board = {int:current_board}' . ($modSettings['postmod_active'] ? '
					AND approved = {int:is_approved}' : '') . '
					AND {query_see_board}
				ORDER BY id_topic DESC
				LIMIT ' . $limit,
				[
					'current_board' => $board,
					'is_approved' => 1,
				]
			);
		} else {
			$request = $smcFunc['db_query']('', '
				SELECT
					t.id_topic, t.id_board, t.num_replies, t.num_views
				FROM {db_prefix}topics AS t
				WHERE
					id_board = {int:current_board}' . ($modSettings['postmod_active'] ? '
					AND approved = {int:is_approved}' : '') . '
					AND {query_see_topic_board}
				ORDER BY id_topic DESC
				LIMIT ' . $limit,
				[
					'current_board' => $board,
					'is_approved' => 1,
				]
			);
		}

		if ($smcFunc['db_num_rows']($request) == 0) {
			return null;
		}

		$topic_list = [];
		while (list ($id_topic, $id_board, $replies, $views) = $smcFunc['db_fetch_row']($request)) {
			$topic_list[] = $id_topic;
			$this->posts[$id_topic] = [
				'replies' => $replies,
				'views' => $views,
			];
		}
		$smcFunc['db_free_result']($request);

		if (!defined('SMF_VERSION')) {
			$boards_can = [
				'post_reply_any' => boardsAllowedTo('post_reply_any'),
				'post_reply_own' => boardsAllowedTo('post_reply_own'),
				'moderate_board' => boardsAllowedTo('moderate_board'),
			];
		} else {
			$boards_can = boardsAllowedTo(['post_reply_own', 'post_reply_any', 'moderate_board'], true, false);
		}

		$can_reply_own = $boards_can['post_reply_own'] === [0] || in_array($board, $boards_can['post_reply_own']);
		$can_reply_any = $boards_can['post_reply_any'] === [0] || in_array($board, $boards_can['post_reply_any']);
		$can_moderate = $boards_can['moderate_board'] === [0] || in_array($board, $boards_can['moderate_board']);

		$request = $smcFunc['db_query']('', '
			SELECT
				m.subject, COALESCE(mem.real_name, m.poster_name) AS poster_name, m.poster_time,
				m.body, m.smileys_enabled, m.id_msg, m.icon, t.id_topic, m.id_member
			FROM {db_prefix}topics AS t
				JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE t.id_topic IN ({array_int:topic_list})',
			[
				'topic_list' => $topic_list,
			]
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$this->findMessageIcons($row['icon']);
			$row['body'] = nl2br(
				$this->truncate(
					strip_tags(
						strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), ['<br>' => "\n"])
					)
				)
			);

			censorText($row['subject']);
			censorText($row['body']);

			$this->posts[$row['id_topic']] += [
				'subject' => $row['subject'],
				'preview' => $row['body'],
				'time' => timeformat($row['poster_time']),
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'poster' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name'],
				'icon' => sprintf(
					'<img src="%s/post/%s.%s" class="icon" alt="%2$s" />',
					$settings[$this->icons[$row['icon']]],
					$row['icon'],
					defined('SMF_VERSION') ? 'png' : 'gif'
				),
				'can_reply' => !empty($row['locked']) ? $can_moderate : $can_reply_any || ($can_reply_own && $row['id_member'] == $user_info['id']),
			];
		}

		$smcFunc['db_free_result']($request);
	}

	private function findMessageIcons($icon)
	{
		global $context, $modSettings, $settings;

		if (!defined('SMF_VERSION')) {
			// $this->icons says where each icon should come from - here we set up the ones which will always exist!
			if (empty($this->icons)) {
				$stable_icons = [
					'xx',
					'thumbup',
					'thumbdown',
					'exclamation',
					'question',
					'lamp',
					'smiley',
					'angry',
					'cheesy',
					'grin',
					'sad',
					'wink',
					'moved',
					'recycled',
					'wireless',
					'clip',
				];
				$this->icons = [];
				foreach ($stable_icons as $stable_icon) {
					$this->icons[$stable_icon] = 'images_url';
				}
			}

			// Message Icon Management... check the images exist.
			if (empty($modSettings['messageIconChecks_disable'])) {
				// If the current icon isn't known, then we need to do something...
				if (!isset($this->icons[$icon])) {
					$this->icons[$icon] = file_exists(
						$settings['theme_dir'] . '/images/post/' . $icon . '.gif'
					) ? 'images_url' : 'default_images_url';
				}
			} elseif (!isset($this->icons[$icon])) {
				$this->icons[$icon] = 'images_url';
			}
		} else {
			if (empty($this->icons)) {
				$this->icons = [];
				foreach ($context['stable_icons'] as $stable_icon) {
					$this->icons[$stable_icon] = 'images_url';
				}
			}

			if (!isset($this->icons[$icon])) {
				$this->icons[$icon] = file_exists(
					$settings['theme_dir'] . '/images/post/' . $icon . ''
				) ? 'images_url' : 'default_images_url';
			}
		}
	}

	public function __toString()
	{
		global $context, $txt, $options, $scripturl;

		$ret = '';

		if ($this->posts == []) {
			$ret .= $this->error('empty');
		} else {
			foreach ($this->posts as $topic) {
				$ret .= '
						<article>
						<div class="title_bar">
							<h4 class="titlebg">
								' . $topic['icon'] . '
								<a href="' . $topic['href'] . '">' . $topic['subject'] . '</a>
							</h4>
						</div>';

				$ret .= '
							<p><small>' . $txt['posted_by'] . ' ' . $topic['poster'] . ' | ' . $topic['time'] . '
							(' . $topic['views'] . ' ' . $txt['views'] . ')';

				if (!empty($topic['replies'])) {
					$ret .= '
							<a href="' . $topic['href'] . '">' . $topic['replies'] . ' ' . $txt['replies'] . '</a>';
				}

				if ($topic['can_reply']) {
					$ret .= ' | <a href="' . $topic['href'] . '#quickreply">' . $txt['reply'] . '</a>';
				}

				$ret .= '
							</small></p>';

				$ret .= '<p>
							' . $topic['preview'] . '
						</p></article>';
			}
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'newspaper',
			],
			'board' => [
				'type' => 'select',
				'preload' => function ($field) {
					global $smcFunc;

					$request = $smcFunc['db_query']('', '
		SELECT
			id_cat, c.name, id_board, b.name
		FROM {db_prefix}boards AS b
			JOIN {db_prefix}categories AS c USING (id_cat)
		WHERE redirect = {string:empty_string}
		ORDER BY board_order',
						[
							'empty_string' => '',
						]
					);
					$field['options'] = [];
					while ([$id_cat, $cat_name, $id_board, $name] = $smcFunc['db_fetch_row']($request)) {
						if (!isset($field['options'][$id_cat])) {
							$field['options'][$id_cat] = [
								'name' => $cat_name,
								'boards' => [],
							];
						}

						$field['options'][$id_cat]['boards'][] = [
							'id' => $id_board,
							'name' => $name,
						];
					}
					$smcFunc['db_free_result']($request);

					return $field;
				},
				'value' => '1',
			],
			'limit' => [
				'type' => 'text',
				'value' => '5',
			],
		];
	}
}