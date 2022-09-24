<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\cache;

use serve\cache\Cache;
use serve\tests\TestCase;
use function strtotime;

/**
 * @group unit
 */
class CacheTest extends TestCase
{
	/**
	 *
	 */
	public function getStore()
	{
		return $this->mock('\serve\cache\stores\FileStore');
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('get')->once()->with('foobar')->andReturn('loaded from cache');

		$this->assertEquals('loaded from cache', $cache->get('foobar'));
	}

	/**
	 *
	 */
	public function testPut(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foobar', 'loaded from cache');

		$store->shouldReceive('get')->once()->with('foobar')->andReturn('loaded from cache');

		$cache->put('foobar', 'loaded from cache');

		$this->assertEquals('loaded from cache', $cache->get('foobar'));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foo', 'loaded from cache');

		$store->shouldReceive('has')->with('foo')->once()->andReturn(true);

		$store->shouldReceive('has')->with('bar')->once()->andReturn(false);

		$cache->put('foo', 'loaded from cache');

		$this->assertTrue($cache->has('foo'));

		$this->assertFalse($cache->has('bar'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foo', 'loaded from cache');

		$store->shouldReceive('delete')->with('foo')->once();

		$store->shouldReceive('has')->with('foo')->once()->andReturn(false);

		$cache->put('foo', 'loaded from cache');

		$cache->delete('foo');

		$this->assertFalse($cache->has('foo'));
	}

	/**
	 *
	 */
	public function testExpired(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('expired')->with('foo', strtotime('-1 week'))->once()->andReturn(true);

		$this->assertTrue($cache->expired('foo'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('clear')->once();

		$cache->clear();
	}
}
