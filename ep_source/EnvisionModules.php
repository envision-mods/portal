<?php
/**************************************************************************************
* EnvisionModules.php                                                                 *
***************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains all the modules used with Envision Portal.

	void module_usercp()
		- !!!
	void module_stats()
		- !!!
	void module_announce()
		- !!!
	void module_news()
		- !!!
	void module_recent()
		- !!!
	void module_search()
		- !!!
	void module_poll()
		- !!!
	void module_online()
		- !!!
	void module_calendar()
		- !!!
	void module_topPosters()
		- !!!
	void module_staff()
		- !!!
	void module_custom()
		- !!!
	void module_theme_selector()
		- !!!
	void module_new_members()
		- !!!
	void module_shoutbox()
		- !!!
	void module_sitemenu()
		- !!!
*/

function module_usercp()
{
	global $context, $txt, $scripturl, $settings, $user_info;

	// Only display this info if we are logged in.
	if (!$user_info['is_guest'])
	{
		// Set the logout variable.
		$logout = sprintf($scripturl . '?action=logout;%1$s=%2$s', $context['session_var'], $context['session_id']);

		$_SESSION['logout_url'] = ep_getUrl();

		// What does the user want the time formatted as?
		$s = strpos($user_info['time_format'], '%S') === false ? '' : ':%S';
		if (strpos($user_info['time_format'], '%H') === false && strpos($user_info['time_format'], '%T') === false)
		{
			$h = strpos($user_info['time_format'], '%l') === false ? '%I' : '%l';
			$time_fmt = $h . ':%M' . $s . ' %p';
		}
		else
			$time_fmt = '%H:%M' . $s;

		// Some time vars, and a bullet image.
		//!!! Now uses timeformat() instead of date() for localization
		$time = timeformat(time(), '%a, ' . $time_fmt);
		$b = '<img src="' . $context['epmod_image_url'] . 'bullet' . ($context['right_to_left'] ? '_rtl' : '') . '.gif" alt="" />&nbsp;';

		echo '
							<span class="ep_hello">', $txt['hello_member_ndt'], ', ', $user_info['name'], '</span><br />';

		if (!empty($user_info['avatar']['image']))
			echo '
							<div style="padding: 1ex">
								<a href="', $scripturl, '?action=profile">', $user_info['avatar']['image'], '</a>
							</div>';
		else
			echo '
							<br />';

		echo '
							<ul class="ep_list ep_paddingleft">
								<li class="lefttext">', $b, $txt['total_posts'], ': ', $user_info['posts'], '</li>
								<li class="lefttext">', $b, $txt['view'], ': <a href="', $scripturl, '?action=unread">', $txt['ep_user_posts'], '</a> | <a href="', $scripturl, '?action=unreadreplies">', $txt['ep_user_replies'], '</a></li>
								<li class="lefttext">', $b, $txt['view'], ': <a href="', $scripturl, '?action=pm">', $txt['ep_inbox'], '</a> | <a href="', $scripturl, '?action=pm;f=sent">', $txt['ep_outbox'], '</a></li>
							</ul><br />
							<div class="floatleft">
								<img src="', $context['ep_icon_url'], '/eye.png" alt="" title="', $txt['ip'], '" /> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">', $_SERVER['REMOTE_ADDR'], '</a><br />
								<img src="', $context['ep_icon_url'], '/clock.png" alt="" title="', $txt['ep_time'], '" />&nbsp;', $time, '
							</div>
							<br class="clear" />
							<form action="" style="padding-top: 5px;"><input type="button" value="', $txt['logout'], '" onclick="parent.location=\'' . $logout . '\'" class="button_submit" /></form>';
	}
	// They're a guest? Show the guest info here instead, and a login box.
	else
	{
		$_SESSION['login_url'] = ep_get_url();

		echo '
							', $txt['hello_guest'], ' <strong>', $txt['guest'], '</strong>.<br />
							', $txt['login_or_register'], '<br />
							<br />
							<form action="', $scripturl, '?action=login2" method="post">
								<table border="0" cellspacing="2" cellpadding="0" class="table">
									<tr>
										<td class="lefttext"><label for="user">', $txt['ep_login_user'], ':</label>&nbsp;</td>
										<td class="lefttext"><input type="text" name="user" id="user" size="10" /></td>
									</tr>
									<tr>
										<td class="lefttext"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</td>
										<td class="lefttext"><input type="password" name="passwrd" id="passwrd" size="10" /></td>
									</tr>
									<tr>
										<td class="lefttext"><label for="cookielength">', $txt['ep_length'], '</label>&nbsp;</td>
										<td>
										<select name="cookielength" id="cookielength">
											<option value="60">', $txt['one_hour'], '</option>
											<option value="1440">', $txt['one_day'], '</option>
											<option value="10080">', $txt['one_week'], '</option>
											<option value="302400">', $txt['one_month'], '</option>
											<option value="-1" selected="selected">', $txt['forever'], '</option>
										</select>
										</td>
									</tr>
									<tr>
										<td class="righttext" colspan="2"><input type="submit" value="', $txt['login'], '" class="button_submit" /></td>
									</tr>
								</table>
							</form>
							', $txt['welcome_guest_activate'], '';
	}
}

function module_stats($params)
{
	global $txt, $smcFunc, $scripturl, $settings, $modSettings, $context;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		if (!isset($params['stat_choices']['checked']) || empty($params['stat_choices']))
		{
			module_error();
			return;
		}
		else
			$stat_choices = explode(',', $params['stat_choices']['checked']);

		// in_array workaround (array_combine > PHP 5)
		$stat_choices = array_combine($stat_choices, $stat_choices);

		// Nothing to show, return.
		if (isset($stat_choices['-2']))
		{
			module_error('empty');
			return;
		}

		$totals = array();

		if (isset($stat_choices[3]))
		{
			// How many cats? Er...categories. Not cats...xD
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(id_cat)
				FROM {db_prefix}categories');
			list ($totals['cats']) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}

		if (isset($stat_choices[4]))
		{
			// How many boards?
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(id_board)
				FROM {db_prefix}boards
				WHERE redirect = {string:blank_redirect}',
				array(
					'blank_redirect' => '',
				)
			);
			list ($totals['boards']) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}

		// An image to act as a bullet.
		$b = '<img src="' . $context['ep_icon_url'] . '/chart_pie.png" alt="" title="' . $txt['forum_stats'] . '" />&nbsp;';

		// Start the output.
		echo '
				<ul class="ep_list">';

		foreach($stat_choices as $type)
		{
			$type = (int) $type;
			echo '
					<li>' . $b;
			switch ($type)
			{
				case 0:
					echo $txt['total_members'] . ': <a href="' . $scripturl . '?action=mlist">' . comma_format($modSettings['totalMembers']) . '</a>';
					break;
				case 1:
					echo $txt['total_posts'] . ': ' . comma_format($modSettings['totalMessages']);
					break;
				case 2:
					echo $txt['total_topics'] . ': ' . comma_format($modSettings['totalTopics']);
					break;
				case 3:
					echo $txt['total_cats'] . ': ' . comma_format($totals['cats']);
					break;
				case 4:
					echo $txt['total_boards'] . ': ' . comma_format($totals['boards']);
					break;
				case 5:
					echo $txt['most_online_today'] . ': ' . comma_format($modSettings['mostOnlineToday']);
					break;
				default: // case 6:
       				echo $txt['most_online_ever'] . ': ' . comma_format($modSettings['mostOnline']);
					break;
			}
			echo '
					</li>';
		}
		echo '
			</ul>';

		// No longer need this.
		unset($totals);

	}
	else
		module_error();
}

function module_announce($params)
{
	global $context;

	// Grab the parameters, if they exist.
	if (is_array($params))
	{
		$msg = html_entity_decode($params['msg'], ENT_QUOTES);

		// Does this exist?
		if (!empty($msg))
			echo parse_bbc($msg);
		// No? Error!
		else
			module_error();
	}
	// I guess $params isn't an array....shame.
	else
		module_error();
}

