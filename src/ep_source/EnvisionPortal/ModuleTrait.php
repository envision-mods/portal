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

/**
 * Trait providing common functionality for modules in Envision Portal.
 *
 * This trait provides methods for handling errors and capturing output,
 * which can be used by modules to enhance their implementation.
 */
trait ModuleTrait
{
	/**
	 * Generates an error message.
	 *
	 * @param string $type       The type of error message.
	 * @param string $error_type The type of error.
	 * @param bool   $log_error  Whether to log the error.
	 * @return string The error message.
	 */
	public function error($type = 'error', $error_type = 'general', $log_error = false): string
	{
		global $txt;

		$error_string = $txt['ep_module_' . $type] ?? $type;

		if ($log_error) {
			log_error($error_string, $error_type);
		}

		return sprintf($error_type == 'critical' ? '<p class="error">%s</p>' : '', $error_string);
	}

	/**
	 * Captures the output of a callback function.
	 *
	 * @param callable $callback The callback function to execute.
	 * @param mixed    ...$args  Optional arguments to pass to the callback.
	 * @return string The captured output.
	 */
	private function captureOutput(callable $callback, ...$args): string
	{
		ob_start();
		\call_user_func($callback, ...$args);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}
