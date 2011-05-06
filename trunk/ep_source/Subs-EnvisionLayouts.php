<?php
/**************************************************************************************
* Subs-EnvisionLayouts.php                                                            *
/**************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function list_getLayouts($start, $items_per_page, $sort, $where, $where_params = array())
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
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
		array_merge($where_params, array(
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
		))
	);

	$layout = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$layout[$row['id_layout']] = $row;

		$action_request = $smcFunc['db_query']('', '
			SELECT
				action
			FROM {db_prefix}ep_layout_actions
			WHERE id_layout = {int:id_layout}',
			array(
				'id_layout' => $row['id_layout'],
			)
		);

		$actions = array();
		while ($action = $smcFunc['db_fetch_row']($action_request))
			$actions[] = $action[0];
		$smcFunc['db_free_result']($action_request);

		$layout[$row['id_layout']]['action_list'] = implode(', ', $actions);
	}
	$smcFunc['db_free_result']($request);

	return $layout;
}

function list_getNumLayouts($where, $where_params = array())
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(id_layout)
		FROM {db_prefix}ep_layouts
		WHERE ' . $where,
		array_merge($where_params, array(
		))
	);
	list ($num_layouts) = $smcFunc['db_fetch_row']($request);
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
		array(
			'zero' => 0,
			'layout_name' => strtolower($layout_name),
		)
	);

	if ($smcFunc['db_num_rows']($request) !== 0)
	{
		list ($id_layout) = $smcFunc['db_fetch_row']($request);
		if (isset($_POST['layout_picker']) && $id_layout == $_POST['layout_picker'])
			return $layout_name;
		else
			return false;
	}
	else
		return $layout_name;
}

/**
 * Loads all the section values minus the disabled modules section for any pre-defined layouts.
 *
 * @param int $style specifies which prese layout style to use.
 * - 1 - Default Envision Portal Layout)
 * - 2 - (OMEGA Layout) <--- This actually covers all layout styles, so no need for anymore!
 * @return array the layout formatted according to $style.
 *
 * @since 1.0
 */
