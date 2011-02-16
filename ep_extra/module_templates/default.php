<?php
/**
 * This file handles Envision Portal's default module template.
 *
 * Module templates must meet the following criteria:
 *
 * - The filename is the template name
 * - Only one function should exist
 * - The function name should be in the following format: <samp>ep_template_{TEMPLATE_NAME}</samp>
 *
 * @package moduletemplate
 * @copyright 2009-2010 Envision Portal
 * @license http://envisionportal.net/index.php?action=about;sa=legal Envision Portal License (Based on BSD)
 * @link http://envisionportal.net Support, news, and updates
 * @since 1.1
 * @version 1.1
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Renders a module.
 *
 * @param array $module Array with all of the module information.  This array gets populated within the loadLayout function of Subs-EnvisionPortal.php.
 * @param int $style The type of style being used:
 * - 1 - Modular
 * - 0 - Block
 * @param int $location This is the module's index position. This variable is set to zero (0) if the module is either the first in a column or alone.
 */

function ep_template_default($module, $style, $location = 0)
{
	global $txt, $settings, $scripturl, $modSettings;

	// Which Layout Style to show?
	if (empty($style))
	{
		if (!empty($module['header_display']) || $module['header_display'] == 2)
			echo '
			<div id="ep_module_', $module['type'], '_', $module['id'], '" class="cat_bar', (!$module['is_collapsed'] || empty($modSettings['ep_collapse_modules']) ? ' block_header' : ''), '"', (!empty($location) ? ' style="margin-top: 7px;"' : ''), '>
				<h3 class="catbg">
					', !empty($modSettings['ep_collapse_modules']) && $module['header_display'] != 2 ? '<img class="ep_curveblock floatright hand" id="' . $module['type'] . 'collapse_' . $module['id'] . '" src="' . $settings['images_url'] . '/collapse.gif" alt="" title="' . $txt['ep_core_modules'] . '" />' : '', '
					' . $module['module_icon'] . $module['module_title'] . '
				</h3>
			</div>';
		else
			echo '
				<span class="upperframe"><span></span></span>';

		echo '
				<div id="', $module['type'], 'module_', $module['id'], '"', $module['is_collapsed'] && !empty($modSettings['ep_collapse_modules']) && !empty($module['header_display']) ? ' style="display: none;"' : '', '>
					<div class="roundframe blockframe">
						', !empty($module['fields']) ? $module['function']($module['fields']) : $module['function'](), '
					</div>
				<span class="lowerframe"><span></span></span></div>';
	}
	else
	{
		if (!empty($module['header_display']))
			echo '
							<div class="cat_bar"', (!empty($location) ? ' style="margin-top: 7px;"' : ''), '>
								<h3 class="catbg">
									', !empty($modSettings['ep_collapse_modules']) && $module['header_display'] != 2 ? '<img class="ep_curveblock floatright hand" id="' . $module['type'] . 'collapse_' . $module['id'] . '" src="' . $settings['images_url'] . '/collapse.gif" alt="" title="' . $txt['ep_core_modules'] . '" />' : '', '
								' . (empty($module['icon']) ? '' : '
								<img src="' . $module['icon'] . '" alt="" title="' . $module['title'] . '" class="icon" style="margin-left: 0px;" />&nbsp;') . (empty($module['action']) && empty($module['url']) ? '' : (!empty($module['url']) ? $module['url'] : '<a href="' . $scripturl . '?' . $module['action'] . '">')) . $module['title'] . (empty($module['action']) && empty($module['url']) ? '' : '</a>') . '
								</h3>
							</div>';

		echo '
							<div id="', $module['type'], 'module_', $module['id'], '" style="padding: 0.5em 4px;', $module['is_collapsed'] && !empty($modSettings['ep_collapse_modules']) && !empty($module['header_display']) ? ' display: none;' : '', '">
								', !empty($module['params']) ? $module['function']($module['params']) : $module['function'](), '
							</div>';
	}
}

?>