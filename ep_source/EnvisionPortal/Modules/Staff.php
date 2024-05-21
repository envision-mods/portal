<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;
use EnvisionPortal\SharedMemberDataInterface;

class Staff implements ModuleInterface, SharedMemberDataInterface
{
	use ModuleTrait;

	private array $groups  = [];
	private array $custom_fields = [];
	private array $custom_field_data = [];
	private int $list_type;
	private bool $grouping;
	private array $members_list;

	public function __invoke(array $fields)
	{
		global $smcFunc;

		if (preg_match('/(?<=;|^)[1-9][0-9]*+(?:,[1-9][0-9]*+)*+$/', $fields['groups'], $matches) === 1) {
			$this->groups = explode(',', $matches[0]);
			$this->list_type = !isset($fields['list_type']) ? 1 : (int)$fields['list_type'];
			$this->grouping = $fields['grouping'] == 1;
			$this->groups = array_combine($this->groups, $this->groups);

			/*
			 * This nice regex will match only the desired list.  It ignores
			 * the ordering metadata which is only useful in the admin section.
			 */
			if (preg_match('/(?<=;|^)(?:[^,]*+)*+,(?:[^,]++)++$/', $fields['custom_fields'], $matches) === 1) {
				$this->custom_fields = explode(',', $matches[0]);
			}

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

			$this->loadCustomFields();
		}
	}

	function loadCustomFields()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
		SELECT
			col_name, field_name, field_type, field_length, field_options,
			default_value, bbc, enclose
		FROM {db_prefix}custom_fields');

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$this->custom_field_data[] = $row;
		}
		$smcFunc['db_free_result']($request);
	}

	function processCustomField()
	{
		global $txt, $user_profile, $settings, $scripturl;

		foreach ($this->custom_field_data as $row)
			if (in_array($row['col_name'], $this->custom_fields))
				foreach ($this->members_list as $member) {
					$exists = isset($user_profile[$member], $user_profile[$member]['options'][$row['col_name']]);
					$value = $exists ? $user_profile[$member]['options'][$row['col_name']] : $row['default_value'];
					$currentKey = 0;

					if ($row['field_type'] == 'check') {
						$value = $value !== '' ? $txt['yes'] : $txt['no'];
					} elseif ($row['field_type'] == 'select' || $row['field_type'] == 'radio') {
						$options = explode(',', $row['field_options']);
						foreach ($options as $k => $v) {
							if ($value === $v) {
								$currentKey = $k;
							}
						}
					}

					// Parse BBCode
					if ($row['bbc'])
						$value = parse_bbc($value);
					elseif ($row['field_type'] == 'textarea')
						// Allow for newlines at least
						$value = strtr($value, array("\n" => '<br>'));

					// Enclosing the user input within some other text?
					if ($row['enclose'] !== '')
						$value = strtr($row['enclose'], array(
							'{SCRIPTURL}' => $scripturl,
							'{IMAGES_URL}' => $settings['images_url'],
							'{DEFAULT_IMAGES_URL}' => $settings['default_images_url'],
							'{INPUT}' => un_htmlspecialchars($value),
							'{KEY}' => $currentKey
						));

					$this->custom_field_info[$row['col_name']][$member] = array(
						'name' => tokenTxtReplace($row['field_name']),
						'value' => tokenTxtReplace($value),
					);
				}
	}

	public function fetchMemberIds(): array
	{
		return $this->members_list;
	}

	public function __toString()
	{
		global $memberContext;

		if ($this->groups == []) {
			$ret = $this->error('empty');
		} else {
			$this->processCustomField();

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

					// This standard profile field is only available in SMF 2.0.
					if (!defined('SMF_VERSION') && $memberContext[$member]['location'] != '' && $this->list_type & 0x4) {
						$ret .= '
									<p>' . $memberContext[$member]['location'] . '</p>';
					}

					if ($memberContext[$member]['blurb'] != '' && $this->list_type & 0x8) {
						$ret .= '
									<p>' . $memberContext[$member]['blurb'] . '</p>';
					}

					foreach ($this->custom_fields as $custom_field) {
						if (isset($this->custom_field_info[$custom_field], $this->custom_field_info[$custom_field][$member]) && $this->custom_field_info[$custom_field][$member]['value'] != '') {
							$ret .= '
									<p>' . $this->custom_field_info[$custom_field][$member]['value'] . '</p>';
						}
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
				'preload' => function ($field) {
					// This standard profile field is only available in SMF 2.0.
					if (defined('SMF_VERSION')) {
						unset($field['options'][2]);
					}

					return $field;
				},
				'options' => [0x1, 0x2, 0x4, 0x8, 0x10],
			],
			'custom_fields' => [
				'type' => 'checklist',
				'preload' => function ($field) {
					global $smcFunc;

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
						$field['options'][$col_name] = $col_name;
						$field['options_names'][$col_name] = $field_name;
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