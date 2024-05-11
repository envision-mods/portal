<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace EnvisionPortal;

/**
 * @internal
 */
class ManageEnvisionMenu
{
	private MenuHelper $um;

	public function __construct(string $sa)
	{
		global $context, $settings, $txt;

		isAllowedTo('admin_forum');

		$context['page_title'] = $txt['admin_menu_title'];
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $txt['admin_menu'],
			'description' => $txt['admin_menu_desc'],
			'tabs' => [
				'manmenu' => [
					'description' => $txt['admin_manage_menu_desc'],
				],
				'addbutton' => [
					'description' => $txt['admin_menu_add_button_desc'],
				],
			],
		];
		$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/admin.js"></script>';
		$this->um = new MenuHelper;

		$subActions = [
			'manmenu' => 'ManageMenu',
			'addbutton' => 'AddButton',
			'editbutton' => 'EditButton',
			'savebutton' => 'SaveButton',
		];
		call_user_func([$this, $subActions[$sa] ?? current($subActions)]);
	}

	public function ManageMenu(): void
	{
		// Get rid of all of em!
		if (isset($_POST['removeAll'])) {
			checkSession();
			$this->um->deleteallButtons();
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=epmenu');
		} // User pressed the 'remove selection button'.
		elseif (isset($_POST['removeButtons'], $_POST['remove']) && is_array($_POST['remove'])) {
			checkSession();
			$this->um->deleteButton(array_filter($_POST['remove'], 'ctype_digit'));
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=epmenu');
		} // Changing the status?
		elseif (isset($_POST['save'])) {
			checkSession();
			$this->um->updateButton($_POST);
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=epmenu');
		}

		$this->listButtons();
	}

	private function listButtons(): void
	{
		global $context, $txt, $scripturl, $sourcedir;

		$button_names = $this->um->getButtonNames();
		$listOptions = [
			'id' => 'menu_list',
			'items_per_page' => 20,
			'base_href' => $scripturl . '?action=admin;area=epmenu',
			'default_sort_col' => 'name',
			'default_sort_dir' => 'desc',
			'get_items' => [
				'function' => [$this->um, 'list_getMenu'],
			],
			'get_count' => [
				'function' => [$this->um, 'list_getNumButtons'],
			],
			'no_items_label' => $txt['ep_menu_no_buttons'],
			'columns' => [
				'name' => [
					'header' => [
						'value' => $txt['ep_menu_button_name'],
					],
					'data' => [
						'db_htmlsafe' => 'name',
					],
					'sort' => [
						'default' => 'name',
						'reverse' => 'name DESC',
					],
				],
				'type' => [
					'header' => [
						'value' => $txt['ep_menu_button_type'],
					],
					'data' => [
						'function' => fn($rowData): string => $txt['ep_menu_' . $rowData['type'] . '_link'],
					],
					'sort' => [
						'default' => 'type',
						'reverse' => 'type DESC',
					],
				],
				'position' => [
					'header' => [
						'value' => $txt['ep_menu_button_position'],
					],
					'data' => [
						'function' => fn(array $rowData): string => sprintf(
							'%s %s',
							$txt['ep_menu_' . $rowData['position']],
							isset($button_names[$rowData['parent']])
								? $button_names[$rowData['parent']][1]
								: ucwords($rowData['parent'])
						),
					],
					'sort' => [
						'default' => 'position',
						'reverse' => 'position DESC',
					],
				],
				'status' => [
					'header' => [
						'value' => $txt['ep_menu_button_active'],
						'class' => 'centertext',
					],
					'data' => [
						'function' => fn(array $rowData): string => sprintf(
							'<input type="checkbox" name="status[%1$s]" id="status_%1$s" value="%1$s"%2$s />',
							$rowData['id_button'],
							$rowData['status'] == 'inactive' ? '' : ' checked="checked"'
						),
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'status',
						'reverse' => 'status DESC',
					],
				],
				'actions' => [
					'header' => [
						'value' => $txt['ep_menu_actions'],
						'class' => 'centertext',
					],
					'data' => [
						'function' => fn(array $rowData): string => sprintf(
							'<a href="%s?action=admin;area=epmenu;sa=editbutton;in=%d" class="button_submit button">%s</a>',
							$scripturl,
							$rowData['id_button'],
							$txt['modify']
						),
						'class' => 'centertext',
					],
				],
				'check' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<input type="checkbox" name="remove[]" value="%d" class="input_check" />',
							'params' => [
								'id_button' => false,
							],
						],
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'href' => $scripturl . '?action=admin;area=epmenu;sa=manmenu',
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => sprintf(
						'
						<input type="submit" name="removeButtons" value="%s" onclick="return confirm(\'%s\');" class="button_submit button" />
						<input type="submit" name="removeAll" value="%s" onclick="return confirm(\'%s\');" class="button_submit button" />
						<a href="%s?action=admin;area=epmenu;sa=addbutton" class="button_submit button">%s</a>
						<input type="submit" name="save" value="%s" class="button_submit button" />',
						$txt['ep_menu_remove_selected'],
						$txt['ep_menu_remove_confirm'],
						$txt['ep_menu_remove_all'],
						$txt['ep_menu_remove_all_confirm'],
						$scripturl,
						$txt['ep_admin_add_button'],
						$txt['save']
					),
					'class' => 'righttext',
				],
			],
		];
		require_once $sourcedir . '/Subs-List.php';
		createList($listOptions);
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'menu_list';
	}

	private function getInput(): array
	{
		$member_groups = Util::listGroups([-3]);
		$button_names = $this->um->getButtonNames();
		$args = [
			'in' => FILTER_VALIDATE_INT,
			'name' => FILTER_UNSAFE_RAW,
			'position' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => in_array($v, ['before', 'child_of', 'after']) ? $v : false,
			],
			'parent' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => isset($button_names[$v]) ? $v : false,
			],
			'type' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => in_array($v, ['forum', 'external']) ? $v : false,
			],
			'link' => FILTER_UNSAFE_RAW,
			'permissions' => [
				'filter' => FILTER_CALLBACK,
				'flags' => FILTER_REQUIRE_ARRAY,
				'options' => fn($v) => isset($member_groups[$v]) ? $v : false,
			],
			'status' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => in_array($v, ['active', 'inactive']) ? $v : false,
			],
			'target' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => in_array($v, ['_self', '_blank']) ? $v : false,
			],
		];

		return filter_input_array(INPUT_POST, $args, false) ?: [];
	}

	private function validateInput(array $data): array
	{
		$post_errors = [];
		$required_fields = [
			'name',
			'link',
			'parent',
		];

		// If your session timed out, show an error, but do allow to re-submit.
		if (checkSession('post', '', false) != '') {
			$post_errors[] = 'ep_menu_session_verify_fail';
		}

		// These fields are required!
		foreach ($required_fields as $required_field) {
			if (empty($data[$required_field])) {
				$post_errors[$required_field] = 'ep_menu_empty_' . $required_field;
			}
		}

		// Stop making numeric names!
		if (is_numeric($data['name'])) {
			$post_errors['name'] = 'ep_menu_numeric';
		}

		// Let's make sure you're not trying to make a name that's already taken.
		if (!empty($this->um->checkButton($data['in'], $data['name']))) {
			$post_errors['name'] = 'ep_menu_mysql';
		}

		return $post_errors;
	}

	public function SaveButton(): void
	{
		global $context, $txt;

		if (isset($_POST['submit'])) {
			$data = array_replace(
				[
					'target' => '_self',
					'type' => 'forum',
					'position' => 'before',
					'permissions' => [],
					'status' => 'active',
					'parent' => '',
				],
				$this->getInput()
			);
			$post_errors = $this->validateInput($data);

			// I see you made it to the final stage, my young padawan.
			if (empty($post_errors)) {
				$this->um->saveButton($data);
				$this->um->rebuildMenu();

				// Before we leave, we must clear the cache. See, SMF
				// caches its menu at level 2 or higher.
				clean_cache('menu_buttons');

				redirectexit('action=admin;area=epmenu');
			} else {
				$context['page_title'] = $txt['ep_menu_edit_title'];
				$context['button_names'] = $this->um->getButtonNames();
				$context['post_error'] = $post_errors;
				$context['error_title'] = empty($data['in'])
					? 'ep_menu_errors_create'
					: 'ep_menu_errors_modify';
				$context['button_data'] = [
					'name' => $data['name'],
					'type' => $data['type'],
					'target' => $data['target'],
					'position' => $data['position'],
					'link' => $data['link'],
					'parent' => $data['parent'],
					'permissions' => Util::listGroups(
						array_filter($data['permissions'], 'strlen')
					),
					'status' => $data['status'],
					'id' => $data['in'],
				];
				$context['template_layers'][] = 'form';
				$context['template_layers'][] = 'errors';
			}
		} else {
			fatal_lang_error('no_access', false);
		}
	}

	public function EditButton(): void
	{
		global $context, $txt;

		$row = isset($_GET['in']) ? $this->um->fetchButton($_GET['in']) : [];
		if (empty($row)) {
			fatal_lang_error('no_access', false);
		}

		$context['button_data'] = [
			'id' => $row['id'],
			'name' => $row['name'],
			'target' => $row['target'],
			'type' => $row['type'],
			'position' => $row['position'],
			'permissions' => Util::listGroups($row['permissions']),
			'link' => $row['link'],
			'status' => $row['status'],
			'parent' => $row['parent'],
		];
		$context['page_title'] = $txt['ep_menu_edit_title'];
		$context['button_names'] = $this->um->getButtonNames();
		$context['template_layers'][] = 'form';
	}

	public function AddButton(): void
	{
		global $context, $txt;

		$context['button_data'] = [
			'name' => 'ep_ep_',
			'link' => '',
			'target' => '_self',
			'type' => 'forum',
			'position' => 'before',
			'status' => 'active',
			'permissions' => Util::listGroups([-3]),
			'parent' => 'home',
			'id' => 0,
		];
		$context['page_title'] = $txt['ep_menu_add_title'];
		$context['button_names'] = $this->um->getButtonNames();
		$context['template_layers'][] = 'form';
	}
}
