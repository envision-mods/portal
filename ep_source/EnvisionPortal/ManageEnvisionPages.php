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
class ManageEnvisionPages
{
	private DataMapper $dataMapper;

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
		$this->dataMapper = new DataMapper;

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
			$entries = $this->dataMapper->fetchBy(
				['id_page', 'name', 'type', 'slug', 'status', 'description']
			);

			foreach ($entries as $item) {
				$status = !empty($updates['status'][$item['id_page']]) ? 'active' : 'inactive';

				if ($status != $item['status']) {
				}
			}

			redirectexit('action=admin;area=eppages');
		}

		$this->listPages();
	}

	private function listPages(): void
	{
		global $context, $txt, $scripturl, $sourcedir;

		$entries = $this->dataMapper->fetchBy(
			['id_page', 'name', 'type', 'slug', 'status', 'description']
		);

		$listOptions = [
			'id' => 'list',
			'items_per_page' => 20,
			'base_href' => $scripturl . '?action=admin;area=eppages;sa=manmenu',
			'default_sort_col' => 'name',
			'default_sort_dir' => 'desc',
			'get_items' => [
				'function' => [Util::class, 'process'],
				'params' => [
					$entries,
				],
			],
			'get_count' => [
				'function' => fn() => count($entries),
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
						'function' => fn(Page $rowData): string => sprintf(
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
						'function' => fn(Page $rowData): string => sprintf(
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
		$context['default_list'] = 'list';
	}

	private function getInput(): array
	{
		$member_groups = Util::listGroups([-3]);
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
			$post_errors[] = 'envision_pages_numeric';
		}

		// Let's make sure you're not trying to make a slug that's already taken.
		$entries = $this->dataMapper->fetchBy(
			['id_page'],
			[
				'slug' => $data['slug'],
				'id' => $data['in'] ?: 0,
			], [], ['slug = {string:slug}', 'id_page != {int:id}']
		);
		if ($entries != []) {
			$post_errors[] = 'envision_pages_mysql';
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
			if ($post_errors === []) {
				/*
				 * Check specifically for four-byte characters.
				 *
				 * UTF-8 has single bytes (0-127), leading bytes (192-254) and continuation
				 * bytes (128-191).  The leading byte precedes up to three continuation bytes.
				 * The algorithm for UTF-8 always consumes bytes in the same order no matter
				 * what CPU it is running on.
				 *
				 * Unicode code points  Encoding  Binary value
				 * -------------------  --------  ------------
				 *  U+000000-U+00007f   0xxxxxxx  0xxxxxxx
				 *
				 *  U+000080-U+0007ff   110yyyxx  00000yyy xxxxxxxx
				 *                      10xxxxxx
				 *
				 *  U+000800-U+00ffff   1110yyyy  yyyyyyyy xxxxxxxx
				 *                      10yyyyxx
				 *                      10xxxxxx
				 *
				 *  U+010000-U+10ffff   11110zzz  000zzzzz yyyyyyyy xxxxxxxx
				 *                      10zzyyyy
				 *                      10yyyyxx
				 *                      10xxxxxx
				 */
				foreach (['name', 'description', 'body'] as $el) {
					$data[$el] = preg_replace_callback(
						'/[\xF0-\xF4][\x80-\xbf]{3}/',
						function ($m) {
							$val = (ord($m[0][0]) & 0x07) << 18;
							$val += (ord($m[0][1]) & 0x3F) << 12;
							$val += (ord($m[0][2]) & 0x3F) << 6;
							$val += ord($m[0][3]) & 0x3F;

							return '&#' . $val . ';';
						},
						$data[$el]
					);
				}

				$page = new Page(
					(int)$data['in'],
					$data['slug'],
					$data['name'],
					$data['type'],
					$data['body'],
					array_map('intval', array_filter(explode(',', $data['permissions']), 'is_string')),
					$data['status'],
					$data['description'],
					0
				);

				if ($page->getId() == 0) {
					$this->dataMapper->insert($page);
				} else {
					$this->dataMapper->update($page);
				}
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
					'permissions' => Util::listGroups(
						array_filter($data['permissions'], 'is_string')
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

		if (!isset($_GET['in'])) {
			fatal_lang_error('no_access', false);
		}

		$entries = $this->dataMapper->fetchBy(['*'], ['id' => $_GET['in']], [], ['id_page = {int:id}']);
		$row = $entries[0] ?? [];
		if ($row == []) {
			fatal_lang_error('no_access', false);
		}

		$context['data'] = [
			'id' => $row['id_page'],
			'name' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['name']),
			'slug' => $row['slug'],
			'type' => $row['type'],
			'types' => $this->getTypes(),
			'permissions' => Util::listGroups(explode(',', $row['permissions'])),
			'body' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['body']),
			'status' => $row['status'],
			'description' => preg_replace_callback('/&#([1-9][0-9]{4,6});/', 'fixchar__callback', $row['description']),
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
			'permissions' => Util::listGroups([-3]),
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
					PageModeInterface::class
				)
			)
		);
	}
}