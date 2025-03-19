<?php

require_once './vendor/autoload.php';

// What are you doing here, SMF?
const SMF = 1;

function remove_integration_function(string $string, string $integration)
{
}

function add_integration_function(string $string, string $integration)
{
}

function allowedTo(string $string)
{
	return true;
}

function isAllowedTo(string $string)
{
	return true;
}

function call_integration_hook($hook, $parameters = array())
{
	// You're fired!  You're all fired!  Get outta here!
	return [];
}

function fatal_error($msg, $log)
{
	echo $msg;
}

function fatal_lang_error($msg, $log)
{
	throw new Error($msg);
}

function loadLanguage($template_name, $lang = '', $fatal = true, $force_reload = false)
{
}

function checkSession($method, $param, $fatal)
{
}

function redirectexit($url)
{
}

function parse_bbc($text)
{
	$text = strip_tags($text);
	// BBcode array
	$find = array(
		'~\[b\](.*?)\[/b\]~s',
		'~\[i\](.*?)\[/i\]~s',
		'~\[u\](.*?)\[/u\]~s',
		'~\[quote\]([^"><]*?)\[/quote\]~s',
		'~\[size=([^"><]*?)\](.*?)\[/size\]~s',
		'~\[color=([^"><]*?)\](.*?)\[/color\]~s',
		'~\[url\]((?:ftp|https?)://[^"><]*?)\[/url\]~s',
		'~\[img\](https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s'
	);
	// HTML tags to replace BBcode
	$replace = array(
		'<b>$1</b>',
		'<i>$1</i>',
		'<span style="text-decoration:underline;">$1</span>',
		'<pre>$1</'.'pre>',
		'<span style="font-size:$1px;">$2</span>',
		'<span style="color:$1;">$2</span>',
		'<a href="$1">$1</a>',
		'<img src="$1" alt="" />'
	);
	// Replacing the BBcodes with corresponding HTML tags
	return preg_replace($find, $replace, $text);
}

function fixchar__callback($matches)
{
	if (!isset($matches[1]))
		return '';

	$num = $matches[1][0] === 'x' ? hexdec(substr($matches[1], 1)) : (int) $matches[1];

	// <0x20 are control characters, > 0x10FFFF is past the end of the utf8 character set
	// 0xD800 >= $num <= 0xDFFF are surrogate markers (not valid for utf8 text), 0x202D-E are left to right overrides
	if ($num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num === 0x202D || $num === 0x202E)
		return '';
	// <0x80 (or less than 128) are standard ascii characters a-z A-Z 0-9 and punctuation
	elseif ($num < 0x80)
		return chr($num);
	// <0x800 (2048)
	elseif ($num < 0x800)
		return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	// < 0x10000 (65536)
	elseif ($num < 0x10000)
		return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	// <= 0x10FFFF (1114111)
	else
		return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
}

class TestObj
{
	public static $last_query;
	public static $last_params;
	public static array $last_insert;
	public static bool $fake_queries = false;
	public static PDO $pdo;
}

global $smcFunc, $user_info, $txt;

global $context, $settings, $txt, $user_info;

// Set up necessary global variables
$context = [
	'html_headers' => '',
	'admin_menu_name' => 'Admin Menu',
];
$settings = ['default_theme_url' => '/theme/url'];
$txt = [
	'admin_menu_title' => 'Admin Menu Title',
	'admin_menu' => 'Admin Menu',
	'admin_menu_description' => 'Admin Menu Description',
	'admin_manage_menu_description' => 'Manage Menu Description',
	'admin_menu_add_page_description' => 'Add Page Description',
	'parent_guests_only' => 'Guests',
	'parent_members_only' => 'Members',
];
$user_info = ['is_admin' => true, 'is_guest' => false, 'language' => '', 'id' => 1, 'name' => 'Test User', 'groups' => [0], 'permissions' => []];

require __DIR__ . '/../src/ep_languages/ManageEnvisionPages.english.php';

