<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\cli\output\helpers;

use serve\cli\output\helpers\UnorderedList;
use serve\cli\output\Output;
use serve\tests\TestCase;

/**
 * @group unit
 */
class UnorderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList(): void
	{
		$output = $this->mock(Output::class);

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '* one' . PHP_EOL;
		$expected .= '* two' . PHP_EOL;
		$expected .= '* three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three']));
	}

	/**
	 *
	 */
	public function testNestedLists(): void
	{
		$output = $this->mock(Output::class);

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '* one' . PHP_EOL;
		$expected .= '* two' . PHP_EOL;
		$expected .= '* three' . PHP_EOL;
		$expected .= '  * one' . PHP_EOL;
		$expected .= '  * two' . PHP_EOL;
		$expected .= '  * three' . PHP_EOL;
		$expected .= '* four' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three', ['one', 'two', 'three'], 'four']));
	}

	/**
	 *
	 */
	public function testCustomMarker(): void
	{
		$output = $this->mock(Output::class);

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '# one' . PHP_EOL;
		$expected .= '# two' . PHP_EOL;
		$expected .= '# three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three'], '#'));
	}
}
