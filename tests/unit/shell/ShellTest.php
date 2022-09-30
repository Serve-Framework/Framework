<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\utility;

use serve\shell\Shell;
use serve\tests\TestCase;

use function dirname;
use function file_get_contents;
use function file_put_contents;
use function str_contains;
use function trim;
use function unlink;

/**
 * @group unit
 */
class ShellTest extends TestCase
{
	/**
	 *
	 */
	public function testBuiltIn(): void
	{
		$cli = new Shell;

		$cli->cmd('cd "' . dirname(__FILE__) . '"')->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testIsSuccefullTrue(): void
	{
		$cli = new Shell;

		$cli->cmd('cd "' . dirname(__FILE__) . '"')->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testIsSuccefullFalse(): void
	{
		$cli = new Shell;

		$cli->cmd('cfddfdsf ' . dirname(__FILE__))->run();

		$this->assertFalse($cli->is_successful());
	}

	/**
	 *
	 */
	public function testErrorOutout(): void
	{
		$cli = new Shell;

		$output = $cli->cmd('cfddfdsf ' . dirname(__FILE__))->run();

		$this->assertTrue(str_contains($output, 'not found'));
	}

	/**
	 *
	 */
	public function testCustom(): void
	{
		$cli = new Shell;

		$cli->cmd('ruby')->option('v')->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testCd(): void
	{
		$cli = new Shell;

		$output = $cli->cd(dirname(__FILE__))->cmd('ls')->run();

		$this->assertTrue($cli->is_successful());

		$this->assertEquals('ShellTest.php', trim($output));
	}

	/**
	 *
	 */
	public function testOptionShorthand(): void
	{
		$cli = new Shell;

		$output = $cli->cmd('ruby')->option('h')->run();

		$this->assertTrue($cli->is_successful());

		$this->assertTrue(str_contains($output, 'Usage: ruby'));
	}

	/**
	 *
	 */
	public function testOptionLonghand(): void
	{
		$cli = new Shell;

		$output = $cli->cmd('ruby')->option('help')->run();

		$this->assertTrue($cli->is_successful());

		$this->assertTrue(str_contains($output, 'Usage: ruby'));
	}

	/**
	 *
	 */
	public function testInputOutput(): void
	{
		$cli = new Shell;

		$input = dirname(__FILE__) . '/input.txt';

		$output = dirname(__FILE__) . '/output.txt';

		file_put_contents($input, 'Test');

		$cli->cmd('cat')->input($input)->output($output)->run();

		$this->assertTrue($cli->is_successful());

		$this->assertEquals('Test', trim(file_get_contents($output)));

		unlink($input);

		unlink($output);
	}
}