/*

// Just an example with the file_input param type.
function module_announce($params)
{
	// file_input parameter test.
	if (is_array($params))
	{
		if(count($params['msg']) <= 0)
		{
			echo 'No files defined yet.  Please upload some files via your Admin Panel!';
			return;
		}
		foreach($params['msg'] as $key => $file)
			if ($file['has_thumb'] && $file['is_image'])
				echo '<a href="', $file['href'], ';image" target="_blank"><img src="', $file['thumb_href'], '" border="0" /></a><br />';
	}
	else
		module_error();
}
*/


function module_news($params)
{
	global $context, $txt, $settings, $modSettings;

	// Grab the parameters, if they exist.
	if (is_array($params))
	{
		$board = empty($params['board']) ? 1 : $params['board'];
		$limit = empty($params['limit']) ? 5 : $params['limit'];

		// Store the board news
		$input = ep_boardNews($board, $limit);

		// Default - Any content?
		if (empty($input))
		{
			module_error('empty');
			return;
		}

		foreach ($input as $news)
		{
			echo '
									<div class="ep_news">
										<img src="', $settings['images_url'], '/on.png" alt="" />
										<p>
											<a href="', $news['href'], '"><strong>', $news['subject'], '</strong></a> ', $txt['by'], ' ', (!empty($modSettings['ep_color_members']) ? $news['color_poster'] : $news['poster']), '<br />
											<span class="smalltext">', $news['time'], '</span>
										</p>';

			if (!$news['is_last'])
				echo '
										<div class="ep_dashed clear"><!-- // --></div>';
			else
				echo '
										<div class="clear"><!-- // --></div>';

			echo '
									</div>';

		}
	}
	// Throw an error.
	else
		module_error();
}

function module_recent($params)
{
	global $txt, $context, $scripturl, $modSettings, $memberContext, $settings;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		$function = !empty($params['post_topic']) ? 'ep_recentTopics' : 'ep_recentPosts';
		$num_recent = empty($params['num_recent']) ? 8 : $params['num_recent'];
		$show_avatars = (bool) empty($params['show_avatars']) ? false : true;

		// Access the function.
		$input = $function($num_recent);

		// The count var.
		$count = 0;

		if ($show_avatars)
		{
			$userids = array();

			// Grab all user ids.
			foreach($input as $recentId)
				$userids[] = $recentId['poster']['id'];

			// Load all members data in one call.
			loadMemberData($userids);
		}

		// Return the recent posts/topics.
		foreach ($input as $recent)
		{
			$count++;

			// Only load this if the poster id is not zero!
			if ($recent['poster']['id'] != 0 && $show_avatars)
			{
				loadMemberContext($recent['poster']['id']);

				// The member data will go into here.
				$recent['poster']['more_info'] = $memberContext[$recent['poster']['id']];
			}

			echo '
							<div class="ep_recent_icon">', $recent['poster']['id'] != 0 ? (!empty($recent['poster']['more_info']['avatar']['href']) ? '
								<img class="ep_recent_avatar" src="' . $recent['poster']['more_info']['avatar']['href'] . '" title="' . $recent['poster']['name'] . '" alt="" />' : '
								') : '', '
							</div>
							<div class="ep_recent">
								<p>
									<a href="', $recent['href'], '" title="', $recent['preview'], '"><strong>', $recent['subject'], '</strong></a>', !$recent['is_new'] ? '' : ' <a href="' . $scripturl . '?topic=' . $recent['topic'] . '.msg' . $recent['new_from'] . ';topicseen#new" rel="nofollow"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" border="0" /></a>', '<br />
									<span class="smalltext">', ($function == 'topics' ? $txt['topic_started'] : $txt['ep_last_poster']), ': ', (!empty($modSettings['ep_color_members']) ? $recent['poster']['color_link'] : $recent['poster']['link']), ' ', $txt['in'], ' ', $recent['board']['link'], ' | ', $recent['time'], '</span>
								</p>';

			// Create a horizontal rule in between posts/topics except for the last one.
			if ($count != count($input))
				echo '
								<div class="ep_dashed clear"><!-- // --></div>';
			else
				echo '
								<div class="clear"><!-- // --></div>';

			echo '
							</div>';
		}
	}
	// Throw an error.
	else
		module_error();
}

function module_search()
{
	global $scripturl, $txt, $context;

	echo '
							<div class="centertext">
								<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '" name="searchform" id="searchform">
								<div class="centertext" style="margin-top: -5px;"><input name="search" size="18" maxlength="100" tabindex="', $context['tabindex']++, '" type="text" class="input_text" /></div>

								<script type="text/javascript"><!-- // --><![CDATA[
									function initSearch()
									{
										if (document.forms.searchform.search.value.indexOf("%u") != -1)
											document.forms.searchform.search.value = unescape(document.forms.searchform.search.value);
									}
									createEventListener(window);
									window.addEventListener("load", initSearch, false);
								// ]]></script>

								<select name="searchtype" tabindex="', $context['tabindex']++, '" style="margin: 5px 5px 0 0;">
									<option value="1" selected="selected">', $txt['ep_match_all_words'], '</option>
									<option value="2">', $txt['ep_match_any_words'], '</option>
								</select><input style="margin-top: 5px;" name="submit" value="', $txt['search'], '" tabindex="', $context['tabindex']++, '" type="submit" class="button_submit" />
								</form>
							</div>';
}

function module_poll($params)
{
	global $txt, $boardurl, $user_info, $context, $smcFunc, $modSettings;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		$function = empty($params['options']) ? 'ep_showPoll' : 'ep_' . $params['options'];
		$topic = !isset($params['topic']) ? 0 : $params['topic'];

		// List the allowed functions to use.
		$allowed_functions = array(
			'ep_showPoll',
			'ep_topPoll',
			'ep_recentPoll',
		);

		// Default - Any content?
		if ($function == 'ep_showPoll' && empty($topic))
		{
			module_error('empty');
			return;
		}

		// Now check if we can use the function provided in $function.
		if (in_array($function, $allowed_functions))
		{
			// Function found, let's return it, with the right params!
			if ($function == 'ep_showPoll')
				$poll = $function($topic);
			else
				$poll = $function();

			// Show the poll!
			if (!empty($poll['allow_vote']))
			{
				echo '
							<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
								<input type="hidden" name="poll" value="', $poll['id'], '" />
								<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
									<tr>
										<td><strong>', $poll['question'], '</strong></td>
									</tr>
									<tr>
										<td>', $poll['allowed_warning'], '</td>
									</tr>';

				foreach ($poll['options'] as $option)
					echo '
									<tr>
										<td><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></td>
									</tr>';

				echo '
									<tr>
										<td><input type="submit" value="', $txt['poll_vote'], '" class="button_submit" /></td>
									</tr>
								</table>
								<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</form>';
			}
			elseif (!empty($poll['allow_view_results']))
			{
				echo '
							<strong>', $poll['question'], '</strong><br />';

				foreach ($poll['options'] as $option)
					echo '
							&bull; ', $option['option'], '<br />
							<div class="statsbar" style="width: 49%; margin: 2px 0 5px;">', $option['bar'], '</div>
							<span class="floatright righttext">', $option['votes'], ' (', $option['percent'], '%)</span><br />';

				echo '
							<strong>', $poll['total_votes'], ' members voted</strong>';
			}
			// Cannot see it I'm afraid!
			else
				echo '
							<p class="error">', $txt['poll_cannot_see'], '</p>';
		}
		// Throw an error.
		else
			module_error();
	}
	// Throw an error.
	else
		module_error();
}

