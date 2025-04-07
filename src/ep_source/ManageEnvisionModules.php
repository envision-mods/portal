<?php

/**
 * @package   Envision Portal
 * @version   3.0.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

function Modules()
{
	global $context, $txt, $settings;

	isAllowedTo('admin_forum');
	loadLanguage('ep_languages/EnvisionHelp+ep_languages/ManageEnvisionModules');
	loadTemplate('ep_template/ManageEnvisionModules', 'ep_css/admin');

	$context[$context['admin_menu_name']]['tab_data'] = [
		'title' => $txt['ep_admin_modules'],
		'description' => $txt['ep_admin_modules_desc'],
		'tabs' => [
			'epmanmodules' => [
				'description' => $txt['ep_admin_modules_manmodules_desc'],
			],
		],
	];
	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/admin.js"></script>';

	if (isset($_REQUEST['xml'])) {
		$context['template_layers'] = [];
	}

	$subActions = [
		'epmanmodules' => 'ManageEnvisionModules',
		'epsavemodules' => 'SaveEnvisionModules',
		'modify' => 'ModifyModule',
		'modify2' => 'ModifyModule2',
		'epaddlayout' => 'AddEnvisionLayout',
		'epaddlayout2' => 'AddEnvisionLayout2',
		'epdeletelayout' => 'DeleteEnvisionLayout',
		'epeditlayout' => 'EditEnvisionLayout',
		'epeditlayout2' => 'EditEnvisionLayout2',
	];
	$call = isset($_REQUEST['sa'], $subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'epmanmodules';
	call_user_func($subActions[$call]);
}

function ManageEnvisionModules()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['page_title'] = $txt['ep_admin_title_manage_modules'];
	$context['in_url'] = $scripturl . '?action=admin;area=epmodules;sa=epmanlayout';
	$context['template_layers'][] = 'select_layout';
	$context['sub_template'] = 'manage_modules';

	$request = $smcFunc['db_query'](
		'',
		'
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts'
	);

	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$context['layout_list'][$row['id_layout']] = $row['name'];
	}

	if (!isset($context['layout_list'])) {
		fatal_lang_error('cant_find_layout_id', false);
	}

	if (!isset($_REQUEST['in']) || !isset($context['layout_list'][$_REQUEST['in']])) {
		$_REQUEST['in'] = key($context['layout_list']);
	}

	$context['selected_layout'] = (int)$_REQUEST['in'];
	$context['ep_cols'] = EnvisionPortal\Portal::getLoadedLayoutFromId($context['selected_layout'], 2);

	if ($context['ep_cols'] === null) {
		fatal_lang_error('cant_find_layout_id', false);
	}

	$context['modules'] = iterator_to_array(
		EnvisionPortal\Util::map(
			fn($cn) => EnvisionPortal\Util::decamelize(substr($cn, strrpos($cn, '\\') + 1)),
			EnvisionPortal\Util::find_classes(
				new GlobIterator(
					__DIR__ . '/EnvisionPortal/Modules/*.php',
					FilesystemIterator::SKIP_DOTS
				),
				'EnvisionPortal\Modules\\',
				'EnvisionPortal\ModuleInterface'
			)
		)
	);

	$context['insert_after_template'] .= '<style>
@font-face {
  font-family: \'Glyphicons Halflings\';
  src: url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.eot\');
  src: url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.eot?#iefix\') format(\'embedded-opentype\'),
       url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.woff2\') format(\'woff2\'),
       url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.woff\') format(\'woff\'),
       url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.ttf\') format(\'truetype\'),
       url(\'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/fonts/glyphicons-halflings-regular.svg#glyphicons_halflingsregular\') format(\'svg\');
}
.glyphicon
{
	position: relative;
	top: 1px;
	display: inline-block;
	font: normal normal 16px/1 \'Glyphicons Halflings\';
	-moz-osx-font-smoothing: grayscale;
	-webkit-font-smoothing: antialiased;
	margin-right: 4px;
	vertical-align: top;
}
.glyphicon-move::before
{
	content: "\e068";
}
</style>
	<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
	<script type="text/javascript">
		makeSortables();
		var postUrl = "action=admin;area=epmodules;sa=epsavelayout;xml;";
		var postUrl2 = "action=admin;area=epmodules;xml;partial";
		var sessVar = "' . $context['session_var'] . '";
		var sessId = "' . $context['session_id'] . '";
		var errorString = ' . JavaScriptEscape($txt['error_string']) . ';
		var modulePositionsSaved = ' . JavaScriptEscape($txt['module_positions_saved']) . ';
		var clickToClose = ' . JavaScriptEscape($txt['click_to_close']) . ';
	</script>';
}

function SaveEnvisionModules()
{
	global $smcFunc;

	checkSession();

	foreach ($_POST['modules'] as $id_layout_position => $col) {
		foreach ($col as $position => $id_position) {
			if (is_numeric($id_position)) {
				// Saving a module that was merely moved.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}ep_module_positions
					SET
						position = {int:position},
						id_layout_position = {int:id_layout_position}
					WHERE id_position = {int:id_position}',
					[
						'id_position' => $id_position,
						'id_layout_position' => $id_layout_position,
						'position' => $position,
					]
				);
			} else {
				// Insert a new row for a module added from the list on the right.
				$smcFunc['db_insert']('insert',
					'{db_prefix}ep_module_positions',
					[
						'id_layout_position' => 'int',
						'type' => 'string',
						'position' => 'int',
					],
					[
						$id_layout_position,
						$id_position,
						$position,
					],
					['id_position']
				);
			}
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ep_layout_positions
			SET
				status = {string:status_value}
			WHERE id_layout_position = {int:id_layout_position}',
			[
				'id_layout_position' => $id_layout_position,
				'status_value' => empty($_POST['enabled'][$id_layout_position]) ? 'inactive' : 'active',
			]
		);
	}

	redirectexit('action=admin;area=epmodules;in=' . $_POST['in']);
}

function ModifyModule()
{
	global $context, $txt;

	if (preg_match('/^[0-9]+$/', $_GET['in']) !== 1) {
		fatal_lang_error('no_access', false);
	}

	[$info, $type] = EnvisionPortal\Portal::loadModule((int)$_GET['in']);

	if ($info === null) {
		fatal_lang_error('no_access', false);
	}

	$data = array_replace_recursive(ep_get_module_info2(), $info);
	$context['module'] = [];
	$cache = [];

	foreach ($data as $key => $field) {
		if (!isset($field['type'])) {
			continue;
		}

		if (isset($field['preload']) && is_callable($field['preload'])) {
			$field = call_user_func($field['preload'], $field);
		}

		$txt_key = strpos($key, 'module_') !== 0 ? $type : 'default';
		$cn = 'EnvisionPortal\Fields\\' . EnvisionPortal\Util::camelize($field['type']);
		$obj = new $cn($field, $key, $txt_key);
		$context['module'][] = [$key, $txt_key, $obj];

		if ($obj instanceof EnvisionPortal\CacheableFieldInterface) {
			if (!isset($cache[$field['type']])) {
				$cache[$field['type']] = $obj->fetchData();
			}

			$obj->setData($cache[$field['type']]);
		}
	}

	$context['module_type'] = $type;
	$context['page_title'] = $txt['ep_modify_mod'];
	$context['module_id'] = $_GET['in'];
	$context['sub_template'] = 'modify_modules';
}

function ModifyModule2()
{
	global $context, $smcFunc;

	checkSession();

	if (preg_match('/^[0-9]+$/', $_POST['in']) !== 1) {
		fatal_lang_error('no_access', false);
	}

	[$info, $type] = EnvisionPortal\Portal::loadModule((int)$_POST['in']);

	if ($info === null) {
		fatal_lang_error('no_access', false);
	}

	$data = array_replace_recursive(ep_get_module_info2(), $info);
	$fields_to_save = [];

	foreach ($data as $key => $field) {
		if (!isset($field['type'])) {
			continue;
		}

		if (isset($field['preload']) && is_callable($field['preload'])) {
			$field = call_user_func($field['preload'], $field);
		}

		$cn = 'EnvisionPortal\Fields\\' . EnvisionPortal\Util::camelize($field['type']);
		$obj = new $cn($field, $key, '');

		if ($obj instanceof EnvisionPortal\UpdateFieldInterface) {
			$fields_to_save[] = [$_POST['in'], $key, $obj->beforeSave($_POST[$key] ?? null)];
		} elseif (isset($_POST[$key]) && $field['value'] != $_POST[$key]) {
			$fields_to_save[] = [$_POST['in'], $key, $_POST[$key]];
		}
	}

	$smcFunc['db_insert']('replace',
		'{db_prefix}ep_module_field_data',
		[
			'id_module_position' => 'int',
			'name' => 'string',
			'value' => 'string',
		],
		$fields_to_save,
		['id_module_position']
	);

	// Looks like we're done here. Depart.
	if (!isset($_REQUEST['xml'])) {
		redirectexit('action=admin;area=epmodules;sa=modify;in=' . $_POST['in']);
	} else {
		$context['sub_template'] = 'generic_xml';
		$context['xml_data'] = [
			'items' => [
				'identifier' => 'item',
				'children' => [
					[
						'value' => 'action=admin;area=epmodules;xml',
					],
				],
			],
		];
	}
}

function AddEnvisionLayout()
{
	global $context, $scripturl, $txt;

	if (isset($_POST['ep_cols'])) {
		foreach ($_POST['ep_cols'] as $i => $col) {
			foreach (['col', 'colspan', 'row', 'rowspan'] as $type) {
				$context['ep_cols'][$i][$type] = $col[$type] ?? 0;
			}

			$context['ep_cols'][$i]['enabled'] = isset($col['enabled']);
			$context['ep_cols'][$i]['is_smf'] = $_POST['smf'] == $i;
		}
	} else {
		$context['ep_cols'] = [
			1 => [
				'row' => 0,
				'rowspan' => 0,
				'col' => 0,
				'colspan' => 3,
				'is_smf' => false,
				'enabled' => true,
			],
			2 => [
				'row' => 1,
				'rowspan' => 0,
				'col' => 0,
				'colspan' => 0,
				'is_smf' => false,
				'enabled' => true,
			],
			3 => [
				'row' => 1,
				'rowspan' => 0,
				'col' => 1,
				'colspan' => 0,
				'is_smf' => true,
				'enabled' => true,
			],
			4 => [
				'row' => 1,
				'rowspan' => 0,
				'col' => 2,
				'colspan' => 0,
				'is_smf' => false,
				'enabled' => true,
			],
			5 => [
				'row' => 2,
				'rowspan' => 0,
				'col' => 0,
				'colspan' => 3,
				'is_smf' => false,
				'enabled' => true,
			],
		];
	}

	$context['page_title'] = $txt['add_layout_title'];
	$context['sub_template'] = 'add_layout';
	$context['template_layers'][] = 'form';
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=epaddlayout2';
	$context['layout_actions'] = [];

	$exceptions = [
		'acceptagreement',
		'activate',
		'announce',
		'attachapprove',
		'buddy',
		'calendar',
		'clock',
		'collapse',
		'coppa',
		'deletemsg',
		'display',
		'dlattach',
		'emailuser',
		'findmember',
		'groups',
		'help',
		'helpadmin',
		'im',
		'jseditor',
		'jsmodify',
		'jsoption',
		'lock',
		'lockvoting',
		'markasread',
		'mergetopics',
		'modifycat',
		'modifykarma',
		'movetopic',
		'movetopic2',
		'notify',
		'notifyboard',
		'notifyannouncements',
		'openidreturn',
		'printpage',
		'quotefast',
		'quickmod',
		'quickmod2',
		'',
		'removepoll',
		'removetopic2',
		'reporttm',
		'requestmembers',
		'restoretopic',
		'sendtopic',
		'smstats',
		'suggest',
		'spellcheck',
		'splittopics',
		'sticky',
		'theme',
		'trackip',
		'about:mozilla',
		'about:unknown',
		'verificationcode',
		'viewprofile',
		'vote',
		'viewquery',
		'viewsmfile',
		'who',
		'.xml',
		'xmlhttp',
	];

	$smf_actions = EnvisionPortal\Integration::getActions();
	for ($i = 0, $n = count($smf_actions); $i < $n; $i++) {
		if (!in_array($smf_actions[$i], $exceptions) && $smf_actions[$i][-1] !== '2') {
			$context['available_actions'][$smf_actions[$i]] = false;
		}
	}

	ksort($context['available_actions']);

	if (isset($_POST['layout_actions'])) {
		foreach ($_POST['layout_actions'] as $action) {
			if (isset($context['available_actions'][$action])) {
				$context['available_actions'][$action] = true;
			} else {
				$context['layout_actions'][$action] = true;
			}
		}
	} else {
		$context['layout_actions'][''] = true;
	}

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

/**
 * Adds the layout specified in the form from {@link AddEnvisionLayout()}.
 *
 * @since 1.0
 */
