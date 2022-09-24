<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\cli;

use serve\application\services\Service;
use serve\exception\ErrorHandler;
use serve\exception\ErrorLogger;
use serve\exception\handlers\CliHandler;
use Throwable;

/**
 * Web error handling service.
 *
 * @author Joe J. Howard
 */
class ErrorHandlerService extends Service
{
	/**
	 * Return the error logger if we are logging errors.
	 *
	 * @param  \Throwable                        $exception "caught" exception
	 * @return \serve\exception\ErrorLogger|null
	 */
	private function getLogger(Throwable $exception)
	{
		if ($this->container->Config->get('application.error_handler.error_reporting') > 0)
		{
			return new ErrorLogger($exception, $this->container->Filesystem, $this->container->Request->environment(), $this->container->Config->get('application.error_handler.log_path'));
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		// Display errors
		$display_errors = $this->container->Config->get('application.error_handler.display_errors');

		// Log errors
		$error_reporting = $this->container->Config->get('application.error_handler.error_reporting');

		// Create the error handler
		$handler = new ErrorHandler($display_errors, $error_reporting);

		// Cli handler
		$handler->handle(Throwable::class, function($exception) use ($handler, $display_errors)
		{
			// Logger
			$handler->setLogger($this->getLogger($exception));

			// Cli handler
			$cliHandler = new CliHandler($exception, $this->container->Input, $this->container->Output);

			// Handle
			return $cliHandler->handle($display_errors);
		});

		// Save the instance
		$this->container->setInstance('ErrorHandler', $handler);
	}
}
