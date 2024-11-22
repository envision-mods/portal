<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

// If SSI.php is in the same place as this file, and SMF isn't defined...
if (file_exists(__DIR__ . '/SSI.php') && !defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} // Hmm... no SSI.php and no SMF?
elseif (!defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

$tables = [
	[
		'name' => 'ep_layouts',
		'columns' => [
			[
				'name' => 'id_layout',
				'type' => 'tinyint',
				'size' => 3,
				'auto' => true,
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 40,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_layout'],
			],
		],
		'default' => [
			'columns' => [
				'id_layout' => 'int',
				'name' => 'string',
			],
			'values' => [
				[1, 'Home'],
				[2, 'Forum'],
				[3, 'Demo Page'],
			],
			'keys' => ['id_layout'],
		],
	],
	[
		'name' => 'ep_layout_actions',
		'columns' => [
			[
				'name' => 'id_layout',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'action',
				'type' => 'varchar',
				'size' => 40,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_layout, action(40)'],
			],
		],
		'default' => [
			'columns' => [
				'id_layout' => 'int',
				'action' => 'string',
			],
			'values' => [
				[1, '[home]'],
				[2, '[boards]'],
				[2, '[topics]'],
				[3, '[page]=demo-page'],
			],
			'keys' => ['id_layout'],
		],
	],
	[
		'name' => 'ep_layout_positions',
		'columns' => [
			[
				'name' => 'id_layout_position',
				'type' => 'smallint',
				'size' => 5,
				'auto' => true,
			],
			[
				'name' => 'id_layout',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'x_pos',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'rowspan',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'y_pos',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'colspan',
				'type' => 'tinyint',
				'size' => 3,
			],
			[
				'name' => 'status',
				'type' => 'enum(\'active\',\'inactive\')',
				'default' => 'active',
			],
			[
				'name' => 'is_smf',
				'type' => 'tinyint',
				'size' => 1,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_layout_position'],
			],
			[
				'columns' => ['id_layout'],
			],
		],
		'default' => [
			'columns' => [
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'x_pos' => 'int',
				'rowspan' => 'int',
				'y_pos' => 'int',
				'colspan' => 'int',
				'status' => 'string',
				'is_smf' => 'int',
			],
			'values' => [
				// landing page
				[1, 1, 1, 1, 1, 3, 'active', 0],
				[2, 1, 2, 1, 1, 1, 'active', 0],
				[3, 1, 2, 1, 2, 1, 'active', 0],
				[4, 1, 2, 1, 3, 1, 'active', 0],
				[5, 1, 3, 1, 1, 3, 'active', 1],
				[6, 2, 1, 1, 1, 1, 'active', 0],
				[7, 2, 1, 1, 2, 3, 'active', 1],
				[8, 3, 1, 1, 1, 3, 'active', 0],
				[9, 3, 2, 2, 1, 1, 'active', 0],
				[10, 3, 2, 1, 2, 2, 'active', 1],
				[11, 3, 3, 1, 2, 2, 'active', 0],
				[12, 3, 2, 2, 4, 1, 'active', 0],
				[13, 3, 1, 1, 4, 1, 'active', 0],
			],
			'keys' => ['id_layout_position', 'id_layout'],
		],
	],
	[
		'name' => 'ep_module_positions',
		'columns' => [
			[
				'name' => 'id_position',
				'type' => 'smallint',
				'size' => 5,
				'auto' => true,
			],
			[
				'name' => 'id_layout_position',
				'type' => 'smallint',
				'size' => 5,
				'unsigned' => true,
			],
			[
				'name' => 'type',
				'type' => 'varchar',
				'size' => 80,
			],
			[
				'name' => 'position',
				'type' => 'tinyint',
				'size' => 2,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_position'],
			],
		],
		'default' => [
			'columns' => [
				'id_position' => 'int',
				'id_layout_position' => 'int',
				'type' => 'string',
				'position' => 'int',
			],
			'values' => [
				// landing page
				// top
				[1, 1, 'announce', 0],
				// left
				[2, 2, 'user_cp', 0],
				[3, 2, 'stats', 1],
				[4, 2, 'online', 2],
				// middle
				[5, 3, 'news', 0],
				[6, 3, 'recent', 1],
				// right
				[7, 4, 'search', 0],
				[8, 4, 'calendar', 1],
				[9, 4, 'poll', 2],
				// boards
				// left
				[10, 6, 'user_cp', 0],
				// demo page
				// top
				[11, 8, 'announce', 0],
				[12, 8, 'new_members', 0],
				// left
				[13, 9, 'user_cp', 0],
				[14, 9, 'stats', 1],
				[15, 9, 'online', 2],
				[16, 9, 'theme_select', 3],
				[17, 9, 'sitemenu', 4],
				// middle
				[18, 11, 'news', 0],
				[19, 11, 'recent', 1],
				[20, 11, 'announce', 2],
				// right
				[21, 12, 'search', 0],
				[22, 12, 'calendar', 1],
				[23, 12, 'poll', 2],
				[24, 12, 'staff', 3],
				[25, 12, 'top_posters', 4],
				[25, 12, 'new_members', 5],
			],
			'keys' => ['id_position'],
		],
	],
	[
		'name' => 'ep_module_field_data',
		'columns' => [
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 80,
				'unsigned' => true,
			],
			[
				'name' => 'id_module_position',
				'type' => 'smallint',
				'size' => 5,
			],
			[
				'name' => 'value',
				'type' => 'text',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['name', 'id_module_position'],
			],
		],
		'default' => [
			'columns' => [
				'name' => 'string',
				'id_module_position' => 'int',
				'value' => 'string',
			],
			'values' => [
				['module_header_display', 11, 'collapse'],
				['msg', 11, 'Demo'],
				['limit', 18, '10'],
				['grouping', 24, '1'],
			],
			'keys' => ['id_layout_position', 'id_layout'],
		],
	],
	[
		'name' => 'envision_pages',
		'columns' => [
			[
				'name' => 'id_page',
				'type' => 'smallint',
				'size' => 5,
				'auto' => true,
			],
			[
				'name' => 'slug',
				'type' => 'varchar',
				'size' => 65,
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'type',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'description',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'body',
				'type' => 'longtext',
			],
			[
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'status',
				'type' => 'enum(\'active\',\'inactive\')',
				'default' => 'active',
			],
			[
				'name' => 'poster_name',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
			],
			[
				'name' => 'created_at',
				'type' => 'timestamp',
				'default' => '1970-01-01 00:00:01',
			],
			[
				'name' => 'updated_at',
				'type' => 'timestamp',
				'default' => '1970-01-01 00:00:01',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_page'],
			],
			[
				'type' => 'unique',
				'columns' => ['slug'],
			],
			[
				'columns' => ['id_member'],
			],
		],
		'default' => [
			'columns' => [
				'id_page' => 'int',
				'name' => 'string-255',
				'slug' => 'string-65',
				'type' => 'string-65',
				'description' => 'string-255',
				'body' => 'string',
				'permissions' => 'string-255',
			],
			'values' => [
				[
					1,
					'Demo Page',
					'demo-page',
					'HTML',
					'A quick sample to demonstrate all modules included with Envision Portal.',
					'Some text shall go here.',
					'2',
				],
				[
					2,
					'Sample PHP Page',
					'sample-php-page',
					'PHP',
					'just a test',
					"<?php\n\n\$var =  \"hello and good morning\";\necho \$var;\n\$var =  \'hola y buenos dias\';\necho \$var;\n\$var =  \'how can I say \"goodbye\" so soon?\';\necho \$var;",
					'-1,0,2',
				],
			],
			'keys' => ['slug'],
		],
	],
	[
		'name' => 'ep_menu',
		'columns' => [
			[
				'name' => 'id_button',
				'type' => 'smallint',
				'size' => 5,
				'unsigned' => true,
				'auto' => true,
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 65,
			],
			[
				'name' => 'type',
				'type' => 'enum(\'forum\',\'external\')',
				'default' => 'forum',
			],
			[
				'name' => 'target',
				'type' => 'enum(\'_self\',\'_blank\')',
				'default' => '_self',
			],
			[
				'name' => 'position',
				'type' => 'varchar',
				'size' => 65,
			],
			[
				'name' => 'link',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'status',
				'type' => 'enum(\'active\',\'inactive\')',
				'default' => 'active',
			],
			[
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'parent',
				'type' => 'varchar',
				'size' => 65,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_button'],
			],
		],
		'default' => [
			'columns' => [
				'id_button' => 'int',
				'name' => 'string-255',
				'position' => 'string-65',
				'link' => 'string-255',
				'permissions' => 'string-255',
				'parent' => 'string-65',
			],
			'values' => [
				[1, 'Demo Page', 'before', 'page=demo-page', '2', 'home'],
			],
			'keys' => ['id_button'],
		],
	],
	[
		'name' => 'ep_shoutboxes',
		'columns' => [
			[
				'name' => 'id_shoutbox',
				'type' => 'tinyint',
				'size' => 2,
				'unsigned' => true,
				'auto' => true,
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_shoutbox'],
			],
		],
		'default' => [
			'columns' => [
				'id_shoutbox' => 'int',
				'name' => 'string-255',
			],
			'values' => [
				[1, 'Default'],
			],
			'keys' => ['id_shoutbox'],
		],
	],
	[
		'name' => 'ep_shouts',
		'columns' => [
			[
				'name' => 'id_shout',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
				'auto' => true,
			],
			[
				'name' => 'message',
				'type' => 'text',
			],
			[
				'name' => 'poster_name',
				'type' => 'varchar',
				'size' => 255,
			],
			[
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
			],
			[
				'name' => 'created_at',
				'type' => 'timestamp',
				'default' => '1970-01-01 00:00:01',
			],
			[
				'name' => 'updated_at',
				'type' => 'timestamp',
				'default' => '1970-01-01 00:00:01',
			],
			[
				'name' => 'id_shoutbox',
				'type' => 'tinyint',
				'size' => 2,
				'unsigned' => true,
				'default' => 1,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_shout'],
			],
			[
				'columns' => ['id_member'],
			],
		],
	],
];

