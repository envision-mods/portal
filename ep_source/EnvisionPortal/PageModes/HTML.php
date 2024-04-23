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

class HTML implements PageModeInterface
{
	public function parse(string $body): string
	{
		return $body;
	}

	public function getMode(): string
	{
		return 'html';
	}
}