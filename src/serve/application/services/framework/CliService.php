<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\cli\commands\Encrypt;
use serve\application\cli\commands\GenerateSecret;
use serve\application\cli\commands\RoutesList;
use serve\application\cli\commands\ServicesList;
use serve\application\services\Service;
use serve\cli\Cli;
use serve\cli\Environment;
use serve\cli\input\Input;
use serve\cli\output\Formatter;
use serve\cli\output\Output;
use serve\console\Console;

/**
 * CLI service.
 *
 * @author Joe J. Howard
 */
class CliService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->registerCli();

        $this->registerInput();

        $this->registerOutput();

        $this->registerConsole();

        $this->registerCommands();
	}

	/**
	 * Registers the CLI.
	 */
	protected function registerCli(): void
	{
		$this->container->singleton('Cli', function($container)
        {
            return new Cli($container->Input, $container->output, new Environment);
        });
	}

	/**
	 * Registers the CLI input reader.
	 */
	protected function registerInput(): void
	{
		$this->container->singleton('Input', function()
        {
            return new Input($_SERVER['argv']);
        });
	}

	/**
	 * Registers the CLI output.
	 */
	protected function registerOutput(): void
	{
		$this->container->singleton('Output', function()
        {
            return new Output(new Formatter, new Environment);
        });
	}

	/**
	 * Registers the Console.
	 */
	protected function registerConsole(): void
	{
		$this->container->singleton('Console', function($container)
        {
            return new Console($container->Input, $container->Output, $container);
        });
	}

	/**
	 * Registers application commands.
	 */
	protected function registerCommands(): void
	{
		// Register reactor commands
        foreach($this->getCommands() as $command => $class)
        {
            $this->container->Console->registerCommand($command, $class);
        }
	}

    /**
     * Returns all registered commands.
     *
     * @return array
     */
    protected function getCommands(): array
    {
        // Define core commands
        $commands =
        [
            'generate_secret' => GenerateSecret::class,
            'list_services'   => ServicesList::class,
            'encrypt'         => Encrypt::class,
            'list_routes'     => RoutesList::class,
        ];

        // Add application commands
        $commands += $this->container->Config->get('application.commands');

        // Return commands
        return $commands;
    }
}