function ep_get_predefined_layouts($style)
{
	// Here's Envision's default layout:
	switch ((int) $style)
	{
		case 2:
			// OMEGA
			return array(
				// row 0
				array(
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 0,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				),
				// row 1
				array(
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 2,
					'status' => 'active',
				),
				array(
					'x_pos' => 1,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				),
				// row 2
				array(
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				array(
					'x_pos' => 2,
					'y_pos' => 3,
					'colspan' => 0,
					'status' => 'active',
				)
			);
			break;
		// Default - Envision Portal
		default:
			return array(
				// top
				array(
					'x_pos' => 0,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'active'
				),
				// left
				array(
					'x_pos' => 1,
					'y_pos' => 0,
					'colspan' => 0,
					'status' => 'active',
				),
				// middle
				array(
					'smf' => true,
					'x_pos' => 1,
					'y_pos' => 1,
					'colspan' => 0,
					'status' => 'active',
				),
				// right
				array(
					'x_pos' => 1,
					'y_pos' => 2,
					'colspan' => 0,
					'status' => 'active',
				),
				// bottom
				array(
					'x_pos' => 2,
					'y_pos' => 0,
					'colspan' => 3,
					'status' => 'inactive',
				)
			);
			break;
	}
}

/**
 * Export a layout in its entirety. Assumes permissions have been worked out.
 *
 * @param int $id_layout the layout to delete
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function exportLayout($id_layout)
{
	global $context, $user_info;

	checkSession();

	if (empty($id_layout) && !is_int($id_layout))
		return false;

	$layout_data = loadLayout((int) $id_layout, true);
	$xml_children = array();

	ep_call_hook('export_layout', array(&$id_layout, &$layout_data));

	foreach ($layout_data as $row_id => $row_data)
	{
		$xml_children[$row_id] = array(
			'identifier' => 'row',
			'children' => array(),
		);
		foreach ($row_data as $column_id => $column_data)
		{
			$xml_children[$row_id]['children'][$column_id] = array(
				'identifier' => 'section',
				'children' => array(),
			);
			foreach ($column_data['extra'] as $extra_id => $extra_data)
			{
				$xml_children[$row_id]['children'][$column_id]['children'][$extra_id] = array(
					'value' => $extra_data,
				);
			}
			if (!empty($column_data['modules']))
				foreach ($column_data['modules'] as $id_position => $modules)
				{
					$xml_children[$row_id]['children'][$column_id]['children'][$id_position] = array(
						'identifier' => 'module',
						'children' => array(),
					);
					foreach ($modules as $module_id => $module_data)
						if ($module_id == 'type')
						{
							$xml_children[$row_id]['children'][$column_id]['children'][$id_position]['children'][$module_id] = array(
								'value' => $module_data,
							);
						}
				}
		}
	}
	$xml_data = array(
		'layouts' => array(
			'identifier' => 'layout',
			'children' => $xml_children,
		),
	);
	$context['sub_template'] = 'generic_xml';
	$context['xml_data'] = $xml_data;
	$context['template_layers'] = array('render_save');
	$_REQUEST['xml'] = true;

	return true;
}

/**
 * Add a new layout. Assumes permissions have been worked out.
 *
 * @param int $id_member the ID of the member creating the layout; usee 0 for admin layouts.
 * $param string $layout_name the namee of the new layout.
 * @param array $layout_actions array of the actions to show this layout on. Pseudo-actions, such as board or topic are wrapped with brackets [].
 * @param array $insert_positions an array of all the layout positions to be inserted. Returned from {@link ep_get_predefined_layouts()}.
 * @return mixed the ID of the new layout on success; false otherwise.
 * @since 1.0
 */
function addLayout($layout_name, $id_member, $layout_actions, $insert_positions)
{
	global $smcFunc;

	// Add the module info to the database
	$columns = array(
		'name' => 'string-65',
		'id_member' => 'int',
	);

	$data = array(
		$layout_name,
		$id_member,
	);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layouts',  $columns, $data, $keys);

	// We need to tell the actions table which ID was inserted
	$iid = $smcFunc['db_insert_id']('{db_prefix}ep_layouts', 'id_layout');

	// Do not continue if it failed.
	if (empty($iid))
		return false;

	// Add the module info to the database
	$columns = array(
		'id_layout' => 'int',
		'action' => 'string',
	);
	if (count($layout_actions) == 1)
		$data = array(
			$iid,
			$layout_actions[0],
		);
	else
		foreach ($layout_actions as $layout_action)
			$data[] = array(
				$iid,
				$layout_action,
			);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_actions',  $columns, $data, $keys);

	// One more to go - insert the layout.
	$columns = array(
		'id_layout' => 'int',
		'x_pos' => 'int',
		'y_pos' => 'int',
		'colspan' => 'int',
		'status' => 'string',
		'is_smf' => 'int',
	);

	$keys = array(
		'id_layout',
		'id_layout_position',
	);

	foreach ($insert_positions as $insert_position)
	{
		$data = array(
			$iid,
			$insert_position['x_pos'],
			$insert_position['y_pos'],
			$insert_position['colspan'],
			$insert_position['status'],
			(int) !empty($insert_position['smf']),
		);

		$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);
	}

	return $iid;
}

