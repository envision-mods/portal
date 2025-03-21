<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ManageEnvisionMenuTest extends TestCase
{
    private $manageEnvisionButtons;

	public static function setUpBeforeClass(): void
	{
		TestObj::$pdo->exec('CREATE TABLE ep_menu (
			id_button INTEGER PRIMARY KEY,
			name TEXT,
			target TEXT,
			type TEXT,
			position TEXT,
			permissions TEXT,
			link TEXT,
			status TEXT,
			parent TEXT
		)');
	}

	public static function tearDownAfterClass(): void
	{
		TestObj::$pdo->exec('DROP TABLE ep_menu');
	}

	public function tearDown(): void
	{
        global $context;

		TestObj::$pdo->exec('DELETE FROM ep_menu');
		TestObj::$pdo->exec('DELETE FROM SQLITE_SEQUENCE where name=\'ep_menu\'');

		$_GET = [];
		$_POST = [];
		unset($context['data']);
		unset($context['post_error']);
	}

	protected function setUp(): void
	{
		$stmt = TestObj::$pdo->prepare('
			INSERT INTO ep_menu (name, target, type, position, permissions, link, status, parent)
			VALUES (:name, :target, :type, :position, :permissions, :link, :status, :parent)
		');
		$stmt->execute([
			'name' => 'Test Button',
			'target' => '_blank',
			'type' => 'link',
			'position' => 'top',
			'permissions' => '1,2,3',
			'link' => 'http://example.com',
			'status' => 'active',
			'parent' => 'parent'
		]);
		$stmt->execute([
			'name' => 'Second Button',
			'target' => '_blank',
			'type' => 'link',
			'position' => 'top',
			'permissions' => '1,2,3',
			'link' => 'http://example.com',
			'status' => 'active',
			'parent' => 'parent'
		]);
		$stmt->execute([
			'name' => 'Third Button',
			'target' => '_blank',
			'type' => 'link',
			'position' => 'top',
			'permissions' => '1,2,3',
			'link' => 'http://example.com',
			'status' => 'active',
			'parent' => 'parent'
		]);

        $this->manageEnvisionButtons = $this->getMockBuilder(EnvisionPortal\ManageEnvisionMenu::class)
                ->disableOriginalConstructor()
            ->onlyMethods(['getInput'])
            ->getMock();
	}

    public function t4estManageMenu()
    {
        $manageEnvisionButtons = new EnvisionPortal\ManageEnvisionMenu('');
        $this->assertArrayHasKey('button_title', $context);
        $this->assertEquals('Admin Menu Title', $context['button_title']);
        $this->assertArrayHasKey('sub_template', $GLOBALS['context']);
        $this->assertEquals('show_list', $GLOBALS['context']['sub_template']);
    }

    public function testSaveButton(): void
    {
        global $context;

        $data = [
            'name' => 'New Test Button',
            'parent' => 'test-button',
            'type' => 'internal',
            'link' => 'board=1',
            'permissions' => ['1'],
            'status' => 'active',
        ];

        $_POST['submit'] = true;
        $this->manageEnvisionButtons->method('getInput')->willReturn($data);
        $this->manageEnvisionButtons->SaveButton();

        $this->assertArrayNotHasKey('button_data', $context);
        $this->assertArrayNotHasKey('post_error', $context);
		$buttons = EnvisionPortal\Button::fetchBy(['*'], []);
		$this->assertCount(4, $buttons);
		$this->assertInstanceOf(EnvisionPortal\Button::class, $buttons[3]);
		$this->assertEquals(['1'], $buttons[3]->permissions);
		$this->assertEquals('active', $buttons[3]->status);
    }

    public function testSaveButtonWithMissingData(): void
    {
        global $context;

        $_POST['submit'] = true;
        $this->manageEnvisionButtons->method('getInput')->willReturn([]);
        $this->manageEnvisionButtons->SaveButton();

        $this->assertArrayHasKey('post_error', $context);
        $this->assertArrayHasKey('name', $context['post_error']);
        $this->assertEquals('ep_menu_empty_name', $context['post_error']['name']);
        $this->assertArrayHasKey('button_data', $context);
        $this->assertEquals('', $context['button_data']['name']);
        $this->assertEquals('', $context['button_data']['parent']);
    }

    public function testEditButton(): void
    {
        global $context;
        $_GET['in'] = 1;

        $manageEnvisionButtons = new EnvisionPortal\ManageEnvisionMenu('editbutton');

        $this->assertArrayHasKey('button_data', $context);
        $this->assertArrayHasKey('name', $context['button_data']);
        $this->assertArrayHasKey('parent', $context['button_data']);
		$this->assertEquals(1, $context['button_data']['id']);
		$this->assertCount(3, $context['button_data']['permissions']);
		$this->assertArrayHasKey(2, $context['button_data']['permissions']);
		$this->assertEquals('active', $context['button_data']['status']);
    }

    public function testEditButtonWithInvalidId(): void
    {
        global $context;
        $_GET['in'] = 9999; // Assuming an invalid ID

        $this->expectException(Error::class);
        $manageEnvisionButtons = new EnvisionPortal\ManageEnvisionMenu('editbutton');

        $this->assertArrayNotHasKey('button_data', $context);
    }

    public function testAddButton(): void
    {
        global $context;

        $manageEnvisionButtons = new EnvisionPortal\ManageEnvisionMenu('addbutton');

        $this->assertArrayHasKey('button_data', $context);
        $this->assertEquals('', $context['button_data']['name']);
    }

	public function testRemoveAll(): void
	{
		$_POST['removeAll'] = true;
		$this->manageEnvisionButtons->ManageMenu();

		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu');
		$count = $stmt->fetchColumn();
		$this->assertEquals(0, $count, 'All buttons should be deleted');
    }

	public function testRemoveMany(): void
	{
		$_POST = ['removeButtons' => true, 'remove' => ['1', '3r', '2']];
		$this->manageEnvisionButtons->ManageMenu();

		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu');
		$count = $stmt->fetchColumn();
		$this->assertEquals(1, $count, 'Only one button should remain after removal');
    }

	public function testChangeStatus(): void
	{
		$_POST = ['save' => true, 'status' => ['2' => 'on']];
		$this->manageEnvisionButtons->ManageMenu();

		$buttons = EnvisionPortal\Button::fetchBy(['id_button', 'status']);
		$this->assertEquals('inactive', $buttons[0]['status'],'nnnnnnnnnnnnnn');
		$this->assertEquals('active', $buttons[1]['status']);
		$this->assertEquals('inactive', $buttons[2]['status']);
	}
}
