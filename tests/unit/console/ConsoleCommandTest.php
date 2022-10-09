<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\console;

use serve\cli\input\Input;
use serve\cli\output\Formatter;
use serve\cli\output\Output;
use serve\console\Command;
use serve\console\Console;
use serve\ioc\Container;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ConsoleCommandTest extends TestCase
{
	/**
	 *
	 */
	public function testRunCommand(): void
	{
		$container = $this->mock(Container::class);
		$input     = $this->mock(Input::class);
		$output    = $this->mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn('bar');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<green>Success: The command was executed.</green>');

		$console->registerCommand('bar', '\serve\tests\unit\console\Foobar');

		$console->run();
	}
}

class Foobar extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Foo description.';

	/**
	 * {@inheritDoc}
	 */
	public function execute(): void
	{
		$this->output->writeLn('<green>Success: The command was executed.</green>');
	}
}
