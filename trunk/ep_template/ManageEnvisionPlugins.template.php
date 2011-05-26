<?php
// Version 1.0; ManageEnvisionPlugins

function template_main()
{
	global $context, $txt, $settings, $options, $scripturl;

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=epplugins;sa=manage2" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['ep_plugins_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div id="ep_plugins_list" class="roundframe">';

	foreach ($context['ep_plugins'] as $id => $ep_plugin)
		echo '
				<img class="ep_plugins_image png_fix" src="', $context['ep_plugins_url'], '/', $id, '/icons/ep_plugin_', $id, '.png" alt="', $ep_plugin['title'], '" />
				<div id="ep_plugins_', $id, '" class="options">
					<label for="ep_plugins_', $id, '_radio_on"><input type="radio" name="ep_plugins_c[', $id, ']" id="ep_plugins_', $id, '_radio_on" value="1"', $ep_plugin['enabled'] ? ' checked="checked"' : '', ' class="input_radio" />', $txt['ep_plugin_enabled'], '</label>
					<label for="ep_plugins_', $id, '_radio_off"><input type="radio" name="ep_plugins_c[', $id, ']" id="ep_plugins_', $id, '_radio_off" value="0"', !$ep_plugin['enabled'] ? ' checked="checked"' : '', ' class="input_radio" />', $txt['ep_plugin_disabled'], '</label>
				</div>
				<h4>', ($ep_plugin['enabled'] && !empty($ep_plugin['url']) ? '<a href="' . $ep_plugin['url'] . '">' . $ep_plugin['title'] . '</a>' : $ep_plugin['title']), '</h4>
				<p>', $ep_plugin['description'], '</p>
					<input type="hidden" value="', $ep_plugin['enabled'] ? 1 : 0, '" name="ep_plugins_c[', $id, ']" id="c_ep_plugins_', $id, '" />
				<br class="clear" />';

	echo '
				<div class="righttext">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" value="', $txt['save'], '" name="save" class="button_submit" />
				</div>
			</div>
			<span class="lowerframe"><span></span></span>
		</form>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/ep_plugins.js"></script>
	</div>';
}

?>
