<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception;

use Closure;
use ErrorException;
use serve\exception\logger\Logger;
use serve\http\response\exceptions\RequestException;
use serve\http\response\exceptions\Stop;
use Throwable;

use function array_merge;
use function array_unique;
use function array_unshift;
use function error_get_last;
use function fwrite;
use function headers_sent;
use function http_response_code;
use function is_array;
use function is_null;
use function ob_end_clean;
use function ob_get_level;
use function register_shutdown_function;
use function restore_error_handler;
use function restore_exception_handler;
use function set_exception_handler;
use function str_contains;

/**
 * Error handler.
 *
 * @author Joe J. Howard
 */
class ErrorHandler
{
	/**
	 * Logger.
	 *
	 * @var \serve\exception\logger\Logger
	 */
	protected $logger;

	/**
	 * Is the shutdown handler disabled?
	 *
	 * @var bool
	 */
	protected $disableShutdownHandler = false;

	/**
	 * Exception types that shouldn't be logged.
	 *
	 * @var array
	 */
	protected $disableLoggingFor = [Stop::class];

	/**
	 * Exception handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Are we logging errors?
	 *
	 * @var bool
	 */
	protected $logErrors;

	/**
	 * Are we displaying errors?
	 *
	 * @var bool
	 */
	protected $displayErrors;

	/**
	 * Are we running a phpunit test?
	 *
	 * @var bool
	 */
	protected $isPhpUnit;

    /**
     * Constructor.
     *
     * @param \serve\exception\logger\Logger $logger        Error logger
     * @param bool                           $displayErrors Display errors
     * @param bool                           $logErrors     Log errors
     */
    public function __construct(Logger $logger, bool $displayErrors, bool $logErrors)
    {
    	$this->logger = $logger;

    	$this->displayErrors = $displayErrors;

        $this->logErrors = $logErrors;

        $this->isPhpUnit = PHP_SAPI == 'cli' && isset($_SERVER['argv']) && is_array($_SERVER['argv']) && str_contains($_SERVER['argv'][0], 'phpunit');

        $this->handle(Throwable::class, $this->getFallbackHandler());

		$this->register();
    }

	/**
	 * Returns the fallback handler.
	 *
	 * @return Closure
	 */
	protected function getFallbackHandler(): Closure
	{
		return function(Throwable $e): void
		{
			if($this->displayErrors)
			{
				$this->write('[' . $e::class . "] {$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]" . PHP_EOL);

				$this->write($e->getTraceAsString() . PHP_EOL);
			}
		};
	}

