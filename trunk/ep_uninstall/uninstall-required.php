<?php
/**
 * This script removes Envision Portal's settings.
 *
 * NOTE: This script is meant to run using the <samp><code></code></samp> elements of the package-info.xml file. This is because certain items in the database and within SMF will need to be removed regardless of whether the user wants to keep data or not. In this instance, the registered hooks need to lose their calls to Envision Portal's functions, else the forum'll crash.
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

	remove_integration_function('integrate_pre_include', '$sourcedir/ep_source/Subs-EnvisionPortal.php');
	remove_integration_function('integrate_pre_load', 'envision_integrate_pre_load');
	remove_integration_function('integrate_load_theme', 'envision_integrate_load_theme');
	remove_integration_function('integrate_actions', 'envision_integrate_actions');
	remove_integration_function('integrate_menu_buttons', 'add_ep_menu_buttons');
	remove_integration_function('integrate_admin_areas', 'add_ep_admin_areas');
	remove_integration_function('integrate_buffer', 'envisionBuffer');
	remove_integration_function('integrate_whos_online', 'envision_whos_online');
	remove_integration_function('integrate_core_features', 'envision_integrate_core_features');
	remove_integration_function('integrate_load_permissions', 'envision_integrate_load_permissions');
	remove_integration_function('integrate_profile_areas', 'envision_integrate_profile_areas');

?>
