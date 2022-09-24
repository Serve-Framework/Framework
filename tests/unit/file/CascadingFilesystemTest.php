<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\file;

use serve\file\CascadingFilesystem;
use serve\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Loader
{
    use CascadingFilesystem;

	/**
	 * Constructor.
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class CascadingFilesystemTest extends TestCase
{
	/**
	 *
	 */
	private function dirPath(): string
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files';
	}
	
	/**
	 *
	 */
	public function testGetFilePath(): void
	{
		$expected = $this->dirPath() . DIRECTORY_SEPARATOR . 'two.php';

		$loader = new Loader($this->dirPath());

		$this->assertEquals($expected, $loader->getFilePath('two'));
	}
	
	/**
	 *
	 */
	public function testsetPath(): void
	{
		$expected = $this->dirPath() . DIRECTORY_SEPARATOR . 'two.php';

		$loader = new Loader('');

		$loader->setPath($this->dirPath());

		$this->assertEquals($expected, $loader->getFilePath('two'));
	}
	
	/**
	 *
	 */
	public function testsetExtension(): void
	{
		$expected = $this->dirPath() . DIRECTORY_SEPARATOR . 'one.txt';

		$loader = new Loader($this->dirPath());

		$loader->setExtension('txt');

		$this->assertEquals($expected, $loader->getFilePath('one'));

		$loader->setExtension('.txt');

		$this->assertEquals($expected, $loader->getFilePath('one'));
	}
	
	/**
	 *
	 */
	public function testRegisterNamespace(): void
	{
		$expected = $this->dirPath() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'two.php';

		$loader = new Loader($this->dirPath());

		$loader->registerNamespace('files', $this->dirPath() . DIRECTORY_SEPARATOR . 'files');

		$this->assertEquals($expected, $loader->getFilePath('files::two'));
	}
	
	/**
	 *
	 */
	public function getCascadingFilePaths(): void
	{
		$expected = $this->dirPath() . DIRECTORY_SEPARATOR . 'two.php';

		$loader = new Loader($this->dirPath());

		$this->assertEquals([$expected], $loader->getFilePath('two'));
	}
}
