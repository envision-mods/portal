<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal\PageModes;

use EnvisionPortal\PageModeInterface;

class Liquid implements PageModeInterface
{
	public function parse(string $body): string
	{
		require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

		$config = [
			'scripturl' => $GLOBALS['scripturl'],
			'user' => $GLOBALS['user_info'],
		];

		$template = new \Liquid\Template;
		$template->setFileSystem(
			new \Liquid\FileSystem\Virtual(fn($filename) => file_get_contents($GLOBALS['boarddir'] . '/' . $filename))
		);
		$template->parse($body);

		return $template->render($config);
	}

	public function getMode(): string
	{
		return 'liquid';
	}
}