<?php
/**************************************************************************************
* ManageEnvisionPages.php                                                             *
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
 * @see ManageEnvisionPages.template.php
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
function Pages()
{
	global $context, $txt;

	loadTemplate('ep_template/ManageEnvisionPages');
	loadLanguage('ep_languages/ManageEnvisionPages');

	$subActions = array(
		'epmanpages' => array('ManageEnvisionPages', 'admin_forum'),
		'epadepage' => array('prepareContext', 'admin_forum'),
		'epsavepage' => array('SavePage', 'admin_forum'),
	);

	// Default to sub action 'epmanpages'
	if (!isset($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'epmanpages';

	// Have you got the proper permissions?
	if (!empty($subActions[$_GET['sa']][1]))
		isAllowedTo($subActions[$_GET['sa']][1]);

	$context['page_title'] = $txt['ep_admin_pages_title'];

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['ep_admin_pages'],
		'description' => $txt['ep_admin_pages_desc'],
		'tabs' => array(
			'epmanpages' => array(
				'description' => $txt['ep_admin_pages_manpages_desc'],
			),
			'epadepage' => array(
				'description' => $txt['ep_admin_pages_adepage_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_GET['sa']][0]();

}

/**
 * Manages existing Envision Pages.
 *
 * @since 1.0
 */
