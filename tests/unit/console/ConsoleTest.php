<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\console;

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
class ConsoleTest extends TestCase
{
	/**
	 *
	 */
	public function testNoCommand(): void
	{
		$container = $this->mock(Container::class);
		$input     = $this->mock(Input::class);
		$output    = $this->mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn(null);
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');
		$output->shouldReceive('write')->times(4)->with(PHP_EOL);
		$output->shouldReceive('write')->once()->with('php console [command] [arguments] [options]');
		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		$output->shouldReceive('formatter')->once()->andReturn($formatter);

		$output->shouldReceive('write')->once()->with(
'------------------------------
| <green>Command</green> | <green>Description</green>      |
------------------------------
| foo     | Foo description. |
------------------------------
');

		$console->registerCommand('foo', '\serve\tests\unit\framework\console\Foo');

		$console->run();
	}

	/**
	 *
	 */
	public function testWrongCommand(): void
	{
		$container = $this->mock(Container::class);
		$input     = $this->mock(Input::class);
		$output    = $this->mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn('bar');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('write')->times(3)->with(PHP_EOL);
		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');
		$output->shouldReceive('write')->once()->with('<red>Unknown command [ bar ].</red>');

		$output->shouldReceive('formatter')->once()->andReturn($formatter);

		$output->shouldReceive('write')->once()->with(
'------------------------------
| <green>Command</green> | <green>Description</green>      |
------------------------------
| foo     | Foo description. |
------------------------------
');

		$console->registerCommand('foo', '\serve\tests\unit\framework\console\Foo');

		$console->run();
	}

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

		$input->shouldReceive('subCommand')->once()->andReturn('foo');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<green>Success: The command was executed.</green>');

		$console->registerCommand('foo', '\serve\tests\unit\framework\console\Bar');

		$console->run();
	}
}

class Bar extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Foo description.';

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{
		$this->output->writeLn('<green>Success: The command was executed.</green>');
	}
}

class Foo extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Foo description.';

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{

	}
}
