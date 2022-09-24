<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\file;

use serve\file\Filesystem;
use serve\tests\TestCase;
use SplFileObject;
use function dirname;
use function glob;
use function is_array;
use function is_resource;
use function str_contains;
use function time;

/**
 * @group unit
 */
class FilesystemTest extends TestCase
{

	/**
	 *
	 */
	private function testFle(): string
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt';
	}

	/**
	 *
	 */
	public function testExists(): void
	{
		$this->assertTrue(Filesystem::exists($this->testFle()));
	}

	/**
	 *
	 */
	public function testIsFile(): void
	{
		$this->assertTrue(Filesystem::isFile($this->testFle()));
	}

	/**
	 *
	 */
	public function testIsDirectory(): void
	{
		$this->assertTrue(Filesystem::isDirectory(dirname(__FILE__)));
	}

	/**
	 *
	 */
	public function testIsDirectoryEmpty(): void
	{
		$dir = dirname(__FILE__);

		$this->assertFalse(Filesystem::isDirectoryEmpty($dir));
	}

	/**
	 *
	 */
	public function testIsReadable(): void
	{
		$dir = dirname(__FILE__);

		$this->assertTrue(Filesystem::isReadable($dir));

		$this->assertFalse(Filesystem::isReadable($dir . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt'));
	}

	/**
	 *
	 */
	public function testIsWritable(): void
	{
		$this->assertEquals(true, Filesystem::isWritable($this->testFle()));
	}

	/**
	 *
	 */
	public function testLastModified(): void
	{
		$modified = Filesystem::lastModified($this->testFle());

		$this->assertTrue(time() >=  $modified);
	}

	/**
	 *
	 */
	public function testSize(): void
	{
		$this->assertTrue(Filesystem::size($this->testFle()) > 0);
	}

	/**
	 *
	 */
	public function testExtension(): void
	{
		$this->assertEquals('txt', Filesystem::extension($this->testFle()));
	}

	/**
	 *
	 */
	public function testMime(): void
	{
		$this->assertEquals('text/plain', Filesystem::mime($this->testFle()));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		Filesystem::delete($this->testFle());

		$this->assertFalse(Filesystem::exists($this->testFle()));

		Filesystem::touch($this->testFle());
		Filesystem::putContents($this->testFle(), 'Test');
	}

	/**
	 *
	 */
	public function testRename(): void
	{
		Filesystem::rename($this->testFle(), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt');

		$this->assertTrue(Filesystem::exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt'));

		Filesystem::touch($this->testFle());
		Filesystem::putContents($this->testFle(), 'Test');

		Filesystem::delete(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt');
	}

	/**
	 *
	 */
	public function testTouch(): void
	{
		Filesystem::touch(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt');

		$this->assertTrue(Filesystem::exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt'));

		Filesystem::delete(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'seven.txt');
	}

	/**
	 *
	 */
	public function testDeleteDirectory(): void
	{
		Filesystem::createDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir' . DIRECTORY_SEPARATOR . 'sub', 0777, true);

		Filesystem::touch(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir' . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'test.txt');

		Filesystem::deleteDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir');

		$this->assertFalse(Filesystem::isDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir'));
	}

	/**
	 *
	 */
	public function testEmptyDirectory(): void
	{
		Filesystem::createDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir' . DIRECTORY_SEPARATOR . 'sub', 0777, true);

		Filesystem::touch(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir' . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'test.txt');

		Filesystem::emptyDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir');

		$this->assertTrue(Filesystem::isDirectoryEmpty(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir'));

		Filesystem::deleteDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir');
	}

	/**
	 *
	 */
	public function testGlob(): void
	{
		$glob = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . '*.txt');

		$this->assertTrue(is_array($glob) && !empty($glob) && str_contains($glob[0], 'tests/unit/file/files/one.txt'));
	}

	/**
	 *
	 */
	public function testList(): void
	{
		$expected = ['empty', 'files', 'one.txt', 'two.php'];

		$this->assertEquals($expected, Filesystem::list(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files'));
	}

	/**
	 *
	 */
	public function testGetContents(): void
	{
		$this->assertEquals('Test', Filesystem::getContents($this->testFle()));
	}

	/**
	 *
	 */
	public function testPutContents(): void
	{
		Filesystem::putContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', 'Tests');

		$this->assertEquals('Tests', Filesystem::getContents($this->testFle()));

		Filesystem::putContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', 'Test');
	}

	/**
	 *
	 */
	public function testPrependContents(): void
	{
		Filesystem::prependContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', '_');

		$this->assertEquals('_Test', Filesystem::getContents($this->testFle()));

		Filesystem::putContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', 'Test');
	}

	/**
	 *
	 */
	public function testAppendContents(): void
	{
		Filesystem::appendContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', '_');

		$this->assertEquals('Test_', Filesystem::getContents($this->testFle()));

		Filesystem::putContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', 'Test');
	}

	/**
	 *
	 */
	public function testTruncateContents(): void
	{
		Filesystem::truncateContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt');

		$this->assertEquals('', Filesystem::getContents($this->testFle()));

		Filesystem::putContents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'one.txt', 'Test');
	}

	/**
	 *
	 */
	public function testCreateDirectory(): void
	{
		Filesystem::createDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir');

		$this->assertTrue(Filesystem::isDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir'));

		Filesystem::deleteDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'testdir');
	}

	/**
	 *
	 */
	public function testFile(): void
	{
		$file = Filesystem::file($this->testFle());

		$this->assertTrue($file instanceof SplFileObject);
	}

	/**
	 *
	 */
	public function testTmpfile(): void
	{
		$this->assertTrue(is_resource(Filesystem::tmpfile()));
	}

	/**
	 *
	 */
	public function testOb_read(): void
	{
		$vars = ['foo' => 'bar'];
		$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'two.php';

		$this->assertEquals('bar', Filesystem::ob_read($path, $vars));
	}
}