function AddEnvisionLayout2()
{
	global $context, $txt, $smcFunc;

	$layout_name = $smcFunc['htmltrim']($_POST['name']);
	$errors = [];
	$layout_actions = [];
	$layout_positions = [];

	if ($layout_name == '') {
		$errors[] = ['no_layout_name', []];
	}

	if (isset($_POST['smf_actions'])) {
		foreach ($_POST['smf_actions'] as $action) {
			if (trim($action) != '') {
				$layout_actions[] = $action;
			}
		}
	}

	if (isset($_POST['layout_actions'])) {
		foreach ($_POST['layout_actions'] as $action) {
			if (trim($action) != '') {
				$layout_actions[] = $action;
			}
		}
	}

	if ($layout_actions == []) {
		$errors[] = ['no_actions', []];
	}

	$all_removed = true;
	foreach ($_POST['ep_cols'] as $i => $col) {
		$all_removed = $all_removed && in_array($col['id'], $_POST['remove']);

		if (isset($col['remove'])) {
			continue;
		}

		foreach (['col', 'colspan', 'row', 'rowspan'] as $type) {
			if (!isset($col[$type]) || !preg_match('/^[0-9]+$/', $col[$type])) {
				$errors[] = ['section_error', [$txt['ep_' . $type], $i]];
			}
		}

		$layout_positions[] = new Layout(
			0,
			(int)$col['row'],
			(int)$col['rowspan'],
			(int)$col['col'],
			(int)$col['colspan'],
			$_POST['smf'] == $i,
			!empty($col['enabled'])
		);
	}

	if ($all_removed) {
		$errors[] = ['cant_delete_all', []];
	}

	if (!in_array('is_smf', $layout_positions)) {
		$errors[] = ['not_enough_columns', []];
	}

	if (count($layout_positions) < 2) {
		$errors[] = ['not_enough_columns', []];
	}

	if ($errors != []) {
		AddEnvisionLayout();
		foreach ($errors as [$error_message, $sprintf_params]) {
			$context['errors'][] = vsprintf($txt['ep_' . $error_message], $sprintf_params);
		}
		$context['error_title'] = 'layout_error_header';
		$context['template_layers'][] = 'errors';
	} else {
		// Prevent double submission of this form.
		checkSubmitOnce('check');

		require_once __DIR__ . '/Subs-EnvisionLayouts.php';
		$layout = addLayout($layout_name, $layout_actions, $layout_positions);

		if (!isset($_REQUEST['xml'])) {
			redirectexit('action=admin;area=epmodules;in=' . $layout);
		} else {
			$context['sub_template'] = 'generic_xml';
			$context['xml_data'] = [
				'items' => [
					'identifier' => 'item',
					'children' => [
						[
							'value' => 'action=admin;area=epmodules;in=' . $context['selected_layout'] . ';xml',
						],
					],
				],
			];
		}
	}
}

