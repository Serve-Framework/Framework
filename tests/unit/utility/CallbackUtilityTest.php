<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\utility;

use serve\tests\TestCase;
use serve\utility\Callback;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class CallbackTester
{
	public $var;

	public function __construct($var)
	{
		$this->var = $var;
	}

	public static function testStaticMethod($foo)
	{
		return $foo;
	}

	public function testMethod()
	{
		return $this->var;
	}

	public static function testStaticMethods($foo, $bar)
	{
		return $foo . $bar;
	}
}

class CallbackTesters
{
	public $foo;

	public $bar;

	public function __construct($foo, $bar)
	{
		$this->foo = $foo;

		$this->bar = $bar;
	}

	public function testMethods()
	{
		return $this->foo . $this->bar;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class CallbackUtilityTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks(): void
	{
		$this->assertEquals('foo', Callback::apply('\serve\tests\unit\framework\utility\CallbackTester@testMethod', 'foo'));

		$this->assertEquals('foo', Callback::apply('\serve\tests\unit\framework\utility\CallbackTester::testStaticMethod', 'foo'));

		$this->assertEquals('foobar', Callback::apply('\serve\tests\unit\framework\utility\CallbackTester::testStaticMethods', ['foo', 'bar']));

		$this->assertEquals('foobar', Callback::apply('\serve\tests\unit\framework\utility\CallbackTesters@testMethods', ['foo', 'bar']));

		$this->assertEquals('foobar', Callback::apply(function ($foo, $bar)
		{
			return $foo . $bar;

		}, ['foo', 'bar']));
	}
}
