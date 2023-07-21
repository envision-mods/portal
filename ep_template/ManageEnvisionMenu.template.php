<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

function template_form_above(): void
{
	global $context, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=epmenu;sa=savebutton" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe noup">';
}

function template_errors_above(): void
{
	global $context, $txt;

	if (!empty($context['post_error'])) {
		echo '
					<div class="errorbox" id="errors">
						<b>', $txt[$context['error_title']], '</b>
						<ul>';

		foreach ($context['post_error'] as $error) {
			echo '
							<li>', $txt[$error], '</li>';
		}

		echo '
						</ul>
					</div>';
	}
}

function template_errors_below(): void
{
}

function template_main(): void
{
	global $context, $txt, $scripturl;

	$sel = fn(bool $x, string $str): string => $x ? ' ' . $str : '';

	echo '
					<div class="settings-grid">
						<b>', $txt['ep_menu_button_name'], ':</b>
						<span>
							<input type="text" name="name" value="', $context['button_data']['name'], '" style="width: 98%;" />
						</span>
						<b>', $txt['ep_menu_button_position'], ':</b>
						<span>
							<select name="position" size="10" style="width: 22%;">';

	foreach (['after', 'child_of', 'before'] as $v) {
		printf(
			'
								<option value="%s"%s>%s...</option>',
			$v,
			$sel($context['button_data']['position'] == $v, 'selected'),
			$txt['ep_menu_' . $v]
		);
	}

	echo '
							</select>
							<select name="parent" size="10" style="width: 75%;">';

	foreach ($context['button_names'] as $idx => $title) {
		printf(
			'
								<option value="%s"%s>%s</option>',
			$idx,
			$sel($context['button_data']['parent'] == $idx, 'selected'),
			str_repeat('&emsp;', $title[0] * 2) . $title[1]
		);
	}

	echo '
							</select>
						</span>
						<b>', $txt['ep_menu_button_type'], ':</b>
						<span>
							<input type="radio" name="type" value="forum"', $sel(
		$context['button_data']['type'] == 'forum',
		'checked'
	), '/>', $txt['ep_menu_forum'], '
							<input type="radio" name="type" value="external"', $sel(
		$context['button_data']['type'] == 'external',
		'checked'
	), '/>', $txt['ep_menu_external'], '
						</span>
						<b>', $txt['ep_menu_link_type'], ':</b>
						<span>
							<input type="radio" name="target" value="_self"', $sel(
		$context['button_data']['target'] == '_self',
		'checked'
	), '/>', $txt['ep_menu_same_window'], '
							<input type="radio" name="target" value="_blank"', $sel(
		$context['button_data']['target'] == '_blank',
		'checked'
	), '/>', $txt['ep_menu_new_tab'], '
						</span>
						<b>', $txt['ep_menu_button_link'], ':</b><br />
						<span>
							<input type="text" name="link" value="', $context['button_data']['link'], '" style="width:98%;" />
							<span class="smalltext">', $txt['ep_menu_button_link_desc'], '</span>
						</span>
						<b>', $txt['ep_menu_button_perms'], ':</b>
						<span>
							<fieldset class="group_perms">
								<legend> ', $txt['avatar_select_permission'], '</legend>';

	foreach ($context['button_data']['permissions'] as $id => $permission) {
		echo '
								<label>
									<input type="checkbox" name="permissions[]" value="', $id, '"', $sel(
			$permission['checked'],
			'checked'
		), ' />
									<span';

		if ($permission['is_post_group']) {
			echo ' title="' . $txt['mboards_groups_post_group'] . '"';
		}

		echo '>', $permission['name'], '</span>
								</label>
								<br>';
	}

	echo '
							</fieldset>
						</span>
						<b>', $txt['ep_menu_button_status'], ':</b>
						<span>
							<input type="radio" name="status" value="active"', $sel(
		$context['button_data']['status'] == 'active',
		'checked'
	), ' />', $txt['ep_menu_button_active'], ' 
							<input type="radio" name="status" value="inactive"', $sel(
		$context['button_data']['status'] == 'inactive',
		'checked'
	), ' />', $txt['ep_menu_button_inactive'], '
						</span>
					</div>';
}

function template_form_below(): void
{
	global $context, $txt;

	echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input name="in" value="', $context['button_data']['id'], '" type="hidden" />
					<div class="righttext padding">
						<input name="submit" value="', $txt['admin_manage_menu_submit'], '" class="button_submit button" type="submit" />
					</div>
				</div>
			<span class="lowerframe"><span></span></span>
			</form>
			<script>
				const f = document.forms.postmodify;
				//~ const i = new Listbox;
				//~ i.init(f.module_icon);
				const c = new EpManage;
				c.initGroupToggle(f);
				c.makeChecks(f);
			</script>';
}
