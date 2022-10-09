<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\cli\input;

use serve\cli\input\Input;
use serve\tests\TestCase;

/**
 * @group unit
 */
class CliInputTest extends TestCase
{
	/**
	 *
	 */
	private function inputArgs()
	{
		return
		[
			'command',
			'sub-command',
			'--foo=bar',
			'--bar',
			'foo',
			'-fbs',
			'--fooz',
			'-l=bar',
			'-b',
		];
	}

	/**
	 *
	 */
	private function inputParams()
	{
		return
		[
			'foo' => 'bar',
			'bar' => 'foo',
			'l'   => 'bar',
		];
	}

	/**
	 *
	 */
	private function inputOptions()
	{
		return
		[
			'f',
			'b',
			's',
			'fooz',
			'b',
		];
	}

	/**
	 *
	 */
	public function testCommand(): void
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals('command', $input->command());
	}

	/**
	 *
	 */
	public function testSubCommand(): void
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals('sub-command', $input->subCommand());
	}

	/**
	 *
	 */
	public function testParameters(): void
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals($this->inputParams(), $input->parameters());
	}

	/**
	 *
	 */
	public function testOptions(): void
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals($this->inputOptions(), $input->options());
	}
}
