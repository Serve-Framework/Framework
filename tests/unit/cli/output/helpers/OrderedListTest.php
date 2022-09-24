<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\cli\output\helpers;

use serve\cli\output\Formatter;
use serve\cli\output\helpers\OrderedList;
use serve\cli\output\Output;
use serve\tests\TestCase;

/**
 * @group unit
 */
class OrderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList(): void
	{
		$formatter = new Formatter;
		$output    = $this->mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three']));
	}
	/**
	 *
	 */
	public function testNestedLists(): void
	{
		$formatter = new Formatter;
		$output    = $this->mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;
		$expected .= '   1. one' . PHP_EOL;
		$expected .= '   2. two' . PHP_EOL;
		$expected .= '   3. three' . PHP_EOL;
		$expected .= '4. four' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three', ['one', 'two', 'three'], 'four']));
	}
	/**
	 *
	 */
	public function testCustomMarker(): void
	{
		$formatter = new Formatter;
		$output    = $this->mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three'], '[%s]'));
	}
}