	/**
	 * Registers the exception handler.
	 */
	protected function register(): void
	{
		// Allows us to handle "fatal" errors
		register_shutdown_function(function(): void
		{
			$e = error_get_last();

			if($e !== null && ($this->logErrors & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

				exit(1);
			}
		});

		// Set the exception handler
		set_exception_handler([$this, 'handler']);
	}

	/**
	 * Disables logging for an exception type.
	 *
	 * @param array|string $exceptionType Exception type or array of exception types
	 */
	public function disableLoggingFor($exceptionType): void
	{
		$this->disableLoggingFor = array_unique(array_merge($this->disableLoggingFor, (array) $exceptionType));
	}

	/**
	 * Disables the shutdown handler.
	 */
	public function disableShutdownHandler(): void
	{
		$this->disableShutdownHandler = true;
	}

	/**
	 * Prepends an exception handler to the stack.
	 *
	 * @param string  $exceptionType Exception type
	 * @param Closure $handler       Exception handler
	 */
	public function handle(string $exceptionType, Closure $handler): void
	{
		array_unshift($this->handlers, ['exceptionType' => $exceptionType, 'handler' => $handler]);
	}

	/**
	 * Clears all error handlers for an exception type.
	 *
	 * @param string $exceptionType Exception type
	 */
	public function clearHandlers(string $exceptionType): void
	{
		foreach($this->handlers as $key => $handler)
		{
			if($handler['exceptionType'] === $exceptionType)
			{
				unset($this->handlers[$key]);
			}
		}
	}

	/**
	 * Replaces all error handlers for an exception type with a new one.
	 *
	 * @param string  $exceptionType Exception type
	 * @param Closure $handler       Exception handler
	 */
	public function replaceHandlers(string $exceptionType, Closure $handler): void
	{
		$this->clearHandlers($exceptionType);

		$this->handle($exceptionType, $handler);
	}

    /**
     * Restore the default error handler.
     */
    public function restore(): void
    {
    	restore_error_handler();

    	restore_exception_handler();
    }

    /**
     * Returns logger.
     *
     * @return \serve\exception\logger\Logger
     */
    public function logger(): Logger
    {
    	return $this->logger;
    }

	/**
	 * Clear output buffers.
	 */
	protected function clearOutputBuffers(): void
	{
		if (!$this->isPhpUnit)
		{
			while(ob_get_level() > 0) ob_end_clean();
		}
	}

	/**
	 * Should the exception be logged?
	 *
	 * @param  Throwable $exception An exception object
	 * @return bool
	 */
	protected function shouldExceptionBeLogged(Throwable $exception): bool
	{
		if(!$this->logErrors)
		{
			return false;
		}

		foreach($this->disableLoggingFor as $exceptionType)
		{
			if ($exception instanceof $exceptionType)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Log an error.
	 *
	 * @param Throwable $exception An exception object
	 */
	protected function logException(Throwable $exception): void
	{
		if ($this->shouldExceptionBeLogged($exception))
		{
			$this->logger->writeException($exception);
		}
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @param Throwable $exception An exception object
	 */
	public function handler(Throwable $exception): void
	{
		try
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// Loop through the exception handlers

			foreach($this->handlers as $handler)
			{
				if($exception instanceof $handler['exceptionType'])
				{
					if($handler['handler']($exception) !== null)
					{
						break;
					}
				}
			}
		}
		catch(Throwable $e)
		{
			if((PHP_SAPI === 'cli' || headers_sent() === false) && $this->displayErrors)
			{
				if(PHP_SAPI !== 'cli')
				{
					http_response_code($exception instanceof RequestException || $exception instanceof Stop ? $exception->getCode() : 500);
				}

				// Empty output buffers

				$this->clearOutputBuffers();

				// One of the exception handlers failed so we'll just show the user a generic error screen

				$this->getFallbackHandler()($exception);

				// We'll also show some information about how the exception handler failed

				$this->write('Additionally, the error handler failed with the following error:' . PHP_EOL);

				$this->getFallbackHandler()($e);

				// And finally we'll log the additional exception

				$this->logException($e);
			}
		}
		finally
		{
			try
			{
				$this->logException($exception);
			}
			finally
			{
				if (!$this->isPhpUnit)
				{
					exit(1);
				}
			}
		}
	}

	/**
	 * Write to output.
	 *
	 * @param string $output String to write to output
	 */
	protected function write(string $output): void
	{
		if(PHP_SAPI === 'cli' && $this->displayErrors && !$this->isPhpUnit)
		{
			fwrite(STDOUT, $output);

			return;
		}

		echo $output;
	}

    /**
     * Set or get the Serve "log_errors" value.
     *
     * @param  bool|null $logErrors (optional) (default NULL)
     * @return bool
     */
    public function logErrors(?bool $logErrors = null): bool
    {
    	if (!is_null($logErrors))
    	{
    		$this->logErrors = $logErrors;
    	}

    	return $this->logErrors;
    }

    /**
     * Set or get the Serve "display_errors" value.
     *
     * @param  bool|null $displayErrors (optional) (default NULL)
     * @return bool
     */
    public function displayErrors(?bool $displayErrors = null): bool
    {
    	if (!is_null($displayErrors))
    	{
    		$this->displayErrors = $displayErrors;
    	}

    	return $this->displayErrors;
    }
}
