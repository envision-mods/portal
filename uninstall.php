<?php
/**
 * This script removes Envision Portal's settings.
 *
 * NOTE: This script is meant to run using the <samp><database></database></samp> elements of the package-info.xml
 * file. Since using the <samp><database></samp> elements automatically calls on db_extend('packages'), we will only be
 * calling that if we are running this script standalone.
 *
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
	die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');
}

$ep_settings = [
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
];

$request = $smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission = {string:permission}',
	[
		'permission' => 'ep_view',
	]
);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable = {string:setting0}
		OR variable = {string:setting1}
		OR variable LIKE {string:setting2}',
	[
		'setting0' => 'ep_menu',
		'setting1' => 'ep_button_count',
		'setting2' => 'ep_button%',
	]
);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:ep_settings})',
	[
		'ep_settings' => $ep_settings,
	]
);
