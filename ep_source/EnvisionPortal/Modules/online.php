<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Online implements ModuleInterface
{
	public function getTotals()
	{
		global $sourcedir, $txt, $user_info;

		// Get the user online list.
		require_once($sourcedir . '/Subs-MembersOnline.php');
		$membersOnlineOptions = [
			'show_hidden' => allowedTo('moderate_forum'),
			'sort' => 'log_time',
			'reverse_sort' => true,
		];
		$membersOnlineStats = getMembersOnlineStats($membersOnlineOptions);
		$show_buddies = !empty($user_info['buddies']);
		$this->groups = array_map(
			function ($group) use ($membersOnlineStats) {
				return [
					$group['name'],
					array_filter(
						$membersOnlineStats['users_online'],
						function ($user) use ($group) {
							return $user['group'] == $group['id'];
						}
					),
				];
			},
			$membersOnlineStats['online_groups']
		);

		if (!empty($membersOnlineStats['num_guests'])) {
			$this->totals[] = comma_format(
					$membersOnlineStats['num_guests']
				) . ' ' . ($membersOnlineStats['num_guests'] == 1 ? $txt['guest'] : $txt['guests']);
		}
		if (!empty($membersOnlineStats['num_spiders'])) {
			$this->totals[] = comma_format(
					$membersOnlineStats['num_spiders']
				) . ' ' . ($membersOnlineStats['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
		}
		if ($show_buddies) {
			$this->totals[] = comma_format(
					$membersOnlineStats['num_buddies']
				) . ' ' . ($membersOnlineStats['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
		}
		if (!empty($membersOnlineStats['num_users_hidden'])) {
			$this->totals[] = comma_format($membersOnlineStats['num_users_hidden']) . ' ' . $txt['hidden'];
		}
	}

	private $totals = [];
	private $groups = [];
	private $online_groups = [];
	private $show_online = [];
	private $fields;

	public function __invoke(array $fields)
	{
		$this->fields = $fields;

		$this->online_groups = array_flip(explode(',', $this->fields['online_groups']));
		$this->show_online = array_flip(explode(',', $this->fields['show_online']));
		$this->getTotals();
	}

	public function __toString()
	{
		$ret = '';

		if ($this->totals != []) {
			$ret .= ' (' . implode(', ', $this->totals) . ')';
		}

		$ret .= '
						<ul>';

		foreach ($this->groups as [$name, $users]) {
			$ret .= '
							<li><strong>' . $name . '</strong>:
								<ul>';

			foreach ($users as $user) {
				$ret .= '
									<li>' . $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'] . '</li>';
			}

			$ret .= '
								</ul>
							</li>';
		}

		$ret .= '
						</ul>';

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'users',
			],
			'module_link' => [
				'value' => 'action=who',
			],
			'show_online' => [
				'type' => 'checklist',
				'options' => ['guests', 'spiders', 'buddies', 'hidden'],
				'value' => '0,1,3',
				'order' => true,
			],
			'online_groups' => [
				'type' => 'grouplist',
				'not_allowed' => [-1, 0, 3],
				'value' => '-3',
			],
		];
	}
}