<?php

declare(strict_types=1);

/**
 * @package   Envision Portal
 * @version   2.0.2
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2014, John Rayes
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace EnvisionPortal;

/**
 * Encodes and decodes layout position properties into a compact 12-bit integer.
 *
 * ## Bitmask Structure (12 bits total):
 * ```
 * | Bits | Field    | Max Value | Bitwise Shift |
 * |------|---------|-----------|---------------|
 * | 3    | x_pos   | 7         | << 9          |
 * | 3    | rowspan | 7         | << 6          |
 * | 3    | y_pos   | 7         | << 3          |
 * | 3    | colspan | 7         | << 0          |
 * ```
 * 
 * ### Encoding Formula (`toBits`)
 * Converts four integer values (`x_pos`, `rowspan`, `y_pos`, `colspan`) into a **12-bit** packed integer:
 * ```
 * bitmask = (x_pos << 9) | (rowspan << 6) | (y_pos << 3) | (colspan)
 * ```
 *
 * ### Decoding Formula (`fromBits`)
 * Extracts the four original values from a **12-bit** integer:
 * ```
 * x_pos   = (bitmask >> 9) & 0x7
 * rowspan = (bitmask >> 6) & 0x7
 * y_pos   = (bitmask >> 3) & 0x7
 * colspan = (bitmask) & 0x7
 * ```
 *
 * ## Constraints:
 * - Each value **must be within the range [0, 7]** (3-bit max: `0b111 = 7`).
 * - `is_smf` and `status` **are NOT stored in the bitmask** and should be handled separately.
 *
 * @param int $x_pos   Horizontal position (0-7).
 * @param int $rowspan Number of rows spanned (0-7).
 * @param int $y_pos   Vertical position (0-7).
 * @param int $colspan Number of columns spanned (0-7).
 *
 * @return int Encoded 12-bit integer.
 */
class Layout implements \ArrayAccess
{
	use ArrayAccessTrait;

	public int $id;
	public int $x;
	public int $rowspan;
	public int $y;
	public int $colspan;
	public bool $is_smf;
	public bool $enabled;

	/**
	 * @param int $x_pos The X-axis position in the layout (0-7).
	 * @param int $rowspan The number of rows spanned (0-7).
	 * @param int $y_pos The Y-axis position in the layout (0-7).
	 * @param int $colspan The number of columns spanned (0-7).
	 */
	public function __construct(int $id, int $x, int $rowspan, int $y, int $colspan, bool $is_smf, bool $enabled)
	{
		$this->id = $id;
		$this->x = $x;
		$this->rowspan = $rowspan;
		$this->y = $y;
		$this->colspan = $colspan;
		$this->is_smf = $is_smf;
		$this->enabled = $enabled;
		$this->x_pos = $this->clampTo3Bit($x_pos);
		$this->rowspan = $this->clampTo3Bit($rowspan);
		$this->y_pos = $this->clampTo3Bit($y_pos);
		$this->colspan = $this->clampTo3Bit($colspan);
	}

	/**
	 * Clamps the input to a 3-bit integer range (0-7).
	 *
	 * If the value is out of range, it is clamped to the nearest valid value.
	 *
	 * @param int $v Input value.
	 * @return int Clamped value in the range [0, 7].
	 */
	public function clampTo3Bit(int $v): int
	{
		// If out of the 3-bit range (0-7), clamp to min (0) or max (7).
		if ($v < 0) {
			return 0;
		} elseif ($v > 7) {
			return 7;
		}
		return $v;
	}
	
	/**
	 * Encodes layout position attributes into a compact 12-bit integer.
	 *
	 * This function converts four integer values (`x_pos`, `rowspan`, `y_pos`, and `colspan`)
	 * into a single bitmask representation. Each value is constrained to a maximum of 7 (3-bit storage).
	 *
	 * ### Bit Allocation Table:
	 * | Field     | Bits Used | Max Value | Encoding Shift |
	 * |-----------|----------|-----------|---------------|
	 * | `x_pos`   | 3 bits   | 7         | `<< 9`  |
	 * | `rowspan` | 3 bits   | 7         | `<< 6`  |
	 * | `y_pos`   | 3 bits   | 7         | `<< 3`  |
	 * | `colspan` | 3 bits   | 7         | `<< 0`  |
	 *
	 * **Maximum possible value for `toBits`:** `8151`
	 *
	 * @return int A compact integer representation of the layout properties (range: 0-8151).
	 */
	public function toBits(): int
	{
		$area = 0;
		$area |= ($this->x & 0x7) << 9;
		$area |= ($this->rowspan & 0x7) << 6;
		$area |= ($this->y & 0x7) << 3;
		$area |= ($this->colspan & 0x7);

		return $area;
	}

	/**
	 * Decodes a bitmask into layout position attributes and applies them to a Layout object.
	 *
	 * This function extracts the `x_pos`, `rowspan`, `y_pos`, and `colspan` values from
	 * a compact integer bitmask and updates the corresponding properties of a given `Layout` object.
	 *
	 * ### Bit Allocation Table:
	 * | Field     | Bits Used | Max Value | Decoding Mask |
	 * |-----------|----------|-----------|--------------|
	 * | `x_pos`   | 3 bits   | 7         | `($area >> 9) & 0x7`  |
	 * | `rowspan` | 3 bits   | 7         | `($area >> 6) & 0x7`  |
	 * | `y_pos`   | 3 bits   | 7         | `($area >> 3) & 0x7`  |
	 * | `colspan` | 3 bits   | 7         | `($area) & 0x7`  |
	 *
	 * @param int $area The encoded layout bitmask (range: 0-8151).
	 */
	public function fromBits(int $area): void
	{
		$this->x = ($area >> 9) & 0x7;
		$this->rowspan = ($area >> 6) & 0x7;
		$this->y = ($area >> 3) & 0x7;
		$this->colspan = $area & 0x7;
	}
}
