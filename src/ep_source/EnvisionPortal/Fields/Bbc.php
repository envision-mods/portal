<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal\Fields;

use EnvisionPortal\FieldInterface;

class Bbc implements FieldInterface
{
	private string $key;
	private string $type;

	public function __construct(array $field, string $key, string $type)
	{
		$this->key = $key;
		$this->type = $type;
		require_once $GLOBALS['sourcedir'] . '/Subs-Editor.php';
		$editorOptions = [
			'id' => $key,
			'value' => $field['value'],
			'required' => true,
			'height' => '150px',
			'width' => '100%',
		];
		create_control_richedit($editorOptions);
	}

	public function __toString(): string
	{
		global $context;

		$ret = '';

		if ($context['show_bbc']) {
			$ret .= '
					<div id="bbcBox_message"></div>';
		}
		if ($context['smileys']['postform'] != [] || $context['smileys']['popup'] != []) {
			$ret .= '
					<div id="smileyBox_message"></div>';
		}
		template_control_richedit($this->key, 'smileyBox_message', 'bbcBox_message');

		return $ret;
	}
}
