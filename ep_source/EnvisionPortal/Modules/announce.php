<?php

namespace EnvisionPortal\Modules;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
class Announce implements ModuleInterface
{
	use ModuleTrait;

	private $fields;

	public function __invoke(array $fields)
	{
		$this->fields = $fields;
	}

	public function __toString()
	{
		if (!empty($this->fields['msg'])) {
			$ret = parse_bbc($this->fields['msg']);
		} else {
			$ret = $this->error('empty');
		}

		return $ret;
	}

	public function getDefaultProperties(): array
	{
		return [
			'module_icon' => [
				'value' => 'globe',
			],
			'msg' => [
				'type' => 'bbc',
				'value' => 'Welcome to Envision Portal!',
			],
		];
	}
}
