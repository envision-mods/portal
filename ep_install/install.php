<?php
/**
 * This script prepares the database for all the tables and other database changes Envision Portal requires.
 *
 * NOTE: This script is meant to run using the <samp><database></database></samp> elements of the package-info.xml file. This is so admins have the choice to uninstall any database data installed with the mod. Also, since using the <samp><database></samp> elements automatically calls on db_extend('packages'), we will only be calling that if we are running this script standalone.
 *
 * @package installer
 * @since 1.0
 */

/**
 * Before attempting to execute, this file attempts to load SSI.php to enable access to the database functions.
*/
// If SSI.php is in the same place as this file, and SMF isn't defined...
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

DatabasePopulation();

// Try to chmod ep_ajax.php to 644. This might not work.
@chmod((dirname(__FILE__) . '/ep_ajax.php'), 0644);

// !!! SMF doesn't believe in the update setting for create table, so we'll use our own instead.
function ep_db_create_table($name, $columns, $indexes, $parameters)
{
	global $smcFunc, $db_prefix;

	// Make sure the name has the proper...what's that thing called? (SMF's way makes an unsafe assumption imo)
	$name = str_replace('{db_prefix}', $db_prefix, $name);
	$table_name = ((substr($name, 0, 1) == '`') ? $name : ('`' . $name));
	$table_name = ((substr($name, -1) == '`') ? $table_name : ($table_name . '`'));

	// If the table doesn't exist, create it. We're basically done here after that.
	if (!in_array(str_replace('{db_prefix}', $db_prefix, $name), $smcFunc['db_list_tables']()))
		return $smcFunc['db_create_table']($name, $columns, $indexes, $parameters, 'update');

	$query = $smcFunc['db_query']('', '
		SHOW COLUMNS
		FROM {raw:table_name}',
		array(
			'table_name' => $table_name,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($query))
	{
		foreach ($columns as $key => $column)
		{
			if ($row['Field'] == $column['name'])
			{
				$type = (isset($column['size']) ? ($column['type'] . '(' . $column['size'] . ')') : $column['type']);
				if ($row['Type'] != $type)
					$smcFunc['db_change_column']($table_name, $column['name'], $column);
				unset($columns[$key]);
				break;
			}
		}
	}

	$smcFunc['db_free_result']($query);

	if (!empty($columns))
	{
		foreach ($columns as $column)
			if(!empty($column))
				$smcFunc['db_add_column']($table_name, $column);
	}
	return true;
}

//!!! Installs Envision Portal Tables for SMF 2.0.x with default values!
function DatabasePopulation()
{
	global $smcFunc, $modSettings;

	$ep_tables = array(
		array(
			'name' => 'layouts',
			'columns' => array(
				array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 40,
				),
				array(
					'name' => 'approved',
					'type' => 'tinyint',
					'size' => 1,
					'unsigned' => true,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_layout')
				),
				array(
					'columns' => array('id_member')
				)
			),
			'default' => array(
				'columns' => array(
					'id_layout' => 'int',
					'id_member' => 'int',
				),
				'values' => array(
					array(1, 0),
				),
				'keys' => array('id_layout', 'id_member')
			)
		),
		array(
			'name' => 'layout_actions',
			'columns' => array(
				array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'action',
					'type' => 'varchar',
					'size' => 40,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_layout, action(40)')
				)
			),
			'default' => array(
				'columns' => array(
					'id_layout' => 'int',
					'action' => 'string',
				),
				'values' => array(
					array(1, '[home]'),
				),
				'keys' => array('id_layout')
			)
		),
		array(
			'name' => 'layout_positions',
			'columns' => array(
				array(
					'name' => 'id_layout_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
				),
				array(
					'name' => 'x_pos',
					'type' => 'tinyint',
					'size' => 3,
				),
				array(
					'name' => 'y_pos',
					'type' => 'tinyint',
					'size' => 3,
				),
				array(
					'name' => 'colspan',
					'type' => 'tinyint',
					'size' => 3,
				),
				array(
					'name' => 'status',
					'type' => 'enum(\'active\',\'inactive\')',
					'default' => 'active',
				),
				array(
					'name' => 'is_smf',
					'type' => 'tinyint',
					'size' => 1,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_layout_position')
				),
				array(
					'columns' => array('id_layout')
				),
			),
			'default' => array(
				'columns' => array(
					'id_layout_position' => 'int',
					'id_layout' => 'int',
					'x_pos' => 'int',
					'y_pos' => 'int',
					'colspan' => 'int',
					'status' => 'string',
				),
				'values' => array(
					array(1, 1, 0, 0, 3, 'active'),
					array(2, 1, 1, 0, 0, 'active'),
					array(3, 1, 1, 1, 0, 'active'),
					array(4, 1, 1, 2, 0, 'active'),
					array(5, 1, 2, 0, 3, 'inactive'),
				),
				'keys' => array('id_layout_position', 'id_layout')
			)
		),
		array(
			'name' => 'modules',
			'columns' => array(
				array(
						'name' => 'id_module',
						'type' => 'smallint',
						'size' => 5,
						'unsigned' => true,
						'auto' => true,
				),
				array(
						'name' => 'type',
						'type' => 'varchar',
						'size' => 80,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_module')
				),
				array(
					'type' => 'unique',
					'columns' => array('type')
				)
			),
			'default' => array(
				'columns' => array(
					'id_module' => 'int',
					'type' => 'string',
				),
				'values' => array(
					array(1, 'announce'),
					array(2, 'usercp'),
					array(3, 'stats'),
					array(4, 'online'),
					array(5, 'news'),
					array(6, 'recent'),
					array(7, 'search'),
					array(8, 'calendar'),
					array(9, 'poll'),
					array(10, 'top_posters'),
					array(11, 'theme_select'),
					array(12, 'new_members'),
					array(13, 'staff'),
					array(14, 'sitemenu'),
					array(15, 'shoutbox'),
					array(16, 'custom'),
				),
				'keys' => array('id_module', 'type')
			)
		),
		array(
			'name' => 'module_positions',
			'columns' => array(
				array(
					'name' => 'id_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'id_layout_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
				),
				array(
					'name' => 'id_module',
					'type' => 'tinyint',
					'size' => 3,
					'unsigned' => true,
				),
				array(
					'name' => 'position',
					'type' => 'tinyint',
					'size' => 2,
				)
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_position')
				),
				array(
					'columns' => array('id_layout_position')
				),
				array(
					'columns' => array('id_module')
				),
			),
			'default' => array(
				'columns' => array(
					'id_position' => 'int',
					'id_layout_position' => 'int',
					'id_module' => 'int',
					'position' => 'int',
				),
				'values' => array(
					// top
					array(1, 1, 1, 0),
					// left
					array(2, 2, 2, 0),
					array(3, 2, 3, 1),
					array(4, 2, 4, 2),
					//middle
					array(5, 3, 5, 0),
					array(6, 3, 6, 1),
					// right
					array(7, 4, 7, 0),
					array(8, 4, 8, 1),
					array(9, 4, 9, 2),
				),
				'keys' => array('id_position', 'id_layout_position', 'id_module')
			)
		),
		array(
			'name' => 'module_field_data',
			'columns' => array(
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 80,
					'unsigned' => true,
				),
				array(
					'name' => 'id_module_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
				),
				array(
					'name' => 'value',
					'type' => 'text',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('name', 'id_module_position')
				),
			),
		),
		array(
			'name' => 'member_data',
			'columns' => array(
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
				),
				array(
					'name' => 'variable',
					'type' => 'varchar',
					'size' => 80,
				),
				array(
					'name' => 'value',
					'type' => 'text',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_member', 'variable')
				),
			),
		),
		array(
			'name' => 'log_actions',
			'columns' => array(
				array(
					'name' => 'id_action',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
				),
				array(
					'name' => 'action',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'time',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
				),
				array(
					'name' => 'extra',
					'type' => 'text',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_action'),
				),
				array(
					'columns' => array('id_member'),
				),
				array(
					'columns' => array('action'),
				),
			),
		),
		array(
			'name' => 'envision_pages',
			'columns' => array(
				array(
					'name' => 'id_page',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'page_name',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'type',
					'type' => 'tinyint',
					'size' => 3,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'header',
					'type' => 'longtext',
				),
				array(
					'name' => 'body',
					'type' => 'longtext',
				),
				array(
					'name' => 'page_views',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
				),
				array(
					'name' => 'permissions',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'status',
					'type' => 'tinyint',
					'size' => 2,
					'unsigned' => true,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_page')
				),
				array(
					'type' => 'unique',
					'columns' => array('page_name')
				),
			)
		),
		array(
			'name' => 'envision_menu',
			'columns' => array(
				array(
					'name' => 'id_button',
					'type' => 'smallint',
					'size' => 5,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 65,
				),
				array(
					'name' => 'slug',
					'type' => 'varchar',
					'size' => 65,
				),
				array(
					'name' => 'type',
					'type' => 'enum(\'forum\',\'external\')',
					'default' => 'forum',
				),
				array(
					'name' => 'target',
					'type' => 'enum(\'_self\',\'_blank\')',
					'default' => '_self',
				),
				array(
					'name' => 'position',
					'type' => 'varchar',
					'size' => 65,
				),
				array(
					'name' => 'link',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'status',
					'type' => 'tinyint',
					'size' => 2,
					'unsigned' => true,
				),
				array(
					'name' => 'permissions',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'parent',
					'type' => 'varchar',
					'size' => 65,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_button')
				),
			)
		),
		array(
			'name' => 'shoutboxes',
			'columns' => array(
				array(
					'name' => 'id_shoutbox',
					'type' => 'tinyint',
					'size' => 2,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_shoutbox')
				),
			),
			'default' => array(
				'columns' => array(
					'id_shoutbox' => 'int',
					'name' => 'string-255',
				),
				'values' => array(
					array(1, 'Default'),
				),
				'keys' => array('id_shoutbox')
			)
		),
		array(
			'name' => 'shouts',
			'columns' => array(
				array(
					'name' => 'id_shout',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'message',
					'type' => 'text',
					'null' => false,
				),
				array(
					'name' => 'poster_name',
					'type' => 'varchar',
					'size' => 255,
				),
				array(
					'name' => 'poster_time',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
				),
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
				),
				array(
					'name' => 'id_shoutbox',
					'type' => 'tinyint',
					'size' => 2,
					'unsigned' => true,
					'default' => 1,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_shout')
				),
				array(
					'columns' => array('poster_time'),
				),
				array(
					'columns' => array('id_member'),
				),
			),
		)
	);

	db_extend('packages');

	foreach ($ep_tables as $table)
	{
		ep_db_create_table('{db_prefix}ep_' . $table['name'], $table['columns'], $table['indexes'], array(), 'update');

		if (isset($table['default']))
			$smcFunc['db_insert']('ignore', '{db_prefix}ep_' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
	}

	// Makes sense to let everyone view a portal, no? But don't modify the permissions if the admin has already set them.
	$request = $smcFunc['db_query']('', '
		SELECT id_group
		FROM {db_prefix}permissions
		WHERE permission = {string:permission}',
		array(
			'permission' => 'ep_view',
		)
	);

	$num = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	if (empty($num))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_group
			FROM {db_prefix}membergroups
			WHERE id_group NOT IN ({array_int:exclude_groups})
			' . (empty($modSettings['permission_enable_postgroups']) ? '
				AND min_posts = {int:min_posts}' : ''),
			array(
				'exclude_groups' => array(1, 3),
				'min_posts' => -1,
			)
		);

		$groups = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$groups[] = array($row['id_group'], 'ep_view', empty($modSettings['permission_enable_deny']) ? 1 : -1);

		$groups[] = array(-1, 'ep_view', !empty($modSettings['permission_enable_deny']) ? 1 : -1);
		$groups[] = array(0, 'ep_view', !empty($modSettings['permission_enable_deny']) ? 1 : -1);

		if (!empty($groups))
			$smcFunc['db_insert']('ignore',
				'{db_prefix}permissions',
				array('id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
				$groups,
				array('id_group', 'permission')
			);
	}

	// Finally insert the default settings into the SMF Settings table!
	updateSettings(array(
		'ep_pages_mode' => '1',
		'ep_forum_modules' => '1',
		'ep_collapse_modules' => '1',
		'ep_column_enable_animations' => '1',
		'ep_column_animation_speed' => '2',
		'ep_module_enable_animations' => '1',
		'ep_module_animation_speed' => '2',
		'ep_enable_custommod_icons' => '1',
		'ep_icon_directory' => 'ep_extra/module_icons',
		'ep_image_directory' => 'ep_extra/module_images',
	));

	// Now presenting... *drumroll*
	add_integration_function('integrate_pre_include', '$sourcedir/ep_source/Subs-EnvisionPortal.php');
	add_integration_function('integrate_pre_load', 'envision_integrate_pre_load');
	add_integration_function('integrate_load_theme', 'envision_integrate_load_theme');
	add_integration_function('integrate_actions', 'envision_integrate_actions');
	add_integration_function('integrate_menu_buttons', 'add_ep_menu_buttons');
	add_integration_function('integrate_admin_areas', 'add_ep_admin_areas');
	add_integration_function('integrate_buffer', 'envisionBuffer');
	add_integration_function('integrate_whos_online', 'envision_whos_online');
	add_integration_function('integrate_core_features', 'envision_integrate_core_features');
	add_integration_function('integrate_load_permissions', 'envision_integrate_load_permissions');
	add_integration_function('integrate_profile_areas', 'envision_integrate_profile_areas');
}

?>
