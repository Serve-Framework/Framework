<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\console;

use serve\cli\input\Input;
use serve\cli\output\Output;
use serve\ioc\Container;

/**
 * Database manager.
 *
 * @author Joe J. Howard
 */
abstract class Command
{
	use CommandHelperTrait;

	/**
	 * Input.
	 *
	 * @var \serve\cli\input\Input
	 */
	protected $input;

	/**
	 * Output.
	 *
	 * @var \serve\cli\output\Output
	 */
	protected $output;

	/**
	 * Container.
	 *
	 * @var \serve\ioc\Container|null
	 */
	protected $container;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	protected $commandName;

	/**
	 * Command description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation = [];

	/**
	 * Available params.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Available options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Constructor.
	 *
	 * @param \serve\cli\input\Input    $input     Input
	 * @param \serve\cli\output\Output  $output    Output
	 * @param \serve\ioc\Container|null $container Container instance (optional) (default null)
	 */
	public function __construct(Input $input, Output $output, Container $container = null)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return $this->commandInformation;
	}

	/**
	 * Executes the command.
	 */
	abstract public function execute();

}
