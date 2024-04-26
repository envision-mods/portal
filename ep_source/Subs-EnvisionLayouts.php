<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

/**
 * Export a layout in its entirety. Assumes permissions have been worked out.
 *
 * @param int $id_layout the layout to delete
 *
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function exportLayout($id_layout)
{
	global $context, $user_info;

	checkSession();

	if (empty($id_layout) && !is_int($id_layout)) {
		return false;
	}

	$layout_data = loadLayout((int)$id_layout, true);
	$xml_children = [];

	ep_call_hook('export_layout', [&$id_layout, &$layout_data]);

	foreach ($layout_data as $row_id => $row_data) {
		$xml_children[$row_id] = [
			'identifier' => 'row',
			'children' => [],
		];
		foreach ($row_data as $column_id => $column_data) {
			$xml_children[$row_id]['children'][$column_id] = [
				'identifier' => 'section',
				'children' => [],
			];
			foreach ($column_data['extra'] as $extra_id => $extra_data) {
				$xml_children[$row_id]['children'][$column_id]['children'][$extra_id] = [
					'value' => $extra_data,
				];
			}
			if (!empty($column_data['modules'])) {
				foreach ($column_data['modules'] as $id_position => $modules) {
					$xml_children[$row_id]['children'][$column_id]['children'][$id_position] = [
						'identifier' => 'module',
						'children' => [],
					];
					foreach ($modules as $module_id => $module_data) {
						if ($module_id == 'type') {
							$xml_children[$row_id]['children'][$column_id]['children'][$id_position]['children'][$module_id] = [
								'value' => $module_data,
							];
						}
					}
				}
			}
		}
	}
	$xml_data = [
		'layouts' => [
			'identifier' => 'layout',
			'children' => $xml_children,
		],
	];
	$context['sub_template'] = 'generic_xml';
	$context['xml_data'] = $xml_data;
	$context['template_layers'] = ['render_save'];
	$_REQUEST['xml'] = true;

	return true;
}

/**
 * Add a new layout.  Assumes permissions have been worked out.
 *
 * @param string $layout_name      the name of the new layout.
 * @param array  $layout_actions   array of the actions to show this layout on.  Pseudo-actions,
 *                                 such as board or topic, are wrapped with brackets [].
 * @param array  $layout_positions an array of all the layout positions to be inserted.
 *
 * @return int|false the ID of the new layout on success; false otherwise.
 * @since 1.0
 */
function addLayout(string $layout_name, array $layout_actions, array $layout_positions)
{
	global $smcFunc;

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layouts', ['name' => 'string-40'], [$layout_name], ['id_layout']);
	$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layouts', 'id_layout');

	if (empty($iid)) {
		return false;
	}

	$columns = [
		'id_layout' => 'int',
		'action' => 'string',
	];

	$data = [];
	foreach ($layout_actions as $layout_action) {
		$data[] = [
			$iid,
			$layout_action,
		];
	}

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_actions', $columns, $data, ['id_layout']);

	$columns = [
		'id_layout' => 'int',
		'x_pos' => 'int',
		'rowspan' => 'int',
		'y_pos' => 'int',
		'colspan' => 'int',
		'status' => 'string',
		'is_smf' => 'int',
	];

	$keys = [
		'id_layout',
		'id_layout_position',
	];

	$data = [];
	foreach ($layout_positions as $layout_position) {
		$data[] = [
			$iid,
			$layout_position['x_pos'],
			$layout_position['rowspan'],
			$layout_position['y_pos'],
			$layout_position['colspan'],
			$layout_position['status'],
			$layout_position['is_smf'],
		];
	}

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions', $columns, $data, $keys);

	return $iid;
}

/**
 * Edit a layout.  Assumes permissions have been worked out.
 *
 * @param int    $id_layout        the ID of the layout to edit.
 * @param string $layout_name      the name of the new layout.
 * @param array  $layout_actions   array of the actions to show this layout on. Pseudo-actions, such as board or topic
 *                                 are wrapped with brackets [].
 * @param array  $layout_positions an array of all the layout positions to be inserted.
 * @param int    $smf_pos          the position ID of SMF. Usually, all layouts that are not the homepage (which has
 *                                 the id_layout of 1) have SMF residing somewhere on them.
 * @param array  $remove_positions an array of all position IDs to be removed. Leave empty to not delete a thing.
 */
