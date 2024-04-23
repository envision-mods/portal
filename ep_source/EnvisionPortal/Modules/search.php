<?php

namespace EnvisionPortal\Modules;

use EnvisionPortal\ModuleInterface;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Search implements ModuleInterface
{
	private $fields;

	public function __invoke(array $fields)
	{
		$this->fields = $fields;
	}

	public function __toString()
	{
		global $scripturl, $txt, $context;

		$ret = '
							<div class="centertext">
								<form action="' . $scripturl . '?action=search2" method="post" accept-charset="' . $context['character_set'] . '" name="searchform" id="searchform">
								<div class="centertext" style="margin-top: -5px;"><input name="search" size="18" maxlength="100"  type="text" class="input_text" /></div>

								<select name="searchtype" style="margin: 5px 5px 0 0;">
									<option value="1" selected="selected">' . $txt['ep_match_all_words'] . '</option>
									<option value="2">' . $txt['ep_match_any_words'] . '</option>
								</select><input style="margin-top: 5px;" name="submit" value="' . $txt['search'] . '" type="submit" class="button_submit" />
								</form>
							</div>';

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'magnifier',
			],
			'module_link' => [
				'value' => 'action=search',
			],
		];
	}
}