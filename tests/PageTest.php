<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		TestObj::$pdo->exec('CREATE TABLE envision_pages (
			id_page INTEGER PRIMARY KEY,
			slug TEXT,
			name TEXT,
			type TEXT,
			body TEXT,
			permissions TEXT,
			status TEXT,
			description TEXT,
			views INTEGER,
			poster_name TEXT,
			id_member INTEGER,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME
		)');
	}

	public static function tearDownAfterClass(): void
	{
		TestObj::$pdo->exec('DROP TABLE envision_pages');
	}

	public function tearDown(): void
	{
		TestObj::$pdo->exec('DELETE FROM envision_pages');
		TestObj::$pdo->exec('DELETE FROM SQLITE_SEQUENCE where name=\'envision_pages\'');
	}

	protected function setUp(): void
	{
		$stmt = TestObj::$pdo->prepare('INSERT INTO envision_pages (id_page, slug, name, type, body, permissions, status, description, views) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([1, 'test-slug', 'Test Page', 'html', '<p>Body</p>', '1', 'active', 'A test page', 10]);
		$stmt->execute([2, 'second-slug', 'Second Page', 'php', '<?php echo "Hello"; ?>', '1,2', 'inactive', 'Second test page', 5]);
		$stmt->execute([3, 'third-slug', 'Third Page', 'html', '<div>Third Body</div>', '2', 'active', 'Third test page', 7]);

		$this->page = new EnvisionPortal\Page(1, 'test-slug', 'Test Page', 'html', '<p>Body</p>', ['1'], 'active', 'A test page', 10);
	}

	public function testPageProperties(): void
	{
		$this->assertEquals(1, $this->page->id);
		$this->assertEquals('test-slug', $this->page->slug);
		$this->assertEquals('Test Page', $this->page->name);
		$this->assertEquals('html', $this->page->type);
		$this->assertEquals('<p>Body</p>', $this->page->body);
		$this->assertEquals(['1'], $this->page->permissions);
		$this->assertEquals('active', $this->page->status);
		$this->assertEquals('A test page', $this->page->description);
		$this->assertEquals(10, $this->page->views);
	}

	public function testFetchBy(): void
	{
		$pages = EnvisionPortal\Page::fetchBy(['id_page', 'name'], []);
		$this->assertIsArray($pages);
		$this->assertCount(3, $pages);

		$this->assertInstanceOf(EnvisionPortal\Page::class, $pages[0]);
		$this->assertEquals(1, $pages[0]->id);
		$this->assertEquals('Test Page', $pages[0]->name);

		$this->assertInstanceOf(EnvisionPortal\Page::class, $pages[1]);
		$this->assertEquals(2, $pages[1]->id);
		$this->assertEquals('Second Page', $pages[1]->name);

		$this->assertInstanceOf(EnvisionPortal\Page::class, $pages[2]);
		$this->assertEquals(3, $pages[2]->id);
		$this->assertEquals('Third Page', $pages[2]->name);
	}

	public function testInsert(): void
	{
		$this->page->insert();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM envision_pages');
		$count = $stmt->fetchColumn();
		$this->assertEquals(4, $count);
		$this->assertEquals('{db_prefix}envision_pages', TestObj::$last_insert[1]);
		$this->assertArrayHasKey('name', TestObj::$last_insert[2]);
		$this->assertStringContainsString('INSERT INTO {db_prefix}envision_pages', TestObj::$last_query);

		$pages = EnvisionPortal\Page::fetchBy(['id_page', 'name'], ['id_page = 4']);
		$this->assertEquals('Test Page', $pages[0]->name);
	}

	public function testUpdate(): void
	{
		$this->page->name = 'Updated Page';
		$this->page->update();

		$stmt = TestObj::$pdo->query('SELECT name FROM envision_pages WHERE id_page = 1');
		$name = $stmt->fetchColumn();
		$this->assertEquals('Updated Page', $name);
		$this->assertStringContainsString('UPDATE {db_prefix}envision_pages SET', TestObj::$last_query);
	}

	public function testDelete(): void
	{
		$this->page->delete();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM envision_pages WHERE id_page = 1');
		$count = $stmt->fetchColumn();
		$this->assertEquals(0, $count);
		$this->assertStringContainsString('DELETE FROM {db_prefix}envision_pages', TestObj::$last_query);
	}

	public function testIncrementViews(): void
	{
		for ($i = 0; $i < 2; $i++) {
			$this->page->incrementViews();
			$stmt = TestObj::$pdo->query('SELECT views FROM envision_pages WHERE id_page = 1');
			$views = $stmt->fetchColumn();
			$this->assertEquals(11, $views);
			$this->assertEquals(11, $this->page->views);
			$this->assertStringContainsString('UPDATE {db_prefix}envision_pages', TestObj::$last_query);
			$this->assertArrayHasKey('viewed_page_1', $_SESSION);
			$this->assertEquals('1', $_SESSION['viewed_page_1']);
		}
	}

	/**
	 * @dataProvider getBodyProvider
	 */
	public function testGetBody(string $type, string $input, string $expectedOutput): void
	{
		$this->page->type = $type;
		$this->page->body = $input;
		$this->assertEquals($expectedOutput, $this->page->getBody());

		$reflection = new ReflectionClass($this->page);
		$property = $reflection->getProperty('mode');
		$property->setAccessible(true);

		$this->assertInstanceOf(EnvisionPortal\PageModeInterface::class, $property->getValue($this->page));
	}

	public static function getBodyProvider(): array
	{
		return [
			['PHP', '<?php echo "Hello"; ?>', 'Hello'],
			['BBCode', '[b]Hello[/b]', '<b>Hello</b>'],
			['HTML', '<p>Hello</p>', '<p>Hello</p>'],
		];
	}

	public function testIsAllowed(): void
	{
		$this->assertTrue($this->page->isAllowed());
	}

	public function testDeleteMany(): void
	{
		$this->page->deleteMany([2, 3]);
		$this->assertStringContainsString('DELETE FROM {db_prefix}envision_pages WHERE', TestObj::$last_query);
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM envision_pages');
		$count = $stmt->fetchColumn();
		$this->assertEquals(1, $count);
	}

	public function testDeleteAll(): void
	{
		$this->page->deleteAll();
		$stmt = TestObj::$pdo->query('SELECT COUNT(*) FROM envision_pages');
		$count = $stmt->fetchColumn();
		$this->assertEquals(0, $count);
		$this->assertStringContainsString('TRUNCATE {db_prefix}envision_pages', TestObj::$last_query);
	}
}
