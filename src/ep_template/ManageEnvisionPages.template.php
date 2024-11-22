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
		<form action="', $scripturl, '?action=admin;area=eppages;sa=savepage" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify">
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
						<b>', $txt['envision_pages_page_name'], ':</b>
						<input type="text" name="name" value="', $context['data']['name'], '" style="width: 98%;" />
						<b>', $txt['envision_pages_page_slug'], ':</b>
						<span>
							<input type="text" name="slug" value="', $context['data']['slug'], '" style="width: 98%;" />
							<span class="smalltext">', $txt['envision_pages_page_slug_desc'], '</span>
						</span>
						<b>', $txt['envision_pages_page_description'], ':</b>
						<span>
							<input type="text" name="description" value="', $context['data']['description'], '" style="width: 98%;" />
						</span>
						<b>', $txt['envision_pages_page_type'], ':</b>
						<span>';

	foreach ($context['data']['types'] as [$cn, $type, $obj]) {
		echo '
							<label>
								<input type="radio" name="type" data-mode="', $obj->getMode(
		), '" value="', $cn, '"', $sel(
			$context['data']['type'] == $cn,
			' checked'
		), '/>', $txt['envision_pages_' . $type], '
							</label>';
	}

	echo '
						</span>
						<b>', $txt['envision_pages_page_perms'], ':</b>
						<span>
							<fieldset class="group_perms">
								<legend> ', $txt['avatar_select_permission'], '</legend>';

	foreach ($context['data']['permissions'] as $id => $permission) {
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
						<b>', $txt['envision_pages_page_status'], ':</b>
						<span>
							<input type="radio" name="status" value="active"', $sel(
		$context['data']['status'] == 'active',
		'checked'
	), ' />', $txt['envision_pages_page_active'], ' 
							<input type="radio" name="status" value="inactive"', $sel(
		$context['data']['status'] == 'inactive',
		'checked'
	), ' />', $txt['envision_pages_page_inactive'], '
						</span>
					</div>';
}

function template_form_below(): void
{
	global $context, $txt;

	echo '
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>
<br>
<textarea name="body" style="width: 98%; height: 30em;" rows="30">', htmlspecialchars($context['data']['body']), '</textarea>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input name="in" value="', $context['data']['id'], '" type="hidden" />
					<div class="righttext padding">
						<input name="submit" value="', $txt['admin_manage_menu_submit'], '" class="button' . (defined('SMF_VERSION') ? '' : '_submit') . ' button" type="submit" />
					</div>
				</div>
			<span class="lowerframe"><span></span></span>
			</form>
			<script>
				const f = document.forms.postmodify;
				initGroupToggle(f);
				makeChecks(f);
				let type = \'text\';
				for (let o of f.type)
					if (o.checked)
						type = o.dataset.mode;
				c.initAceEdior(f, f.body, type);
			</script>';
}
