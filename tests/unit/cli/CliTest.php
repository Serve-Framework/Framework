<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\cli;

use serve\cli\Cli;
use serve\tests\TestCase;

/**
 * @group unit
 */
class CliTest extends TestCase
{
	/**
	 *
	 */
	public function testCli(): void
	{
		$input  = $this->mock('\serve\cli\input\Input');
		$output = $this->mock('\serve\cli\output\Output');
		$env    = $this->mock('\serve\cli\Environment');
		$cli    = new Cli($input, $output, $env);

		$this->assertEquals($input, $cli->input());
		$this->assertEquals($output, $cli->output());
		$this->assertEquals($env, $cli->environment());
	}
}
