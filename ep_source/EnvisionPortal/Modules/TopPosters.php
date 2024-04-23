<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;
use EnvisionPortal\SharedMemberDataInterface;

class TopPosters implements ModuleInterface, SharedMemberDataInterface
{
	use ModuleTrait;

	private $groups = [];

	public function __invoke(array $fields)
	{
		global $smcFunc, $user_info;

		$this->list_type = (int)($fields['list_type'] ?? 0x9);
		$num_posters = (int)($fields['num_posters'] ?? 5);
		$this->members_list = [];

		if ($num_posters == 0) {
			return;
		}

		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE posts > 0' . (!$user_info['is_admin'] ? '
				AND is_activated = 1' : '') . '
			ORDER BY posts DESC
			LIMIT {int:num_posters}',
			[
				'num_posters' => $num_posters,
			]
		);

		while ([$id_member] = $smcFunc['db_fetch_row']($request)) {
			$this->members_list[] = $id_member;
		}
	}

	public function fetchMemberIds(): array
	{
		return $this->members_list;
	}

	public function __toString()
	{
		global $memberContext, $txt;

		if ($this->members_list == []) {
			$ret = $this->error('empty');
		} else {
			$ret = '
						<ul';

			if ($this->list_type & 0x1) {
				$ret .= ' data-avatar';
			}

			$ret .= '>';

			foreach ($this->members_list as $member) {
				$ret .= '
									<li>';

				if ($this->list_type & 0x1) {
					$ret .= '
									' . $memberContext[$member]['avatar']['image'];
				}

				$ret .= '
									<p>' . $memberContext[$member]['link'] . '</p>';

				if ($memberContext[$member]['title'] != '' && $this->list_type & 0x2) {
					$ret .= '
									<p>' . $memberContext[$member]['title'] . '</p>';
				}

				if ($memberContext[$member]['group'] != '' && $this->list_type & 0x4) {
					$ret .= '
									<p>' . $memberContext[$member]['group'] . '</p>';
				}

				if ($memberContext[$member]['posts'] != '' && $this->list_type & 0x8) {
					$ret .= '
									<p>' . $memberContext[$member]['posts'] . ' ' . $txt['posts'] . '</p>';
				}

				$ret .= '
							</li>';
			}

			$ret .= '
								</ul>
							</li>';
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'rosette',
			],
			'list_type' => [
				'type' => 'bitwise_checklist',
				'value' => '9',
				'options' => [0x1, 0x2, 0x4, 0x8],
			],
			'num_posters' => [
				'type' => 'text',
				'value' => '5',
			],
		];
	}
}