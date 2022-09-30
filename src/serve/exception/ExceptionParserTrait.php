<?php

namespace serve\exception;

use ErrorException;
use serve\http\response\exceptions\RequestException;
use serve\utility\Str;
use Throwable;

use function array_keys;
use function array_pop;
use function array_reverse;
use function array_shift;
use function count;
use function explode;
use function fclose;
use function feof;
use function fgets;
use function file_get_contents;
use function fopen;
use function get_class;
use function get_parent_class;
use function in_array;
use function is_readable;
use function preg_match;
use function str_replace;
use function strpos;
use function substr;
use function trim;

/**
 * Exception helper functions.
 *
 * @author Joe J. Howard
 */
trait ExceptionParserTrait
{
	/**
	 * "Context" for error line.
	 *
	 * @var int
	 */
	protected $sourcePadding = 6;

	/**
	 * Get text version of PHP error constant.
	 *
	 * @see    http://php.net/manual/en/errorfunc.constants.php
	 * @param  Throwable $exception Exception
	 * @return string
	 */
	protected function errType(Throwable $exception): string
	{
		if($exception instanceof ErrorException || get_class($exception) === 'ErrorException')
		{
			$code = $exception->getCode();

			$codes =
			[
				E_ERROR             => 'E_ERROR',
				E_PARSE             => 'E_PARSE',
				E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
				E_STRICT            => 'E_STRICT',
				E_NOTICE            => 'E_NOTICE',
				E_WARNING           => 'E_WARNING',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED        => 'E_DEPRECATED',
				E_USER_NOTICE       => 'E_USER_NOTICE',
				E_USER_WARNING      => 'E_USER_WARNING',
				E_USER_ERROR        => 'E_USER_ERROR',
				E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'E_ERROR';
		}
		elseif ($exception instanceof RequestException || $this->exceptionParentName($exception) === 'RequestException')
		{
			return Str::camel2case($this->exceptionClassName($exception));
		}

		return Str::camel2case($this->exceptionClassName($exception));
	}

	/**
	 * Get the current exception class without namespace.
	 *
	 * @return string
	 */
	protected function exceptionClassName(Throwable $exception): string
	{
		$class = explode('\\', get_class($exception));

		return array_pop($class);
	}

	/**
	 * Get the current exception's parent class without namespace.
	 *
	 * @param  Throwable $exception Exception
	 * @return string
	 */
	protected function exceptionParentName(Throwable $exception): string
	{
		$class = explode('\\', get_parent_class($exception));

		return array_pop($class);
	}

	/**
	 * Convert PHP error code to pretty name.
	 *
	 * @see    http://php.net/manual/en/errorfunc.constants.php
	 * @param  Throwable $exception Exception
	 * @return string
	 */
	protected function errName(Throwable $exception): string
	{
		if($exception instanceof ErrorException || get_class($exception) === 'ErrorException')
		{
			$code = $exception->getCode();

			$codes =
			[
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_STRICT            => 'Strict Mode Error',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'Error Exception';
		}
		elseif ($exception instanceof RequestException || $this->exceptionParentName($exception) === 'RequestException')
		{
			return Str::camel2case($this->exceptionClassName($exception));
		}

		return Str::camel2case($this->exceptionClassName($exception));
	}

    /**
     * Get the exception call trace.
     *
     * @param  Throwable $exception Exception
     * @return array
     */
    protected function errTrace(Throwable $exception): array
	{
	    $trace = array_reverse(explode("\n", $exception->getTraceAsString()));

	    array_shift($trace);

	    array_pop($trace);

	    $length = count($trace);

	    $result = [];

	    foreach ($trace as $call)
	    {
	    	$result[] = substr($call, strpos($call, ' '));
	    }

	    return $result;
	}

	/**
	 * Get source code of error line context.
	 *
	 * @param  Throwable $exception Exception
	 * @return array
	 */
	protected function errSource(Throwable $exception): array
	{
		if(!is_readable($exception->getFile()))
		{
			return [];
		}

		$handle      = fopen($exception->getFile(), 'r');
		$lines       = [];
		$currentLine = 0;

		while(!feof($handle))
		{
			$currentLine++;

			$sourceCode = fgets($handle);

			if($currentLine > $exception->getLine() + $this->sourcePadding)
			{
				break; // Exit loop after we have found what we were looking for
			}

			if($currentLine >= ($exception->getLine() - $this->sourcePadding) && $currentLine <= ($exception->getLine() + $this->sourcePadding))
			{
				$lines[$currentLine] = $sourceCode;
			}
		}

		fclose($handle);

		return $lines;
	}

	/**
	 * Get the classname of the error file.
	 *
	 * @param  Throwable $exception Exception
	 * @return string
	 */
	protected function errClass(Throwable $exception): string
	{
		if(!is_readable($exception->getFile()))
		{
			return '';
		}

		$content   = file_get_contents($exception->getFile());
		$lines     = explode('/\r\n/', $content);
		$namespace = '';
		$class     = '';

		// Find namespace;
		foreach($lines as $line)
		{
			preg_match('/namespace [^;]+/', $content, $matches);

			if ($matches)
			{
				$namespace = trim(str_replace('namespace ', '', $matches[0]));

				break;
			}
		}

		// Find class or object;
		foreach($lines as $line)
		{
			preg_match('/(class|interface|trait) [A-z1-9_]+/', $content, $matches);

			if ($matches)
			{
				$class = trim(str_replace(['class', 'interface', 'trait'], ['', '', ''], $matches[0]));

				break;
			}
		}

		if ($class === '')
		{
			return '';
		}

		return $namespace . '\\' . $class;
	}
}
