<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;
use EnvisionPortal\SharedMemberDataInterface;

class NewMembers implements ModuleInterface, SharedMemberDataInterface
{
	use ModuleTrait;

	private $groups = [];
	private int $list_type;
	private array $members_list;

	public function __invoke(array $fields)
	{
		global $smcFunc, $user_info;

		$this->list_type = (int)($fields['list_type'] ?? 0x7);
		$limit = (int)($fields['limit'] ?? 3);
		$this->members_list = [];

		if ($limit == 0) {
			return;
		}

		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members' . (!$user_info['is_admin'] ? '
			WHERE is_activated = 1' : '') . '
			ORDER BY id_member DESC
			LIMIT {int:limit}',
			[
				'limit' => $limit,
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

				if ($memberContext[$member]['registered'] != '' && $this->list_type & 0x2) {
					$ret .= '
									<p>' . $memberContext[$member]['registered'] . '</p>';
				}

				if ($memberContext[$member]['posts'] != '' && $this->list_type & 0x4) {
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
				'value' => 'overlays',
			],
			'list_type' => [
				'type' => 'bitwise_checklist',
				'value' => '7',
				'options' => [0x1, 0x2, 0x4],
			],
			'limit' => [
				'type' => 'text',
				'value' => '3',
			],
		];
	}
}