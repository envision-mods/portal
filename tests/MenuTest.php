<?php

declare(strict_types=1);

namespace EnvisionPortal\Tests;

use EnvisionPortal\Menu;

class MenuTest extends \PHPUnit\Framework\TestCase
{
	private array $menuButtons;

	protected function setUp(): void
	{
		// Initial menu structure for testing
		$this->menuButtons = [
			'home' => ['title' => 'Home', 'sub_buttons' => []],
			'about' => ['title' => 'About', 'sub_buttons' => []],
		];
	}

	/**
	 * Data provider for button insertion tests.
	 *
	 * @return array
	 */
	public static function buttonInsertionProvider(): array
	{
		return [
			'Insert before existing button' => [
				'button' => [
					'title' => 'New Button',
					'href' => '/new',
					'target' => '_self',
					'show' => true,
				],
				'parentKey' => 'home',
				'position' => 'before',
				'buttonKey' => 'new_button',
				'expectedOrder' => ['new_button', 'home', 'about'],
			],
			'Insert after existing button' => [
				'button' => [
					'title' => 'New Button',
					'href' => '/new',
					'target' => '_self',
					'show' => true,
				],
				'parentKey' => 'home',
				'position' => 'after',
				'buttonKey' => 'new_button',
				'expectedOrder' => ['home', 'new_button', 'about'],
			],
			'Insert as a child of an existing button' => [
				'button' => [
					'title' => 'Child Button',
					'href' => '/child',
					'target' => '_blank',
					'show' => true,
				],
				'parentKey' => 'home',
				'position' => 'child_of',
				'buttonKey' => 'child_button',
				'expectedOrder' => ['home', 'about'], // No change to top-level order
				'expectedChildren' => ['child_button'], // Verify child added
			],
		];
	}

	/**
	 * @dataProvider buttonInsertionProvider
	 */
	public function testButtonInsertion(
		array $button,
		string $parentKey,
		string $position,
		string $buttonKey,
		array $expectedOrder,
		array $expectedChildren = []
	): void {
		Menu::addMenuButton($button, $this->menuButtons, $parentKey, $position, $buttonKey);

		// Verify top-level button order
		$this->assertSame(
			$expectedOrder,
			array_keys($this->menuButtons),
			'The top-level button order is incorrect.'
		);

		// If children are expected, verify their existence
		if ($expectedChildren) {
			$this->assertArrayHasKey('sub_buttons', $this->menuButtons[$parentKey]);
			$this->assertSame(
				$expectedChildren,
				array_keys($this->menuButtons[$parentKey]['sub_buttons']),
				'The child buttons are not in the correct order.'
			);
		}
	}

	public function testReplayMenu(): void
	{
		global $context;

		$context = [];
		Menu::replay($this->menuButtons);

		// Verify the replayed menu is stored in the global context
		$this->assertArrayHasKey('replayed_menu_buttons', $context);
		$this->assertSame($this->menuButtons, $context['replayed_menu_buttons']);
	}

	public function testMainIntegration(): void
	{
		global $user_info, $scripturl, $modSettings;

		$modSettings = [
			'integrate_menu_buttons' => '',
			'ep_button_count' => 1,
			'ep_button_1' => json_encode([
				'name' => 'Dynamic Button',
				'type' => 'forum',
				'link' => 'index.php?action=dyn',
				'target' => '_self',
				'active' => true,
				'groups' => [1],
				'parent' => 'home',
				'position' => 'after',
			]),
		];

		$scripturl = '/forum/index.php';

		Menu::main($this->menuButtons);

		// Check if the dynamic button was added
		$this->assertArrayHasKey('ep_button_1', $this->menuButtons);
		$this->assertSame('Dynamic Button', $this->menuButtons['ep_button_1']['title']);
	}
}