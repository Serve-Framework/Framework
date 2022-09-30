<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\cli;

use serve\cli\Cli;
use serve\cli\Environment;
use serve\cli\input\Input;
use serve\cli\output\Output;
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
		$input  = $this->mock(Input::class);
		$output = $this->mock(Output::class);
		$env    = $this->mock(Environment::class);
		$cli    = new Cli($input, $output, $env);

		$this->assertEquals($input, $cli->input());
		$this->assertEquals($output, $cli->output());
		$this->assertEquals($env, $cli->environment());
	}
}
