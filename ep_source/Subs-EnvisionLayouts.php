<?php
/**************************************************************************************
 * EnvisionPortal                                                                      *
 * Community Portal Application for SMF                                                *
 * =================================================================================== *
 * Software by:                  EnvisionPortal (http://envisionportal.net/)           *
 * Software for:                 Simple Machines Forum                                 *
 * Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
 * Support, News, Updates at:    http://envisionportal.net/                            *
 **************************************************************************************/

if (!defined('SMF')) {
	die('Hacking attempt...');
}

function list_getLayouts($start, $items_per_page, $sort, $where, $where_params = [])
{
	global $smcFunc;

	$request = $smcFunc['db_query'](
		'',
		'
		SELECT
			el.id_layout, name, action,
			mem.id_member, mem.real_name, mg.group_name
		FROM {db_prefix}ep_layouts AS el
			LEFT JOIN {db_prefix}ep_layout_actions AS ela ON (ela.id_layout = el.id_layout)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = el.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
		WHERE ' . $where . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:per_page}',
		array_merge($where_params, [
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
		])
	);

	$layout = [];
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$layout[$row['id_layout']] = $row;

		$action_request = $smcFunc['db_query']('', '
			SELECT
				action
			FROM {db_prefix}ep_layout_actions
			WHERE id_layout = {int:id_layout}',
			[
				'id_layout' => $row['id_layout'],
			]
		);

		$actions = [];
		while ($action = $smcFunc['db_fetch_row']($action_request)) {
			$actions[] = $action[0];
		}
		$smcFunc['db_free_result']($action_request);

		$layout[$row['id_layout']]['action_list'] = implode(', ', $actions);
	}
	$smcFunc['db_free_result']($request);

	return $layout;
}

function list_getNumLayouts($where, $where_params = [])
{
	global $smcFunc;

	$request = $smcFunc['db_query'](
		'',
		'
		SELECT COUNT(id_layout)
		FROM {db_prefix}ep_layouts
		WHERE ' . $where,
		array_merge($where_params, [
		])
	);
	[$num_layouts] = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $num_layouts;
}

// We need to make sure that the layout name doesn't exist in any of the other layouts.
function checkLayoutName($layout_name)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_layout
		FROM {db_prefix}ep_layouts
		WHERE LOWER(name) = {string:layout_name}
			AND id_member = {int:zero}',
		[
			'zero' => 0,
			'layout_name' => strtolower($layout_name),
		]
	);

	if ($smcFunc['db_num_rows']($request) !== 0) {
		[$id_layout] = $smcFunc['db_fetch_row']($request);
		if (isset($_POST['layout_picker']) && $id_layout == $_POST['layout_picker']) {
			return $layout_name;
		} else {
			return false;
		}
	} else {
		return $layout_name;
	}
}

/**
 * Loads all the section values minus the disabled modules section for any pre-defined layouts.
 *
 * @param int $style specifies which prese layout style to use.
 *                   - 1 - Default Envision Portal Layout)
 *                   - 2 - (OMEGA Layout) <--- This actually covers all layout styles, so no need for anymore!
 *
 * @return array the layout formatted according to $style.
 *
 * @since 1.0
 */
