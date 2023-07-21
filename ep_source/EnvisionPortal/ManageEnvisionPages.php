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

class ManageEnvisionPages
{
	private PagesHelper $um;

	public function __construct(string $sa)
	{
		global $context, $settings, $txt;

		isAllowedTo('admin_html');

		$context['page_title'] = $txt['admin_menu_title'];
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $txt['admin_menu'],
			'description' => $txt['admin_menu_description'],
			'tabs' => [
				'manpages' => [
					'description' => $txt['admin_manage_menu_description'],
				],
				'addpage' => [
					'description' => $txt['admin_menu_add_page_description'],
				],
			],
		];
		$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/ep_scripts/admin.js"></script>';
		$this->um = new PagesHelper;

		$subActions = [
			'manmenu' => 'ManageMenu',
			'addpage' => 'AddPage',
			'editpage' => 'EditPage',
			'savepage' => 'SavePage',
		];
		call_user_func([$this, $subActions[$sa] ?? current($subActions)]);
	}

	public function ManageMenu(): void
	{
		// Get rid of all of em!
		if (isset($_POST['removeAll'])) {
			checkSession();
			$this->um->deleteallPages();
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=eppages');
		} // User pressed the 'remove selection page'.
		elseif (isset($_POST['removePages'], $_POST['remove']) && is_array($_POST['remove'])) {
			checkSession();
			$this->um->deletePage(array_filter($_POST['remove'], 'ctype_digit'));
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=eppages');
		} // Changing the status?
		elseif (isset($_POST['save'])) {
			checkSession();
			$this->um->updatePage($_POST);
			$this->um->rebuildMenu();
			redirectexit('action=admin;area=eppages');
		}

		$this->listPages();
	}

	private function listPages(): void
	{
		global $context, $txt, $scripturl, $sourcedir;

		$listOptions = [
			'id' => 'menu_list',
			'items_per_page' => 20,
			'base_href' => $scripturl . '?action=admin;area=eppages;sa=manmenu',
			'default_sort_col' => 'name',
			'default_sort_dir' => 'description',
			'get_items' => [
				'function' => [$this->um, 'list_getMenu'],
			],
			'get_count' => [
				'function' => [$this->um, 'list_getNumPages'],
			],
			'no_items_label' => $txt['envision_pages_no_pages'],
			'columns' => [
				'name' => [
					'header' => [
						'value' => $txt['envision_pages_page_name'],
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
						'value' => $txt['envision_pages_page_type'],
					],
					'data' => [
						'function' => fn($rowData): string => $txt['envision_pages_' . Util::decamelize(
							substr($rowData['type'], strrpos($rowData['type'], '\\') ?: -1 + 1)
						)],
					],
					'sort' => [
						'default' => 'type',
						'reverse' => 'type DESC',
					],
				],
				'status' => [
					'header' => [
						'value' => $txt['envision_pages_page_active'],
						'class' => 'centertext',
					],
					'data' => [
						'function' => fn(array $rowData): string => sprintf(
							'<input type="checkbox" name="status[%1$s]" id="status_%1$s" value="%1$s"%2$s />',
							$rowData['id_page'],
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
					'data' => [
						'function' => fn(array $rowData): string => sprintf(
							'
								<a href="%s?page=%s" target="_blank" class="button_submit button">%s</a>
								<a href="%1$s?action=admin;area=eppages;sa=editpage;in=%d" class="button_submit button">%s</a>',
							$scripturl,
							$rowData['slug'],
							$txt['envision_pages_view'],
							$rowData['id_page'],
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
								'id_page' => false,
							],
						],
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'href' => $scripturl . '?action=admin;area=eppages;sa=manmenu',
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => sprintf(
						'
						<input type="submit" name="removePages" value="%s" onclick="return confirm(\'%s\');" class="button_submit button" />
						<input type="submit" name="removeAll" value="%s" onclick="return confirm(\'%s\');" class="button_submit button" />
						<a href="%s?action=admin;area=eppages;sa=addpage" class="button_submit button">%s</a>
						<input type="submit" name="save" value="%s" class="button_submit button" />',
						$txt['envision_pages_remove_selected'],
						$txt['envision_pages_remove_confirm'],
						$txt['envision_pages_remove_all'],
						$txt['envision_pages_remove_all_confirm'],
						$scripturl,
						$txt['ep_admin_add_page'],
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
		$member_groups = $this->um->listGroups([-3]);
		$args = [
			'in' => FILTER_VALIDATE_INT,
			'name' => FILTER_UNSAFE_RAW,
			'slug' => FILTER_UNSAFE_RAW,
			'description' => FILTER_UNSAFE_RAW,
			'body' => FILTER_UNSAFE_RAW,
			'type' => FILTER_UNSAFE_RAW,
			'permissions' => [
				'filter' => FILTER_CALLBACK,
				'flags' => FILTER_REQUIRE_ARRAY,
				'options' => fn($v) => isset($member_groups[$v]) ? $v : false,
			],
			'status' => [
				'filter' => FILTER_CALLBACK,
				'options' => fn($v) => in_array($v, ['active', 'inactive']) ? $v : false,
			],
		];

		return filter_input_array(INPUT_POST, $args, false) ?: [];
	}

	private function validateInput(array $data): array
	{
		$post_errors = [];
		$required_fields = [
			'name',
			'body',
			'description',
		];

		// If your session timed out, show an error, but do allow to re-submit.
		if (checkSession('post', '', false) != '') {
			$post_errors[] = 'envision_pages_session_verify_fail';
		}

		// These fields are required!
		foreach ($required_fields as $required_field) {
			if (empty($data[$required_field])) {
				$post_errors[$required_field] = 'envision_pages_empty_' . $required_field;
			}
		}

		// Stop making numeric slugs!
		if (is_numeric($data['slug'])) {
			$post_errors['slug'] = 'envision_pages_numeric';
		}

		// Let's make sure you're not trying to make a slug that's already taken.
		if (!empty($this->um->checkPage($data['in'], $data['slug']))) {
			$post_errors['slug'] = 'envision_pages_mysql';
		}

		return $post_errors;
	}

	public function SavePage(): void
	{
		global $context, $txt;

		if (isset($_POST['submit'])) {
			$data = array_replace(
				[
					'type' => 'HTML',
					'permissions' => [],
					'status' => 'active',
				],
				$this->getInput()
			);
			if (empty($data['slug'])) {
				require_once dirname(__DIR__) . '/vendor/autoload.php';
				$data['slug'] = (new \Cocur\Slugify\Slugify)->slugify($data['name']);
			}

			$post_errors = $this->validateInput($data);

			// I see you made it to the final stage, my young padawan.
			if (empty($post_errors)) {
				$this->um->savePage($data);
				redirectexit('action=admin;area=eppages');
			} else {
				$context['page_title'] = $txt['envision_pages_edit_title'];
				$context['post_error'] = $post_errors;
				$context['error_title'] = empty($data['in'])
					? 'envision_pages_errors_create'
					: 'envision_pages_errors_modify';
				$context['data'] = [
					'name' => $data['name'],
					'slug' => $data['slug'],
					'description' => $data['description'],
					'type' => $data['type'],
					'types' => $this->getTypes(),
					'body' => $data['body'],
					'permissions' => $this->um->listGroups(
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

	public function EditPage(): void
	{
		global $context, $txt;

		$row = isset($_GET['in']) ? $this->um->fetchPage($_GET['in']) : [];
		if (empty($row)) {
			fatal_lang_error('no_access', false);
		}

		$context['data'] = [
			'id' => $row['id'],
			'name' => $row['name'],
			'slug' => $row['slug'],
			'description' => $row['description'],
			'type' => $row['type'],
			'types' => $this->getTypes(),
			'permissions' => $this->um->listGroups($row['permissions']),
			'body' => $row['body'],
			'status' => $row['status'],
		];
		$context['page_title'] = $txt['envision_pages_edit_title'];
		$context['template_layers'][] = 'form';
	}

	public function AddPage(): void
	{
		global $context, $txt;

		$context['data'] = [
			'name' => '',
			'slug' => '',
			'description' => '',
			'body' => '',
			'type' => 'HTML',
			'types' => $this->getTypes(),
			'status' => 'active',
			'permissions' => $this->um->listGroups([-3]),
			'id' => 0,
		];
		$context['page_title'] = $txt['envision_pages_add_title'];
		$context['template_layers'][] = 'form';
	}

	private function getTypes(): array
	{
		return iterator_to_array(
			Util::map(
				fn($cn) => [
					str_replace('EnvisionPortal\PageModes\\', '', $cn),
					Util::decamelize(substr($cn, strrpos($cn, '\\') + 1)),
					new $cn,
				],
				Util::find_classes(
					new \GlobIterator(
						__DIR__ . '/PageModes/*.php',
						\FilesystemIterator::SKIP_DOTS
					),
					'EnvisionPortal\PageModes\\',
					'EnvisionPortal\PageMode'
				)
			)
		);
	}
}
