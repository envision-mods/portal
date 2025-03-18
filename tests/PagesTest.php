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
		$stmt->execute([1, 'test-slug', 'Test Page', 'html', '<p>Body</p>', '1', 'active', 'A test page', 10]);
	}

	/**
	 * @dataProvider pageProvider
	 */
	public function testFetchReturnsPageInstance($identifier): void
	{
		$page = EnvisionPortal\Pages::fetch($identifier);
		$this->assertInstanceOf(EnvisionPortal\Page::class, $page);
		$this->assertEquals('Test Page', $page->name);
	}

	public static function pageProvider(): array
	{
		return [
			['test-slug'],
			['1'],
		];
	}

	public function testFetchReturnsNullForInvalidPage(): void
	{
		$page = EnvisionPortal\Pages::fetch('non-existent-slug');
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