/**
 * Calls {@link EnvisionDeleteLayout()} to delete a layout specified in $_POST['layout_picker'].
 *
 * @since 1.0
 */
function DeleteEnvisionLayout()
{
	global $context, $scripturl, $smcFunc, $txt;

	$context['page_title'] = $txt['delete_layout'];
	$context['template_layers'][] = 'form';
	$context['sub_template'] = 'delete_layout';
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=epdeletelayout';

	if (empty($_POST['layouts'])) {
		$request = $smcFunc['db_query'](
			'',
			'
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts'
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$context['layouts'][$row['id_layout']] = $row['name'];
		}

		if (!isset($context['layouts'])) {
			fatal_lang_error('cant_find_layout_id', false);
		}

		if (!isset($_GET['in']) || !isset($context['layouts'][$_GET['in']])) {
			$_GET['in'] = key($context['layouts']);
		}

		$context['selected_layout'] = (int)$_GET['in'];

		// Register this form and get a sequence number in $context.
		checkSubmitOnce('register');
	} else {
		// Prevent double submission of this form.
		checkSubmitOnce('check');

		checkSession();
		require_once __DIR__ . '/Subs-EnvisionLayouts.php';

		if (!deleteLayout($_POST['layouts'])) {
			fatal_lang_error('no_layout_selected', false);
		} else {
			redirectexit('action=admin;area=epmodules;sa=epmanmodules');
		}
	}
}

