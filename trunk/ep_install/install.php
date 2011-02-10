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
// change permission if file exists...
@chmod($boarddir . '/ep_ajax.php', 0644);

//!!! Installs Envision Portal Tables for SMF 2.0.x with default values!
function DatabasePopulation()
{
	global $smcFunc, $modSettings;

	$ep_tables = array();

	// ep_groups table
	$ep_tables[] = array(
		'name' => 'ep_groups',
		'columns' => array(
			0 => array(
					'name' => 'id_group',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			3 => array(
					'name' => 'active',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'default' => 0,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_group')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_member')
			)
		),
		'default' => array(
			'columns' => array(
				'id_group' => 'int', 'id_member' => 'int', 'name' => 'string-255', 'active' => 'int'
			),
			'values' => array(
			   array(1, 0, 'Default', 1),
			),
			'keys' => array('id_group', 'id_member')
		)
	);

	// ep_layouts table
	$ep_tables[] = array(
		'name' => 'ep_layouts',
		'columns' => array(
			0 => array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_group',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 65,
					'null' => false,
					'default' => '',
			),
			3 => array(
					'name' => 'actions',
					'type' => 'text',
					'null' => false,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_layout')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_group')
			)
		),
		'default' => array(
			'columns' => array(
				'id_layout' => 'int',
				'id_group' => 'int',
				'name' => 'string-65',
				'actions' => 'string',
			),
			'values' => array(
				// Only 1 action really necessary to start with!
				array(1, 1, 'Homepage', '[home]'),
			),
			'keys' => array('id_layout', 'id_group')
		)
	);

	// ep_layout_positions table
	$ep_tables[] = array(
		'name' => 'ep_layout_positions',
		'columns' => array(
			0 => array(
					'name' => 'id_layout_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'column',
					'type' => 'varchar',
					'size' => 16,
					'null' => false,
					'default' => '0:0',
			),
			3 => array(
					'name' => 'row',
					'type' => 'varchar',
					'size' => 16,
					'null' => false,
					'default' => '0:0',
			),
			4 => array(
					'name' => 'enabled',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'default' => 1,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_layout_position')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_layout')
			),
		),
		'default' => array(
			'columns' => array(
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'column' => 'string-16',
				'row' => 'string-16',
				'enabled' => 'int',
			),
			'values' => array(
				/*
				format = array(auto, layout for that action, position of the section, needs more work, is the section enabled?).
				*/
				// [home]
				array(1, 1, '0:3', '0:0', 1),
				array(2, 1, '0:0', '1:0', 1),
				array(3, 1, '1:0', '1:0', 1),
				array(4, 1, '2:0', '1:0', 1),
				array(5, 1, '0:3', '2:0', 0),
				array(6, 1, '0:0', '0:0', -1),
			),
			'keys' => array('id_layout_position', 'id_layout')
		)
	);

	// ep_module_positions table
	$ep_tables[] = array(
		'name' => 'ep_module_positions',
		'columns' => array(
			0 => array(
					'name' => 'id_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_layout_position',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'id_layout',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			3 => array(
					'name' => 'id_module',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			4 => array(
					'name' => 'id_clone',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			5 => array(
					'name' => 'position',
					'type' => 'tinyint',
					'size' => 2,
					'null' => false,
					'default' => 0,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_position')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_layout_position')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_layout')
			),
			3 => array(
				'type' => 'key',
				'columns' => array('id_module')
			),
			4 => array(
				'type' => 'key',
				'columns' => array('id_clone')
			)
		),
		'default' => array(
			'columns' => array(
				'id_position' => 'int',
				'id_layout_position' => 'int',
				'id_layout' => 'int',
				'id_module' => 'int',
				'position' => 'int',
			),
			'values' => array(
				/*

				NOTE:  SMF Module will have an id_module = 0 and id_clone = 0!  The SMF Module cannot be cloned!
				[home] action will NOT have an SMF Module, so for [home] id_module && id_clone will both never equal 0!!!

				action=[home] modules begin...
				--------------------------
				*/
				// top
				array(1, 1, 1, 1, 0),
				// left
				array(2, 2, 1, 2, 0),
				array(3, 2, 1, 3, 1),
				array(4, 2, 1, 4, 2),
				//middle
				array(5, 3, 1, 5, 0),
				array(6, 3, 1, 6, 1),
				// right
				array(7, 4, 1, 7, 0),
				array(8, 4, 1, 8, 1),
				array(9, 4, 1, 9, 2),

				// Disabled Modules have an id_layout_position where dlp.enabled = -1
				array(10, 6, 1, 10, 0),
				array(11, 6, 1, 11, 1),
				array(12, 6, 1, 12, 2),
				array(13, 6, 1, 13, 3),
				array(14, 6, 1, 14, 4),
				array(15, 6, 1, 15, 5),
				array(16, 6, 1, 16, 6),
			),
			'keys' => array('id_position', 'id_layout_position', 'id_layout', 'id_module', 'id_clone')
		)
	);

	// ep_modules table
	$ep_tables[] = array(
		'name' => 'ep_modules',
		'columns' => array(
			0 => array(
					'name' => 'id_module',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			2 => array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			3 => array(
					'name' => 'title_link',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			4 => array(
					'name' => 'target',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'unsigned' => true,
					'default' => 0,
			),
			5 => array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			6 => array(
					'name' => 'files',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			7 => array(
					'name' => 'functions',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			8 => array(
					'name' => 'header_display',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'unsigned' => true,
					'default' => 1,
			),
			9 => array(
					'name' => 'template',
					'type' => 'varchar',
					'size' => 40,
			),
			10 => array(
					'name' => 'groups',
					'type' => 'varchar',
					'size' => 255,
					'default' => '-3',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_module')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('name')
			)
		),
		'default' => array(
			'columns' => array(
				'id_module' => 'int',
				'name' => 'string-255',
				'title' => 'string-255',
				'title_link' => 'string-255',
				'target' => 'int',
				'icon' => 'string-255',
			),
			'values' => array(
				// Default Envision modules
				array(1, 'announce', 'Announcement', '', 1, 'world.png'),
				array(2, 'usercp', 'User Panel', 'action=profile', 1, 'heart.png'),
				array(3, 'stats', 'Statistics', 'action=stats', 1, 'stats.png'),
				array(4, 'online', 'Who&#039;s Online', 'action=who', 1, 'user.png'),
				array(5, 'news', 'Site News', '', 1, 'cog.png'),
				array(6, 'recent', 'Recent Topics', 'action=recent', 1, 'pencil.png'),
				array(7, 'search', 'Search', 'action=search', 1, 'magnifier.png'),
				array(8, 'calendar', 'Calendar', 'action=calendar', 1, 'cal.png'),
				array(9, 'poll', 'Poll', '', 1, 'comments.png'),
				array(10, 'top_posters', 'Top Posters', 'action=stats', 1, 'rosette.png'),
				array(11, 'theme_select', 'Theme Changer', 'action=theme;sa=pick', 1, 'palette.png'),
				array(12, 'new_members', 'Latest Members', 'action=stats', 1, 'overlays.png'),
				array(13, 'staff', 'Forum Staff', '', 1, 'rainbow.png'),
				array(14, 'sitemenu', 'Site Navigation', '', 1, 'star.png'),
				array(15, 'shoutbox', 'Shoutbox', '', 1, 'comments.png'),
				array(16, 'custom', 'Custom', '', 1, ''),
			),
			'keys' => array('id_module', 'name')
		)
	);

	// ep_module_clones table
	$ep_tables[] = array(
		'name' => 'ep_module_clones',
		'columns' => array(
			0 => array(
					'name' => 'id_clone',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_module',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			3 => array(
					'name' => 'is_clone',
					'type' => 'tinyint',
					'size' => 1,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			4 => array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			5 => array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			6 => array(
					'name' => 'title_link',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			7 => array(
					'name' => 'target',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'unsigned' => true,
					'default' => 0,
			),
			8 => array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			9 => array(
					'name' => 'files',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			10 => array(
					'name' => 'functions',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			11 => array(
					'name' => 'header_display',
					'type' => 'tinyint',
					'size' => 1,
					'null' => false,
					'unsigned' => true,
					'default' => 1,
			),
			12 => array(
					'name' => 'template',
					'type' => 'varchar',
					'size' => 40,
			),
			13 => array(
					'name' => 'groups',
					'type' => 'varchar',
					'size' => 255,
					'default' => '-3',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_clone')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_module')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_member')
			)
		)
	);

	// ep_module_parameters table
	$ep_tables[] = array(
		'name' => 'ep_module_parameters',
		'columns' => array(
			0 => array(
					'name' => 'id_param',
					'type' => 'bigint',
					'size' => 20,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_clone',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'id_module',
					'type' => 'int',
					'size' => 4,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			3 => array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			4 => array(
					'name' => 'type',
					'type' => 'varchar',
					'size' => 16,
					'null' => false,
					'default' => '',
			),
			5 => array(
					'name' => 'value',
					'type' => 'text',
					'null' => false,
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_param')
			),
			1 => array(
				'type' => 'key',
				'columns' => array('id_clone')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_module')
			)
		),
		'default' => array(
			'columns' => array(
				'id_param' => 'int',
				'id_module' => 'int',
				'name' => 'string-255',
				'type' => 'string-16',
				'value' => 'string',
			),
			'values' => array(
				// Announcement
				array(1, 1, 'msg', 'large_text', 'Welcome to Envision Portal!'),
				// Stats
				array(2, 3, 'stat_choices', 'checklist', '0,1,2,5,6:members;posts;topics;categories;boards;ontoday;onever:order'),
				// Online
				array(3, 4, 'online_pos', 'select', '0:top;bottom'),
				array(4, 4, 'show_online', 'checklist', '0,1,2:users;buddies;guests;hidden;spiders:order'),
				array(5, 4, 'online_groups', 'list_groups', '-3:-1,0,3:order'),
				// News
				array(6, 5, 'board', 'list_boards', '1'),
				array(7, 5, 'limit', 'int', '5'),
				// Recent Topics/Posts
				array(8, 6, 'post_topic', 'select', '1:posts;topics'),
				array(9, 6, 'show_avatars', 'check', '1'),
				array(10, 6, 'num_recent', 'int', '10'),
				// Calendar
				array(11, 8, 'display', 'select', '0:month;info'),
				array(12, 8, 'animate', 'select', '1:none;horiz'),
				array(13, 8, 'show_months', 'select', '1:year;asdefined'),
				array(14, 8, 'previous', 'int', '1'),
				array(15, 8, 'next', 'int', '1'),
				array(16, 8, 'show_options', 'checklist', '0,1,2:events;holidays;birthdays:order'),
				// Poll
				array(17, 9, 'options', 'select', '2:showPoll;topPoll;recentPoll'),
				array(18, 9, 'topic', 'int', '0'),
				// Top Posters
				array(19, 10, 'show_avatar', 'check', '1'),
				array(20, 10, 'show_postcount', 'check', '1'),
				array(21, 10, 'num_posters', 'int', '10'),
				// Latest Members
				array(22, 12, 'limit', 'int', '3'),
				array(23, 12, 'list_type', 'select', '0:0;1;2'),
				// Forum Staff
				array(24, 13, 'list_type', 'select', '1:0;1;2'),
				array(25, 13, 'name_type', 'select', '0:0;1;2'),
				array(26, 13, 'groups', 'list_groups', '1,2:-1,0:order'),
				// Site Navigation
				array(27, 14, 'onesm', 'check', '0'),
				// Shoutbox
				array(28, 15, 'id', 'db_select', '1;id_shoutbox:{db_prefix}ep_shoutboxes;name:custom'),
				array(29, 15, 'refresh_rate', 'int', '5'),
				array(30, 15, 'max_count', 'int', '15'),
				array(31, 15, 'max_chars', 'int', '128'),
				array(32, 15, 'text_size', 'select', '1:small;medium'),
				array(33, 15, 'member_color', 'check', '1'),
				array(34, 15, 'message', 'text', ''),
				array(35, 15, 'message_position', 'select', '1:top;after;bottom'),
				array(36, 15, 'message_groups', 'list_groups', '-3:3'),
				array(37, 15, 'mod_groups', 'list_groups', '1:-1,0,3'),
				array(38, 15, 'mod_own', 'list_groups', '0,1,2:-1,3'),
				array(39, 15, 'bbc', 'list_bbc', 'b;i;u;s;pre;left;center;right;url;sup;sub;php;nobbc;me'),
				// Custom PHP/BBC/HTML
				array(40, 16, 'code_type', 'select', '1:0;1;2'),
				array(41, 16, 'code', 'rich_edit', ''),
			),
			'keys' => array('id_param', 'id_clone', 'id_module')
		)
	);

	// ep_module_files table
	$ep_tables[] = array(
		'name' => 'ep_module_files',
		'columns' => array(
			0 => array(
					'name' => 'id_file',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'auto' => true,
			),
			1 => array(
					'name' => 'id_thumb',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			2 => array(
					'name' => 'id_param',
					'type' => 'bigint',
					'size' => 20,
					'unsigned' => true,
					'null' => false,
			),
			3 => array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			4 => array(
					'name' => 'file_type',
					'type' => 'int',
					'size' => 3,
					'unsigned' => true,
					'null' => false,
					'default' => 0,
			),
			5 => array(
					'name' => 'filename',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			),
			6 => array(
					'name' => 'file_hash',
					'type' => 'varchar',
					'size' => 40,
					'null' => false,
					'default' => '',
			),
			7 => array(
					'name' => 'fileext',
					'type' => 'varchar',
					'size' => 8,
					'null' => false,
					'default' => '',
			),
			8 => array(
					'name' => 'size',
					'type' => 'int',
					'size' => 10,
					'null' => false,
					'default' => 0,
					'unsigned' => true,
			),
			9 => array(
					'name' => 'downloads',
					'type' => 'mediumint',
					'size' => 8,
					'null' => false,
					'default' => 0,
					'unsigned' => true,
			),
			10 => array(
					'name' => 'width',
					'type' => 'mediumint',
					'size' => 8,
					'null' => false,
					'default' => 0,
					'unsigned' => true,
			),
			11 => array(
					'name' => 'height',
					'type' => 'mediumint',
					'size' => 8,
					'null' => false,
					'default' => 0,
					'unsigned' => true,
			),
			12 => array(
					'name' => 'mime_type',
					'type' => 'varchar',
					'size' => 255,
					'null' => false,
					'default' => '',
			)
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_file')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('id_member', 'id_file')
			),
			2 => array(
				'type' => 'key',
				'columns' => array('id_param')
			),
			3 => array(
				'type' => 'key',
				'columns' => array('file_type')
			)
		)
	);

	// ep_envision_pages table
	$ep_tables[] = array(
		'name' => 'ep_envision_pages',
		'columns' => array(
			0 => array(
				'name' => 'id_page',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'page_name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			2 => array(
				'name' => 'type',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			3 => array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			4 => array(
				'name' => 'body',
				'type' => 'longtext',
				'null' => false,
			),
			5 => array(
				'name' => 'page_views',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			6 => array(
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'status',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_page')
			),
			1 => array(
				'type' => 'unique',
				'columns' => array('page_name')
			),
		)
	);

	// ep_envision_menu table
	$ep_tables[] = array(
		'name' => 'ep_envision_menu',
		'columns' => array(
			0 => array(
				'name' => 'id_button',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			2 => array(
				'name' => 'slug',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'type',
				'type' => 'enum(\'forum\',\'external\')',
				'default' => 'forum',
			),
			4 => array(
				'name' => 'target',
				'type' => 'enum(\'_self\',\'_blank\')',
				'default' => '_self',
			),
			5 => array(
				'name' => 'position',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
			6 => array(
				'name' => 'link',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			7 => array(
				'name' => 'status',
				'type' => 'enum(\'active\',\'inactive\')',
				'default' => 'active',
			),
			8 => array(
				'name' => 'permissions',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			9 => array(
				'name' => 'parent',
				'type' => 'varchar',
				'size' => 65,
				'null' => false,
				'default' => '',
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_button')
			),
		)
	);

	// ep_shoutboxes table
	$ep_tables[] = array(
		'name' => 'ep_shoutboxes',
		'columns' => array(
			0 => array(
				'name' => 'id_shoutbox',
				'type' => 'tinyint',
				'size' => 2,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
		),
		'indexes' => array(
			0 => array(
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
	);

	// ep_shouts table
	$ep_tables[] = array(
		'name' => 'ep_shouts',
		'columns' => array(
			0 => array(
				'name' => 'id_shout',
				'type' => 'int',
				'size' => 10,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
				'auto' => true,
			),
			1 => array(
				'name' => 'message',
				'type' => 'text',
				'null' => false,
			),
			2 => array(
				'name' => 'poster_name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => '',
			),
			3 => array(
				'name' => 'poster_time',
				'type' => 'int',
				'size' => 10,
				'unsigned' => true,
			),
			4 => array(
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'unsigned' => true,
			),
			5 => array(
				'name' => 'id_shoutbox',
				'type' => 'tinyint',
				'size' => 2,
				'unsigned' => true,
				'default' => 1,
			),
		),
		'indexes' => array(
			0 => array(
				'type' => 'primary',
				'columns' => array('id_shout')
			),
			array(
				'columns' => array('poster_time'),
			),
			array(
				'columns' => array('id_member'),
			),
		)
	);

	db_extend('packages');

	// Create all of the tables!
	foreach($ep_tables as $table)
	{
		$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes'], array(), 'update');
		// Insert all defaults if we have any...
		if (isset($table['default']))
			$smcFunc['db_insert']('ignore', '{db_prefix}' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
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
		'ep_icon_directory' => 'envisionportal/module_icons',
		'ep_image_directory' => 'envisionportal/module_images',
	));

	// Now presenting... *drumroll*
	add_integration_function('integrate_pre_load', 'envision_integrate_pre_load');
	add_integration_function('integrate_load_theme', 'envision_integrate_load_theme');
	add_integration_function('integrate_actions', 'envision_integrate_actions');
	add_integration_function('integrate_menu_buttons', 'add_ep_menu_buttons');
	add_integration_function('integrate_admin_areas', 'add_ep_admin_areas');
	add_integration_function('integrate_buffer', 'envisionBuffer');
	add_integration_function('integrate_whos_online', 'envision_whos_online');
}

?>
