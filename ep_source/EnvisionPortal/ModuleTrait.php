<?php

namespace EnvisionPortal;

/**
 * @package EnvisionPortal
 * @since   1.0
 */
trait ModuleTrait
{
	public function error($type = 'error', $error_type = 'general', $log_error = false): string
	{
		global $txt;

		$error_string = $txt['ep_module_' . $type] ?? $type;

		if ($log_error) {
			log_error($error_string, $error_type);
		}

		return sprintf($error_type == 'critical' ? '<p class="error">%s</p>' : '', $error_string);
	}

	private function captureOutput(callable $callback, ...$args): string
	{
		ob_start();
		call_user_func($callback, ...$args);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}