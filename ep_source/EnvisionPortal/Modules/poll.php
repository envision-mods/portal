<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Poll implements ModuleInterface
{
	private array $boards_can;

	private function fetchPoll(array $where, array $where_params): ?array
	{
		global $smcFunc, $modSettings, $user_info;

		if ($this->boards_can['poll_view'] == []) {
			return null;
		}

		if ($modSettings['postmod_active']) {
			$where[] = 'approved = {int:is_approved}';
			$where_params['is_approved'] = 1;
		}
		$recycle_board = !empty($modSettings['recycle_enable']) && !empty($modSettings['recycle_board']) ? $modSettings['recycle_board'] : 0;
		if ($recycle_board) {
			$where[] = 'id_board != {int:recycle_board}';
			$where_params['recycle_board'] = $recycle_board;
		}
		if ($this->boards_can['poll_view'] != [0]) {
			$where[] = 'id_board IN ({array_int:boards})';
			$where_params['boards'] = $this->boards_can['poll_view'];
		}

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT
				id_topic
			FROM {db_prefix}topics
				JOIN {db_prefix}polls AS p USING (id_poll)
			WHERE
				' . implode("\n\t\t\t\tAND ", $where) . '
			ORDER BY id_poll DESC
			LIMIT 1',
			$where_params
		);

		// Either this topic has no poll, or the user cannot view it.
		if ($smcFunc['db_num_rows']($request) == 0) {
			return null;
		}

		[$topic] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT
				id_poll, question, voting_locked, hide_results, max_votes, guest_vote,
				expire_time != 0 AND expire_time < ' . time() . ' AS is_expired, id_board
			FROM {db_prefix}topics
				JOIN {db_prefix}polls AS p USING (id_poll)
			WHERE
				id_topic = {int:current_topic}',
			[
				'current_topic' => $topic,
			]
		);

		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$row += ['options' => [], 'total_votes' => 0, 'has_voted' => 0];

		$request = $smcFunc['db_query']('', '
			SELECT pc.id_choice, pc.label, pc.votes, lp.id_choice IS NOT NULL
			FROM {db_prefix}poll_choices AS pc
				LEFT JOIN {db_prefix}log_polls AS lp ON lp.id_choice = pc.id_choice AND lp.id_poll = {int:id_poll} AND lp.id_member = {int:current_member}
			WHERE pc.id_poll = {int:id_poll}
			ORDER BY pc.id_choice',
			[
				'current_member' => $user_info['id'],
				'id_poll' => $row['id_poll'],
			]
		);
		while ([$id_choice, $label, $votes, $voted_this] = $smcFunc['db_fetch_row']($request)) {
			$row['options'][] = [$id_choice, $label, $votes, $voted_this];
			$row['total_votes'] += $votes;
			$row['has_voted'] |= $voted_this;
		}
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(DISTINCT id_member) AS total
			FROM {db_prefix}log_polls
			WHERE id_poll = {int:id_poll}
				AND id_member != {int:not_guest}',
			[
				'id_poll' => $row['id_poll'],
				'not_guest' => 0,
			]
		);
		[$row['total_voters']] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $row;
	}

	private $pollinfo;

	public function __invoke(array $fields)
	{
		global $user_info;

		if (!defined('SMF_VERSION')) {
			$this->boards_can = [
				'poll_view' => boardsAllowedTo('poll_view'),
				'poll_vote' => boardsAllowedTo('poll_vote'),
				'moderate_board' => boardsAllowedTo('moderate_board'),
			];
		} else {
			$this->boards_can = boardsAllowedTo(['poll_view', 'poll_vote', 'moderate_board'], true, false);
		}

		$topic = $fields['topic'] ?? 1;
		$options = $fields['options'] ?? 'recent';

		if ($options == 'def') {
			$opt = ['id_topic = {int:current_topic}', '{raw:qsb}'];
		} elseif ($options == 'recent') {
			$opt = ['voting_locked = 0', '{raw:qsb}'];
		}

		$row = $this->fetchPoll($opt, [
			'current_topic' => $topic,
			'qsb' => str_replace('b.', '', $user_info['query_wanna_see_board']),
		]);

		if ($row !== null) {
			$can_vote = $this->boards_can['poll_vote'] === [0] || in_array(
					$row['id_board'],
					$this->boards_can['poll_vote']
				);
			$can_moderate = $this->boards_can['moderate_board'] === [0] || in_array(
					$row['id_board'],
					$this->boards_can['moderate_board']
				);

			$options = [];
			$divisor = $row['total_votes'] == 0 ? 1 : $row['total_votes'];
			$precision = $row['total_votes'] == 100 ? 0 : 1;

			foreach ($row['options'] as [$id_choice, $label, $votes]) {
				censorText($label);

				$options[$id_choice] = [
					'votes' => $votes,
					'option' => parse_bbc($label),
					'percent' => round(($votes * 100) / $divisor, $precision),
				];
			}

			$this->pollinfo = [
				'id' => $row['id_poll'],
				'question' => $row['question'],
				'total_voters' => $row['total_voters'],
				'is_locked' => $row['voting_locked'] != 0,
				'options' => $options,
				'max_votes' => min(count($row['options']), $row['max_votes']),

				// You're allowed to vote if:
				// 1. the poll did not expire, and
				// 2. you're either not a guest OR guest voting is enabled... and
				// 3. you're not trying to view the results, and
				// 4. the poll is not locked, and
				// 5. you have the proper permissions, and
				// 6. you haven't already voted before.
				'allow_vote' => !$row['is_expired'] && $row['voting_locked'] == 0 && $can_vote && !$row['has_voted'],

				// You're allowed to view the results if:
				// 1. you're just a super-nice-guy, or
				// 2. anyone can see them (hide_results == 0), or
				// 3. you can see them after you voted (hide_results == 1), or
				// 4. you've waited long enough for the poll to expire. (whether hide_results is 1 or 2.)
				'allow_view_results' => $can_moderate || $row['hide_results'] == 0 || ($row['hide_results'] == 1 && $row['has_voted']) || $row['is_expired'],
			];
		}
	}

	public function __toString()
	{
		global $txt, $boardurl, $context;

		$ret = $txt['poll_cannot_see'];

		if ($this->pollinfo['allow_vote']) {
			$ret = '
			<form action="' . $boardurl . '/SSI.php?ssi_function=pollVote" method="post" accept-charset="' . $context['character_set'] . '">
				<b>' . $this->pollinfo['question'] . '</b><br />
				' . ($this->pollinfo['max_votes'] > 1 ? sprintf(
						$txt['poll_options6'],
						$this->pollinfo['max_votes']
					) . '<br />' : '');

			foreach ($this->pollinfo['options'] as $i => $option) {
				$ret .= '
				<label><input type="' . ($this->pollinfo['max_votes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" value="' . $i . '" class="input_check" /> ' . $option['option'] . '</label><br />';
			}

			$ret .= '
				<input type="submit" value="' . $txt['poll_vote'] . '" class="button_submit" />
				<input type="hidden" name="poll" value="' . $this->pollinfo['id'] . '" />
				<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
			</form>';
		} elseif ($this->pollinfo['allow_view_results']) {
			$ret = '
				<b>' . $this->pollinfo['question'] . '</b>';

			foreach ($this->pollinfo['options'] as $option) {
				$ret .= '
				<div>' . $option['option'] . '</div>
					<div class="statsbar">
						<div class="bar" style="width: ' . $option['percent'] . '%;"><div></div></div>
					</div>
					<div class="righttext">' . $option['votes'] . ' (' . $option['percent'] . '%)</div>';
			}

			$ret .= '
				<b>' . $txt['poll_total_voters'] . ': ' . $this->pollinfo['total_voters'] . '</b>';
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'blueprint--pencil',
			],
			'options' => [
				'type' => 'select',
				'value' => 'recent',
				'options' => ['recent', 'def'],
			],
			'topic' => [
				'type' => 'text',
				'value' => '0',
			],
		];
	}
}