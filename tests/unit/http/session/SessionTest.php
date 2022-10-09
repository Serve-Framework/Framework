<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\http\session;

use serve\http\session\Flash;
use serve\http\session\Session;
use serve\http\session\storage\NativeSessionStorage;
use serve\http\session\Token;
use serve\tests\TestCase;

/**
 * @group unit
 */
class SessionTest extends TestCase
{
	private function getConfig()
	{
		return
		[
			'cookie_name'  => 'serve_session',
			'expire'       => '+1 month',
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => true,
		];
	}

	/**
	 *
	 */
	private function mockSession()
	{
		$token = $this->mock(Token::class);

		$flash = $this->mock(Flash::class);

		$store = $this->mock(NativeSessionStorage::class);

		$store->shouldReceive('session_name')->withArgs(['serve_session'])->andReturn('serve_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobar');

		return new Session($token, $flash, $store, $this->getConfig());
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$this->expectNotToPerformAssertions();

		$this->mockSession();
	}

	/**
	 *
	 */
	public function testIteration(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$i = 0;

		foreach ($session as $key => $value)
		{
			$i++;
		}

		$this->assertEquals(1, $i);
	}

	/**
	 *
	 */
	public function testSet(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->get('foo'));
	}

	/**
	 *
	 */
	public function testSetMultiple(): void
	{
		$session = $this->mockSession();

		$session->setMultiple([
		    'foo' => 'bar',
		    'bar' => 'foo',
		]);

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertEquals('foo', $session->get('bar'));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertTrue($session->has('foo'));
	}

	/**
	 *
	 */
	public function testHasNot(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertFalse($session->has('bar'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertNull($session->get('bar'));
	}

	/**
	 *
	 */
	public function testGetAll(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->set('bar', 'foo');

		$this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $session->get());
	}

	/**
	 *
	 */
	public function testAsArray(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->set('bar', 'foo');

		$this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $session->asArray());
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->remove('foo');

		$this->assertNull($session->get('foo'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->clear();

		$this->assertEquals([], $session->get());
	}

	/**
	 *
	 */
	public function testSave(): void
	{
		$this->expectNotToPerformAssertions();

		$token = $this->mock(Token::class);

		$flash = $this->mock(Flash::class);

		$store = $this->mock(NativeSessionStorage::class);

		$store->shouldReceive('session_name')->withArgs(['serve_session'])->andReturn('serve_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobartoken');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$session->set('foo', 'bar');

		$flash->shouldReceive('getRaw')->andReturn(['flash' => 'bar']);

		$token->shouldReceive('get')->andReturn(['foobartoken']);

		$store->shouldReceive('write')->withArgs([[
			'serve_data'  => ['foo' => 'bar'],
			'serve_flash' => ['flash' => 'bar'],
			'serve_token' => 'foobartoken',
		]]);

		$store->shouldReceive('send');

		$session->save();
	}

	/**
	 *
	 */
	public function testConfigure(): void
	{
		$token = $this->mock(Token::class);

		$flash = $this->mock(Flash::class);

		$store = $this->mock(NativeSessionStorage::class);

		$store->shouldReceive('session_name')->once()->withArgs(['serve_session'])->andReturn('serve_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobartoken');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$config = $this->getConfig();

		$config['cookie_name'] = 'foobar_cookie_name';

		$store->shouldReceive('session_name')->withArgs(['foobar_cookie_name']);

		$store->shouldReceive('session_set_cookie_params')->withArgs([$config]);

		$session->configure($config);
	}

	/**
	 *
	 */
	public function testDestroy(): void
	{
		$token = $this->mock(Token::class);

		$flash = $this->mock(Flash::class);

		$store = $this->mock(NativeSessionStorage::class);

		$store->shouldReceive('session_name')->withArgs(['serve_session'])->andReturn('serve_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobar');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$token->shouldReceive('regenerate');

		$flash->shouldReceive('clear');

		$session->set('foo', 'bar');

		$session->destroy();

		$this->assertEquals([], $session->get());
	}
}