/**
 * Loads the form for the admin to edit a layout.
 *
 * @since 1.0
 */
function EditEnvisionLayout()
{
	global $context, $scripturl, $smcFunc, $txt;

	$request = $smcFunc['db_query'](
		'',
		'
			SELECT
				id_layout, name
			FROM {db_prefix}ep_layouts'
	);

	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$context['layout_list'][$row['id_layout']] = $row['name'];
	}

	if (!isset($context['layout_list'])) {
		fatal_lang_error('cant_find_layout_id', false);
	}

	if (!isset($_GET['in']) || !isset($context['layout_list'][$_GET['in']])) {
		$_GET['in'] = key($context['layout_list']);
	}

	$context['layout_name'] = $context['layout_list'][$_GET['in']];
	$context['selected_layout'] = (int)$_GET['in'];
	$context['ep_cols'] = EnvisionPortal\Portal::getLoadedLayoutFromId($context['selected_layout'], 2);

	if ($context['ep_cols'] === null) {
		fatal_lang_error('cant_find_layout_id', false);
	}

	$context['page_title'] = $txt['edit_layout'];
	$context['module_id'] = $_GET['in'];
	$context['template_layers'][] = 'select_layout';
	$context['template_layers'][] = 'form';
	$context['sub_template'] = 'edit_layout';
	$context['post_url'] = $scripturl . '?action=admin;area=epmodules;sa=epeditlayout2';
	$context['layout_actions'] = [];

	$exceptions = [
		'acceptagreement',
		'activate',
		'announce',
		'attachapprove',
		'buddy',
		'calendar',
		'clock',
		'collapse',
		'coppa',
		'deletemsg',
		'display',
		'dlattach',
		'emailuser',
		'findmember',
		'groups',
		'help',
		'helpadmin',
		'im',
		'jseditor',
		'jsmodify',
		'jsoption',
		'lock',
		'lockvoting',
		'markasread',
		'mergetopics',
		'modifycat',
		'modifykarma',
		'movetopic',
		'movetopic2',
		'notify',
		'notifyboard',
		'notifyannouncements',
		'openidreturn',
		'printpage',
		'quotefast',
		'quickmod',
		'quickmod2',
		'',
		'removepoll',
		'removetopic2',
		'reporttm',
		'requestmembers',
		'restoretopic',
		'sendtopic',
		'smstats',
		'suggest',
		'spellcheck',
		'splittopics',
		'sticky',
		'theme',
		'trackip',
		'about:mozilla',
		'about:unknown',
		'verificationcode',
		'viewprofile',
		'vote',
		'viewquery',
		'viewsmfile',
		'who',
		'.xml',
		'xmlhttp',
	];

	$smf_actions = EnvisionPortal\Integration::getActions();
	for ($i = 0, $n = count($smf_actions); $i < $n; $i++) {
		if (!in_array($smf_actions[$i], $exceptions) && $smf_actions[$i][-1] !== '2') {
			$context['available_actions'][$smf_actions[$i]] = false;
		}
	}

	ksort($context['available_actions']);

	if (isset($_POST['layout_actions'])) {
		foreach ($_POST['layout_actions'] as $action) {
			if (isset($context['available_actions'][$action])) {
				$context['available_actions'][$action] = true;
			} else {
				$context['layout_actions'][$action] = true;
			}
		}
	} else {
		$request = $smcFunc['db_query']('', '
			SELECT action
			FROM {db_prefix}ep_layout_actions
			WHERE id_layout = {int:current_layout}',
			[
				'current_layout' => $_GET['in'],
			]
		);

		while ([$action] = $smcFunc['db_fetch_row']($request)) {
			if (isset($context['available_actions'][$action])) {
				$context['available_actions'][$action] = true;
			} else {
				$context['layout_actions'][$action] = true;
			}
		}
	}

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

