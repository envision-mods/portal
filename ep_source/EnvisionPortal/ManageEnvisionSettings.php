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
 * @internal
 */
class ManageEnvisionSettings implements ActionInterface, ProvidesSubActionInterface
{
	use ActionTrait;
	use ProvidesSubActionTrait;

	public function execute(): void
	{
	global $context, $txt;

	isAllowedTo('admin_forum');

	loadLanguage('ep_languages/EnvisionHelp+ManageSettings+ep_languages/ManageEnvisionSettings');

	if (isset($_REQUEST['xml'])) {
		$context['template_layers'] = [];
	}

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = [
		'title' => &$txt['ep_admin_config'],
		'help' => $txt['ep_admin_config_help'],
		'description' => $txt['ep_admin_config_desc'],
		'tabs' => [
			'epinfo' => [
				'description' => $txt['ep_admin_config_info_desc'],
			],
			'epgeneral' => [
				'description' => $txt['ep_admin_config_general_desc'],
			],
		],
	];

		$this->addSubAction('epinfo', [$this, 'epinfo']);
		$this->addSubAction('epgeneral', [$this, 'epgeneral']);

		$this->callSubAction($_REQUEST['sa'] ?? null);
	}

function epinfo()
{
	global $context, $txt;

	// Our credits info. =D
	$context['credits'] = [
		[
			'pretext' => $txt['ep_credits_info'],
			'groups' => [
				[
					'title' => $txt['ep_credits_groups_dev'],
					'members' => [
						'John &quot;live627&quot; Rayes',
					],
				],
				[
					'title' => $txt['ep_credits_special'],
					'members' => [
						$txt['ep_credits_all_friends'],
					],
				],
				[
					'title' => $txt['ep_credits_fugue'],
					'members' => [
						$txt['ep_credits_fugue_message'],
					],
				],
			],
			'posttext' => $txt['ep_credits_anyone'],
		],
	];

	$context['page_title'] = $txt['ep_admin_config_title'];
	$context['sub_template'] = 'portal_info';
	loadTemplate('ep_template/ManageEnvisionSettings', 'ep_css/admin');
}

	public static function getConfigVars(): array
	{
		global $txt;

		return [
		['check', 'ep_portal_mode', 'subtext' => $txt['ep_portal_mode_subtext']],
		$txt['ep_pages_title'],
		['check', 'ep_pages_mode', 'subtext' => $txt['ep_pages_subtext']],
		['callback', 'ep_admin_config'],
		[
			'permissions',
			'ep_view',
			'text_label' => $txt['ep_view_permissions'],
			'subtext' => $txt['ep_view_permissions_subtext']
		],
		$txt['ep_admin_config_modules'],
		['check', 'ep_collapse_modules'],
	];
	}

	public function epgeneral(): void
	{
		global $context, $scripturl, $sourcedir, $txt;

		$config_vars = self::getConfigVars();

	require_once($sourcedir . '/ManageServer.php');

	// Saving?
	if (isset($_GET['save'])) {
		checkSession();

		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=epconfig;sa=epgeneral');
	}

	$context['sub_template'] = 'show_settings';
	$context['post_url'] = $scripturl . '?action=admin;area=epconfig;save;sa=epgeneral';
	$context['settings_title'] = $txt['ep_admin_config_general'];
	$context['page_title'] = $txt['ep_admin_config_general'];
	loadTemplate('ep_template/ManageEnvisionSettings');

	prepareDBSettingContext($config_vars);
}
}