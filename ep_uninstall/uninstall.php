<?php
/**
 * This script removes Envision Portal's settings.
 *
 * NOTE: This script is meant to run using the <samp><database></database></samp> elements of the package-info.xml file. Since using the <samp><database></samp> elements automatically calls on db_extend('packages'), we will only be calling that if we are running this script standalone.
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
	die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');

// Only admins can uninstall...
if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

// An array of all table names, minus the prefixes, to uninstall.
$ep_tables = array('groups', 'layouts', 'layout_positions', 'module_positions', 'modules', 'module_clones', 'module_parameters', 'module_files', 'envision_pages', 'envision_menu', 'shoutboxes', 'shouts');

// storing all settings to be removed.
$ep_settings = array(
	'ep_pages_mode',
	'ep_forum_modules',
	'ep_collapse_modules',
	'ep_color_members',
	'ep_module_display_style',
	'ep_module_enable_animations',
	'ep_module_animation_speed',
	'ep_icon_directory',
	'ep_disable_custommod_icons',
	'ep_enable_custommod_icons',
);

db_extend('packages');

// Remove the tables.
foreach ($ep_tables as $table)
	$smcFunc['db_drop_table']('{db_prefix}ep_' . $table);

// Purge the permissions
$request = $smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission = {string:permission}',
	array(
		'permission' => 'ep_view',
	)
);

// Let's remove ep settings rows
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:ep_settings})',
	array(
		'ep_settings' => $ep_settings,
	)
);

?>
