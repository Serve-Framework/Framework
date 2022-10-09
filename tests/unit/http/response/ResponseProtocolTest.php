<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\http\response;

use serve\http\response\Protocol;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ResponseProtocolTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$protocol = new Protocol;

		$protocol->set('https');

		$this->assertEquals('https', $protocol->get());
	}

	/**
	 *
	 */
	public function testSecure(): void
	{
		$protocol = new Protocol;

		$this->assertFalse($protocol->isSecure());

		$protocol->set('https');

		$this->assertTrue($protocol->isSecure());
	}
}