function module_online($params)
{
	global $scripturl, $smcFunc, $txt, $modSettings, $context, $user_info;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		$online_groups = array();
		$show_online = array();
		$show_online = !isset($params['show_online']['checked']) || empty($params['show_online']) || $params['show_online']['checked'] < 0 ? NULL : explode(',', $params['show_online']['checked']);
		$online_pos = empty($params['online_pos']) ? 'top' : $params['online_pos'];
		$online_groups = !isset($params['online_groups']) ? array('-3') : explode(',', $params['online_groups']);

		// Yes, much faster than using in_array.
		$online_groups = array_combine($online_groups, $online_groups);

		// Get all info.
		$online = ep_whosOnline();
		$online_info = '';

		// Ok, lets build the list of online things to show.
		if (!empty($show_online))
		{
			$online_info .= '<ul class="ep_list ep_paddingleft">';
			foreach ($show_online as $option => $type)
			{
				$type = (int) $type;

				// Guests don't have any buddies.
				if (($type == 1 && !$user_info['is_guest']) || $type != 1)
					$online_info .= '<li>&bull;&nbsp;';

				switch ($type)
				{
					case 0:  // Users
						$online_info .= $txt['users'] . ': ' . $online['num_users'];
						break;
					case 1:  // Buddies
						$online_info .= !$user_info['is_guest'] ? $txt['buddies'] . ': ' . $online['num_buddies'] : '';
						break;
					case 2:  // Guests
						$online_info .= $txt['guests'] . ': ' . $online['num_guests'];
						break;
					case 3: // Hidden
						$online_info .= $txt['hidden'] . ': ' . $online['num_users_hidden'];
						break;
					default: // Spiders
						$online_info .= $txt['spiders'] . ': ' . $online['num_spiders'];
						break;
				}

				// Guests don't have any buddies, sorry.
				if (($type == 1 && !$user_info['is_guest']) || $type != 1)
					$online_info .= '</li>';
			}
			$online_info .= '</ul>';
		}

		if (!empty($show_online) && $online_pos == 'top' && !empty($online_info))
			echo $online_info;

		// Grab the online groups, if we have any.
		if (!isset($online_groups['-2']) && !empty($online['online_groups']))
		{
			// Need to order the array of online groups based on the $online_groups array.
			if (!isset($online_groups['-3']))
			{
				foreach ($online_groups as $groupid)
				{
					$id_group = (int) $groupid;

					if (!empty($online['online_groups'][$id_group]))
					{
						if (!isset($beginul))
						{
							if (!empty($show_online) && $online_pos == 'top' && !empty($online_info))
								echo '
									<hr />';

							// Ready to begin the output of groups.
							echo '
									<div class="ep_control_flow">
										<ul class="ep_list ep_paddingleft">';
							$beginul = true;
						}

						echo '
												<li><strong>' . $online['online_groups'][$id_group]['name'] . '</strong>:
													<ul class="ep_list_indent">';

						// Get all users for this group
						foreach ($online['users'] as $user)
						{
							if ($user['group'] == $id_group)
								echo '
										<li>', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'] , '</li>';
						}

						echo '
													</ul>
												</li>';
					}
				}
			}
			else
			{
				// Is -3 and need to load all groups and all users online.  A bit tricky, but we need to do this SUPER FAST no matter how any groups/users online!!

				// Loading up all users
				foreach ($online['online_groups'] as $group)
				{
					if (!isset($start_group))
					{
						$start_group = true;

						if (!isset($beginul))
						{
							if (!empty($show_online) && $online_pos == 'top' && !empty($online_info))
								echo '<hr />';

							// Ready to begin the output of groups.
							echo '
								<div class="ep_control_flow">
									<ul class="ep_list ep_paddingleft">';
							$beginul = true;
						}
					}

						echo '
												<li><strong>' . $group['name'] . '</strong>:
													<ul class="ep_list_indent">';

						foreach ($online['users'] as $user)
							if ($user['group'] == $group['id'])
								echo '
														<li>', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'] , '</li>';
								echo '
													</ul></li>';

				}
				// No longer needed.
				unset($start_group);
			}

			// Close it up.
			if (!empty($beginul))
			{
				echo '
							</ul>
						</div>';
				unset($beginul);
				$endul = true;
			}

			if (!empty($show_online) && !empty($online_info) && $online_pos == 'bottom')
			{
				if (!empty($online['online_groups']) && !isset($online_groups['-2']) && !empty($endul))
				{
					echo '
							<hr />';
					unset($endul);
				}
			}
		}
		if ($online_pos == 'bottom' && !empty($online_info))
			echo $online_info;
	}

	// Throw an error.
	else
		module_error();
}

