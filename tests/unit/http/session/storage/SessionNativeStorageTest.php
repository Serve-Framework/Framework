<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\http\session\storage;

use serve\http\session\storage\NativeSessionStorage;
use serve\tests\TestCase;

use function dirname;
use function is_int;
use function md5;
use function strtotime;

/**
 * @group unit
 */
class SessionNativeStorageTest extends TestCase
{
	/**
	 *
	 */
	private function getSessionConfig(): array
	{
		return
		[
			'cookie_name'  => 'serve_session',
			'expire'       => strtotime('+1 month'),
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => false,
		];
	}

	/**
	 *
	 */
	private function sessionSavePath(): string
	{
		return dirname(__FILE__) . '/tmp';
	}

	/**
	 * @runInSeparateProcess
	 */
	private function mockStorage(): NativeSessionStorage
	{
		return new NativeSessionStorage($this->getSessionConfig(), $this->sessionSavePath());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSavePath(): void
	{
		$storage = $this->mockStorage();

		$savePath = $storage->session_save_path();

		$storage->session_save_path('foo/bar');

		$this->assertEquals('foo/bar', $storage->session_save_path());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWrite(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRead(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testDestroy(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$storage->session_destroy();

		$this->assertEquals(null, $storage->read());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionId(): void
	{
		$storage = $this->mockStorage();

		$storage->session_id(md5('foo'));

		$this->assertEquals(md5('foo'), $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionName(): void
	{
		$storage = $this->mockStorage();

		$storage->session_name('foo');

		$this->assertEquals('foo', $storage->session_name());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRegenId(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$oldId = $storage->session_id();

		$storage->session_regenerate_id();

		$this->assertFalse($oldId === $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionParams(): void
	{
		$storage = $this->mockStorage();

		$params = $this->getSessionConfig();

		$storage->session_set_cookie_params($params);

		$expected =
		[
			'lifetime' => strtotime('+1 month'),
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => false,
			'samesite' => '',
		];

		$this->assertEquals($expected, $storage->session_get_cookie_params());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGc(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$gc = $storage->session_gc();

		$this->assertTrue(is_int($gc));
	}
}
