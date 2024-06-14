<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\ModuleTrait;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class UserCP implements ModuleInterface
{
	use ModuleTrait;

	private $fields;

	public function __invoke(array $fields)
	{
		$this->fields = $fields;
	}

	private function getUrl()
	{
		global $scripturl;

		$cur_url = $_SERVER['REQUEST_URL'];

		return $cur_url;
	}

	function __toString()
	{
		global $context, $txt, $scripturl, $settings, $user_info;

		if (!$user_info['is_guest']) {
			$buttons = [
				'unread' => ['text' => 'ep_user_posts', 'lang' => true, 'url' => $scripturl . '?action=unread'],
				'replies' => [
					'text' => 'ep_user_replies',
					'lang' => true,
					'url' => $scripturl . '?action=unreadreplies',
				],
				'inbox' => ['text' => 'ep_inbox', 'lang' => true, 'url' => $scripturl . '?action=pm'],
				'outbox' => ['text' => 'ep_outbox', 'lang' => true, 'url' => $scripturl . '?action=pm;f=sent'],
				'profile' => ['text' => 'profile', 'lang' => true, 'url' => $scripturl . '?action=profile'],
				'logout' => [
					'text' => 'logout',
					'lang' => true,
					'url' => $scripturl . '?action=logout;' . $context['session_var'] . '=' . $context['session_id'],
				],
			];

			call_integration_hook('integrate_ep_usercp', [$buttons]);

			$_SESSION['logout_url'] = $this->getUrl();

			// What does the user want the time formatted as?
			$s = strpos($user_info['time_format'], '%S') === false ? '' : ':%S';
			if (preg_match('/%[[HT]/', $user_info['time_format']) === 0) {
				$h = strpos($user_info['time_format'], '%l') === false ? '%I' : '%l';
				$time_fmt = $h . ':%M' . $s . ' %p';
			} else {
				$time_fmt = '%H:%M' . $s;
			}

			$ret = '
					<h4>' . $txt['ep_hello'] . ', ' . $user_info['name'] . '</h4>';

			if (!empty($user_info['avatar']['image'])) {
				$ret .= '
					<div style="padding: 1ex">
						<a href="' . $scripturl . '?action=profile">' . $user_info['avatar']['image'] . '</a>
					</div>';
			}

			$ret .= '
					<ul>
						<li>' . $txt['total_posts'] . ': ' . $user_info['posts'] . '</li>
						<li>' . timeformat(time(), '%a, ' . $time_fmt) . '</li>
					</ul>
					' . $this->captureOutput('template_button_strip', $buttons, '');
		} else {
			$_SESSION['login_url'] = $this->getUrl();

			$ret = '
				<h4>' . $txt['hello_guest'] . ' ' . $txt['guest'] . '</h4>
				<form action="' . $scripturl . '?action=login2" method="post">
					<label for="user">' . $txt['ep_login_user'] . ':</label>
					<input type="text" name="user" id="user" size="10" />
					<label for="passwrd">' . $txt['password'] . ':</label>
					<input type="password" name="passwrd" id="passwrd" size="10" />
					<label for="cookielength">' . $txt['ep_length'] . '</label>
					<select name="cookielength" id="cookielength">
						<option value="60">' . $txt['one_hour'] . '</option>
						<option value="1440">' . $txt['one_day'] . '</option>
						<option value="10080">' . $txt['one_week'] . '</option>
						<option value="302400">' . $txt['one_month'] . '</option>
						<option value="-1" selected="selected">' . $txt['forever'] . '</option>
					</select>
					<div class="centertext"><input type="submit" value="' . $txt['login'] . '" class="' . (defined('SMF_VERSION') ? 'button' :  'button_submit') . '" /></div>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />';

			if (defined('SMF_VERSION')) {
				$ret .= '
					<input type="hidden" name="' . $context['login_token_var'] . '" value="' . $context['login_token'] . '">';
			}

			$ret .= '
				</form>
				' . $txt['welcome_guest_activate'] . '';
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'user',
			],
			'module_link' => [
				'value' => 'action=profile',
			],
		];
	}
}