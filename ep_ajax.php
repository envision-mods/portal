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
			WHERE page_name = {string:name}
			AND id_page != {int:id}',
			array(
				'name' => $_GET['pn'],
				'id' => $_GET['id'],
			)
		);

		$check = $smcFunc['db_num_rows']($query);

		$smcFunc['db_free_result']($query);

		echo $_GET['pn'] . (($check > 0 || empty($_GET['id'])) ? $txt['ep_pages_ajax_navailable'] : $txt['ep_pages_ajax_available']);
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
			WHERE name = {string:name}
			AND id_button != {int:id}',
			array(
				'name' => $_GET['bn'],
				'id' => $_GET['id'],
			)
		);

		$check = $smcFunc['db_num_rows']($query);
		
		$smcFunc['db_free_result']($query);
		
		echo $_GET['bn'] . (($check > 0 || empty($_GET['id'])) ? $txt['ep_pages_ajax_navailable'] : $txt['ep_pages_ajax_available']);
	}
}
else
	redirectexit();

?>