function module_calendar($params)
{
	global $smcFunc, $context, $scripturl, $options, $txt;

	// Grab the params.
	if (is_array($params))
	{
		// TODO:  Output of Text-Based Display!
		$display = !isset($params['display']) || $params['display'] == 'month' ? 0 : 1;
		$animate = !isset($params['animate']) || $params['animate'] == 'horiz' ? 1 : 0;
		$show_months = !isset($params['show_months']) || $params['show_months'] == 'asdefined' ? 1 : 0;
		$prev_months = !isset($params['previous']) ? 1 : (int) $params['previous'];
		$next_months = !isset($params['next']) ? 1 : (int) $params['next'];
		$show_options = !isset($params['show_options']['checked']) || empty($params['show_options']) || $params['show_options']['checked'] < 0 ? NULL : explode(',', $params['show_options']['checked']);
		$show_prev_next = !empty($next_months) || !empty($prev_months) || empty($show_months) ? true : false;

		if (!empty($animate))
			echo '
			<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>';

		// Set $unique_id to the info for this calendar module instance and reset the $context variable.
		$unique_id = $context['ep_mod_calendar'][0];
		unset($context['ep_mod_calendar'][0]);
		$context['ep_mod_calendar'] = array_values($context['ep_mod_calendar']);

		// Obtain todays info.
		$today = array(
			'day' => (int) strftime('%d', forum_time()),
			'month' => (int) strftime('%m', forum_time()),
			'year' => (int) strftime('%Y', forum_time()),
			'date' => strftime('%Y-%m-%d', forum_time()),
		);

		// Some calendar options
		$calOptions = array(
			'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
			'show_week_num' => false,
		);

		// Calendar Information.  Mostly parameter values.
		$curCalInfo = array(
			'display' => $display,
			'animate' => $animate,
			'show_months' => $show_months,
			'show_options' => $show_options,
			'show_prev_next' => $show_prev_next,
			'prev_months' => $prev_months,
			'next_months' => $next_months,
			'unique_id' => $unique_id,
		);

		$calEvents = '';

		// Calculate the first and last months for the year or as defined!
		if (empty($show_months))
		{
			$curCalInfo += array(
				 'first_month' => 1,
				 'last_month' => 12,
			 );
		}
		else
		{
			$curCalInfo += array(
				'first_month' => empty($prev_months) ? $today['month'] : $today['month'] - $prev_months,
				'last_month' => empty($next_months) ? $today['month'] : $today['month'] + $next_months,
			);
		}

		echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				var current_' . $unique_id . ' = "' . $unique_id . '_' . $today['date'] . '";

				function ep_collapse_' . $unique_id . '(id)
				{
					var new_' . $unique_id . ' = "' . $unique_id . '_' . '" + id;

					if (new_' . $unique_id . ' == current_' . $unique_id . ')
						return false;

					document.getElementById(current_' . $unique_id . ').style.display = "none";
					document.getElementById(new_' . $unique_id . ').style.display = "";
					current_' . $unique_id . ' = new_' . $unique_id . ';
					return true;
				}

				function ep_highlight_' . $unique_id . '(obj, event)
				{
					if (event == "over")
						obj.className = "information days hand";
					else
						obj.className = "windowbg days hand";
				}';

		if ($show_prev_next && empty($animate))
			echo '
				first_' . $unique_id . ' = ' . $curCalInfo['first_month'] . ';
				last_' . $unique_id . ' = ' . $curCalInfo['last_month'] . ';
				current' . $unique_id . ' = ' . $today['month'] . ';

				function nextCalMonth_' . $unique_id . '()
				{
					var obj = document.getElementById("cal_' . $unique_id . '_" + current' . $unique_id . ');
					obj.style.display = "none";

					if (current'. $unique_id . ' == last_' . $unique_id . ') { current'. $unique_id . ' = ' . $curCalInfo['first_month'] . '; }
					else { current' . $unique_id . '++ }
					var object = document.getElementById("cal_' . $unique_id . '_" + current' . $unique_id . ');
					object.style.display = "table-row";
				}

				function previousCalMonth_' . $unique_id . '()
				{
					var obj = document.getElementById("cal_' . $unique_id . '_" + current' . $unique_id . ');
					obj.style.display = "none";

					if (current' . $unique_id . ' == first_' . $unique_id . ') { current' . $unique_id . ' = last_' . $unique_id . '; }
					else { current' . $unique_id . '--; }
					var object = document.getElementById("cal_' . $unique_id . '_" + current' . $unique_id . ');
					object.style.display = "table-row";
				}';
		elseif (!empty($animate))
		{
			echo '

				var $j = jQuery.noConflict();

				$j(document).ready(function(){
					var liCount' . $unique_id . ' = $j(\'#calmask' . $unique_id . ' ul li\').length;
					var ulWidth' . $unique_id . ' = liCount' . $unique_id . ' * 180;

					$j(\'#calmask' . $unique_id . ' ul\').css({width: ulWidth' . $unique_id . '});

					var maskWidth' . $unique_id . ' = 170;
					var listWidth' . $unique_id . ' = ulWidth' . $unique_id . ';
					var roomToMoveW' . $unique_id . ' = maskWidth' . $unique_id . ' - listWidth' . $unique_id . ';
					var currentLeftPos' . $unique_id . ' = - ' . (empty($curCalInfo['show_months']) ? $today['month'] - 1 : $curCalInfo['prev_months']) . ' * 180;';

					// RTL must start at the last li tag in here!
					if ($context['right_to_left'])
						echo '

						$j(\'#calmask' . $unique_id . ' ul\').position({
							left: (ulWidth' . $unique_id . ' - 180)
						});';

					echo '

					var startSlide = ' . ($context['right_to_left'] ? 'Math.abs(currentLeftPos' . $unique_id . ') + 10' : 'currentLeftPos' . $unique_id) . '

					// Slide to the current month!
					$j(\'#calmask' . $unique_id . ' ul\').animate({
						left: startSlide
					});

					// Prevent default click event on Next/Previous links
					$j("#controls' . $unique_id . ' a").click(function(event) {
						event.preventDefault();
					});

					$j(\'#nextLink' . $unique_id . '\').click(function(){
						if(currentLeftPos' . $unique_id . ' > roomToMoveW' . $unique_id . ' + 10){
								var widPos' . $unique_id . ' = currentLeftPos' . $unique_id . ' - maskWidth' . $unique_id . ';
								if(widPos' . $unique_id . ' < roomToMoveW' . $unique_id . '){
									widPos' . $unique_id . ' = roomToMoveW' . $unique_id . ';
								}
								$j(\'#calmask' . $unique_id . ' ul\').animate({
									left: ' . ($context['right_to_left'] ? 'Math.abs(widPos' . $unique_id . ') + 20' : 'widPos' . $unique_id . ' - 10') . '
								});
								currentLeftPos' . $unique_id . ' = widPos' . $unique_id . ' - 10;
							 }
					});

					$j(\'#prevLink' . $unique_id . '\').click(function(){
						if(currentLeftPos' . $unique_id . ' < 0){
							var widPos' . $unique_id . ' = currentLeftPos' . $unique_id . ' + maskWidth' . $unique_id . ';
							if(widPos' . $unique_id . ' > 0){
								widPos' . $unique_id . ' = 0;
							}
							$j(\'#calmask' . $unique_id . ' ul\').animate({
								left: ' . ($context['right_to_left'] ? '- widPos' . $unique_id : 'widPos' . $unique_id . ' + 10') . '
							});
							currentLeftPos' . $unique_id . ' = widPos' . $unique_id . ' + 10;
						}
					});
				})';
		}
			echo '

			// ]]></script>';

		// Build the array of months!
		$calData = array();

		// Do we start with previous months or next months?
		if ($context['right_to_left'])
		{
			if (!empty($curCalInfo['next_months']) || empty($curCalInfo['show_months']))
			{
				$calNext = array();

				$nextCount = empty($curCalInfo['show_months']) ? ($today['month'] < 12 ? 12 - $today['month'] : 0) : $curCalInfo['next_months'];

				$cNMonth = $today['month'];
				$cNYear = $today['year'];
				$x = 1;

				for ($i = 0; $i < $nextCount; $i++)
				{
					if ($cNMonth + $x == 13)
					{
						$cNMonth = 1;
						$cNYear++;
					}
					else
						$cNMonth++;

					$iNext = count($calData) - 1;
					$calData[$i] = array(
						'month' => $cNMonth,
						'year' => $cNYear,
					);
				}

				// Reverse the array so it displays correctly!
				if (count($calData) > 1)
					$calData = array_reverse($calData);
			}
		}
		else
		{
			if (!empty($curCalInfo['prev_months']) || empty($curCalInfo['show_months']))
			{
				$calPrev = array();

				// Get the previous count for this!
				$prevCount = empty($curCalInfo['show_months']) ? ($today['month'] > 1 ? $today['month'] - 1 : 0) : $curCalInfo['prev_months'];

				$cPMonth = $today['month'];
				$cPYear = $today['year'];
				$x = 1;

				for ($i = 0; $i < $prevCount; $i++)
				{
					if ($cPMonth - $x == 0)
					{
						$cPMonth = 12;
						$cPYear--;
					}
					else
						$cPMonth--;

					$calData[$i] = array(
						'month' => $cPMonth,
						'year' => $cPYear,
					);
				}

				if (count($calData) > 1)
					$calData = array_reverse($calData);
			}
		}

		// Current Month!
		$calData['currMonth'] = array(
			'month' => $today['month'],
			'year' => $today['year'],
		);

		if ($context['right_to_left'])
		{
			if (!empty($curCalInfo['prev_months']) || empty($curCalInfo['show_months']))
			{
				$calPrev = array();

				// Get the previous count for this!
				$prevCount = empty($curCalInfo['show_months']) ? ($today['month'] > 1 ? $today['month'] - 1 : 0) : $curCalInfo['prev_months'];

				$cPMonth = $today['month'];
				$cPYear = $today['year'];
				$x = 1;

				for ($i = 0; $i < $prevCount; $i++)
				{
					if ($cPMonth - $x == 0)
					{
						$cPMonth = 12;
						$cPYear--;
					}
					else
						$cPMonth--;

					$iPrev = count($calData) - 1;
					$calData[$iPrev] = array(
						'month' => $cPMonth,
						'year' => $cPYear,
					);
				}
			}
		}
		else
		{
			if (!empty($curCalInfo['next_months']) || empty($curCalInfo['show_months']))
			{
				$calNext = array();

				$nextCount = empty($curCalInfo['show_months']) ? ($today['month'] < 12 ? 12 - $today['month'] : 0) : $curCalInfo['next_months'];

				$cNMonth = $today['month'];
				$cNYear = $today['year'];
				$x = 1;

				for ($i = 0; $i < $nextCount; $i++)
				{
					if ($cNMonth + $x == 13)
					{
						$cNMonth = 1;
						$cNYear++;
					}
					else
						$cNMonth++;

					$iNext = count($calData) - 1;
					$calData[$iNext] = array(
						'month' => $cNMonth,
						'year' => $cNYear,
					);
				}
			}
		}

		// Time to make the donuts!
		$calCurr = ep_calendar_getinfo($calData, $today, $calOptions, $curCalInfo, $context['right_to_left']);

		// yeah, using <table> element for structuring purposes here to be most compatible!
		echo '
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td align="center" valign="middle">';

		if ($show_prev_next)
			echo '
				<div id="controls' . $unique_id . '" style="width: 170px; font-size: 10px;">
					<a id="nextLink' . $unique_id . '" class="floatright" style="display:inline;" href="' . (empty($animate) ? 'javascript:nextCalMonth_' . $unique_id . '();' : 'javascript:void(0);') . '" onfocus="if(this.blur)this.blur();">', $txt['ep_calendar_next'], '</a>
					<a id="prevLink' . $unique_id . '" class="floatleft" style="display:inline;" href="' . (empty($animate) ? 'javascript:previousCalMonth_' . $unique_id . '();' : 'javascript:void(0);') . '" onfocus="if(this.blur)this.blur();">', $txt['ep_calendar_prev'], '</a>
				</div>
				</td>
				</tr>
				<tr>
					<td align="center" valign="middle">';

		if (!empty($animate))
			echo '
				<div id="calmask' . $unique_id . '" style="position:relative; width: 170px; overflow: hidden;">
					<ul style="position:relative; left: 0px; top: 0px; margin: 0px; padding: 0px; overflow: hidden;">';

		$month_var = array();
		$year_var = array();
		$xYears = 1;

		// Looping through it all now!
		foreach($calCurr as $year => $calMonth)
		{
			if (empty($animate))
			{
				// How many years to multiply 12 months by?
				if ($today['year'] < $year)
					$xYears = $year - $today['year'];
				elseif ($today['year'] > $year)
					$xYears = $today['year'] - $year;
			}

			foreach($calMonth as $month => $calInfo)
			{
				if (isset($month_var[$calInfo['current_month']], $year_var[$calInfo['current_year']]))
					continue;

				// Calculate monthly cal ids for non-animation!
				if (empty($animate))
					$idMonth = $calInfo['current_year'] > $today['year'] ? ($xYears * 12) + $calInfo['current_month'] : ($calInfo['current_year'] < $today['year'] ? $calInfo['current_month'] - (12 * $xYears) : $calInfo['current_month']);

				$monthYear = '<a href="' . $scripturl . '?action=calendar;year=' . $calInfo['current_year'] . ';month=' . $calInfo['current_month'] . '">' . $txt['months_titles'][$calInfo['current_month']] . ' ' . $calInfo['current_year'] . '</a>';

				// Return the calendar.
				if (!empty($animate))
					echo '
						<li style="float: left; width: 170px; display: inline; margin: 0px; padding: 0px 10px 0px 0px;">';
				else
					echo '
					<div id="cal_' . $unique_id . '_' . $idMonth . '" style="display: ' . ($today['month'] == $calInfo['current_month'] && $today['year'] == $calInfo['current_year'] ? 'table-row' : 'none') . ';">';

					echo '
								<div class="ep_month_grid">
									<div class="cat_bar">
										<h3 class="catbg centertext" style="font-size: small;">
											', $monthYear, '
										</h3>
									</div>
									<table cellspacing="1" class="calendar_table" style="width: 170px;">
										<tr>';

				// List the days.
				foreach ($calInfo['week_days'] as $day)
					echo '
												<th class="titlebg2 days" scope="col" style="padding: 2px; margin: 0px; font-size: x-small;">', $day%2 ? $smcFunc['substr']($txt['days_short'][$day], 0, 1) : $smcFunc['substr']($txt['days_short'][$day], 0, 2), '</th>';

				echo '
											</tr>';

				// List the weeks.
				foreach ($calInfo['weeks'] as $week_key => $week)
				{
					echo '
											<tr>';

					foreach ($week['days'] as $day_key => $day)
					{
						// Filling up the $calEvents for later use.
						$calEvents .= ep_calendarEvents($day, $calInfo['current_month'], $calInfo['current_year'], $curCalInfo);

						if (empty($day['day']))
						{
							unset($calInfo['weeks'][$week_key]['days'][$day_key]);
							echo '
												<td class="windowbg days">';
						}
						else
						{
							$has_info = !empty($day['holidays']) || !empty($day['birthdays']) || !empty($day['events']);

							echo '
												<td', (!empty($day['day']) && !$day['is_today'] ? ' onmouseover="ep_highlight_' . $unique_id . '(this, \'over\');" onmouseout="ep_highlight_' . $unique_id . '(this, \'out\');"' : ''), ' onclick="return ep_collapse_' . $unique_id . '(\'', ($has_info ? strftime('%Y-%m-%d', mktime(0, 0, 0, $calInfo['current_month'], $day['day'], $calInfo['current_year'])) : '0-0-0'), '\');" class="', ($day['is_today'] ? 'calendar_today ' : 'windowbg '), 'days hand" style="height: 20px; font-size: x-small;', ($has_info ? 'font-weight: bold;' : ''), '">', $day['day'];
						}

						echo '
												</td>';
					}

					echo '
											</tr>';
				}

				echo '
										</table>
									</div>';
				if (!empty($animate))
						echo '
								</li>';
				else
						echo '
								</div>';

				$month_var[$month] = $month;
			}

			$year_var[$year] = $year;
		}

		if (!empty($animate))
			echo '
					</ul>
				</div>';

		echo '
							</td></tr><tr>
							<td align="center" valign="middle">
							<hr style="width: 100%; float: left;" />';

		// Outputting the Events/Birthdays/Holidays if set and any found.
		echo $calEvents;
		echo '
							<div class="smalltext" id="', $unique_id, '_0-0-0" style="display: none; text-align: center;">', $txt['ep_nocal_found'], '</div>
						</td>
					</tr>
				</table>';
	}
	// All done, if something wrong, throw an error!
	else
		module_error();
}

