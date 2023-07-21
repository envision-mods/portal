<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace EnvisionPortal;

interface SharedMemberDataInterface
{
	/**
	 * Returns member identifiers to load additional data on.
	 *
	 * These ids are usually populated during invocation of this
	 * module, then passed directly to SMF (loadMemberData() and
	 * loadMemberContext(), respectively).  This is needed to combine
	 * queries when multiple modules need to show member data.
	 *
	 * @return array
	 */
	public function fetchMemberIds(): array;
}