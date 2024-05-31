<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\cache\stores;

use serve\cache\stores\FileStore;
use serve\file\Filesystem;
use serve\tests\TestCase;

use function serialize;
use function strtotime;

/**
 * @group unit
 */
class CacheFileStoreTest extends TestCase
{
	/**
	 *
	 */
	public function getFilesystem()
	{
		return $this->mock(Filesystem::class);
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->once()->with('/app/storage/cache/foo.cache')->andReturn(true);

		$filesystem->shouldReceive('getContents')->once()->with('/app/storage/cache/foo.cache')->andReturn(serialize('loaded from cache'));

		$this->assertEquals('loaded from cache', $store->get('foo'));
	}

	/**
	 *
	 */
	public function testGetNot(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->once()->with('/app/storage/cache/foo.cache')->andReturn(false);

		$this->assertEquals(null, $store->get('foo'));
	}

	/**
	 *
	 */
	public function testPut(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('putContents')->once()->with('/app/storage/cache/foobar.cache', serialize('loaded from cache'));

		$store->put('foobar', 'loaded from cache');
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(false);

		$this->assertTrue($store->has('foo'));

		$this->assertFalse($store->has('bar'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/foo.cache')->once();

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(false);

		$store->delete('foo');

		$store->delete('bar');
	}

	/**
	 *
	 */
	public function testExpired(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('modified')->with('/app/storage/cache/foo.cache')->once()->andReturn(strtotime('-2 months'));

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('modified')->with('/app/storage/cache/bar.cache')->once()->andReturn(strtotime('-15 days'));

		$this->assertTrue($store->expired('foo', strtotime('+1 month')));

		$this->assertFalse($store->expired('bar', strtotime('+1 month')));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$filesystem = $this->getFilesystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('list')->with('/app/storage/cache')->once()->andReturn(['foo.cache', 'bar.cache']);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/foo.cache')->once();

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/bar.cache')->once();

		$store->clear();
	}
}
