<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ManageEnvisionPagesTest extends TestCase
{
    private $manageEnvisionPages;

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
	}

    public function t4estManageMenu()
    {
        $manageEnvisionPages = new EnvisionPortal\ManageEnvisionPages('');
        $this->assertArrayHasKey('page_title', $context);
        $this->assertEquals('Admin Menu Title', $context['page_title']);
        $this->assertArrayHasKey('sub_template', $GLOBALS['context']);
        $this->assertEquals('show_list', $GLOBALS['context']['sub_template']);
    }

    public function testSavePage(): void
    {
        global $context;

        $this->manageEnvisionPages = $this->getMockBuilder(EnvisionPortal\ManageEnvisionPages::class)
                ->disableOriginalConstructor()
            ->onlyMethods(['getInput'])
            ->getMock();
        $data = [
            'name' => 'Test Page',
            'slug' => 'test-page',
            'description' => 'A test page description',
            'body' => '<p>Test content</p>',
            'type' => 'HTML',
            'permissions' => ['1'],
            'status' => 'active',
        ];

        $_POST['submit'] = true;
        $this->manageEnvisionPages->method('getInput')->willReturn($data);
        $this->manageEnvisionPages->SavePage();

        $this->assertArrayNotHasKey('data', $context);
        $this->assertArrayNotHasKey('post_error', $context);
		$pages = EnvisionPortal\Page::fetchBy(['*'], []);
		$this->assertCount(4, $pages);
		$this->assertInstanceOf(EnvisionPortal\Page::class, $pages[3]);
        $this->assertEquals('Test Page', $pages[3]->name);
        $this->assertEquals('test-page', $pages[3]->slug);
        $this->assertEquals('A test page description', $pages[3]->description);
        $this->assertEquals('<p>Test content</p>', $pages[3]->body);
		$this->assertEquals(['1'], $pages[3]->permissions);
		$this->assertEquals('active', $pages[3]->status);
		$this->assertEquals(0, $pages[3]->views);
    }

    public function testSavePageWithMissingData(): void
    {
        global $context;

        $this->manageEnvisionPages = $this->getMockBuilder(EnvisionPortal\ManageEnvisionPages::class)
                ->disableOriginalConstructor()
            ->onlyMethods(['getInput'])
            ->getMock();
        $data = [
        ];

        $_POST['submit'] = true;
        $this->manageEnvisionPages->method('getInput')->willReturn($data);
        $this->manageEnvisionPages->SavePage();

        $this->assertArrayHasKey('post_error', $context);
        $this->assertArrayHasKey('name', $context['post_error']);
        $this->assertEquals('envision_pages_empty_name', $context['post_error']['name']);
        $this->assertArrayHasKey('data', $context);
        $this->assertEquals('', $context['data']['name']);
        $this->assertEquals('', $context['data']['slug']);
        $this->assertEquals('', $context['data']['description']);
        $this->assertEquals('', $context['data']['body']);
    }

    public function testEditPage(): void
    {
        global $context;
        $_GET['in'] = 1;

        $manageEnvisionPages = new EnvisionPortal\ManageEnvisionPages('editpage');

        $this->assertArrayHasKey('data', $context);
        $this->assertArrayHasKey('name', $context['data']);
        $this->assertArrayHasKey('slug', $context['data']);
        $this->assertArrayHasKey('description', $context['data']);
        $this->assertArrayHasKey('body', $context['data']);
		$this->assertEquals(1, $context['data']['id']);
		$this->assertEquals('test-slug', $context['data']['slug']);
		$this->assertEquals('Test Page', $context['data']['name']);
		$this->assertEquals('html', $context['data']['type']);
		$this->assertIsList($context['data']['types']);
		$this->assertEquals('html', $context['data']['types'][1][0]);
		$this->assertEquals('<p>Body</p>', $context['data']['body']);
		$this->assertCount(3, $context['data']['permissions']);
		$this->assertArrayHasKey(2, $context['data']['permissions']);
		$this->assertEquals('active', $context['data']['status']);
		$this->assertEquals('A test page', $context['data']['description']);
    }

    public function testEditPageWithInvalidId(): void
    {
        global $context;
        $_GET['in'] = 9999; // Assuming an invalid ID

        $this->expectException(Error::class);
        $manageEnvisionPages = new EnvisionPortal\ManageEnvisionPages('editpage');

        $this->assertArrayNotHasKey('data', $context);
    }

    public function testAddPage(): void
    {
        global $context;

        $manageEnvisionPages = new EnvisionPortal\ManageEnvisionPages('addpage');

        $this->assertArrayHasKey('data', $context);
        $this->assertEquals('', $context['data']['name']);
        $this->assertEquals('', $context['data']['slug']);
        $this->assertEquals('', $context['data']['description']);
        $this->assertEquals('', $context['data']['body']);
		$this->assertEquals('HTML', $context['data']['type']);
		$this->assertIsList($context['data']['types']);
		$this->assertIsList($context['data']['types'][0]);
		$this->assertEquals('bb_code', $context['data']['types'][0][0]);
		$this->assertEquals('BBCode', $context['data']['types'][[0][1]);
		$this->assertInstanceOf(EnvisionPortal\PageModeInterface::class, $context['data']['types'][[0][1]);
		$this->assertIsList($context['data']['types'][1]);
		$this->assertEquals('html', $context['data']['types'][1][0]);
		$this->assertEquals('HTML', $context['data']['types'][1][1]);
    }
}