TestObj::$pdo = new PDO('sqlite::memory:');
TestObj::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
TestObj::$pdo->exec('CREATE TABLE membergroups (
	id_group INTEGER PRIMARY KEY AUTOINCREMENT,
	group_name TEXT NOT NULL DEFAULT \'\',
	description TEXT NOT NULL,
	online_color TEXT NOT NULL DEFAULT \'\',
	min_posts INTEGER NOT NULL DEFAULT -1,
	max_messages INTEGER NOT NULL DEFAULT 0,
	icons TEXT NOT NULL DEFAULT \'\',
	group_type INTEGER NOT NULL DEFAULT 0,
	hidden INTEGER NOT NULL DEFAULT 0,
	id_parent INTEGER NOT NULL DEFAULT -2,
	tfa_required INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_min_posts ON membergroups (min_posts);

INSERT INTO membergroups 
	(id_group, group_name, description, online_color, min_posts, icons, group_type) 
VALUES 
	(1, \'Administrator\', \'\', \'#FF0000\', -1, \'5#iconadmin.png\', 1),
	(2, \'Global Moderator\', \'\', \'#0000FF\', -1, \'5#icongmod.png\', 0),
	(3, \'Moderator\', \'\', \'\', -1, \'5#iconmod.png\', 0),
	(4, \'Newbie\', \'\', \'\', 0, \'1#icon.png\', 0),
	(5, \'Jr. Member\', \'\', \'\', 50, \'2#icon.png\', 0),
	(6, \'Full Member\', \'\', \'\', 100, \'3#icon.png\', 0),
	(7, \'Sr. Member\', \'\', \'\', 250, \'4#icon.png\', 0),
	(8, \'Hero Member\', \'\', \'\', 500, \'5#icon.png\', 0);');

$smcFunc['db_query'] = function (string $identifier, string $query, array $params = []) {
	global $modSettings, $smcFunc;

	TestObj::$last_query = preg_replace('/\s+/', ' ', $query);
	TestObj::$last_params = $params;

	if (isset($params['variable']) && $params['variable'] == 'integrate_menu_buttons') {
		return [[$modSettings[$params['variable']] ?? null]];
	}

	if (!TestObj::$fake_queries) {
		return TestObj::$pdo->query(
			preg_replace(
				['/TRUNCATE/', '/NOW\(\)/'],
				['DELETE FROM', 'DATE(\'now\')'],
				$smcFunc['db_quote']($query, $params),
			),
		);
	}

	return null;
};

$smcFunc['db_fetch_assoc'] = fn (?PDOStatement $stmt) => $stmt?->fetch(PDO::FETCH_ASSOC);
$smcFunc['db_fetch_row'] = fn (?PDOStatement $stmt) => $stmt?->fetch(PDO::FETCH_NUM);
$smcFunc['db_free_result'] = fn (?PDOStatement $stmt) => $stmt?->closeCursor();

$smcFunc['db_quote'] = function (string $db_string, array $db_values, ?object $connection = null): string {
	// Only bother if there's something to replace.
	if (str_contains($db_string, '{')) {
		$conn = $connection ?? TestObj::$pdo;

		$replacement__callback = function (array $matches) use ($db_values, $conn): string {
			if ($matches[1] === 'db_prefix') {
				return '';
			}

			if ($matches[1] === 'empty') {
				return '\'\'';
			}

			if (!isset($matches[2])) {
				throw new \InvalidArgumentException('Invalid value inserted or no type specified.');
			}

			if ($matches[1] === 'literal') {
				return $conn->quote($matches[2]);
			}

			if (!isset($db_values[$matches[2]])) {
				throw new \InvalidArgumentException('The database value you\'re trying to insert does not exist: ' . htmlspecialchars($matches[2]));
			}

			$replacement = $db_values[$matches[2]];

			switch ($matches[1]) {
				case 'int':
					if (!is_numeric($replacement) || (string) $replacement !== (string) (int) $replacement) {
						throw new \InvalidArgumentException('Wrong value type sent to the database. Integer expected. (' . $matches[2] . ')');
					}
					return (string) (int) $replacement;

				case 'string':
				case 'text':
					return $conn->quote((string) $replacement);

				case 'array_int':
					if (is_array($replacement)) {
						if (empty($replacement)) {
							throw new \InvalidArgumentException('Database error, given array of integer values is empty. (' . $matches[2] . ')');
						}
						foreach ($replacement as $key => $value) {
							if (!is_numeric($value) || (string) $value !== (string) (int) $value) {
								throw new \InvalidArgumentException('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')');
							}
							$replacement[$key] = (int) $value;
						}
						return implode(', ', $replacement);
					}
					throw new \InvalidArgumentException('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')');

				case 'array_string':
					if (is_array($replacement)) {
						if (empty($replacement)) {
							throw new \InvalidArgumentException('Database error, given array of string values is empty. (' . $matches[2] . ')');
						}
						foreach ($replacement as $key => $value) {
							$replacement[$key] = $conn->quote((string) $value);
						}
						return implode(', ', $replacement);
					}
					throw new \InvalidArgumentException('Wrong value type sent to the database. Array of strings expected. (' . $matches[2] . ')');

				case 'date':
					if (preg_match('~^\d{4}-\d{2}-\d{2}$~', $replacement)) {
						return $conn->quote($replacement);
					}
					throw new \InvalidArgumentException('Wrong value type sent to the database. Date expected. (' . $matches[2] . ')');

				case 'time':
					if (preg_match('~^\d{2}:\d{2}:\d{2}$~', $replacement)) {
						return $conn->quote($replacement);
					}
					throw new \InvalidArgumentException('Wrong value type sent to the database. Time expected. (' . $matches[2] . ')');

				case 'datetime':
					if (preg_match('~^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$~', $replacement)) {
						return $conn->quote($replacement);
					}
					throw new \InvalidArgumentException('Wrong value type sent to the database. Datetime expected. (' . $matches[2] . ')');

				case 'float':
					if (!is_numeric($replacement)) {
						throw new \InvalidArgumentException('Wrong value type sent to the database. Floating point number expected. (' . $matches[2] . ')');
					}
					return (string) (float) $replacement;

				case 'identifier':
					return '`' . str_replace('`', '``', $replacement) . '`';

				case 'raw':
					return (string) $replacement;

				default:
					throw new \InvalidArgumentException('Undefined type used in the database query. (' . $matches[1] . ':' . $matches[2] . ')');
			}
		};

		// Do the quoting and escaping
		$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', $replacement__callback, $db_string);

		unset($db_values, $conn);
	};

	return $db_string;
};

$smcFunc['db_insert'] = function ($method, $table, $columns, $data, $keys) use ($smcFunc) {
	// Create the mold for a single row insert.
	$insertData = '(';
	foreach ($columns as $columnName => $type)
	{
		// Are we restricting the length?
		if (strpos($type, 'string-') !== false)
			$insertData .= sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . '), ', $columnName);
		else
			$insertData .= sprintf('{%1$s:%2$s}, ', $type, $columnName);
	}
	$insertData = substr($insertData, 0, -2) . ')';

	// Create an array consisting of only the columns.
	$indexed_columns = array_keys($columns);

	// Inserting data as a single row can be done as a single array.
	if (!is_array($data[array_rand($data)])) {
		$data = [$data];
	}

	// Here's where the variables are injected to the query.
	$insertRows = array();
	foreach ($data as $dataRow)
		$insertRows[] = $smcFunc['db_quote']($insertData, array_combine($indexed_columns, $dataRow));

	// Determine the method of insertion.
	$queryTitle = match ($method) {
		'replace' => 'REPLACE',
		'ignore' => 'INSERT IGNORE',
		default => 'INSERT',
	};

	// Do the insert.
	$smcFunc['db_query']('', '
		' . $queryTitle . ' INTO ' . $table . '(`' . implode('`, `', $indexed_columns) . '`)
		VALUES
			' . implode(',
			', $insertRows),
		array(
			'security_override' => true,
		)
	);

	TestObj::$last_insert = [$method, $table, $columns, $data, $keys];
};

$smcFunc['htmltrim'] = fn(string $string): string => trim($string);
$smcFunc['htmlspecialchars'] = fn(string $string): string => htmlspecialchars($string, ENT_QUOTES);

session_start();