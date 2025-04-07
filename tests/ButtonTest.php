<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
	private $button;

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
		TestObj::$pdo->exec('DELETE FROM ep_menu');
		TestObj::$pdo->exec('DELETE FROM SQLITE_SEQUENCE where name=\'ep_menu\'');
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

		$this->button = new \EnvisionPortal\Button(
			1,
			'Test Button',
			'_blank',
			'link',
			'top',
			[1, 2, 3],
			'http://example.com',
			'active',
			'parent'
		);
	}

	public function testButtonProperties()
	{
		$this->assertEquals(1, $this->button->id);
		$this->assertEquals('Test Button', $this->button->name);
		$this->assertEquals('_blank', $this->button->target);
		$this->assertEquals('link', $this->button->type);
		$this->assertEquals('top', $this->button->position);
		$this->assertEquals([1, 2, 3], $this->button->permissions);
		$this->assertEquals('http://example.com', $this->button->link);
		$this->assertEquals('active', $this->button->status);
		$this->assertEquals('parent', $this->button->parent);
	}

	public function testFetchBy(): void
	{
		$buttons = EnvisionPortal\Button::fetchBy(['id_button', 'name'], []);
		$this->assertIsArray($buttons);
		$this->assertCount(3, $buttons);

		$this->assertInstanceOf(EnvisionPortal\Button::class, $buttons[0]);
		$this->assertEquals(1, $buttons[0]->id);
		$this->assertEquals('Test Button', $buttons[0]->name);

		$this->assertInstanceOf(EnvisionPortal\Button::class, $buttons[1]);
		$this->assertEquals(2, $buttons[1]->id);
		$this->assertEquals('Second Button', $buttons[1]->name);

		$this->assertInstanceOf(EnvisionPortal\Button::class, $buttons[2]);
		$this->assertEquals(3, $buttons[2]->id);
		$this->assertEquals('Third Button', $buttons[2]->name);
	}

	public function testInsert(): void
	{
		$this->button->insert();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu');
		$count = $stmt->fetchColumn();
		$this->assertEquals(4, $count);
		$this->assertEquals('{db_prefix}ep_menu', TestObj::$last_insert[1]);
		$this->assertArrayHasKey('name', TestObj::$last_insert[2]);
		$this->assertStringContainsString('INSERT INTO {db_prefix}ep_menu', TestObj::$last_query);

		$buttons = EnvisionPortal\Button::fetchBy(['id_button', 'name'], ['id_button = 4']);
		$this->assertEquals('Test Button', $buttons[0]->name);
	}

	public function testUpdate(): void
	{
		$this->button->name = 'Updated Button';
		$this->button->update();

		$stmt = TestObj::$pdo->query('SELECT name FROM ep_menu WHERE id_button = 1');
		$name = $stmt->fetchColumn();
		$this->assertEquals('Updated Button', $name);
		$this->assertStringContainsString('UPDATE {db_prefix}ep_menu SET', TestObj::$last_query);
	}

	public function testDelete(): void
	{
		$this->button->delete();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu WHERE id_button = 1');
		$count = $stmt->fetchColumn();
		$this->assertEquals(0, $count);
		$this->assertStringContainsString('DELETE FROM {db_prefix}ep_menu', TestObj::$last_query);
	}

	public function testDeleteMany(): void
	{
		EnvisionPortal\Button::deleteMany([2, 3]);
		$this->assertStringContainsString('DELETE FROM {db_prefix}ep_menu WHERE', TestObj::$last_query);
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu');
		$count = $stmt->fetchColumn();
		$this->assertEquals(1, $count);
	}

	public function testDeleteAll(): void
	{
		EnvisionPortal\Button::deleteAll();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM ep_menu');
		$count = $stmt->fetchColumn();
		$this->assertEquals(0, $count);
		$this->assertStringContainsString('TRUNCATE {db_prefix}ep_menu', TestObj::$last_query);
	}
}
