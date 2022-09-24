<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\config;

use serve\config\Config;
use serve\config\Loader;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ConfigTest extends TestCase
{
	/**
	 *
	 */
	public function getLoader()
	{
		return $this->mock(Loader::class);
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->get());
	}

	/**
	 *
	 */
	public function testGetWithEnvironment(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', 'foo')->andReturn(['greeting' => 'hello']);

		$config = new Config($loader, 'foo');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->get());
	}

	/**
	 *
	 */
	public function testGetDefault(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader, 'foo');

		$this->assertEquals('hello', $config->getDefault('settings.greeting'));
	}

	/**
	 *
	 */
	public function testSet(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn([]);

		$config = new Config($loader);

		$this->assertNull($config->get('settings.greeting'));

		$config->set('settings.greeting', 'hello');

		$this->assertEquals('hello', $config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$config->remove('settings.greeting');

		$this->assertNull($config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testSave(): void
	{
		$loader = $this->getLoader();
		$config = new Config($loader);

		$loader->shouldReceive('save')->once()->with(['foo' => ['bar' => 'foobar']], null);

		$config->set('foo.bar', 'foobar');

		$config->save();
	}
}
