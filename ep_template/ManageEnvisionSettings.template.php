<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

function template_portal_info()
{
	global $context, $txt, $portal_ver, $forum_version;

	echo '
		<section id="ep_admin_section">
			<section>
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
			</section>
			<section>
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
							<em id="ep_latest_version" class="no_wrap">??</em>
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</section>
		</section>
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $txt['ep_credits'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">';

	// Start the credits.
	foreach ($context['credits'] as $section) {
		// Show some "pre text".
		if (isset($section['pretext'])) {
			echo '
				<p>', $section['pretext'], '</p><br />';
		}

		// Show the section title.
		if (isset($section['title'])) {
			echo '
				<p><strong>', $section['title'], '</strong></p>';
		}

		// And now, list the members and groups.
		foreach ($section['groups'] as $group) {
			// Make sure there are members first.
			if (!empty($group['members'])) {
				echo '
				<p>';

				// Show the title.
				if (!empty($group['title'])) {
					echo '
					<strong>', $group['title'], '</strong>: ';
				}

				echo '<span class="smalltext">' . implode(', ', $group['members']) . '</span>';

				echo '
				</p>';
			}
		}

		// Thanking our translators!
		if (isset($section['translators'])) {
			echo '
			<div class="centertext"><span class="smalltext">', $section['translators'], '</span></div>';
		}

		// And for some "post text".
		if (isset($section['posttext'])) {
			echo '
			<br />
				<p>', $section['posttext'], '</p>';
		}
	}
	echo '
				<hr />
				<p>', $txt['ep_credits_contribute'], ' <b><a href="https://github.com/envision-mods/portal">', $txt['ep_credits_contribute_github'], '</a></b></p>
			</div>
			<span class="botslice"><span></span></span>
		</div>';
}

function template_callback_ep_admin_config(): void
{
	global $txt;

	echo '
									</dl>
									<div class=' . (defined('SMF_VERSION') ? 'descbox' : 'plainbox') . ' centertext">', $txt['ep_admin_config_general_optional'], '</div>
									<dl class=settings>';
}