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
use ErrorException;

class PHP implements PageModeInterface
{
	public function parse(string $body): string
	{
		set_error_handler(
			function ($err_severity, $err_msg, $err_file, $err_line) {
				// Error was suppressed with the @-operator.
				if (error_reporting() == 0 || error_reporting(
					) == (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR)) {
					return true;
				}

				// Ignore errors that should not be logged.
				$error_match = error_reporting() & $error_level;
				if (empty($error_match)) {
					return false;
				}

				switch ($err_severity) {
					case E_ERROR:
						throw new ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_WARNING:
						throw new WarningException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_PARSE:
						throw new ParseException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_NOTICE:
						throw new NoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_CORE_ERROR:
						throw new CoreErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_CORE_WARNING:
						throw new CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_COMPILE_ERROR:
						throw new CompileErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_COMPILE_WARNING:
						throw new CoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_USER_ERROR:
						throw new UserErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_USER_WARNING:
						throw new UserWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_USER_NOTICE:
						throw new UserNoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_STRICT:
						throw new StrictException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_RECOVERABLE_ERROR:
						throw new RecoverableErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_DEPRECATED:
						throw new DeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
					case E_USER_DEPRECATED:
						throw new UserDeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
				}
			}
		);

		set_exception_handler(
			function ($exception) {
				$result = '';
				foreach ($exception->getTrace() as $key => $stackPoint) {
					$result .= strtr(
						sprintf(
							'#%d. %s(%s): %s(%s)<br>',
							$key,
							$stackPoint['file'] ?? '',
							$stackPoint['line'] ?? '',
							(isset($stackPoint['class']) ? $stackPoint['class'] . $stackPoint['type'] : '') . $stackPoint['function'] ?? '[internal function]',
							implode(
								', ',
								array_map(
									function (array $stackPoint): array {
										$args = [];
										if (isset($stackPoint['args'])) {
											foreach ($stackPoint['args'] as $arg) {
												if (is_object($arg)) {
													$args[] = get_class($arg);
												} elseif (is_resource($arg)) {
													$args[] = get_resource_type($arg);
												} else {
													$args[] = gettype($arg);
												}
											}
										}

										return $args;
									},
									$stackPoint['args']
								)
							)
						),
						['(): ' => '']
					);
				}

				fatal_error(
					str_replace(
						$GLOBALS['boaarddir'],
						'.',
						sprintf(
							"'%s' with message '%s' in %s:%s<br>Stack trace:<br>%s<br>  thrown in %s on line %s",
							get_class($exception),
							$exception->getMessage(),
							$exception->getFile(),
							$exception->getLine(),
							$result . '#' . (++$key) . ' {main}',
							$exception->getFile(),
							$exception->getLine()
						)
					)
				);
			}
		);

		ob_start(
			function ($output) {
				$error = error_get_last();
				$output = "";
				if (!empty($error)) {
					switch ($error['type']) {
						case E_PARSE:
						case E_ERROR:
						case E_CORE_ERROR:
						case E_COMPILE_ERROR:
						case E_USER_ERROR:
							$output = '<b>Fatal Error</b><br>';
							break;
						case E_WARNING:
						case E_USER_WARNING:
						case E_COMPILE_WARNING:
						case E_RECOVERABLE_ERROR:
							$output = '<b>Warning</b><br>';
							break;
						case E_NOTICE:
						case E_USER_NOTICE:
							$output = '<b>Notice</b><br>';
							break;
						case E_STRICT:
							$output = '<b>Strict</b><br>';
							break;
						case E_DEPRECATED:
						case E_USER_DEPRECATED:
							$output = '<b>Deprecated</b><br>';
							break;
					}
					$output .= $error['message'];
				}

				return $output;
			}
		);

		eval(strtr($body, ['<?php' => '', '?>' => '']));

		return ob_get_clean();
	}

	public function getMode(): string
	{
		return 'PHP';
	}
}

class WarningException extends ErrorException
{
}

class ParseException extends ErrorException
{
}

class NoticeException extends ErrorException
{
}

class CoreErrorException extends ErrorException
{
}

class CoreWarningException extends ErrorException
{
}

class CompileErrorException extends ErrorException
{
}

class CompileWarningException extends ErrorException
{
}

class UserErrorException extends ErrorException
{
}

class UserWarningException extends ErrorException
{
}

class UserNoticeException extends ErrorException
{
}

class StrictException extends ErrorException
{
}

class RecoverableErrorException extends ErrorException
{
}

class DeprecatedException extends ErrorException
{
}

class UserDeprecatedException extends ErrorException
{
}