function module_topPosters($params)
{
	global $context, $txt, $scripturl, $smcFunc, $modSettings;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		$show_avatar = !isset($params['show_avatar']) ? true : $params['show_avatar'];
		$show_postcount = !isset($params['show_postcount']) ? true : $params['show_postcount'];
		$num_posters = !isset($params['num_posters']) ? 10 : $params['num_posters'];

		if (empty($num_posters))
		{
			module_error('empty');
			return;
		}

		// The query.
		$request = $smcFunc['db_query']('', '
			SELECT mem.id_member, mem.real_name, mem.posts, mem.avatar, mg.online_color,
				IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			WHERE mem.posts > {int:no_posts}
			ORDER BY mem.posts DESC
			LIMIT {int:num_posters}',
			array(
				'no_posts' => 0,
				'num_posters' => $params['num_posters'],
			)
		);

		// Setup an array for the members.
		$posters = array();

		// Insert the info into the array.
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$posters[] = array(
				'id' => $row['id_member'],
				'member' => array(
					'name' => $row['real_name'],
					'color_link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '"><span style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</span></a>',
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
				),
				'avatar' => array(
					'name' => $row['avatar'],
					'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="ep_recent_avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img class="ep_recent_avatar" src="' . $row['avatar'] . '" alt="" border="0" />' : '<img class="ep_recent_avatar" src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" border="0" />'),
					'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
				),
				'num_posts' => comma_format($row['posts']),
			);
		}

		// And now! The Top $params['num_posters'] posters!
		$i = 1;
		foreach ($posters as $poster)
		{
			echo '
								', ($show_avatar && !empty($poster['avatar']['image']) ? '
								<div class="ep_recent_icon">
									' . $poster['avatar']['image'] . '
								</div>' : ''), '
								<div class="ep_recent">
									<div class="smalltext">', (!empty($modSettings['ep_color_members']) ? $poster['member']['color_link'] : $poster['member']['link']), '</div>
									', ($show_postcount ? $poster['num_posts'] . ' ' . $txt['posts'] : ''), '
								</div>
								<div style="clear: both;"></div>';

			if ($i < $num_posters)
				echo '
								<br class="clear" />';

			$i++;
		}
	}
	// Throw an error.
	else
		module_error();
}

