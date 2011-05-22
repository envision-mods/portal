<?php
// Version 1.0; ManageEnvisionSettings

/**
 * This file handles showing Envision Portal's settings.
 *
 * @package template
 * @since 1.0
*/

function template_ep_admin_log()
{
	echo '
	<div id="admincenter">';

	loadSubTemplate('show_list');

	echo '
	</div>';
}

/**
 * Renders the general imformation page.
 *
 * This function handles output of data populated by {@link EnvisionPortalInfo()}:
 * - upgraded Envision version advisory
 * - latest news from envisionportal.net
 * - basic version check
 * - list of current forum admins
 * - credits
 *
 * @see EnvisionPortalInfo()
 * @since 1.0
*/
function template_portal_info()
{
	global $context, $txt, $portal_ver, $forum_version;

	echo '
	<div id="admincenter">
		<div id="ep_update_section"></div>
		<div id="ep_admin_section">
			<div id="ep_live_news" class="floatleft">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['ep_admin_config_latest_news'], '
					</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
						<div id="epAnnouncements">', $txt['ep_admin_config_unable_news'], '</div>
					</div>
				<span class="botslice"><span></span></span>
				</div>
			</div>
			<div id="epVersionTable" class="floatright">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['ep_admin_config_support_info'], '
					</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
						<div id="ep_version_details">
							<strong>', $txt['ep_admin_config_version_info'], '</strong><br />
							', $txt['ep_admin_config_installed_version'], ':
							<em id="ep_installed_version" class="no_wrap">', $portal_ver, '</em><br />
							', $txt['ep_admin_config_latest_version'], ':
							<em id="ep_latest_version" class="no_wrap">??</em><br />
							<br />
							<strong>', $txt['administrators'], ':</strong>
							', implode(', ', $context['administrators']);

	// If we have lots of admins... don't show them all.
	if (!empty($context['more_admins_link']))
		echo '
							(', $context['more_admins_link'], ')';

	echo '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $txt['ep_credits'], '
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">';

	// Start the credits.
	foreach ($context['credits'] as $section)
	{
		// Show some "pre text".
		if (isset($section['pretext']))
			echo '
				<p>', $section['pretext'], '</p><br />';

		// Show the section title.
		if (isset($section['title']))
			echo '
				<p><strong>', $section['title'], '</strong></p>';

		// And now, list the members and groups.
		foreach ($section['groups'] as $group)
		{
			// Make sure there are members first.
			if (!empty($group['members']))
			{
				echo '
				<p>';

				// Show the title.
				if (!empty($group['title']))
					echo '
					<strong>', $group['title'], '</strong>: ';

				echo '<span class="smalltext">' . implode(', ', $group['members']) . '</span>';

				echo '
				</p>';
			}
		}

		// Thanking our translators!
		if (isset($section['translators']))
			echo '
			<div class="centertext"><span class="smalltext">', $section['translators'], '</span></div>';

		// And for some "post text".
		if (isset($section['posttext']))
			echo '
			<br />
				<p>', $section['posttext'], '</p>';
	}
	echo '
				<hr />
				<p>', $txt['ep_credits_contribute'], '</p>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br class="clear" />';

	$context['insert_after_template'] .= '<script type="text/javascript" src="http://news.envisionportal.net/news.js?v=' . urlencode($portal_ver) . ';smf_version=' . urlencode($forum_version) . '"></script>';
}

?>