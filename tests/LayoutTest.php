<?php

use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
	/**
	 * Data provider for bitwise encoding and decoding tests.
	 *
	 * Provides a range of valid and boundary values to ensure robustness.
	 *
	 * @return array
	 */
	public static function bitwiseDataProvider(): array
	{
		return [
			'minimum values' => [0, 0, 0, 0],
			'maximum values' => [7, 7, 7, 7],
			'mixed values 1' => [3, 5, 1, 6],
			'mixed values 2' => [7, 0, 3, 4],
			'single max' => [7, 1, 2, 3],
			'single min' => [0, 7, 6, 5],
			'all mid-range' => [4, 4, 4, 4],
		];
	}

	/**
	 * @dataProvider bitwiseDataProvider
	 */
	public function testToBits(int $x, int $rowspan, int $y, int $colspan)
	{
		$layout = new EnvisionPortal\Layout(0, $x, $rowspan, $y, $colspan, true, true);
		$encoded = $layout->toBits();

		$expected = ($x << 9) | ($rowspan << 6) | ($y << 3) | $colspan;

		$this->assertSame($expected, $encoded, 'toBits() did not return the expected encoded value.');
	}

	/**
	 * @dataProvider bitwiseDataProvider
	 */
	public function testFromBits(int $x, int $rowspan, int $y, int $colspan)
	{
		$layout = new EnvisionPortal\Layout(1, 0, 0, 0, 0, true, true);
		$layout->fromBits(($x << 9) | ($rowspan << 6) | ($y << 3) | $colspan);

		$this->assertSame($x, $layout->x, 'Decoded x value is incorrect.');
		$this->assertSame($rowspan, $layout->rowspan, 'Decoded rowspan is incorrect.');
		$this->assertSame($y, $layout->y, 'Decoded y value is incorrect.');
		$this->assertSame($colspan, $layout->colspan, 'Decoded colspan is incorrect.');
	}

	/**
	 * Test invalid values (should not be allowed in a strict system).
	 *
	 * @dataProvider invalidBitwiseDataProvider
	 */
	public function testBitwiseEncodingWithInvalidValues(int $x, int $rowspan, int $y, int $colspan)
	{
		$layout = new EnvisionPortal\Layout(0, $x, $rowspan, $y, $colspan, true, true);
		$encoded = $layout->toBits();

		$expected = ($x << 9) | ($rowspan << 6) | ($y << 3) | $colspan;

		$this->assertNotSame($expected, $encoded, 'toBits() did not return the expected encoded value.');
		$layout = new EnvisionPortal\Layout(1, 0, 0, 0, 0, true, true);

		$expected = (min(max($x, 0), 7) << 9) | (min(max($rowspan, 0), 7) << 6) | (min(max($y, 0), 7) << 3) | min(max($colspan, 0), 7);

		$this->assertSame($expected, $encoded, 'toBits() did not return the expected encoded value.');
	}

	/**
	 * Data provider for invalid values (out of range).
	 *
	 * @return array
	 */
	public static function invalidBitwiseDataProvider(): array
	{
		return [
			'negative values' => [-1, 3, 2, 1],
			'x too high' => [8, 3, 2, 1],
			'rowspan too high' => [3, 9, 2, 1],
			'y too high' => [3, 3, 10, 1],
			'colspan too high' => [3, 3, 2, 8],
		];
	}
}
