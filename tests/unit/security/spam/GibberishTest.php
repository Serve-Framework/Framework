<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\security\spam;

use serve\security\spam\gibberish\Gibberish;
use serve\tests\TestCase;

use function dirname;

/**
 * @group unit
 */
class GibberishTest extends TestCase
{
	/**
	 *
	 */
	public function testGibberish(): void
	{
		$gibberish = new Gibberish(dirname(__FILE__) . '/Gibberish.txt');

		$this->assertFalse($gibberish->test('Hello world this is real text.'));

		$this->assertTrue($gibberish->test('worfsdfald fasdfreal.'));
	}
}
