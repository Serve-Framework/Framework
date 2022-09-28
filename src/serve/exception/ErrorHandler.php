<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception;

use serve\http\response\exceptions\Stop;
use serve\http\response\exceptions\RequestException;
use serve\exception\logger\Logger;
use Closure;
use ErrorException;
use Throwable;
use function array_merge;
use function array_unique;
use function array_unshift;
use function error_get_last;
use function error_reporting;
use function get_class;
use function ini_get;
use function ini_set;
use function intval;
use function is_null;
use function register_shutdown_function;
use function restore_error_handler;
use function restore_exception_handler;
use function set_exception_handler;
use function strval;
use function is_array;
use function headers_sent;
use function ob_end_clean;
use function ob_get_level;

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
	 * Display errors.
	 *
	 * @var bool
	 */
	protected $displayErrors;

	/**
	 * Log errors.
	 *
	 * @var bool
	 */
	protected $logErrors;

    /**
     * Constructor.
     */
    public function __construct(Logger $logger, bool $displayErrors = true, bool $logErrors = true)
    {
    	$this->logger = $logger;
    	
        $this->display_errors($displayErrors);

        $this->log_errors($logErrors);

		$this->register();
    }

	/**
	 * Registers the exception handler.
	 */
	protected function register(): void
	{
		$this->registerFallbackHandler();

		$this->registerShutdownHandler();

		set_exception_handler([$this, 'handler']);
	}

	/**
	 * Registers a fallback exception handler.
	 */
	protected function registerFallbackHandler(): void
	{
		// Add a basic exception handler to the stack as a fullback
		$this->handle(Throwable::class, function ($e)
		{
			echo '[ ' . get_class($e) . '] ' . $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]';

			echo PHP_EOL;

			echo $e->getTraceAsString();

			return false;
		});
	}

	/**
	 * Registers the default shutdown handler. Allows us to handle "fatal" errors.
	 */
	protected function registerShutdownHandler(): void
	{
		register_shutdown_function(function (): void
		{
			$e = error_get_last();

			if($e !== null && (error_reporting() & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], $e['file'], $e['line']));
			}
		});
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
	 * Returns the fallback handler.
	 *
	 * @return \Closure
	 */
	protected function getFallbackHandler(): Closure
	{
		return function (Throwable $e): void
		{
			if($this->displayErrors())
			{
				$this->logger->writeMessage('[ ' . $e::class . "]  {$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]" . PHP_EOL);

				$this->logger->writeMessage($e->getTraceAsString() . PHP_EOL);
			}
		};
	}

	/**
	 * Handle an exception.
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
			// Handle errors and break if handler returns anything
			foreach($this->handlers as $handler)
			{
				if ($exception instanceof $handler['exceptionType'])
				{
					if ($this->handleException($exception, $handler['handler'] !== null))
					{
						break;
					}
				}
			}
		}
		// The error handler failed
		catch(Throwable $e)
		{
			if((PHP_SAPI === 'cli' || headers_sent() === false) && $this->displayErrors)
			{
				if (PHP_SAPI !== 'cli')
				{
					http_response_code($exception instanceof RequestException ? $exception->getCode() : 500);
				}

				// Empty output buffers
				$this->clearOutputBuffers();

				// One of the exception handlers failed so we'll just show the user a generic error screen
				$this->getFallbackHandler()($exception);

				// We'll also show some information about how the exception handler failed
				$this->logger->writeMessage('Additionally, the error handler failed with the following error:' . PHP_EOL);

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
				exit(1);
			}
		}
	}

	/**
	 * Handle the exception.
	 *
	 * @param  \Throwable  $exception  Exceotion
	 * @param  \Closure    $handler    Exception handler
	 * @return mixed
	 */
	protected function handleException(Throwable $exception, Closure $handler): mixed
	{
		return $handler($exception);
	}

	/**
	 * Logs the exception.
	 *
	 * @param \Throwable $exception An exception object
	 */
	protected function logException(Throwable $exception): void
	{
		if($this->shouldExceptionBeLogged($exception))
		{
			try
			{
				$this->logger()->writeException($exception);
			}
			catch(Throwable $e)
			{
				error_log(sprintf('%s on line %s in %s.', $e->getMessage(), $e->getLine(), $e->getLine()));
			}
		}
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
	 * Clear output buffers.
	 */
	protected function clearOutputBuffers(): void
	{
		$argv = isset($_SERVER['argv']) && is_array($_SERVER['argv']) && isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : '';

		if (PHP_SAPI !== 'cli' && !str_contains($argv, 'phpunit'))
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
		// No error logging
		if (!$this->logErrors)
		{
			return false;
		}

		// Loop disabled logging exceptions
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
     * Set or get the "display_errors" value.
     *
     * @param  bool|null $displayErrors (optional) (default NULL)
     * @return bool
     */
    public function display_errors(?bool $displayErrors = null): bool
    {
    	if (!is_null($displayErrors))
    	{
    		$this->displayErrors = $displayErrors;
    	}

    	return $this->displayErrors;
    }

    /**
     * Set or get error logging.
     *
     * @param  bool|null $logErrors (optional) (default NULL)
     * @return bool
     */
    public function log_errors(?bool $logErrors = null): int
    {
    	if (!is_null($logErrors))
    	{
    		$this->logErrors = $logErrors;
    	}

    	return $this->logErrors;
    }
}