function module_staff($params)
{
	global $sourcedir, $user_profile, $smcFunc, $modSettings, $txt, $scripturl, $memberContext;

	// Grab the params, if they exist.
	if (is_array($params))
	{
		$groups = !isset($params['groups']) ? array(1, 2) : explode(',', $params['groups']);
		$list_type = !isset($params['list_type']) ? 1 : (int) $params['list_type'];
		$name_type = !isset($params['name_type']) ? 0 : (int) $params['name_type'];

		// Just to make it quicker instead of using in_array();
		$groups = array_combine($groups, $groups);

		// No Guests and/or Regular Members please.
		if (!empty($groups) && !isset($groups['-1'], $groups[0]) && $params['groups'] != '')
		{
			// How many groups?
			$num_groups = count($groups);

			// Any group associations?
			if ($num_groups <= 1 && isset($groups['-2']))
			{
				module_error('empty');
				return;
			}

			$query = $smcFunc['db_query']('', '
				SELECT mem.id_member, mg.id_group, mg.group_name, mg.online_color
				FROM {db_prefix}members AS mem, {db_prefix}membergroups AS mg
				WHERE mem.id_group >= 1 AND mem.id_group IN ({array_int:groups}) AND mem.id_group = mg.id_group',
				array(
					'groups' => $groups,
				)
			);

			$groupinfo['groups'] = array();
			$all_members = array();
			$all_groups = array();
			while ($row = $smcFunc['db_fetch_assoc']($query))
			{
				if (!empty($row['id_member']))
				{
					if (!isset($groupinfo['groups'][$row['id_group']]))
						$groupinfo['groups'][$row['id_group']] = array(
							'name' => $row['group_name'],
							'color' => $row['online_color'],
							'members' => array(),
						);

					$groupinfo['groups'][$row['id_group']]['members'][] = $row['id_member'];
					$all_groups[] = $row['id_group'];
					$all_members[] = $row['id_member'];
				}
			}

			// A bit of checking here on what to load.
			if (!empty($all_members) && $list_type == 0 && empty($name_type))
				loadMemberData($all_members, false, 'minimal');
			elseif (!empty($all_members))
				loadMemberData($all_members);
			else
			{
				module_error('empty');
				return;
			}

			// Does this group have any members?
			$empty_groups = array();
			$empty_groups = array_diff($groups, $all_groups);

			// Reset groups in case there are no members within that group
			$groups = array_diff($groups, $empty_groups);
			$name = empty($list_type) && empty($name_type) ? 'real_name' : 'name';

			foreach ($groups as $key => $id_group)
			{
				$groupinfo['groups'][$id_group]['memberinfo'] = array();

				foreach ($groupinfo['groups'][$id_group]['members'] as $member)
				{
					if (empty($member))
						continue;

					// Which array to use?  Choices... Choices...
					if ($list_type == 0 && empty($name_type))
						$groupinfo['groups'][$id_group]['memberinfo'] = $user_profile[$member];
					else
					{
						loadMemberContext($member, true);
						$groupinfo['groups'][$id_group]['memberinfo'] = $memberContext[$member];
					}

					// A few common variables here :)
					$href = $scripturl . '?action=profile;u=' . $member;
					$color_link = '<a href="' . $href . '">' . (!empty($groupinfo['groups'][$id_group]['color']) && !empty($modSettings['ep_color_members']) ? '<span style="color: ' . $groupinfo['groups'][$id_group]['color'] . ';">' . $groupinfo['groups'][$id_group]['memberinfo'][$name] . '</span>' : $groupinfo['groups'][$id_group]['memberinfo'][$name]) . '</a>';

					// Staff Title, a bit of checking here.
					$staff_title = empty($name_type) ? (!empty($groupinfo['groups'][$id_group]['name']) ? $groupinfo['groups'][$id_group]['name'] : '') : ($name_type == 1 ? (!empty($groupinfo['groups'][$id_group]['memberinfo']['title']) ? $groupinfo['groups'][$id_group]['memberinfo']['title'] : '') : ($name_type == 2 ? (!empty($groupinfo['groups'][$id_group]['memberinfo']['title']) ? $groupinfo['groups'][$id_group]['memberinfo']['title'] : (!empty($groupinfo['groups'][$id_group]['name']) ? $groupinfo['groups'][$id_group]['name'] : '')) : ''));

					// Names Only.
					if ($list_type == 0)
					{
						echo '
							<div class="ep_staff">
									<div class="smalltext">', $color_link, '
										', (!empty($staff_title) ? '<br />' . $staff_title : ''), '</div>
								</div>';
					}
					// Avatars Only.
					elseif ($list_type == 1)
					{
						if (!empty($groupinfo['groups'][$id_group]['memberinfo']['avatar']['href']))
						{
							echo '
								<a href="' . $href . '"><img src="' . $groupinfo['groups'][$id_group]['memberinfo']['avatar']['href'] . '" class="ep_staff_avatar" alt="" title="' . (!empty($staff_title) ? $staff_title . ' - ' : '') . $groupinfo['groups'][$id_group]['memberinfo']['name'] . '" /></a>';
						}
					}
					// Both Names and Avatars.
					else
					{
						if (!empty($groupinfo['groups'][$id_group]['memberinfo']['avatar']['href']))
						{
							echo '
								<div style="float: left;">
									<a href="' . $href . '"><img src="' . $groupinfo['groups'][$id_group]['memberinfo']['avatar']['href'] . '" class="ep_staff_avatar" alt="" title="' . $groupinfo['groups'][$id_group]['memberinfo'][$name] . '" /></a>
								</div>';
						}

						// Need to create a space if no Staff Title, for appearance purposes only.
						echo '
								<div class="ep_staff">
									<div class="smalltext">', $color_link, '<br />
										', (!empty($staff_title) ? $staff_title : '&nbsp;'), '</div>
								</div>';

						echo '
							<br class="clear" />';
					}
				}

				if ($list_type == 0)
				{
					if ($key < (count($groups) - 1))
						echo '
							<p class="ep_dashed"></p>';
				}
			}
		}
		// Throw an error.
		else
			module_error();
	}
	// Throw an error.
	else
		module_error();
}

function envision_error_handler($output)
{
	$error = error_get_last();
	$output = "";
	if (!empty($error))
		foreach ($error as $info => $string)
			if ($info == 'message')
				$output .= $string;

	return $output;
}

function module_custom($params)
{
	global $context;

	// !!!
	if (is_array($params))
	{
		$type = (!empty($params['code_type']) ? (int) $params['code_type'] : 0);
		$content = (!empty($params['code']) ? $params['code'] : '');

		if (empty($content))
		{
			module_error('empty');
			return;
		}

		// BBC
		if ($type == 2)
		{
			$content = parse_bbc(strip_tags($content));
			echo trim($content);
		}
		// HTML
		elseif ($type == 1)
		{
			$content = html_entity_decode($content, ENT_QUOTES);
			echo trim($content);
		}
		// PHP...
		elseif ($type == 0)
		{
			$content = trim(html_entity_decode($content, ENT_QUOTES));
			$content = trim($content, '<?php');
			$content = trim($content, '?>');

			// Syntax errors?
			if (!@eval('return true;' . $content))
				module_error();
			else
			{
				ob_start('envision_error_handler');
				eval($content);
				$code = ob_get_contents();
				ob_end_clean();

				echo $code;
			}
		}
	}
	else
		module_error();
}

