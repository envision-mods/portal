<?php

declare(strict_types=1);

namespace EnvisionPortal\Tests;

use EnvisionPortal\ModuleInterface;
use EnvisionPortal\Util;

class UtilTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @dataProvider decamelizeProvider
	 */
	public function testDecamelize(string $input, string $expected)
	{
		$this->assertEquals($expected, Util::decamelize($input));
	}

	public function decamelizeProvider(): array
	{
		return [
			['helloWorld', 'hello_world'],
			['TestCaseString', 'test_case_string'],
			['example123', 'example123'],
			['JSONParser', 'json_parser'],
		];
	}

	/**
	 * @dataProvider camelizeProvider
	 */
	public function testCamelize(string $input, string $expected)
	{
		$this->assertEquals($expected, Util::camelize($input));
	}

	public function camelizeProvider(): array
	{
		return [
			['hello_world', 'HelloWorld'],
			['test_case_string', 'TestCaseString'],
			['example123', 'Example123'],
			['json_parser', 'JsonParser'],
		];
	}

	/**
	 * @dataProvider replaceVarsProvider
	 */
	public function testReplaceVars(string $template, array $variables, string $expected)
	{
		$this->assertEquals($expected, Util::replaceVars($template, $variables));
	}

	public function replaceVarsProvider(): array
	{
		return [
			['Hello {{name}}, welcome to {{place}}!', ['name' => 'John', 'place' => 'Earth'], 'Hello John, welcome to Earth!'],
			['Hello {{name}}, welcome to {{unknown}}!', ['name' => 'John'], 'Hello John, welcome to unknown!'],
			['{{greeting}} {{name}}!', ['greeting' => 'Hi', 'name' => 'Jane'], 'Hi Jane!'],
			['{{missing}} value', [], 'missing value'],
		];
	}

	/**
	 * @dataProvider processProvider
	 */
	public function testProcess(int $start, int $items_per_page, string $sort, array $list, array $expected)
	{
		$this->assertEquals($expected, Util::process($start, $items_per_page, $sort, $list));
	}

	public function processProvider(): array
	{
		return [
			[
				0,
				2,
				'id',
				[
					['id' => 3, 'name' => 'Charlie'],
					['id' => 1, 'name' => 'Alice'],
					['id' => 2, 'name' => 'Bob'],
				],
				[
					['id' => 1, 'name' => 'Alice'],
					['id' => 2, 'name' => 'Bob'],
				],
			],
			[
				1,
				1,
				'id DESC',
				[
					['id' => 3, 'name' => 'Charlie'],
					['id' => 1, 'name' => 'Alice'],
					['id' => 2, 'name' => 'Bob'],
				],
				[
					['id' => 2, 'name' => 'Bob'],
				],
			],
		];
	}

	/** @test */
	public function find_classes_finds_classes_implementing_interface()
	{
		$result = Util::find_classes(
			new \GlobIterator(
				__DIR__ . '/EnvisionPortal/Modules/*.php',
				\FilesystemIterator::SKIP_DOTS
			),
			'EnvisionPortal\Modules\\',
			ModuleInterface::class
		);

		$this->assertIsIterable($result);
		foreach ($result as $value) {
			$this->assertTrue(class_exists($value));
			$this->assertTrue(is_subclass_of($value, class: ModuleInterface::class));
		}
	}

	public function testListGroups()
	{
		global $txt, $smcFunc, $modSettings;

		// Mock global variables
		$txt = ['parent_guests_only' => 'Guests', 'parent_members_only' => 'Members'];
		$smcFunc = [
			'db_query' => fn() => new \ArrayIterator([
				[1, 'Admin', -1],
				[2, 'Moderator', 0],
			]),
			'db_fetch_row' => function (\Iterator $iterator): mixed {
				$return = $iterator->current();
				$iterator->next();
				return $return;
			},
			'db_free_result' => fn($result) => null,
		];

		$modSettings = ['permission_enable_postgroups' => true];

		$groups = Util::listGroups([-1, 2], false);

		$this->assertCount(4, $groups);
		$this->assertArrayHasKey(-1, $groups);
		$this->assertArrayHasKey(2, $groups);
		$this->assertEquals('Guests', $groups[-1]['name']);
		$this->assertTrue($groups[-1]['checked']);
		$this->assertFalse($groups[1]['checked']);
		$this->assertEquals('Admin', $groups[1]['name']);
		$this->assertEquals('Moderator', $groups[2]['name']);

		$groups = Util::listGroups([-3], false);

		$this->assertTrue($groups[-1]['checked']);
		$this->assertTrue($groups[1]['checked']);
	}

	/**
	 * @dataProvider mapProvider
	 */
	public function testMap(callable $callback, iterable $iterator, array $expected)
	{
		// Call the Util::map method
		$result = iterator_to_array(Util::map($callback, $iterator));

		// Assert that the result matches the expected output
		$this->assertSame($expected, $result);
	}

	public function mapProvider(): array
	{
		return [
			// Test case 1: Simple transformation
			[
				fn($value, $key) => [$key, $value * 2],
				[1, 2, 3],
				[[0, 2], [1, 4], [2, 6]],
			],
			// Test case 2: Keys and values swapped
			[
				fn($value, $key) => [$value, $key],
				['a' => 1, 'b' => 2, 'c' => 3],
				[[1, 'a'], [2, 'b'], [3, 'c']],
			],
			// Test case 3: Filtering values
			[
				function ($value, $key) {
					if ($value % 2 === 0) {
						return [$key, $value];
					}
					return null;
				},
				[1, 2, 3, 4],
				[null, [1, 2], null, [3, 4]],
			],
			// Test case 4: Empty input
			[
				fn($value, $key) => [$key, $value],
				[],
				[],
			],
			// Test case 5: Associative array with transformations
			[
				fn($value, $key) => [$key . '_key', $value * $value],
				['x' => 2, 'y' => 3],
				[['x_key', 4], ['y_key', 9]],
			],
		];
	}
}