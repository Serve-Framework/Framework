<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\web;

use serve\application\services\Service;
use serve\exception\ErrorHandler;
use serve\exception\handlers\WebHandler;
use serve\exception\logger\Logger;
use Throwable;

/**
 * Web error handling service.
 *
 * @author Joe J. Howard
 */
class ErrorHandlerService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		// Display errors
		$display_errors = $this->container->Config->get('application.error_handler.display_errors');

		// Log errors
		$log_errors = $this->container->Config->get('application.error_handler.log_errors');

		// Logger
		$logger = $this->getLogger();

		// Create the error handler
		$handler = new ErrorHandler($logger, $display_errors, $log_errors);

		// Web handler
		$handler->handle(Throwable::class, function ($exception) use ($handler, $display_errors)
		{
			// Web handler
			$webHandler = new WebHandler($this->container->Request, $this->container->Response, $this->container->View);

			// Handle
			return $webHandler->handle($exception, $display_errors);
		});

		// Save the instance
		$this->container->setInstance('ErrorHandler', $handler);
	}

	/**
	 * Return the error logger.
	 *
	 * @return \serve\exception\logger\Logger
	 */
	private function getLogger(): Logger
	{
		return new Logger($this->container->Filesystem, $this->container->Request->environment(), $this->container->Config->get('application.error_handler.log_path'));
	}

}