db_extend('packages');

foreach ($tables as $table) {
	$smcFunc['db_create_table'](
		'{db_prefix}' . $table['name'],
		$table['columns'],
		$table['indexes'],
		[],
		'ignore'
	);

	if (isset($table['default'])) {
		$smcFunc['db_insert'](
			'ignore',
			'{db_prefix}' . $table['name'],
			$table['default']['columns'],
			$table['default']['values'],
			$table['default']['keys']
		);
	}
}

$buttons = [];
$request = $smcFunc['db_query'](
	'',
	'
	SELECT
		id_button, name, target, type, position, link, status, permissions, parent
	FROM {db_prefix}ep_menu'
);

while ($row = $smcFunc['db_fetch_assoc']($request)) {
	$buttons['ep_button_' . $row['id_button']] = json_encode([
		'name' => $row['name'],
		'target' => $row['target'],
		'type' => $row['type'],
		'position' => $row['position'],
		'groups' => array_map('intval', explode(',', $row['permissions'])),
		'link' => $row['link'],
		'active' => $row['status'] == 'active',
		'parent' => $row['parent'],
	]);
}
$smcFunc['db_free_result']($request);

if (!empty($buttons)) {
	$request = $smcFunc['db_query'](
		'',
		'
		SELECT MAX(id_button)
		FROM {db_prefix}ep_menu'
	);
	[$max] = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable LIKE {string:settings_search}
			AND variable NOT IN ({array_string:settings})',
		[
			'settings_search' => 'ep_button%',
			'settings' => array_keys($buttons),
		]
	);
	updateSettings(['ep_button_count' => $max] + $buttons);
}

