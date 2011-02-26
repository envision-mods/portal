<?php
// Version 1.0; EnvisionPortal

/**
 * Template for displaying everything above the portal. In this case, the basic rendering of the layout is done here. Modules that go after MF are held in a buffer and saved for later.
 *
 * @since 1.0
 */
function template_portal_above()
{
	global $context, $txt, $modSettings, $settings, $user_info;

	if (!empty($context['envision_columns']))
	{
		$ep_module_display_style = !empty($modSettings['ep_module_display_style']) ? $modSettings['ep_module_display_style'] : 0;

		echo '
		<table class="ep_main">';

		foreach ($context['envision_columns'] as $row_id => $row_data)
		{
			echo '
			<tr class="tablerow', $row_id, '" valign="top">';

			foreach ($row_data as $column_id => $column_data)
				if (!empty($column_data['modules']))
				{
					echo '
				<td class="tablecol_', $column_id, '"', $column_data['html'], '>';

						if ($column_data['is_smf'])
						{
							ob_start();
							$buffer = true;
						}
						else
							template_module_column($ep_module_display_style, $column_data['modules']);

					echo '
				</td>';

				}

			echo '
			</tr>';
		}
		echo '
		</table>';
	}

	$context['envision_buffer'] = !empty($buffer) ? ob_get_clean() : '';
}

// This must be here to maintain balance!  DO NOT REMOVE!
function template_portal()
{
}

/**
 * Outputs everything in the buffer started in template_portal_above() and destroys it.
 *
 * @since 1.0
 */
function template_portal_below()
{
	global $context;

	// Everything trapped by the buffer gets written here. It's the best and easiest way that I know of...
	echo $context['envision_buffer'];
}

/**
 * Sets up the column if the display style is set to Modular and calls the apropriate template for this module or cloned module (clone).
 *
 * @since 1.0
 */
function template_module_column($style = 0, $column = array())
{
	global $context, $settings, $modSettings;

	// Modular Style
	if (!empty($style))
		echo '
					<span class="clear upperframe"><span></span></span>
					<div class="roundframe"><div class="innerframe">';

	$i = 0;
	foreach ($column as $m)
	{
		call_user_func_array('ep_template_' . $m['template'], array($m, $style, $i));
		$i++;
	}

	// Modular Style
	if (!empty($style))
		echo '
					</div></div>
					<span class="lowerframe margin2"><span></span></span>';
}

/**
 * Template used to render a Envision Page.
 *
 * @since 1.0
 */
function template_envision_pages()
{
	global $context;

	echo '
					<div class="cat_bar">
						<h3 class="catbg">
							', $context['page_data']['title'], '
						</h3>
					</div>
					<span class="upperframe"><span></span></span>
						<div class="roundframe">
							', $context['page_data']['body'], '
						</div>
					<span class="lowerframe"><span><!-- // --></span></span>
				';
}

/**
 * Template used to view the shout history.
 *
 * @since 1.0
 */
function template_view_shouts()
{
	global $context, $txt;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['shoutbox_view_msg'], '
				</h3>
			</div>';

		echo '
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">';

	if (empty($context['shouts']))
		echo '
					', $txt['shoutbox_no_msg'];

	else
		foreach ($context['shouts'] as $shout)
			echo $shout;

		echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>';
}
?>
