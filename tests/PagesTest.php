<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PagesTest extends TestCase
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
		$stmt->execute([1, 'test-page', 'Test Page', 'html', '<p>Test page content</p>', '1', 'active', 'A test page', 10]);
	}

	/**
	 * @dataProvider pageProvider
	 */
	public function testFetchReturnsPageInstance(string $identifier): void
	{
		$page = EnvisionPortal\Pages::fetch($identifier);
		$this->assertInstanceOf(EnvisionPortal\Page::class, $page);
		$this->assertEquals('Test Page', $page->name);
	}

	public static function pageProvider(): array
	{
		return [
			['test-page'],
			['1'],
		];
	}

	/**
	 * @dataProvider pageProvider
	 */
	public function testMainPageExists(string $identifier): void
	{
		global $context, $modSettings, $smcFunc;

		$modSettings['ep_portal_mode'] = true;
		$_GET['page'] = $identifier;

		EnvisionPortal\Pages::main();

		$this->assertArrayHasKey('page_title', $context);
		$this->assertEquals('Test Page', $context['page_title']);
		$this->assertEquals(1, $_SESSION['last_page_id']);
		$this->assertArrayHasKey('page_data', $context);
		$this->assertEquals('<p>Test page content</p>', $context['page_data']['body']);
	}

	public static function missingPageProvider(): array
	{
		return [
			['missing-page'],
			['9999'],
		];
	}

	/**
	 * @dataProvider missingPageProvider
	 */
	public function testMainPageDoesNotExist(string $identifier): void
	{
		$_GET['page'] = $identifier;

		$this->expectException(Error::class);
		$this->expectExceptionMessage('ep_pages_not_exist');

		EnvisionPortal\Pages::main();
	}

	public function testMainPageNotAllowed(): void
	{
		$this->expectException(Error::class);
		$this->expectExceptionMessage('ep_pages_not_exist');

		EnvisionPortal\Pages::main();
	}

	public function testFetchReturnsNullForInvalidPage(): void
	{
		$page = EnvisionPortal\Pages::fetch('non-existent-page');
		$this->assertNull($page);
	}

	public function testSetMetaTag(): void
	{
		global $context;

		$context['meta_tags'] = [];
		EnvisionPortal\Pages::setMetaTag('description', 'Test Description');

		$this->assertCount(1, $context['meta_tags']);
		$this->assertEquals('description', $context['meta_tags'][0]['name']);
		$this->assertEquals('Test Description', $context['meta_tags'][0]['content']);
	}

	public function testSetMetaProperty(): void
	{
		global $context;

		$context['meta_tags'] = [];
		EnvisionPortal\Pages::setMetaProperty('title', 'Test Title');

		$this->assertCount(1, $context['meta_tags']);
		$this->assertEquals('og:title', $context['meta_tags'][0]['property']);
		$this->assertEquals('Test Title', $context['meta_tags'][0]['content']);
	}
}
