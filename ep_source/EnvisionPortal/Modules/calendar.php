<?php

namespace EnvisionPortal\Modules;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class calendar implements ModuleInterface
{
	private $fields;
	private bool $daysaslink;
	private bool $can_post;

	public function __invoke(array $fields)
	{
		global $modSettings;

		$this->fields = $fields;
		$this->can_post = allowedTo('calendar_post');
		$this->daysaslink = !empty($modSettings['cal_daysaslink']);
	}

	public function __toString()
	{
		global $context, $scripturl, $smcFunc, $txt;

		$today = getdate(forum_time());

		$ret = '
				<div class="catbg"><a href="' . $scripturl . '?action=calendar;year=' . $today['year'] . ';month=' . $today['mon'] . '">' . $txt['months_titles'][$today['mon']] . ' ' . $today['year'] . '</a></div>';

		for ($i = 0; $i < 7; $i++) {
			$ret .= '
				<div class="titlebg">' . $smcFunc['substr']($txt['days'][$i], 0, 1) . '</div>';
		}

		$begin = date('N', mktime(0, 0, 0, $today['mon'], 1, $today['year']));
		for ($i = 0; $i < 42; $i++) {
			$dates[] = getdate(mktime(0, 0, 0, $today['mon'], $i - $begin + 1, $today['year']));
		}

		for ($i = 0; $i < 42; $i++) {
			$class = 'windowbg';
			if ($dates[$i]['mon'] != $today['mon'] || $dates[$i]['year'] != $today['year']) {
				$class = 'windowbg2';
			} elseif ($dates[$i]['yday'] == $today['yday'] && $dates[$i]['year'] == $today['year']) {
				$class = 'calendar_today';
			}

			$action = '';
			if ($this->daysaslink && $this->can_post) {
				$action = 'action=calendar;sa=post;' . $context['session_var'] . '=' . $context['session_id'];
			} elseif ($dates[$i]['wday'] == 0) {
				$action = 'action=calendar;viewweek';
			}

			if ($action != '') {
				$ret .= '
				<a class="' . $class . '" href="' . $scripturl . '?' . $action . ';month=' . $dates[$i]['mon'] . ';year=' . $dates[$i]['year'] . ';day=' . $dates[$i]['mday'] . '">' . $dates[$i]['mday'] . '</a>';
			} else {
				$ret .= '
				<span class="' . $class . '">' . $dates[$i]['mday'] . '</span>';
			}
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'calendar',
			],
			'display_options' => [
				'type' => 'checklist',
				'options' => ['month', 'events', 'holidays', 'birthdays'],
				'value' => '0,1,2',
				'order' => true,
			],
		];
	}
}
