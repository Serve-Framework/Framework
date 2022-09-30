<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\http\response;

use serve\http\response\Headers;
use serve\tests\TestCase;

use function count;
use function date;

/**
 * @group unit
 */
class HeadersTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$headers = new Headers;

		$headers->set('foo', 'bar');

		$this->assertEquals('bar', $headers->get('foo'));
	}

	/**
	 *
	 */
	public function testSetMultiple(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$this->assertEquals(2, count($headers->asArray()));
	}

	/**
	 *
	 */
	public function TestHas(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$this->assertTrue($headers->has('Keep-Alive'));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$headers->remove('Keep-Alive');

		$this->assertEquals(null, $headers->get('Keep-Alive'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$headers->clear();

		$this->assertEquals(0, count($headers->asArray()));

		$this->assertEquals(0, count($headers->get()));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSend(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$this->assertFalse($headers->sent());

		$headers->send();

		$this->assertTrue($headers->sent());
	}

	/**
	 *
	 */
	public function testIteration(): void
	{
		$headers = new Headers;

		$headers->setMultiple([
		    'Keep-Alive' => 'timeout=5, max=100',
		    'Date'       => date('c'),
		]);

		$count = 0;

		foreach ($headers as $key => $value)
		{
			$count++;
		}

		$this->assertEquals(2, $count);
	}
}