/**
 * Edit of a layout. Assumes permissions have been worked out.
 *
 * @param int $selected_layout the ID of the layout to edit.
 * @param int $id_member the ID of the member creating the layout; usee 0 for admin layouts.
 * $param string $layout_name the namee of the new layout.
 * @param array $layout_actions array of the actions to show this layout on. Pseudo-actions, such as board or topic are wrapped with brackets [].
 * @param array $layout_positions an array of all the layout positions to be inserted.
 * @param int $smf_pos the position ID of SMF. Usually, all layouts that are not the homepage (which hass the id_layout of 1) have SMF residing somewhere on them.
 * @param array $remove_positions an array of all position IDs to be removed. Leave empty to not delete a thing..
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function editLayout($selected_layout, $layout_name, $id_member, $layout_actions, $layout_positions, $smf_pos, $remove_positions = array())
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_layout
		FROM {db_prefix}ep_layouts
		WHERE id_layout = {int:id_layout}
			AND id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
			'id_layout' => $selected_layout,
		)
	);

	list ($id_layout) = $smcFunc['db_fetch_row']($request);

	if (empty($id_layout))
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT id_layout
		FROM {db_prefix}ep_layout_positions
		WHERE id_layout = {int:id_layout}
			AND is_smf = {int:smf}',
		array(
			'smf' => 1,
			'id_layout' => $selected_layout,
		)
	);

	list ($old_smf_pos) = $smcFunc['db_fetch_row']($request);

	// Update the name
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ep_layouts
		SET name = {string:layout_name}
		WHERE id_layout = {int:id_layout}',
		array(
			'layout_name' => $layout_name,
			'id_layout' => $selected_layout,
		)
	);

	// Delete old actions
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ep_layout_actions
		WHERE id_layout = {int:id_layout}',
		array(
			'id_layout' => $selected_layout,
		)
	);

	// Add the layout actions to the database
	$columns = array(
		'id_layout' => 'int',
		'action' => 'string',
	);

	$data = array();
	if (count($layout_actions) == 1)
		$data = array(
			$selected_layout,
			$layout_actions[0],
		);
	else
		foreach ($layout_actions as $layout_action)
			$data[] = array(
				$selected_layout,
				$layout_action,
			);

	$keys = array(
		'id_layout',
	);

	$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_actions',  $columns, $data, $keys);

	// Update or add positions
	foreach ($_POST['cId'] as $data)
	{
		list (, , $id_layout_position) = explode('_', $data);

		if (strpos($id_layout_position, 'add') !== false)
		{
			// We have a new one to add.
			$columns = array(
				'id_layout' => 'int',
				'x_pos' => 'int',
				'y_pos' => 'int',
				'colspan' => 'int',
				'status' => 'string',
				'is_smf' => 'int',
			);

			$keys = array(
				'id_layout',
				'id_layout_position',
			);

			$data = array(
				$selected_layout,
				$layout_positions['x_pos'][$id_layout_position],
				$layout_positions['y_pos'][$id_layout_position],
				$layout_positions['colspan'][$id_layout_position],
				$layout_positions['status'][$id_layout_position],
				$layout_positions['is_smf'][$id_layout_position],
			);

			$smcFunc['db_insert']('insert', '{db_prefix}ep_layout_positions',  $columns, $data, $keys);
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ep_layout_positions
			SET x_pos = {int:x_pos},
				y_pos = {int:y_pos},
				colspan = {int:colspan},
				status = {string:status},
				is_smf = {int:is_smf}
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $id_layout_position,
				'x_pos' => $layout_positions['x_pos'][$id_layout_position],
				'y_pos' => $layout_positions['y_pos'][$id_layout_position],
				'colspan' => $layout_positions['colspans'][$id_layout_position],
				'status' => $layout_positions['status'][$id_layout_position],
				'is_smf' => $layout_positions['is_smf'][$id_layout_position],
			)
		);
	}

	if ($selected_layout == 1 && $old_smf_pos != $smf_pos)
	{
		// The admin has chosen to move SMF. So modules in the way must begone.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_module_positions
			WHERE id_layout_position = {int:id_layout_position}',
			array(
				'id_layout_position' => $_POST['old_smf_pos'],
			)
		);
	}

	if (!empty($remove_positions))
	{
		// The admin has chosen to remove some columns.
		$killdata = explode('_', $remove_positions);

		// Remove the empty item
		unset($killdata[0]);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_layout_positions
			WHERE id_layout_position IN({array_int:remove_ids})',
			array(
				'remove_ids' => $killdata,
			)
		);
	}

	return true;
}

/**
 * Removes all traces of a layout. Assumes permissions have been worked out.
 *
 * @param int $id_layout the layout to delete
 * @return bool true on success; false otherwise.
 * @since 1.0
 */
function deleteLayout($id_layout)
{
	global $smcFunc, $user_info;

	checkSession();

	// !!! TODO: Check if the layout exists and find module fields linke to this layout

	ep_call_hook('add_layout', array(&$id_layout));

	foreach (array('layouts', 'layout_positions', 'layout_actions') as $table_name)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_' . $table_name . '
			WHERE id_layout = {int:id_layout}',
			array(
				'id_layout' => $id_layout,
			)
		);

	return true;
}

?>