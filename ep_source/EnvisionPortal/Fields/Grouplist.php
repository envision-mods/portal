<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal\Fields;

use EnvisionPortal\{CacheableFieldInterface, UpdateFieldInterface};

class Grouplist implements CacheableFieldInterface, UpdateFieldInterface
{
	private array $membergroups = [];
	private array $field;
	private string $key;
	private string $type;

	public function __construct(array $field, string $key, string $type)
	{
		$this->field = $field;
		$this->key = $key;
		$this->type = $type;
	}

	public function fetchData(): array
	{
		global $modSettings, $smcFunc, $txt;

		loadLanguage('ManageBoards');
		$where = ['id_group != 3'];
		$membergroups = [
			-1 => [
				'name' => $txt['parent_guests_only'],
				'is_post_group' => false,
			],
			0 => [
				'name' => $txt['parent_members_only'],
				'is_post_group' => false,
			],
		];

		if (empty($this->field['inherited'])) {
			$where[] = 'id_parent = {int:not_inherited}';

			if (empty($modSettings['permission_enable_postgroups'])) {
				$where[] = 'min_posts = {int:min_posts}';
			}
		}
		$request = $smcFunc['db_query']('', '
			SELECT
				id_group, group_name, min_posts
			FROM {db_prefix}membergroups
			WHERE ' . implode("\n\t\t\t\tAND ", $where),
			[
				'not_inherited' => -2,
				'min_posts' => -1,
			]
		);

		while ([$id, $name, $min_posts] = $smcFunc['db_fetch_row']($request)) {
			$membergroups[$id] = [
				'name' => trim($name),
				'is_post_group' => $min_posts != -1,
			];
		}
		$smcFunc['db_free_result']($request);

		return $membergroups;
	}

	public function setData(array $data): void
	{
		$disallowed = $this->field['not_allowed'] ?? [];

		if (preg_match('/^[0-9]++(?:,[0-9]++)*+(?=;)/', $this->field['value'], $matches) === 1) {
			$order = explode(',', $matches[0]);
			$this->membergroups = array_diff_key(array_replace(array_flip($order), $data), array_flip($disallowed));
		} else {
			$this->membergroups = $data;
		}
	}

	public function beforeSave($val): string
	{
		$ret = '';
		if (is_array($val)) {
			if (!empty($this->field['order']) && isset($_POST[$this->key . 'order']) && is_array(
					$_POST[$this->key . 'order']
				)) {
				$ret = implode(',', $_POST[$this->key . 'order']) . ';';
			}

			$ret .= implode(',', $val);
		}

		return $ret;
	}

	public function __toString(): string
	{
		global $txt;

		if (preg_match('/(?<=;|^)[0-9]++(?:,[0-9]++)*+$/', $this->field['value'], $matches) === 1) {
			$checked = explode(',', $matches[0]);
		} else {
			$checked = [];
		}

		$ret = '
					<fieldset class="group_perms';

		if (!empty($this->field['order'])) {
			$ret .= ' ordered-checklist" data-up="' . $txt['checks_order_up'] . '" data-down="' . $txt['checks_order_down'];
		}

		$ret .= '">
						<legend> ' . $txt['avatar_select_permission'] . '</legend>
						<ul class="reset">';

		foreach ($this->membergroups as $i => $group) {
			$ret .= '
							<li><label>
								<input type="checkbox" class="input_check" name="' . $this->key . '[]" value="' . $i . '"' . (in_array(
					$i,
					$checked
				) || in_array(-3, $checked) ? ' checked' : '') . ' />
								<span' . ($group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : '') . '>' . $group['name'] . '</span>';

			if (!empty($this->field['order'])) {
				$ret .= '
								<input type="hidden" name="' . $this->key . 'order[]" value="' . $i . '" />';
			}

			$ret .= '
							</label></li>';
		}

		$ret .= '
						</ul></fieldset>';

		return $ret;
	}
}
