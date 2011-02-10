<?php
/**************************************************************************************
* ManageEnvisionMenu.php                                                              *
***************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

/**
 * This file handles management settings for Envision Pages.
 *
 * @package source
 * @copyright 2009-2010 Envision Portal
 * @license http://envisionportal.net/index.php?action=about;sa=legal Envision Portal License (Based on BSD)
 * @link http://envisionportal.net Support, news, and updates
 * @see ManageEnvisionMenu.template.php
 * @since 1.0
 * @version 1.1
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Loads the main configuration for this area.
 *
 * @since 1.0
 */
function Menu()
{
	global $context, $txt;

	loadTemplate('ep_template/ManageEnvisionMenu');
	loadLanguage('ep_languages/ManageEnvisionMenu');

	$subActions = array(
		'epmanmenu' => 'ManageEnvisionMenu',
		'epaddbutton' => 'PrepareContext',
		'epsavebutton' => 'SaveButton',
	);

	// Default to sub action 'epmanmenu'
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'epmanmenu';

	// Have you got the proper permissions?
	isAllowedTo('admin_forum');

	$context['page_title'] = $txt['ep_admin_menu_title'];

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['ep_admin_menu'],
		'description' => $txt['ep_admin_menu_desc'],
		'tabs' => array(
			'epmanmenu' => array(
				'description' => $txt['ep_admin_manage_menu_desc'],
			),
			'epaddbutton' => array(
				'description' => $txt['ep_admin_menu_add_button_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_GET['sa']]();

}

/**
 * Manages existing Envision Buttons.
 *
 * @since 1.0
 */
function ManageEnvisionMenu()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	// Get rid of all of em!
	if (!empty($_POST['removeAll']))
			$smcFunc['db_query']('truncate_table', '
				TRUNCATE {db_prefix}ep_envision_menu',
				array(
				)
			);

	// User pressed the 'remove selection button'.
	if (!empty($_POST['removeButtons']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		// Delete the page!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_envision_menu
			WHERE id_button IN ({array_int:button_list})',
			array(
				'button_list' => $_POST['remove'],
			)
		);

		redirectexit('action=admin;area=epmenu');
	}

	loadLanguage('ManageBoards');

	// Our options for our list.
	$listOptions = array(
		'id' => 'ep_menu_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=epmenu;sa=epmanmenu',
		'default_sort_col' => 'id_button',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => 'list_getMenu',
		),
		'get_count' => array(
			'function' => 'list_getNumButtons',
		),
		'no_items_label' => $txt['ep_envision_menu_no_buttons'],
		'columns' => array(
			'id_button' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_id'],
				),
				'data' => array(
					'db' => 'id_button',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.id_button',
					'reverse' => 'men.id_button DESC',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_name'],
				),
				'data' => array(
					'db_htmlsafe' => 'name',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.name',
					'reverse' => 'men.name DESC',
				),
			),
			'type' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_type'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						return $txt[$rowData[\'type\'] . \'_link\'];
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.type',
					'reverse' => 'men.type DESC',
				),
			),
			'poition' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_position'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						return $txt[\'mboards_order_\' . $rowData[\'position\']] . \' \' . ucwords($rowData[\'parent\']);
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.position',
					'reverse' => 'men.position DESC',
				),
			),
			'link' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_link'],
				),
				'data' => array(
					'db_htmlsafe' => 'link',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.link',
					'reverse' => 'men.link DESC',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_button_active'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// Tell them the status of their button.
						if ($rowData[\'status\'])
							return sprintf(\'<span style="color: green;">%1$s</span>\', $txt[\'active\']);
						else
							return sprintf(\'<span style="color: red;">%1$s</span>\', $txt[\'inactive\']);
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'men.status',
					'reverse' => 'men.status DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['ep_envision_menu_actions'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=epmenu;sa=epaddbutton;edit;bid=%1$d">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_button' => false,
						),
					),
					'class' => 'centertext',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="remove[]" value="%1$d" class="input_check" />',
						'params' => array(
							'id_button' => false,
						),
					),
					'class' => 'centertext',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=epmenu;sa=epmanmenu',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="' . $txt['ep_envision_menu_remove_button_button'] . '" value="' . $txt['ep_envision_menu_remove_selected'] . '" onclick="return confirm(\'' . $txt['ep_envision_menu_remove_confirm'] . '\');" class="button_submit" />
					<input type="submit" name="' . $txt['ep_envision_menu_remove_all_button'] . '" value="' . $txt['ep_envision_menu_remove_all'] . '" onclick="return confirm(\'' . $txt['ep_envision_menu_remove_all_confirm'] . '\');" class="button_submit" />',
					'class' => 'righttext',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'ep_menu_list';
}

/**
 * Saves a Envision Button. Handles creating a new button or modilying an existing one.
 *
 * @since 1.0
 */
function SaveButton()
{
	global $context, $smcFunc, $txt, $sourcedir;

	// Load SMF's default menu context
	setupMenuContext();

	if (isset($_REQUEST['submit']))
	{
		$post_errors = array();
		$required_fields = array(
			'name',
			'link',
		);

		// Make sure we grab all of the content
		$id = isset($_REQUEST['bid']) ? $_REQUEST['bid'] : '';
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$position = isset($_REQUEST['position']) ? $_REQUEST['position'] : '';
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		$link = isset($_REQUEST['link']) ? $_REQUEST['link'] : '';
		$permissions = isset($_REQUEST['permissions']) ? implode(',', $_REQUEST['permissions']) : '';
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
		$parent = isset($_REQUEST['parent']) ? $_REQUEST['parent'] : '';
		$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : 0;

		// These fields are required!
		foreach ($required_fields as $required_field)
			if ($_POST[$required_field] == '')
				$post_errors[$required_field] = 'ep_envision_menu_empty_' . $required_field;

		// Stop making numeric names!
		if (is_numeric($name))
			$post_errors['name'] = 'ep_envision_menu_numeric';

		// Let's make sure you're not trying to make a name that's already taken.
		$query = $smcFunc['db_query']('', '
			SELECT id_button
			FROM {db_prefix}ep_envision_menu
			WHERE name = {string:name}',
			array(
				'name' => $name
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$row = $smcFunc['db_fetch_assoc']($query);

		if ($check != 0 && empty($id))
			$post_errors['name'] = 'ep_envision_menu_mysql';

		if (!empty($id) && $row['id_button'] != $id)
			$post_errors['name'] = 'ep_envision_menu_mysql';

		if (empty($post_errors))
		{
			// I see you made it to the final stage, my young padawan.
			if (!empty($id))
			{
				// Ok, looks like we're modifying, so let's edit the existing page!
				$smcFunc['db_query']('','
					UPDATE {db_prefix}ep_envision_menu
					SET name = {string:name}, type = {string:type}, target = {string:target}, position = {string:position}, link = {string:link}, status = {string:status}, permissions = {string:permissions}, parent = {string:parent}
					WHERE id_button = {int:id}',
					array(
						'id' => (int)$id,
						'name' => $name,
						'type' => $type,
						'target' => $target,
						'position' => $position,
						'link' => $link,
						'status' => $status,
						'permissions' => $permissions,
						'parent' => $parent,
					)
				);

				redirectexit('action=admin;area=epmenu');
			}
			else
			{
				// Adding a brand new page? Ok!
				$smcFunc['db_insert']('insert',
					'{db_prefix}ep_envision_menu',
						array(
							'slug' => 'string', 'name' => 'string', 'type' => 'string', 'target' => 'string', 'position' => 'string', 'link' => 'string', 'status' => 'string', 'permissions' => 'string', 'parent' => 'string',
						),
						array(
							create_slug($name), $name, $type, $target, $position, $link, $status, $permissions, $parent,
						),
						array('id_button')
					);

					redirectexit('action=admin;area=epmenu');
			}
		}
		else
		{
			$context['post_error'] = $post_errors;
			$context['error_title'] = empty($id) ? 'ep_envision_menu_errors_create' : 'ep_envision_menu_errors_modify';

			// Needed for ListGroups()
			require_once($sourcedir . '/ep_source/ManageEnvisionModules.php');

			$context['button_data'] = array(
				'name' => $name,
				'type' => $type,
				'target' => $target,
				'position' => $position,
				'link' => $link,
				'parent' => $parent,
				'permissions' => ListGroups(!empty($_POST['permissions']) ? $_POST['permissions'] : array()),
				'status' => $status,
				'id' => $id,
			);

			$context['page_title'] = $txt['ep_envision_menu_edit_title'];
		}
	}
}
/**
 * Prepares theme context for the template.
 *
 * @since 1.0
 */
function PrepareContext()
{
	global $context, $smcFunc, $txt, $sourcedir;

	// Load SMF's default menu context
	setupMenuContext();

	// Needed for ListGroups()
	require_once($sourcedir . '/ep_source/ManageEnvisionModules.php');

	if (isset($_GET['bid']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT name, target, type, position, link, status, permissions, parent
			FROM {db_prefix}ep_envision_menu
			WHERE id_button = {int:button}
			LIMIT 1',
			array(
				'button' => (int) $_GET['bid'],
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);

		$context['button_data'] = array(
			'id' => $_GET['bid'],
			'name' => $row['name'],
			'target' => $row['target'],
			'type' => $row['type'],
			'position' => $row['position'],
			'permissions' => ListGroups(explode(',', $row['permissions'])),
			'link' => $row['link'],
			'status' => $row['status'],
			'parent' => $row['parent'],
		);
	}
	else
	{
		$context['button_data'] = array(
			'name' => '',
			'link' => '',
			'target' => '_self',
			'type' => 'forum',
			'position' => 'before',
			'status' => 'active',
			'permissions' => ListGroups(array('-3'), array(), array(), 0),
			'parent' => 'home',
			'id' => 0,
		);

		$context['page_title'] = $txt['ep_envision_menu_add_title'];
	}
}

function list_getMenu($start, $items_per_page, $sort)
{
	global $smcFunc, $txt, $scripturl;

	$request = $smcFunc['db_query']('', '
		SELECT id_button, name, target, type, position, link, status, permissions, parent
		FROM {db_prefix}ep_envision_menu AS men
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$envision_menu = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$envision_menu[] = $row;

	return $envision_menu;
}

function list_getNumButtons()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ep_envision_menu',
		array(
		)
	);

	list ($numButtons) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numButtons;
}

function remove_accent($str)
{
	$a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');

	$b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');

	return str_replace($a, $b, $str);
}

function create_slug($str)
{
	return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), remove_accent($str)));
}

?>