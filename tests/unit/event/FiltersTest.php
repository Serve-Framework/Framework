<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\event;

use serve\event\Filters;
use serve\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class FilterCallbackTester
{
	public $var;

	public function __construct($var)
	{
		$this->var = $var;
	}

	public static function testStaticMethodFirst($var)
	{
		return 'foo' . $var;
	}

	public static function testStaticMethodSecond($var)
	{
		return $var . 'baz';
	}

	public function testMethodFirst()
	{
		return 'foo' . $this->var;
	}

	public function testMethodSecond()
	{
		return $this->var . 'baz';
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class FiltersTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks(): void
	{
		$_this = $this;

		$filters = new Filters;

		$filters->on('foo1', '\serve\tests\unit\event\FilterCallbackTester::testStaticMethodFirst');

		$filters->on('foo1', '\serve\tests\unit\event\FilterCallbackTester::testStaticMethodSecond');

		$filters->on('foo2', '\serve\tests\unit\event\FilterCallbackTester@testMethodFirst');

		$filters->on('foo2', '\serve\tests\unit\event\FilterCallbackTester@testMethodSecond');

		$filters->on('foo3', function ($var)
		{
			return 'foo' . $var;
		});

		$filters->on('foo3', function ($var)
		{
			return $var . 'baz';
		});

		$this->assertEquals('foobarbaz', $filters->apply('foo1', 'bar'));

		$this->assertEquals('foobarbaz', $filters->apply('foo2', 'bar'));

		$this->assertEquals('foobarbaz', $filters->apply('foo3', 'bar'));
	}
}