function module_theme_selector()
{
	global $context, $smcFunc, $txt, $scripturl, $boardurl, $modSettings, $user_info, $settings;

	// We need our themes language here.
	loadLanguage('ep_languages/Themes');

	// What theme are we currently using?
	$cur_theme = isset($_GET['theme']) ? $_GET['theme'] : $user_info['theme'];
	$cur_theme = empty($cur_theme) ? -1 : $cur_theme;

	if (empty($modSettings['knownThemes']))
		return;

	// Load the themes up.
	$request = $smcFunc['db_query']('', '
		SELECT id_theme, variable, value
		FROM {db_prefix}themes
		WHERE variable IN ({string:name}, {string:theme_dir}, {string:theme_url}, {string:images_url})
			AND id_theme IN ({array_string:known_themes})
			AND id_member = {int:no_member}',
		array(
			'no_member' => 0,
			'name' => 'name',
			'theme_dir' => 'theme_dir',
			'theme_url' => 'theme_url',
			'images_url' => 'images_url',
			'known_themes' => explode(',', $modSettings['knownThemes']),
		)
	);
	// Themes are going into this array.
	$context['ep_themes'] = array();

	// Put the themes into the array.
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($context['ep_themes'][$row['id_theme']]))
			$context['ep_themes'][$row['id_theme']] = array(
				'id' => $row['id_theme'],
				'selected' => $cur_theme == $row['id_theme'],
			);
		$context['ep_themes'][$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smcFunc['db_free_result']($request);

	if (!isset($context['ep_themes'][$modSettings['theme_guests']]))
	{
		$context['ep_themes'][0] = array(
			'num_users' => 0
		);
		$guest_theme = 0;
	}
	else
		$guest_theme = $modSettings['theme_guests'];

	if ($guest_theme != 0)
		$context['ep_themes'][-1] = $context['ep_themes'][$guest_theme];

	// If we're using the default theme, set it here to prevent undefined errors.
	$context['ep_themes'][-1]['id'] = -1;
	$context['ep_themes'][-1]['name'] = $txt['theme_forum_default'];
	$context['ep_themes'][-1]['images_url'] = $settings['default_images_url'];
	$context['ep_themes'][-1]['selected'] = $cur_theme == 0;

	ksort($context['ep_themes']);

	// Now check if they just changed their theme.
	if (!empty($_POST['ep_submit_change']) && !empty($_POST['ep_theme']))
		ep_changeTheme($user_info['id'], array('id_theme' => (int) $_POST['ep_theme']));

	// Start the form.
	echo '
							<div style="text-align: center;">
								<form action="" method="post">
									<p><img src="', $context['ep_themes'][$cur_theme]['theme_url'], '/images/thumbnail.gif" alt="" id="ep_theme_preview" /></p>
									<p class="smalltext">
										<select name="ep_theme" onchange="ep_change_theme(this)">';

	// List the themes.
	foreach ($context['ep_themes'] as $theme)
	{
		if ($smcFunc['strlen']($theme['name']) > 18)
			$theme['name'] = $smcFunc['substr']($theme['name'], 0, 18) . '...';

		echo '
											<option value="', $theme['id'], '"', $theme['selected'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';
	}

	echo '
										</select>
									</p>
									<p><input type="submit" name="ep_submit_change" value="', $txt['ep_update'], '" class="button_submit" /></p>
								</form>
							</div>';

	// Some javascript.
	echo '
							<script type="text/javascript"><!-- // --><![CDATA[
								ep_th_thumbs = new Array();';

	// Get the thumbnails.
	foreach ($context['ep_themes'] as $theme)
		echo '
								ep_th_thumbs[', $theme['id'], '] = \'', $theme['theme_url'] . '/images/thumbnail.gif\';';

	echo '
								function ep_change_theme(obj)
								{
									var id = obj.options[obj.selectedIndex].value;
									document.getElementById(\'ep_theme_preview\').src = ep_th_thumbs[id];
								}
							// ]]></script>';
}

function module_new_members($params)
{
	global $context, $scripturl, $txt, $smcFunc, $memberContext, $user_info;

	if (is_array($params))
	{
		$limit = (!empty($params['limit']) ? (int) $params['limit'] : 3);
		$list_type = !isset($params['list_type']) ? 1 : (int) $params['list_type'];

		$request = $smcFunc['db_query']('','
			SELECT id_member, real_name, date_registered
			FROM {db_prefix}members
			' . (!$user_info['is_admin'] ? 'WHERE is_activated = 1' : '') . '
			ORDER BY id_member DESC
			LIMIT {int:limit}',
			array(
				'limit' => $limit,
			)
		);

		$i = 0;

		// Names only.
		if ($list_type == 0)
		{
			echo '
				<ul class="ep_list">';

			$members = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$members[] = array(
					'id' => $row['id_member'],
					'name' => $row['real_name'],
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					'date' => timeformat($row['date_registered'], '%B %e, %Y')
				);
			}
			$smcFunc['db_free_result']($request);

			foreach ($members as $member)
			{
				// Increment the counter.
				$i++;

				// And add the member to the list.
				echo '
					<li class="' . ($i == 1 ? 'ep_list_above' : '') . '">&bull;&nbsp;', $member['link'], '<br /><span class="smalltext">' . $member['date'] . '</span></li>';
			}

			echo '
				</ul>';
		}
		// Avatars only.
		elseif ($list_type == 1)
		{
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$all_members[] = $row['id_member'];

			if (!empty($all_members))
				loadMemberData($all_members);

			$members = array();
			foreach ($all_members as $member)
			{
				loadMemberContext($member, true);
				$members[$member] = $memberContext[$member];
			}

			foreach ($members as $member)
			{
				if (!empty($member['avatar']['href']))
					echo '
					<a href="' . $member['href'] . '"><img src="' . $member['avatar']['href'] . '" class="ep_newmem_avatar" alt="" title="' . $member['name'] . '" /></a>';
			}
		}
		// Both names and avatars
		elseif ($list_type == 2)
		{
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$all_members[] = $row['id_member'];

			if (!empty($all_members))
				loadMemberData($all_members);

			$members = array();
			foreach ($all_members as $member)
			{
				loadMemberContext($member, true);
				$members[$member] = $memberContext[$member];
			}

			foreach ($members as $member)
			{
				if (!empty($member['avatar']['href']))
				{
					echo '
						<div style="float: left;">
							<a href="' . $member['href'] . '"><img src="' . $member['avatar']['href'] . '" class="ep_newmem_avatar" alt="" title="' . $member['name'] . '" /></a>
						</div>';
				}

				echo '
						<div class="ep_newmem">
							<div class="smalltext">
								', $member['link'], '<br />
								', timeformat($member['registered_timestamp'], '%B %e, %Y'), '
							</div>
						</div>';

						echo '
							<br class="clear" />';
			}
		}
	}
	else
		module_error();
}

function module_sitemenu($params)
{
	global $context, $scripturl, $settings, $txt;

	if (is_array($params))
	{
		if (empty($context['menu_buttons']))
			setupMenuContext();

		echo '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/epSiteMenu.js"></script>';

		echo '<div id="ep_sitemenu">
			<div style="float: right;"><img src="', $context['epmod_image_url'], 'sitemenu/expandall.png" width="15" height="15" border="0" style="cursor: pointer;" alt="', $txt['ep_site_menu_alt_xall'], '" title="', $txt['ep_site_menu_title_xall'], '" onclick="siteMenu.expandAll();" />&nbsp;<img src="', $context['epmod_image_url'], 'sitemenu/collapseall.png" border="0" style="cursor: pointer;" width="15" height="15" alt="', $txt['ep_site_menu_alt_call'], '" title="', $txt['ep_site_menu_title_call'], '" onclick="siteMenu.collapseAll();" /></div>
			<div style="float: left;" id="ep_menu" class="epsitemenu">';

		foreach ($context['menu_buttons'] as $act => $button)
		{
			echo '
				<div>
        				<span class="', (empty($button['sub_buttons']) ? 'no_subs' : 'subs'), '"><a class="ep_menuheader" href="', $button['href'], '">', $button['title'], '</a></span>';

				if (!empty($button['sub_buttons']))
					foreach ($button['sub_buttons'] as $sub_button)
						echo '
						<a class="sm" href="', $sub_button['href'], '">', $sub_button['title'], '</a>';

			echo '
				</div>';
		}

		echo '
			</div></div>';

		// A dummy span for the param.
		if (!empty($params['onesm']))
			echo '
			<span id="epAllowAll" style="display: none;"></span>';

		echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				createEventListener(window);
				window.addEventListener("load", loadSiteMenu, false);
			// ]]></script>';
	}
	else
		module_error();
}