function ep_get_predefined_layouts($style)
{
	// Here's Envision's default layout:
	switch ((int)$style) {
		case 2:
			// OMEGA
			return [
				// row 0
				[
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 0,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 0,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 0,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				],
				// row 1
				[
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 2,
					'status' => 'active',
				],
				[
					'x_pos' => 1,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				],
				// row 2
				[
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 2,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 2,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				],
				[
					'x_pos' => 2,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				],
			];
		// Default - Envision Portal
		default:
			return [
				// top
				[
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'active',
				],
				// left
				[
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				],
				// middle
				[
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				],
				// right
				[
					'x_pos' => 1,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				],
				// bottom
				[
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'inactive',
				],
			];
	}
}

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
 * Add a new layout. Assumes permissions have been worked out.
 *
 * @param string $layout_name      the name of the new layout.
 * @param array  $layout_actions   array of the actions to show this layout on. Pseudo-actions, such as board or topic
 *                                 are wrapped with brackets [].
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
 * Edit of a layout. Assumes permissions have been worked out.
 *
 * @param int    $id_layout        the ID of the layout to edit.
 * @param string $layout_name      the name of the new layout.
 * @param array  $layout_actions   array of the actions to show this layout on. Pseudo-actions, such as board or topic
 *                                 are wrapped with brackets [].
 * @param array  $layout_positions an array of all the layout positions to be inserted.
 * @param int    $smf_pos          the position ID of SMF. Usually, all layouts that are not the homepage (which hass
 *                                 the id_layout of 1) have SMF residing somewhere on them.
 * @param array  $remove_positions an array of all position IDs to be removed. Leave empty to not delete a thing..
 *
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function editLayout(
	int $id_layout,
	string $layout_name,
	array $layout_actions,
	array $layout_positions,
	int $smf_pos,
	array $remove_positions = []
): bool {
	global $smcFunc;

	// Update the name
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ep_layouts
		SET name = {string:layout_name}
		WHERE id_layout = {int:id_layout}',
		[
			'layout_name' => $layout_name,
			'id_layout' => $id_layout,
		]
	);

	// Delete old actions
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_layout_actions
		WHERE id_layout = {int:id_layout}',
		[
			'id_layout' => $id_layout,
		]
	);

	$columns = [
		'id_layout' => 'int',
		'action' => 'string',
	];

	$data = [];
	foreach ($layout_actions as $layout_action) {
		$data[] = [
			$id_layout,
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
	$update_params = [];
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
			$update_query['x_pos'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:x_pos' . $layout_position['id_layout_position'] . '}';
			$update_query['rowspan'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:rowspan' . $layout_position['id_layout_position'] . '}';
			$update_query['y_pos'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:y_pos' . $layout_position['id_layout_position'] . '}';
			$update_query['colspan'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:colspan' . $layout_position['id_layout_position'] . '}';
			$update_query['status'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {string:status' . $layout_position['id_layout_position'] . '}';
			$update_query['is_smf'] .= 'WHEN {int:id_layout_position' . $layout_position['id_layout_position'] . '} THEN {int:is_smf' . $layout_position['id_layout_position'] . '}';
			$update_params += [
				'id_layout_position' . $layout_position['id_layout_position'] => $layout_positions['id_layout_position'],
				'x_pos' . $layout_position['id_layout_position'] => $layout_positions['x_pos'],
				'rowspan' . $layout_position['id_layout_position'] => $layout_positions['rowspan'],
				'y_pos' . $layout_position['id_layout_position'] => $layout_positions['y_pos'],
				'colspan' . $layout_position['id_layout_position'] => $layout_positions['colspan'],
				'status' . $layout_position['id_layout_position'] => $layout_positions['status'],
				'is_smf' . $layout_position['id_layout_position'] => $layout_positions['is_smf'],
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

	if ($update_params == []) {
		$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions', $columns, $data, $keys);
	} else {
		$smcFunc['db_query'](
			'',
			'
			UPDATE
				{db_prefix}ep_layout_positions
			SET
				x_pos = CASE id_layout_position {raw:x_pos} END,
				rowspan = CASE id_layout_position {raw:rowsspan} END,
				y_pos = CASE id_layout_position {raw:y_pos} END,
				colspan = CASE id_layout_position {raw:colspan} END,
				status = CASE id_layout_position {raw:status} END,
				is_smf = CASE id_layout_position {raw:is_smf} END
			WHERE
				id_layout = {int:id_layout}',
			array_merge($update_params, [
				'id_layout' => $id_layout,
				'x_pos' => implode(' ', $update_query['x_pos']),
				'rowspan' => implode(' ', $update_query['rowspan']),
				'y_pos' => implode(' ', $update_query['y_pos']),
				'colspan' => implode(' ', $update_query['colspans']),
				'status' => implode(' ', $update_query['status']),
				'is_smf' => implode(' ', $update_query['is_smf']),
			])
		);
	}

	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_positions
			WHERE id_layout_position = {int:id_layout_position}',
		[
			'id_layout_position' => $smf_pos,
		]
	);

	if ($remove_positions != []) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_layout_positions
			WHERE id_layout_position IN ({array_int:remove_ids})',
			[
				'remove_ids' => $remove_positions,
			]
		);
	}

	return true;
}

/**
 * Removes all traces of a layout. Assumes permissions have been worked out.
 *
 * @param int[] $layout_list the layout to delete
 *
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function deleteLayouts(array $layout_list): bool
{
	global $smcFunc;

	checkSession();

	foreach (['layouts', 'actions', 'layout_positions', 'module_positions'] as $table_name) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_' . $table_name . '
			WHERE id_layout IN ({int:layout_list})',
			[
				'layout_list' => $layout_list,
			]
		);
	}

	return true;
}

?>