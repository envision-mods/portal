<?php
/**************************************************************************************
* ep_ajax.php                                                                         *
***************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

require_once(dirname(__FILE__) . '/SSI.php');

if (isset($_GET['check']))
{
	// Make sure they're an admin-- we don't want outsiders using this script as a hacking tool...
	isAllowedTo('admin_forum');

	if (isset($_GET['pn']))
	{
		// Let's make sure you're not trying to make a page name that's already taken.
		$query = $smcFunc['db_query']('', '
			SELECT id_page
			FROM {db_prefix}ep_envision_pages
			WHERE page_name = {string:name}',
			array(
				'name' => $_GET['pn']
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$row = $smcFunc['db_fetch_assoc']($query);

		if ($check != 0 && empty($_GET['id']))
			$ret = $_GET['pn'] . $txt['ep_pages_ajax_navailable'];
		elseif ($check != 0 && !empty($_GET['id']) && $row['id_page'] != $_GET['id'])
			$ret = $_GET['pn'] . $txt['ep_pages_ajax_navailable'];
		else
			$ret = $_GET['pn'] . $txt['ep_pages_ajax_available'];

		echo $ret;
	}
}
elseif (isset($_GET['button']))
{
	// Make sure they're an admin-- we don't want outsiders using this script as a hacking tool...
	isAllowedTo('admin_forum');

	if (isset($_GET['bn']))
	{
		// Let's make sure you're not trying to make a page name that's already taken.
		$query = $smcFunc['db_query']('', '
			SELECT id_button
			FROM {db_prefix}ep_envision_menu
			WHERE name = {string:name}',
			array(
				'name' => $_GET['bn']
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$row = $smcFunc['db_fetch_assoc']($query);

		if ($check != 0 && empty($_GET['id']))
			$ret = $_GET['bn'] . $txt['ep_pages_ajax_navailable'];
		elseif (!empty($_GET['id']) && $row['id_button'] != $id)
			$ret = $_GET['bn'] . $txt['ep_pages_ajax_navailable'];
		else
			$ret = $_GET['bn'] . $txt['ep_pages_ajax_available'];

		echo $ret;
	}
}
else
	redirectexit();

?>