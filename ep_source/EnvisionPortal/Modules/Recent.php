<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\{ModuleInterface, ModuleTrait};

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Recent implements ModuleInterface
{
	use ModuleTrait;

	/**
	 * Fetches the topics and their respective boards, ignoring those that the
	 * user cannot see or wants to ignore.  Returns an empty array if none are found.
	 *
	 * @param int   $num_recent     Maximum number of topics to show.  Default is 8.
	 * @param bool  $ignore         Whether to honor ignored boards.  Default is true.
	 * @param array $exclude_boards Boards to exclude as array values.  Default is null.
	 * @param array $include_boards Boards to include as array values.  Do note that, if
	 *                              specifiied, posts coming only from these boards
	 *                              will be counted.  Default is null.
	 *
	 * @return array
	 * @since  1.0
	 */
	private function getTopics(
		int $num_recent = 8,
		bool $ignore = true,
		array $exclude_boards = [],
		array $include_boards = []
	): ?array {
		global $context, $modSettings, $scripturl, $smcFunc, $user_info;

		if (isset($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0) {
			$exclude_boards[] = $modSettings['recycle_board'];
		}

		$where = ['t.id_last_msg >= {int:min_message_id}'];
		$where_params = [];
		if ($modSettings['postmod_active']) {
			$where[] = 't.approved = {int:is_approved}';
			$where[] = 'ml.approved = {int:is_approved}';
			$where_params['is_approved'] = 1;
		}
		if ($exclude_boards != []) {
			$where[] = 't.id_board NOT IN ({array_int:exclude_boards})';
			$where_params['exclude_boards'] = $exclude_boards;
		}
		if ($include_boards != []) {
			$where[] = 't.id_board IN ({array_int:include_boards})';
			$where_params['include_boards'] = $include_boards;
		}

		$boards = [];
		$request = $smcFunc['db_query'](
			'',
			'
			SELECT id_board, name
			FROM {db_prefix}boards
			ORDER BY board_order'
		);
		while (list ($id_board, $name) = $smcFunc['db_fetch_row']($request)) {
			$boards[$id_board] = $name;
		}
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT
				t.id_topic, t.id_board, t.num_replies, t.num_views
			FROM {db_prefix}topics AS t
				JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			WHERE
				{raw:qsb}' . '
				AND ' . implode("\n\t\t\t\tAND ", $where) . '
			ORDER BY id_last_msg DESC
			LIMIT ' . $num_recent,
			array_merge($where_params, [
				'current_member' => $user_info['id'],
				'min_message_id' => $modSettings['maxMsgID'] - 35 * min($num_recent, 5),
				'qsb' => str_replace('b.', 't.', $user_info['query' . ($ignore ? '_wanna' : '') . '_see_board']),
			])
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			return null;
		}

		$id_last_msgs = [];
		$id_first_msgs = [];
		$topic_list = [];
		$board_list = [];
		$topics = [];

		while (list ($id_topic, $id_board, $replies, $views) = $smcFunc['db_fetch_row']($request)) {
			$topic_list[] = $id_topic;
			$board_list[$id_board] = $id_board;
			$topics[$id_topic] = [
				'board_link' => '<a href="' . $scripturl . '?board=' . $id_board . '.0">' . $boards[$id_board] . '</a>',
				'replies' => $replies,
				'views' => $views,
			];
		}
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT
				t.id_topic, id_last_msg, id_first_msg, ml.poster_time, mf.subject, ml.id_member, ml.id_msg,
				COALESCE(mem.real_name, ml.poster_name) AS poster_name
			FROM {db_prefix}topics AS t
				JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = ml.id_member)
			WHERE t.id_topic IN ({array_int:topic_list})',
			[
				'current_member' => $user_info['id'],
				'topic_list' => $topic_list,
			]
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (time() - 86400 > $row['poster_time']) {
				if (time() - 31557600 < $row['poster_time']) {
					$time_fmt = '%d %b';
				} else {
					$time_fmt = '%d %b %Y';
				}
			} else {
				// What does the user want the time formatted as?
				$s = strpos($user_info['time_format'], '%S') === false ? '' : ':%S';
				if (strpos($user_info['time_format'], '%H') === false && strpos(
						$user_info['time_format'],
						'%T'
					) === false) {
					$h = strpos($user_info['time_format'], '%l') === false ? '%I' : '%l';
					$time_fmt = $h . ':%M' . $s . ' %p';
				} else {
					$time_fmt = '%H:%M' . $s;
				}
			}

			$id_last_msgs[] = $row['id_last_msg'];
			$id_first_msgs[] = $row['id_first_msg'];
			censorText($row['subject']);
			$topics[$row['id_topic']] += [
				'poster_link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>',
				'subject' => $row['subject'],
				'time' => timeformat($row['poster_time']),
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
			];
		}
		$smcFunc['db_free_result']($request);

		// Count number of new posts per topic.
		if (!$user_info['is_guest']) {
			$request = $smcFunc['db_query']('', '
				SELECT
					m.id_topic, COUNT(*)
				FROM {db_prefix}messages AS m
					LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = m.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = m.id_board AND lmr.id_member = {int:current_member})
				WHERE
					m.id_msg >= {int:min_msg} AND m.id_msg <= {int:max_msg}
					AND m.id_topic IN ({array_int:topic_list})
					AND m.id_msg > COALESCE(lt.id_msg, lmr.id_msg, 0)
					AND approved = 1
				GROUP BY m.id_topic',
				[
					'current_member' => $user_info['id'],
					'min_msg' => min($id_first_msgs),
					'max_msg' => max($id_last_msgs),
					'topic_list' => $topic_list,
				]
			);
			while (list ($id_topic, $co) = $smcFunc['db_fetch_row']($request)) {
				$topics[$id_topic]['co'] = $co;
			}
			$smcFunc['db_free_result']($request);
		}

		return $topics;
	}

	private $topics = [];

	public function __invoke(array $fields)
	{
		global $context, $scripturl;

		$boards = $fields['boards'] !== '' ? explode(',', $fields['boards']) : [];
		$ex = $fields['prop'] === '0' ? $boards : [];
		$inc = $fields['prop'] === '1' ? $boards : [];
		$this->topics = $this->getTopics($fields['num_recent'], true, $ex, $inc);
		$context['mark_read_button'] = [
			'markread' => [
				'text' => 'mark_as_read',
				'image' => 'markread',
				'url' => $scripturl . '?action=markasread;sa=all;' . $context['session_var'] . '=' . $context['session_id'],
			],
		];
		call_integration_hook('integrate_mark_read_button');
	}

	public function __toString()
	{
		global $context, $txt;

		$ret = '';

		if ($this->topics === null) {
			$ret .= '
							<tr>
								<div class="centertext">
									' . $txt['no_messages'] . '
								</div>
							</tr>';
		} else {
			foreach ($this->topics as $id_topic => $topic) {
				$ret .= '
								<p>';

				if ($context['user']['is_logged'] && isset($topic['co'])) {
					$ret .= '<span class="new_posts">' . $topic['co'] . '</span>';
				}

				$ret .= '<a href="' . $topic['href'] . '">' . $topic['subject'] . '</a>
							<br><small>' . $txt['posted_by'] . ' ' . $topic['poster_link'] . ' | ' . $topic['time'] . ' (' . $topic['board_link'] . ')
							</small></p>';
			}

			if ($context['user']['is_logged']) {
				$ret .= $this->captureOutput('template_button_strip', $context['mark_read_button'], '');
			}
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'receipts-text',
			],
			'module_link' => [
				'value' => 'action=recent',
			],
			'num_recent' => [
				'type' => 'text',
				'value' => '8',
			],
			'prop' => [
				'type' => 'radio',
				'value' => '0',
				'options' => ['0', '1'],
			],
			'boards' => [
				'type' => 'boardlist',
				'value' => '',
			],
		];
	}
}