function editLayout(
	int $id_layout,
	string $layout_name,
	array $layout_actions,
	array $layout_positions,
	int $smf_pos,
	array $remove_positions = []
): void {
	global $smcFunc;

	// Update the name
	EnvisionPortal\DatabaseHelper::update('{db_prefix}ep_layouts', ['name' =>  ['string-40',$layout_name]],'id_layout', $id_layout);

	// Delete old actions
	EnvisionPortal\DatabaseHelper::delete('{db_prefix}ep_layout_actions','id_layout', $id_layout);

	$smcFunc['db_insert'](
		'insert',
		'{db_prefix}ep_layout_actions',
		['id_layout' => 'int', 'action' => 'string'],
		array_map(
			fn($layout_action) => [$id_layout, $layout_action],
			$layout_actions
		),
		['id_layout']
	);

	$columns = [
		'id_layout' => 'int',
		'x_pos' => 'int',
		'rowspan' => 'int',
		'y_pos' => 'int',
		'colspan' => 'int',
		'status' => 'string',
		'is_smf' => 'int',
	];

	$keys = [
		'id_layout',
		'id_layout_position',
	];

	$data = [];
	$update_params = ['id_layout' => $id_layout];
	$update_query = [
		'x_pos' => '',
		'rowspan' => '',
		'y_pos' => '',
		'colspan' => '',
		'status' => '',
		'is_smf' => '',
	];
	foreach ($layout_positions as $layout_position) {
		if (isset($layout_position['id_layout_position'])) {
			$update_query['x_pos'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:x_pos' . $layout_position['id_layout_position'] . '}';
			$update_query['rowspan'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:rowspan' . $layout_position['id_layout_position'] . '}';
			$update_query['y_pos'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:y_pos' . $layout_position['id_layout_position'] . '}';
			$update_query['colspan'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:colspan' . $layout_position['id_layout_position'] . '}';
			$update_query['status'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {string:status' . $layout_position['id_layout_position'] . '}';
			$update_query['is_smf'] .= ' WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:is_smf' . $layout_position['id_layout_position'] . '}';
			$update_params += [
				'id_layout_position' . $layout_position['id_layout_position'] => $layout_position['id_layout_position'],
				'x_pos' . $layout_position['id_layout_position'] => $layout_position['x_pos'],
				'rowspan' . $layout_position['id_layout_position'] => $layout_position['rowspan'],
				'y_pos' . $layout_position['id_layout_position'] => $layout_position['y_pos'],
				'colspan' . $layout_position['id_layout_position'] => $layout_position['colspan'],
				'status' . $layout_position['id_layout_position'] => $layout_position['status'],
				'is_smf' . $layout_position['id_layout_position'] => $layout_position['is_smf'],
			];
		} else {
			$data[] = [
				$id_layout,
				$layout_position['x_pos'],
				$layout_position['rowspan'],
				$layout_position['y_pos'],
				$layout_position['colspan'],
				$layout_position['status'],
				$layout_position['is_smf'],
			];
		}
	}

	if ($data != []) {
		$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions', $columns, $data, $keys);
	}

	if ($update_params != []) {
		$smcFunc['db_query'](
			'',
			'
			UPDATE
				{db_prefix}ep_layout_positions
			SET
				x_pos = CASE id_layout_position ' . $update_query['x_pos'] . ' END,
				rowspan = CASE id_layout_position ' . $update_query['rowspan'] . ' END,
				y_pos = CASE id_layout_position ' . $update_query['y_pos'] . ' END,
				colspan = CASE id_layout_position ' . $update_query['colspan'] . ' END,
				status = CASE id_layout_position ' . $update_query['status'] . ' END,
				is_smf = CASE id_layout_position ' . $update_query['is_smf'] . ' END
			WHERE
				id_layout = {int:id_layout}',
			$update_params
		);
	}

	EnvisionPortal\DatabaseHelper::delete('{db_prefix}ep_module_positions', 'id_layout_position', $smf_pos);

	if ($remove_positions != []) {
		EnvisionPortal\DatabaseHelper::deleteMany('{db_prefix}ep_layout_positions', 'id_layout_position', $remove_positions);
	}
}

/**
 * Removes all traces of a layout. Assumes permissions have been worked out.
 *
 * @param int[] $layout_list the layouts to delete
 */
function deleteLayouts(array $layout_list): void
{
	foreach (['layouts', 'actions', 'layout_positions', 'module_positions'] as $table_name) {
		EnvisionPortal\DatabaseHelper::deleteMany('{db_prefix}ep_' . $table_name, 'id_layout', $layout_list);
	}
}

/// maxx  val 8151
function toBits(int $x_pos, int $rowspan, int $y_pos, int $colspan):  int
{
	$area = 0;
	$area |= ($x_pos & 0x7) << 9;
	$area |= ($rowspan & 0x7) << 6;
	$area |= ($y_pos & 0x7) << 3;
	$area |= ($colspan & 0x7);

	return $area;
}

function fromBits(int $area): array
{
	return [
		($area >> 9) & 0x7,
		($area >> 6) & 0x7,
		($area >> 3) & 0x7,
		$area & 0x7
	];
} 