<?php

namespace EnvisionPortal\Modules;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Stats implements ModuleInterface
{
	use ModuleTrait;

	public function getTotals()
	{
		global $modSettings, $scripturl, $smcFunc;

		$kittens = 0;
		$planks = 0;

		if (isset($this->stat_choices[3])) {
			$request = $smcFunc['db_query'](
				'',
				'
				SELECT COUNT(id_cat)
				FROM {db_prefix}categories'
			);
			[$kittens] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}

		if (isset($this->stat_choices[4])) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(id_board)
				FROM {db_prefix}boards
				WHERE redirect = {string:blank_redirect}',
				[
					'blank_redirect' => '',
				]
			);
			[$planks] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}

		return array_intersect_key(
			array_replace($this->stat_choices, [
				[
					'total_members',
					sprintf('<a href="%s?action=mlist">%s</a>', $scripturl, comma_format($modSettings['totalMembers'])),
				],
				['total_posts', comma_format($modSettings['totalMessages'])],
				['total_topics', comma_format($modSettings['totalTopics'])],
				['total_cats', comma_format($kittens)],
				['total_boards', comma_format($planks)],
				['most_online_today', comma_format($modSettings['mostOnlineToday'])],
				['most_online_ever', comma_format($modSettings['mostOnline'])],
			]),
			$this->stat_choices
		);
	}

	private $totals = [];
	private $stat_choices = [];

	public function __invoke(array $fields)
	{
		$stat_choices = explode(';', $fields['stat_choices']);
		$this->stat_choices = array_flip(explode(',', $stat_choices[1]));
		if ($this->stat_choices != []) {
			$this->totals = $this->getTotals();
		}
	}

	public function __toString()
	{
		global $txt;

		if ($this->totals == []) {
			$ret = $this->error('empty');
		} else {
			$ret = '
					<ul>';

			foreach ($this->totals as [$var, $val]) {
				$ret .= '
						<li>' . $txt[$var] . ': ' . $val . '</li>';
			}

			$ret .= '
					</ul>';
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'chart',
			],
			'module_link' => [
				'value' => 'action=stats',
			],
			'stat_choices' => [
				'type' => 'checklist',
				'options' => ['members', 'posts', 'topics', 'categories', 'boards', 'ontoday', 'onever'],
				'value' => '0,1,2,3,4,5,6;0,1,2,5,6',
				'order' => true,
			],
		];
	}
}
