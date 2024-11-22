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

class BBCode implements PageModeInterface
{
	public function parse(string $body): string
	{
		return parse_bbc($body);
	}

	public function getMode(): string
	{
		return 'text';
	}
}