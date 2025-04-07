<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use EnvisionPortal\Portal;

class PortalTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		TestObj::$pdo->exec('
			CREATE TABLE ep_layout_actions (
				id_layout INTEGER PRIMARY KEY,
				action TEXT NOT NULL
			);
		');
	}

	public static function tearDownAfterClass(): void
	{
		TestObj::$pdo->exec('DROP TABLE ep_layout_actions');
	}

	public function tearDown(): void
	{
		TestObj::$pdo->exec('DELETE FROM ep_layout_actions');
		TestObj::$pdo->exec('DELETE FROM SQLITE_SEQUENCE where name=\'ep_layout_actions\'');
	}

	protected function setUp(): void
	{
		$stmt = TestObj::$pdo->exec("
			INSERT INTO ep_layout_actions (id_layout, action) VALUES
				(1, '[topic]'),
				(2, '[board]=5'),
				(3, 'profile;area=statistics'),
				(4, 'admin;area=serversettings,featuresettings;sa=cache,layout')
		");
	}

	/**
	 * Invoke any private/protected method on the Portal class.
	 *
	 * @param string $methodName
	 * @param array $args Arguments to pass into the method
	 * @return mixed
	 */
	private function invokePortalMethod(string $methodName, array $args = [])
	{
		$portal = new Portal();
		$ref = new ReflectionClass($portal);
		$method = $ref->getMethod($methodName);
		$method->setAccessible(true);
		return $method->invokeArgs($portal, $args);
	}

	/**
	 * @dataProvider bracketSpecsProvider
	 */
	public function testMatchLayoutSpecWithBrackets(string $spec, string|array $params, bool $expected)
	{
		$this->assertSame($expected, $this->invokePortalMethod('matchLayoutSpec', [$spec, $params]));
	}

	public static function bracketSpecsProvider(): array
	{
		return [
			'[topic] exists' => ['[topic]', ['topic' => '123'], true],
			'[topic]=1 mismatch' => ['[topic]=1', ['topic' => '2'], false],
			'[board]=5 match' => ['[board]=5', ['board' => '5'], true],
			'[board]=5 mismatch' => ['[board]=5', ['board' => '6'], false],
			'[custom]=value match' => ['[custom]=value', ['custom' => 'value'], false],
			'[custom]=value match' => ['[custom]=123', ['custom' => 'value'], false],
			'[custom]=value match' => ['[custom]=123', ['custom' => '123'], true],
			'[custom] missing' => ['[custom]', '[custom]', true],
			'[custom] missing' => ['[custom]', [], false],
		];
	}

	/**
	 * @dataProvider actionSpecsProvider
	 */
	public function testMatchLayoutSpecWithActions(string $spec, array $params, bool $expected)
	{
		$this->assertSame($expected, $this->invokePortalMethod('matchLayoutSpec', [$spec, $params]));
	}

	public static function actionSpecsProvider(): array
	{
		return [
			'Exact action match' => ['moderate', ['action' => 'moderate'], true],
			'Wrong action' => ['moderate', ['action' => 'admin'], false],
			'Simple action w/ param match' => ['profile;area=statistics', ['action' => 'profile', 'area' => 'statistics'], true],
			'Simple action w/ param mismatch' => ['profile;area=statistics', ['action' => 'profile', 'area' => 'alerts'], false],
			'Multi-value params match (serversettings)' => ['admin;area=serversettings,featuresettings;sa=cache,layout', ['action' => 'admin', 'area' => 'serversettings', 'sa' => 'cache'], true],
			'Multi-value params match (featuresettings)' => ['admin;area=serversettings,featuresettings;sa=cache,layout', ['action' => 'admin', 'area' => 'featuresettings', 'sa' => 'layout'], true],
			'Multi-value params mismatch' => ['admin;area=serversettings;sa=cache', ['action' => 'admin', 'area' => 'other', 'sa' => 'layout'], false],
		];
	}

	/**
	 * @dataProvider getMatchedLayoutProvider
	 */
	public function testGetMatchedLayout(array $queryParams, ?int $expectedLayout)
	{
		$this->assertSame($expectedLayout, $this->invokePortalMethod('getMatchedLayout', [$queryParams]));
	}

	public static function getMatchedLayoutProvider(): array
	{
		return [
			'topic match' => [['topic' => '123'], 1],
			'board match' => [['board' => '5'], 2],
			'profile match' => [['action' => 'profile', 'area' => 'statistics'], 3],
			'admin multi match' => [['action' => 'admin', 'area' => 'featuresettings', 'sa' => 'layout'], 4],
			'no match' => [['action' => 'none'], null],
		];
	}
}