/**
 * Edits the layout socified in the form loaded from {@link EditEnvisionLayout()}.
 *
 * @since 1.0
 */
function EditEnvisionLayout2()
{
	global $context, $txt, $smcFunc, $sourcedir;

	if (preg_match('/^[0-9]+$/', $_POST['in']) !== 1) {
		fatal_lang_error('no_access', false);
	}

	$layout = (int)$_POST['in'];
	$context['ep_cols'] = EnvisionPortal\Portal::getLoadedLayoutFromId($layout);

	if ($context['ep_cols'] === null) {
		fatal_lang_error('cant_find_layout_id', false);
	}

	$layout_name = $smcFunc['htmltrim']($_POST['name']);
	$errors = [];
	$layout_actions = [];
	$layout_positions = [];

	if ($layout_name == '') {
		$errors[] = ['no_layout_name', []];
	}

	if (isset($_POST['smf_actions'])) {
		foreach ($_POST['smf_actions'] as $action) {
			if (trim($action) != '') {
				$layout_actions[] = $action;
			}
		}
	}

	if (isset($_POST['layout_actions'])) {
		foreach ($_POST['layout_actions'] as $action) {
			if (trim($action) != '') {
				$layout_actions[] = $action;
			}
		}
	}

	if ($layout_actions == []) {
		$errors[] = ['no_actions', []];
	}

	$all_removed = true;
	foreach ($context['ep_cols'] as $col) {
		foreach (['col', 'colspan', 'row', 'rowspan'] as $type) {
			if (!isset($_POST[$type][$col['id']]) || !preg_match('/^[0-9]+$/', $_POST[$type][$col['id']])) {
				$errors[] = ['section_error', [$txt['ep_' . $type], $col['id']]];
			}
		}

		$layout_positions[] = new Layout(
			$col['id'],
			(int)$_POST['row'][$col['id']],
			(int)$_POST['rowspan'][$col['id']],
			(int)$_POST['col'][$col['id']],
			(int)$_POST['colspan'][$col['id']],
			$_POST['smf'] == $col['id'],
			!empty($_POST['enabled'][$col['id']])
		);

		$all_removed = $all_removed && in_array($col['id'], $_POST['remove'] ?? []);
	}

	if (in_array($_POST['smf'], $context['ep_cols'])) {
		$errors[] = ['not_enough_columns', []];
	}

	if ($all_removed) {
		$errors[] = ['cant_delete_all', []];
	}

	if ($errors != []) {
		EditEnvisionLayout();
		foreach ($errors as [$error_message, $sprintf_params]) {
			$context['errors'][] = vsprintf($txt['ep_' . $error_message], $sprintf_params);
		}
		$context['error_title'] = 'edit_layout_error_header';
		$context['template_layers'][] = 'errors';
	} else {
		// Prevent double submission of this form.
		checkSubmitOnce('check');

		require_once($sourcedir . '/ep_source/Subs-EnvisionLayouts.php');
		editLayout(
			$layout,
			$layout_name,
			$layout_actions,
			$layout_positions,
			(int)$_POST['smf'],
			$_POST['remove'] ?? []
		);

		if (!isset($_REQUEST['xml'])) {
			redirectexit('action=admin;area=epmodules;in=' . $layout);
		} else {
			$context['sub_template'] = 'generic_xml';
			$context['xml_data'] = [
				'items' => [
					'identifier' => 'item',
					'children' => [
						[
							'value' => 'action=admin;area=epmodules;in=' . $context['selected_layout'] . ';xml',
						],
					],
				],
			];
		}
	}
}

/**
 * Populates the module's field list with standard fields.
 *
 * The field list which is passed as the parameter is added
 * onto the default fields defined by this function.
 *
 * @return array
 * @since 1.0
 */
function ep_get_module_info2(): array
{
	return [
		'module_title' => [
			'type' => 'text',
		],
		'module_header_display' => [
			'type' => 'select',
			'options' => ['enabled', 'disable', 'collapse'],
			'value' => 'enabled',
		],
		'module_icon' => [
			'type' => 'select',
			'preload' => function ($field) {
				global $context, $txt;

				$css_file = file_get_contents($context['module_icon_dir'] . '/fugue-sprite.css');
				preg_match_all('/(?<=}\.fugue-)[^{]++(?={)/', $css_file, $matches);
				$field['options'] = $matches[0];

				foreach ($matches[0] as $file) {
					$txt['ep_modules']['default']['module_icon'][$file] = $file;
				}

				return $field;
			},
			'value' => '',
		],
		'module_link' => [
			'type' => 'text',
			'value' => '',
		],
		'module_target' => [
			'type' => 'select',
			'options' => ['_self', '_parent', '_blank'],
			'value' => '_self',
		],
		'module_groups' => [
			'type' => 'grouplist',
			'value' => '-3',
		],
	];
}