function module_shoutbox($params)
{
	global $context, $txt, $settings, $user_info;

	if (is_array($params))
	{
		// Grab this MODULE/CLONES id value, which will have the MOD NAME + '_' + TYPE (mod/clone) + '_' + the id_value
		$unique_id = $context['ep_mod_shoutbox'][0];

		// Remove this value so that it doesn't get used again!
		unset($context['ep_mod_shoutbox'][0]);

		// Reset the array keys from 0 to count($context['ep_mod_shoutbox']) - 1
		$context['ep_mod_shoutbox'] = array_values($context['ep_mod_shoutbox']);

		$refresh_rate = !isset($params['refresh_rate']) ? 5000 : ($params['refresh_rate'] < 1 ? 500 : $params['refresh_rate'] * 1000);
		$member_color = !isset($params['member_color']) ? 1 : (int) $params['member_color'];
		$is_message_visible = true;
		$shoutbox_id = !isset($params['id']) ? 1 : (int) $params['id'];
		$max_count = !isset($params['max_count']) ? 15 : (int) $params['max_count'];
		$max_chars = !isset($params['max_chars']) ? 128 : (int) $params['max_chars'];
		$allowed_bbc = !isset($params['bbc']) ? '' : str_replace(';', '|', $params['bbc']);
		$text_size = !isset($params['text_size']) ? 1 : (int) $params['text_size'];
		$parse_bbc = !empty($params['parse_bbc']) ? 1 : 0;
		$message_groups = !isset($params['message_groups']) ? array('-3') : explode(',', $params['message_groups']);
		$message_position = !isset($params['message_position']) ? 'above' : $params['message_position'];
		$message = !isset($params['message']) ? '' : parse_bbc($params['message']);

		// -3 is for everybody...
		if (in_array('-3', $message_groups))
			$message_groups = $user_info['groups'];

		// Match the current group(s) with the parameter to determine if they can view the notice
		$message_groups = array_intersect($user_info['groups'], $message_groups);

		// Shucks, you can't view it
		if (empty($message_groups))
			$is_message_visible = false;

		if (empty($context['shoutbox_loaded']))
			$context['shoutbox_loaded'] = true;

		// On with the show!
		if ($is_message_visible && $message_position == 'top')
			echo '
			<div id="shoutbox_floating_message_', $message_position, '">', $message, '</div>';

		echo '
			<div class="ep_Reserved_Vars_Shoutbox" id="reserved_vars', $unique_id, '" style="display: none;"></div>
			<!--// This div below holds the actual shouts //-->
			<div class="shoutbox_content" id="shoutbox_area', $unique_id, '"';

		if ($context['browser']['is_ie7'] || $context['browser']['is_ie8'])
			echo '
			style="word-wrap: break-word; width: 160px;"';

		echo '
			></div>

			<!--// This div below is just a spacer for the bottom //-->
			<div style="padding-bottom: 9px;"></div>';

		if ($is_message_visible && $message_position == 'after')
			echo '
			<div id="shoutbox_floating_message_', $message_position, '">', $message, '</div>';

		if (!$user_info['is_guest'])
		{
			LoadSmilies();

			echo  '
			<form name="post_shoutbox', $unique_id, '" id="post_shoutbox', $unique_id, '" method="post" action="" accept-charset="', $context['character_set'], '">
				<input name="ep_Reserved_Message" id="shout_input', $unique_id, '" maxlength="', $max_chars, '" type="text" value="" style="width: 90%;" tabindex="', $context['tabindex']++, '" />
				<br class="clear" /><div style="padding-bottom: 3px;"></div>
				<input name="shout_submit" value="', $txt['shoutbox_shout'], '" class="button_submit" type="submit" tabindex="', $context['tabindex']++, '" />
					<img src="', $context['epmod_image_url'], 'shoutbox/emoticon_smile.png" alt="" title="', $txt['shoutbox_emoticons'], '" class="hand" id="toggle_smileys_div', $unique_id, '" />
					<img src="', $context['epmod_image_url'], 'shoutbox/font.png" alt="" title="', $txt['shoutbox_fonts'], '" class="hand" id="toggle_font_styles_div', $unique_id, '" />
					<img src="', $context['epmod_image_url'], 'shoutbox/clock.png" alt="" title="', $txt['shoutbox_history'], '" class="hand" id="toggle_history_div', $unique_id, '" />
					<div class="shout_smileys" id="shout_smileys', $unique_id, '">';

			if (empty($context['smileys']))
				// No smileys!? Get your forum fixed, dude!
				echo '';
			else
			{
				foreach ($context['smileys']['postform'] as $row => $rowData)
				{
					echo '
							<ul>';

					foreach ($rowData['smileys'] as $smileyID => $smiley)
					{
						echo '
								<li class="shout_smiley_img shout_smiley_img', $unique_id, '"><img src="', $settings['smileys_url'] . '/' . $smiley['filename'], '" alt="', $smiley['description'], '" title="', $smiley['description'], '" onclick="insertCode(\'', addslashes($smiley['code']), '\', \'replace\', \'', $unique_id, '\', \'smileys\')" class="smiley_item smiley_item', $unique_id, '" /></li>';
					}
					echo '
							</ul>
							<div class="clear"></div>';
				}
			}

			echo '
					</div>
					<div class="shout_font_styles" id="shout_font_styles', $unique_id, '">';

			// Now process the bbc array...
			LoadShoutBBC();

			if (empty($context['ep_bbc_tags']))
				// What, did someone delete our array?
				echo '';
			else
			{
				foreach ($context['ep_bbc_tags'] as $row => $rowData)
				{
					echo '
							<ul>';

					foreach ($rowData as $bbcID => $tag)
					{
						echo '
								<li class="shout_font_style_img"><img src="', $settings['images_url'] . '/bbc/' . $tag['image'] . '.gif', '" alt="', $tag['description'], '" title="', $tag['description'], '" onclick="insertCode(\'', addslashes($tag['code']), '\', \'surround\', \'', $unique_id, '\', \'font_styles\')" class="font_style_item font_style_item', $unique_id, '" /></li>';
					}
					echo '
							</ul>
							<div class="clear"></div>';
				}
			}

			echo '
					</div>
			</form>';

		}

		if (!empty($context['shoutbox_loaded']))
			echo '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/epShoutbox.js"></script>
		<script type="text/javascript">
			createEventListener(window);
			window.addEventListener("load", loadShouts, false);
			var sessVar = "' . $context['session_var'] . '";
			var sessId = "' . $context['session_id'] . '";
			var theDiv = document.getElementById("reserved_vars' . $unique_id . '");
			theDiv.setAttribute("membercolor", ', $member_color, ');
			theDiv.setAttribute("shoutboxid", ', $shoutbox_id, ');
			theDiv.setAttribute("maxcount", ', $max_count, ');
			theDiv.setAttribute("maxchars", ', $max_chars, ');
			theDiv.setAttribute("textsize", ', $text_size, ');
			theDiv.setAttribute("parsebbc", ', $parse_bbc, ');
			theDiv.setAttribute("allowedbbc", \'', addslashes($allowed_bbc), '\');
			theDiv.setAttribute("lastshout", 0);
			theDiv.setAttribute("moduleid", "', $unique_id, '");
			theDiv.setAttribute("refreshrate", ', $refresh_rate, ');';

		if ($user_info['is_logged'] && !empty($context['shoutbox_loaded']))
			echo '
			document.getElementById("post_shoutbox', $unique_id, '").setAttribute("moduleid", "', $unique_id, '");';

		if (!empty($context['shoutbox_loaded']))
			echo '
		</script>';

		if (empty($context['shoutbox_loaded']))
			$context['shoutbox_loaded'] = true;

		if ($is_message_visible && $message_position == 'bottom')
			echo '
			<div id="shoutbox_floating_message_', $message_position, '">', $message, '</div>';
	}
	else
		module_error();
}

?>