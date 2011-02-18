<?php
// Version 1.0; ManageEnvisionPages

/**
 * This file handles the visuals of Envision Page management.
 *
 * @package template
 * @since 1.0
*/

/**
 * Main template for ading a page.
 *
 * @since 1.0
 */

function template_main()
{
	global $context, $scripturl, $boardurl, $txt, $smcFunc, $settings;

	// Let's begin our AJAX code, followed by all the content.
	echo '
	<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript">

		$(document).ready(function() {
			$("#pnbox").keyup(function() {
				var page_name = $(this).val();
				$("#pn").html(ajax_notification_text);
				$.ajax({
					type: "GET",
					url: "', $boardurl, '/ep_ajax.php?check=true;pn=" + page_name + ";id=', $context['page_data']['id'], '",
					success: function(data) {
						$("#pn").html(data);
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						$("#pn").html(textStatus);
					}
				});
			});
		});
	</script>
		<form action="', $scripturl, '?action=admin;area=eppages;sa=epsavepage" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" class="flow_hidden">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<span class="upperframe"><span></span></span>
				<div class="roundframe">';

	// If an error occurred, explain what happened.
	if (!empty($context['post_error']))
	{
		echo '
					<div class="errorbox" id="errors">
						<strong>', $txt[$context['error_title']], '</strong>
						<ul>';

		foreach ($context['post_error'] as $error)
			echo '
							<li>', $txt[$error], '</li';

		echo '
						</ul>
					</div>';
	}

	echo '
					<dl id="post_header">
						<dt>
							', $txt['ep_envision_pages_page_name'], ':
						</dt>
						<dd>
							<input type="text" name="page_name" id="pnbox" value="', $context['page_data']['page_name'], '" tabindex="1" class="input_text" style="width: 100%;" />
							<div id="pn"></div>
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_type'], ':
						</dt>
						<dd>
							<input type="radio" class="input_check" name="type" value="0"', $context['page_data']['type'] == 0 ? ' checked="checked"' : '', '/>', $txt['ep_envision_pages_page_php'], '<br />
							<input type="radio" class="input_check" name="type" value="1"', $context['page_data']['type'] == 1 ? ' checked="checked"' : '', '/>', $txt['ep_envision_pages_page_html'], '<br />
							<input type="radio" class="input_check" name="type" value="2"', $context['page_data']['type'] == 2 ? ' checked="checked"' : '', '/>', $txt['ep_envision_pages_page_bbc'], '
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_title'], ':
						</dt>
						<dd>
							<input type="text" name="title" value="', $context['page_data']['title'], '" tabindex="1" class="input_text" style="width: 100%;" />
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_perms'], ':
						</dt>
						<dd>
							<fieldset id="group_perms">
								<legend><a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'none\';document.getElementById(\'group_perms_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>';

	$all_checked = true;

	// List all the groups to configure permissions for.
	foreach ($context['page_data']['permissions'] as $permission)
	{
		echo '
								<div id="permissions_', $permission['id'], '">
									<label for="check_group', $permission['id'], '">
										<input type="checkbox" class="input_check" name="permissions[]" value="', $permission['id'], '" id="check_group', $permission['id'], '"', $permission['checked'] ? ' checked="checked"' : '', ' />
										<span', ($permission['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : ''), '>', $permission['name'], '</span>
									</label>
								</div>';

		if (!$permission['checked'])
			$all_checked = false;
	}

	echo '
								<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'permissions[]\');" id="check_group_all"', $all_checked ? ' checked="checked"' : '', ' />
								<label for="check_group_all"><em>', $txt['check_all'], '</em></label><br />
							</fieldset>
							<a href="javascript:void(0);" onclick="document.getElementById(\'group_perms\').style.display = \'block\'; document.getElementById(\'group_perms_groups_link\').style.display = \'none\'; return false;" id="group_perms_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
							<script type="text/javascript"><!-- // --><![CDATA[
								document.getElementById("group_perms").style.display = "none";
								document.getElementById("group_perms_groups_link").style.display = "";
							// ]]></script>
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_status'], ':
						</dt>
						<dd>
							<input type="radio" class="input_check" name="status" value="1"', $context['page_data']['status'] == 1 ? ' checked="checked"' : '', ' />', $txt['ep_envision_pages_page_active'], ' <br />
							<input type="radio" class="input_check" name="status" value="0"', $context['page_data']['status'] == 0 ? ' checked="checked"' : '', ' />', $txt['ep_envision_pages_page_nactive'], '
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_header'], ':
						</dt>
						<dd>
							', template_control_richedit($context['ep_header_content']), '
						</dd>
						<dt>
							', $txt['ep_envision_pages_page_body'], ':
						</dt>
						<dd>
							', template_control_richedit($context['page_content']), '
						</dd>
					</dl>
					<input name="pid" value="', $context['page_data']['id'], '" type="hidden" />
					<div class="righttext padding">
						<input name="submit" value="Submit" class="button_submit" type="submit" />
					</div>
				</div>
			</form>
			<span class="lowerframe"><span></span></span>
			<br class="clear" />';
}

?>