function ManageEnvisionPages()
{
	global $context, $txt, $modSettings, $scripturl, $sourcedir, $smcFunc;

	// Get rid of all of em!
	if (!empty($_POST['removeAll']))
	{
		checkSession();

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}ep_envision_pages');
	}

	// User pressed the 'remove selection button'.
	if (!empty($_POST['removePages']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		// Delete the page!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ep_envision_pages
			WHERE id_page IN ({array_int:page_list})',
			array(
				'page_list' => $_POST['remove'],
			)
		);
	}

	// Our options for our list.
	$listOptions = array(
		'id' => 'ep_page_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=eppages;sa=epmanpages',
		'default_sort_col' => 'id_page',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => 'list_getPages',
		),
		'get_count' => array(
			'function' => 'list_getNumPages',
		),
		'no_items_label' => $txt['ep_envision_pages_no_page'],
		'columns' => array(
			'id_page' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_id'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?page=%1$d" target="_blank">%1$d</a>',
						'params' => array(
							'id_page' => false,
						),
					),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'epp.id_page',
					'reverse' => 'epp.id_page DESC',
				),
			),
			'page_name' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_name'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?page=%1$s" target="_blank">%1$s</a>',
						'params' => array(
							'page_name' => false,
						),
					),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'epp.page_name',
					'reverse' => 'epp.page_num DESC',
				),
			),
			'type' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_type'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// The possible types a page can be.
						$types = array(
							0 => $txt[\'ep_envision_pages_page_php\'],
							1 => $txt[\'ep_envision_pages_page_html\'],
							2 => $txt[\'ep_envision_pages_page_bbc\'],
						);

						// Return what type they\'re using.
						return $types[$rowData[\'type\']];
					'),
					'class' => 'smalltext centertext',
				),
				'sort' => array(
					'default' => 'epp.type',
					'reverse' => 'epp.type DESC',
				),
			),
			'title' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_title'],
				),
				'data' => array(
					'db_htmlsafe' => 'title',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'epp.title',
					'reverse' => 'epp.title DESC',
				),
			),
			'page_views' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_views'],
				),
				'data' => array(
					'db' => 'page_views',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'epp.page_views',
					'reverse' => 'epp.page_views DESC',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_page_status'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// Tell them the status of their page.
						if ($rowData[\'status\'])
							return sprintf(\'<span style="color: green;">%1$s</span>\', $txt[\'ep_envision_pages_page_active\']);
						else
							return sprintf(\'<span style="color: red;">%1$s</span>\', $txt[\'ep_envision_pages_page_nactive\']);
					'),
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'epp.status',
					'reverse' => 'epp.status DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['ep_envision_pages_actions'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=eppages;sa=epadepage;edit;pid=%1$d">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_page' => false,
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
							'id_page' => false,
						),
					),
					'class' => 'centertext',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=eppages;sa=epmanpages',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="' . $txt['ep_envision_pages_remove_pages_button'] . '" value="' . $txt['ep_envision_pages_remove_selected'] . '" onclick="return confirm(\'' . $txt['ep_envision_pages_remove_confirm'] . '\');" class="button_submit" />
					<input type="submit" name="' . $txt['ep_envision_pages_remove_all_button'] . '" value="' . $txt['ep_envision_pages_remove_all'] . '" onclick="return confirm(\'' . $txt['ep_envision_pages_remove_all_confirm'] . '\');" class="button_submit" />',
					'class' => 'righttext',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'ep_page_list';
}
/**
 * Saves a Envision Page. Handles creating a new page or modilying an existing one.
 *
 * @todo Transfer existing functionality to createPage() and modifyPage().
 * @since 1.0
 */
function SavePage()
{
	global $context, $smcFunc, $txt, $sourcedir;

	if (isset($_REQUEST['submit']))
	{
		$post_errors = array();
		$required_fields = array(
			'page_name',
			'title',
			'body',
		);

		// Make sure we grab all of the content
		$id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
		$name = isset($_REQUEST['page_name']) ? $_REQUEST['page_name'] : '';
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		$title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
		$groups = isset($_REQUEST['permissions']) ? implode(',', $_REQUEST['permissions']) : '';
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
		$body = isset($_REQUEST['body']) ? $_REQUEST['body'] : '';
		$header = isset($_REQUEST['ep_header']) ? $_REQUEST['ep_header'] : '';

		// These fields are required!
		foreach ($required_fields as $required_field)
			if ($_POST[$required_field] == '')
				$post_errors[$required_field] = 'ep_envision_pages_empty_' . $required_field;

		// Stop making numeric page names!
		if (is_numeric($name))
			$post_errors['page_name'] = 'ep_envision_pages_numeric';

		// Let's make sure you're not trying to make a page name that's already taken.
		$query = $smcFunc['db_query']('', '
			SELECT id_page
			FROM {db_prefix}ep_envision_pages
			WHERE page_name = {string:name}',
			array(
				'name' => $name
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$row = $smcFunc['db_fetch_assoc']($query);

		if ($check != 0 && empty($id))
			$post_errors['page_name'] = 'ep_envision_pages_mysql';

		if (!empty($id) && $row['id_page'] != $id)
			$post_errors['page_name'] = 'ep_envision_pages_mysql';

		if (empty($post_errors))
		{
			// I see you made it to the final stage, my young padawan.
			if (!empty($id))
			{
				// Ok, looks like we're modifying, so let's edit the existing page!
				$smcFunc['db_query']('','
					UPDATE {db_prefix}ep_envision_pages
					SET page_name = {string:name}, type = {int:type}, title = {string:title}, permissions = {string:groups}, status = {int:status}, header = {string:header}, body = {string:body}
					WHERE id_page = {int:id}',
					array(
						'id' => (int) $id,
						'name' => $name,
						'type' => (int) $type,
						'title' => $title,
						'groups' => $groups,
						'status' => (int) $status,
						'header' => $header,
						'body' => $body,
					)
				);
			}
			else
			{
				// Adding a brand new page? Ok!
				$smcFunc['db_insert']('insert',
					'{db_prefix}ep_envision_pages',
					array(
						'page_name' => 'string-255', 'type' => 'int', 'title' => 'string-255', 'permissions' => 'string-255', 'status' => 'int', 'header' => 'string', 'body' => 'string'
					),
					array(
						$name, (int) $type, $title, $groups, (int) $status, $header, $body
					),
					array('id_page')
				);
			}

			redirectexit('action=admin;area=eppages');

		}
		else
		{
			$context['post_error'] = $post_errors;
			$context['error_title'] = empty($id) ? 'ep_envision_pages_errors_create' : 'ep_envision_pages_errors_modify';

			// Now create the editor.
			$editorOptions_header = array(
				'id' => 'ep_header',
				'labels' => array(
				),
				'value' => $header,
				'height' => '250px',
				'width' => '100%',
				'preview_type' => 2,
				'rich_active' => false,
			);
			$editorOptions_body = array(
				'id' => 'body',
				'labels' => array(
				),
				'value' => $body,
				'height' => '250px',
				'width' => '100%',
				'preview_type' => 2,
				'rich_active' => false,
			);


			// Needed for ep_list_groups()
			require_once($sourcedir . '/ep_source/ManageEnvisionModules.php');

			// Needed for the editor.
			require_once($sourcedir . '/Subs-Editor.php');

			$context['page_data'] = array(
				'page_name' => $name,
				'type' => $type,
				'title' => $title,
				'permissions' => ep_list_groups(!empty($_POST['permissions']) ? $_POST['permissions'] : array()),
				'status' => $status,
				'id' => $id,
			);
			
			create_control_richedit($editorOptions_header);
			create_control_richedit($editorOptions_body);
			$context['ep_header_content'] = $editorOptions_header['id'];
			$context['page_content'] = $editorOptions_body['id'];
			$context['page_title'] = $txt['ep_envision_pages_edit_title'];
		}
	}
}
/**
 * Prepares theme context for the template.
 *
 * @since 1.0
 */
function prepareContext()
{
	global $context, $smcFunc, $txt, $sourcedir;

	// Needed for ep_list_groups()
	require_once($sourcedir . '/ep_source/ManageEnvisionModules.php');

	// Needed for the editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions_header = array(
		'id' => 'ep_header',
		'labels' => array(
		),
		'height' => '250px',
		'width' => '100%',
		'preview_type' => 2,
		'rich_active' => false,
	);
	$editorOptions_body = array(
		'id' => 'body',
		'labels' => array(
		),
		'height' => '250px',
		'width' => '100%',
		'preview_type' => 2,
		'rich_active' => false,
	);

	if (isset($_GET['pid']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT page_name, type, title, body, permissions, status, header
			FROM {db_prefix}ep_envision_pages
			WHERE id_page = {int:page}
			LIMIT 1',
			array(
				'page' => (int) $_GET['pid'],
			)
		);

		// If nothing gets returned, exit... right now.
		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error($txt['ep_envision_pages_not_found']);

		$row = $smcFunc['db_fetch_assoc']($request);

		$smcFunc['db_free_result']($request);
		
		$context['page_data'] = array(
			'page_name' => $row['page_name'],
			'type' => $row['type'],
			'title' => $row['title'],
			'permissions' => ep_list_groups($row['permissions']),
			'status' => $row['status'],
			'id' => $_GET['pid'],
		);

		$editorOptions_header['value'] = $row['header'];
		$editorOptions_body['value'] = $row['body'];

		$context['page_title'] = $txt['ep_envision_pages_edit_title'];
	}
	else
	{
		$context['page_data'] = array(
			'page_name' => '',
			'type' => 2,
			'title' => '',
			'permissions' => ep_list_groups('-3'),
			'status' => 1,
			'id' => 0,
		);

		$editorOptions_header['value'] = '';
		$editorOptions_body['value'] = '';

		$context['page_title'] = $txt['ep_envision_pages_add_title'];
	}

	create_control_richedit($editorOptions_header);
	create_control_richedit($editorOptions_body);
	$context['ep_header_content'] = $editorOptions_header['id'];
	$context['page_content'] = $editorOptions_body['id'];
}

/**
 * Loads the list of Envision Pages for createList().
 *
 * @param int $start determines where to start getting pages. Used in SQL's LIMIT clause.
 * @param int $items_per_page determines how many pages are returned. Used in SQL's LIMIT clause.
 * @param string $sort determines which column to sort by. Used in SQL's ORDER BY clause.
 * @return array the associative array returned by $smcFunc['db_fetch_assoc']().
 * @since 1.0
 */
function list_getPages($start, $items_per_page, $sort)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT epp.id_page, epp.page_name, epp.type, epp.title, epp.page_views, epp.status
		FROM {db_prefix}ep_envision_pages AS epp
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$ep_pages = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$ep_pages[] = $row;

	$smcFunc['db_free_result']($request);

	return $ep_pages;
}

/**
 * Gets the total number of Envision Pages for createList().
 *
 * @return int the total number of Envision Pages
 * @since 1.0
 */
function list_getNumPages()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS num_pages
		FROM {db_prefix}ep_envision_pages',
		array(
		)
	);

	list ($numPages) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numPages;
}

?>