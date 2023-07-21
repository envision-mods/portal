<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\SharedMemberDataInterface;

class Staff implements ModuleInterface, SharedMemberDataInterface
{
	use ModuleTrait;

	private $groups = [];

	public function __invoke(array $fields)
	{
		global $smcFunc, $modSettings, $txt, $scripturl, $memberContext;

		if (preg_match('/(?<=;|^)(?:[1-9][0-9]*+(?:,[1-9][0-9]*+)*+)$/', $fields['groups'], $matches) === 1) {
			$this->groups = explode(',', $matches[0]);
			$this->list_type = !isset($fields['list_type']) ? 1 : (int)$fields['list_type'];
			$this->grouping = $fields['grouping'] == 1;
			$this->groups = array_combine($this->groups, $this->groups);

			$query = $smcFunc['db_query']('', '
				SELECT mem.id_member, mg.id_group, mg.group_name, mg.online_color
				FROM {db_prefix}members AS mem, {db_prefix}membergroups AS mg
				WHERE mem.id_group >= 1 AND mem.id_group IN ({array_int:groups}) AND mem.id_group = mg.id_group',
				[
					'groups' => $this->groups,
				]
			);

			$this->members_list = [];
			while ($row = $smcFunc['db_fetch_assoc']($query)) {
				if ($this->groups[$row['id_group']] == $row['id_group']) {
					$this->groups[$row['id_group']] = [
						'name' => $row['group_name'],
						'color' => $row['online_color'],
						'members' => [],
					];
				}

				$this->groups[$row['id_group']]['members'][] = $row['id_member'];
				$this->members_list[] = $row['id_member'];
			}
		}
	}

	public function fetchMemberIds(): array
	{
		return $this->members_list;
	}

	public function __toString()
	{
		global $memberContext, $scripturl;

		if ($this->groups == []) {
			$ret = $this->error('empty');
		} else {
			$ret = '
						<ul';

			if (!$this->grouping && $this->list_type & 0x1) {
				$ret .= ' data-avatar';
			}

			$ret .= '>';

			foreach ($this->groups as $group) {
				if ($this->grouping) {
					$ret .= '
							<li><b>' . $group['name'] . '</b>
								<ul';
				}

				if ($this->grouping && $this->list_type & 0x1) {
					$ret .= ' data-avatar';
				}

				$ret .= '>';

				foreach ($group['members'] as $member) {
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

					if ($memberContext[$member]['location'] != '' && $this->list_type & 0x4) {
						$ret .= '
									<p>' . $memberContext[$member]['location'] . '</p>';
					}

					if ($memberContext[$member]['blurb'] != '' && $this->list_type & 0x8) {
						$ret .= '
									<p>' . $memberContext[$member]['blurb'] . '</p>';
					}

					if ($memberContext[$member]['website']['url'] != '' && $this->list_type & 0x10) {
						$ret .= '
									<p><a href="' . $memberContext[$member]['website']['url'] . '" title="' . $memberContext[$member]['website']['title'] . '" target="_blank" rel="noopener noreferrer" class="new_win">' . $memberContext[$member]['website']['title'] . '</a></p>';
					}

					$ret .= '
							</li>';
				}
				if ($this->grouping) {
					$ret .= '
								</ul>
							</li>';
				}
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
				'value' => 'rainbow',
			],
			'list_type' => [
				'type' => 'bitwise_checklist',
				'value' => '19',
				'options' => [0x1, 0x2, 0x4, 0x8, 0x10],
			],
			'custom_fields' => [
				'type' => 'checklist',
				'preload' => function ($field) {
					global $txt, $modSettings, $smcFunc;

					$field['options'] = [];
					$request = $smcFunc['db_query'](
						'',
						'
		SELECT
			col_name, field_name
		FROM {db_prefix}custom_fields
		ORDER BY id_field'
					);
					while ([$col_name, $field_name] = $smcFunc['db_fetch_row']($request)) {
						$field['options'][] = $col_name;
						$field['options_names'][] = $field_name;
					}
					$smcFunc['db_free_result']($request);

					return $field;
				},
				'value' => '',
				'order' => true,
			],
			'grouping' => [
				'type' => 'radio',
				'value' => '1',
				'options' => ['0', '1'],
			],
			'groups' => [
				'type' => 'grouplist',
				'not_allowed' => [-1, 0, 3],
				'value' => '1,2',
				'order' => true,
			],
		];
	}
}
