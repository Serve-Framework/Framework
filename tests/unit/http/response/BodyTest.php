<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\http\response;

use serve\http\response\Body;
use serve\tests\TestCase;

/**
 * @group unit
 */
class BodyTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$body = new Body;

		$body->set('foo');

		$this->assertEquals('foo', $body->get());
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$body = new Body;

		$body->set('foo');

		$body->clear();

		$this->assertEquals('', $body->get());
	}

	/**
	 *
	 */
	public function testAppend(): void
	{
		$body = new Body;

		$body->set('foo');

		$body->append(' bar');

		$this->assertEquals('foo bar', $body->get());
	}

	/**
	 *
	 */
	public function testLength(): void
	{
		$body = new Body;

		$body->set('foo');

		$this->assertEquals(3, $body->length());
	}
}
