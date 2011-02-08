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

function ep_template_transparent($module, $style, $location = 0) {

	global $modSettings, $scripturl, $settings, $txt;

	echo '
			<div', (!empty($location) ? ' style="margin-top: 7px;"' : ''), '>';

	if(!empty($module['header_display']) && $module['header_display'] != 0)
	{
		echo '
				<div id="ep_', $module['type'], 'module_', $module['id'], '" class="', (!$module['is_collapsed'] || empty($modSettings['ep_collapse_modules']) ? ' block_header' : ''), '" style="padding-left:4px;">
					<h3 class="ep_cattemp">
						', !empty($modSettings['ep_collapse_modules']) && $module['header_display'] != 2 ? '<img class="ep_curveblock floatright hand" id="' . $module['type'] . 'collapse_' . $module['id'] . '" src="' . $settings['images_url'] . '/collapse.gif" alt="" title="' . $txt['ep_core_modules'] . '" />' : '', '
						' . (empty($module['icon']) ? '' : '
									<img src="' . $module['icon'] . '" alt="" title="' . $module['title'] . '" class="icon" style="margin-left: 0px;" />&nbsp;') . (empty($module['action']) && empty($module['url']) ? '' : (!empty($module['url']) ? $module['url'] : '<a href="' . $scripturl . '?' . $module['action'] . '" target="' . $module['target'] . '">')) . $module['title'] . (empty($module['action']) && empty($module['url']) ? '' : '</a>') . '
					</h3>
				</div>';
	}

	echo '
				<div id="', $module['type'], 'module_', $module['id'], '" style="padding-left:4px; ', $module['is_collapsed'] && !empty($modSettings['ep_collapse_modules']) && $module['header_display'] == 1 ? 'display: none;' : '', '">
					<div class="ep_temp blockframe" style="margin-top: -5px;">
					', !empty($module['params']) ? $module['function']($module['params']) : $module['function'](), '
					</div>
				</div>
			</div>';
}
?>