// Makes sense to let everyone view a portal, no? But don't modify the permissions if the admin has already set them.
$request = $smcFunc['db_query']('', '
		SELECT id_group
		FROM {db_prefix}permissions
		WHERE permission = {string:permission}',
	[
		'permission' => 'ep_view',
	]
);

$num = $smcFunc['db_num_rows']($request);
$smcFunc['db_free_result']($request);

if (empty($num)) {
	$request = $smcFunc['db_query']('', '
			SELECT id_group
			FROM {db_prefix}membergroups
			WHERE id_group NOT IN ({array_int:exclude_groups})' . (empty($modSettings['permission_enable_postgroups']) ? '
				AND min_posts = {int:min_posts}' : ''),
		[
			'exclude_groups' => [1, 3],
			'min_posts' => -1,
		]
	);

	$groups = [
		[-1, 'ep_view', !empty($modSettings['permission_enable_deny']) ? 1 : -1],
		[0, 'ep_view', !empty($modSettings['permission_enable_deny']) ? 1 : -1],
	];
	while ([$id_group] = $smcFunc['db_fetch_row']($request)) {
		$groups[] = [$id_group, 'ep_view', empty($modSettings['permission_enable_deny']) ? 1 : -1];
	}

	if (!empty($groups)) {
		$smcFunc['db_insert']('ignore',
			'{db_prefix}permissions',
			['id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'],
			$groups,
			['id_group', 'permission']
		);
	}
}

// Finally insert the default settings into the SMF Settings table!
updateSettings([
	'ep_pages_mode' => '1',
	'ep_forum_modules' => '1',
	'ep_collapse_modules' => '1',
	'ep_icon_directory' => 'ep_extra/module_icons',
	'ep_image_directory' => 'ep_extra/module_images',
]);

function toBits(int $x_pos, int $rowspan, int $y_pos, int $colspan):  int
{
	$area = 0;
	$area |= ($x_pos & 0x7) << 9;
	$area |= ($rowspan & 0x7) << 6;
	$area |= ($y_pos & 0x7) << 3;
	$area |= ($colspan & 0x7);

	return $area;
}