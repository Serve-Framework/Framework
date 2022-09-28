<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\logger;

use ErrorException;
use PDOException;
use serve\file\Filesystem;
use serve\http\request\Environment;
use serve\http\response\exceptions\RequestException;
use serve\utility\Str;
use Throwable;
use function array_keys;
use function array_pop;
use function array_reverse;
use function array_shift;
use function count;
use function date;
use function explode;
use function fclose;
use function feof;
use function fgets;
use function file_get_contents;
use function fopen;
use function get_class;
use function get_parent_class;
use function implode;
use function in_array;
use function is_readable;
use function ltrim;
use function strpos;
use function substr;
use function time;
use function token_get_all;

/**
 * Error logger class.
 *
 * @author Joe J. Howard
 */
class Logger
{
    /**
     * HttpEnv instance.
     *
     * @var \serve\http\request\Environment
     */
    protected $environment;

    /**
     * "Context" for error line.
     *
     * @var int
     */
    protected $sourcePadding = 6;

    /**
     * Constructor.
     *
     * @param \serve\file\Filesystem          $filesystem  Filesystem instance
     * @param \serve\http\request\Environment $environment HttpEnv instance for logging details
     * @param string                          $path        Directory to store log files in
     */
    public function __construct(Filesystem $filesystem, Environment $environment, string $path)
    {
        $this->fileSystem = $filesystem;

        $this->path = $path;

        $this->environment = $environment;
    }

    /**
     * {@inheritDoc}
     */
    public function writeException(Throwable $exception): void
    {
        $msg = $this->logMsg($exception);

        $path = $this->path . DIRECTORY_SEPARATOR . date('d_m_y') . '_' . $this->errnoToFile($exception) . '.log';

        $this->fileSystem->appendContents($path, $msg);
    }

    /**
     * Build and return the log text.
     *
     * @param  Throwable $exception Current exception
     * @return string
     */
    private function logMsg(Throwable $exception): string
    {
        return
        'DATE    : ' . date('l jS \of F Y h:i:s A', time()) . "\n" .
        'TYPE    : ' . $this->errType($exception) . ' [' . $exception->getCode() . "]\n" .
        'URL     : ' . $this->environment->REQUEST_URL . "\n" .
        'METHOD  : ' . $this->environment->REQUEST_METHOD . "\n" .
        'REFERER : ' . $this->environment->REFERER . "\n" .
        'CLASS   : ' . $this->errClass($exception) . "\n" .
        'FILE    : ' . $exception->getFile() . "\n" .
        'LINE    : ' . $exception->getLine() . "\n" .
        'MESSAGE : ' . $exception->getMessage() . "\n" .
        'IP      : ' . $this->environment->REMOTE_ADDR . "\n" .
        'AGENT   : ' . $this->environment->HTTP_USER_AGENT . "\n" .
        'TRACE   : ' . ltrim(implode("\n\t\t ", $this->errTrace($exception))) . "\n\n\n";
    }

    /**
     * Convert the error code to the log file name.
     *
     * @param  Throwable $exception Current exception
     * @return string
     */
    private function errnoToFile(Throwable $exception): string
    {
        if ($exception instanceof PDOException || get_class($exception) === 'PDOException' || strpos($exception->getMessage(), 'SQLSTATE') !== false)
        {
            return 'database_errors';
        }

        switch($exception->getCode())
        {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'fatal_errors';

            case E_WARNING:
            case E_NOTICE:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'nonfatal_errors';

            default:
                return 'other_errors';
        }
    }

    /**
     * Get text version of PHP error constant.
     *
     * @see    http://php.net/manual/en/errorfunc.constants.php
     * @param  Throwable $exception Current exception
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
        elseif ($exception instanceof RequestException || $exceptionParentName() === 'RequestException')
        {
            return Str::camel2case($exceptionClassName($exception));
        }

        return Str::camel2case($exceptionClassName($exception));
    }

    /**
     * Get the current exception class without namespace.
     *
     * @param  Throwable $exception Current exception
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
     * @param  Throwable $exception Current exception
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
     * @param  Throwable $exception Current exception
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
        elseif ($exception instanceof RequestException || $exceptionParentName() === 'RequestException')
        {
            return Str::camel2case($exceptionClassName());
        }

        return Str::camel2case($exceptionClassName());
    }

    /**
     * Get the exception call trace.
     *
     * @param  Throwable $exception Current exception
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
     * @param  Throwable $exception Current exception
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
     * @param  Throwable $exception Current exception
     * @return string
     */
    protected function errClass(Throwable $exception): string
    {
        if(!is_readable($exception->getFile()))
        {
            return '';
        }

        $handle      = fopen($exception->getFile(), 'r');
        $class      = '';
        $namespace  = '';
        $tokens     = token_get_all(file_get_contents($exception->getFile()));

        foreach ($tokens as $i => $token)
        {
            if ($token[0] === T_NAMESPACE)
            {
                foreach ($tokens as $j => $_token)
                {
                    if ($_token[0] === T_STRING)
                    {
                        $namespace .= '\\' . $_token[1];
                    }
                    elseif ($_token === '{' || $_token === ';')
                    {
                        break;
                    }
                }
            }
            elseif ($token[0] === T_CLASS)
            {
                foreach ($tokens as $j => $_token)
                {
                    if ($_token === '{')
                    {
                        $class = $tokens[$i+2][1];
                    }
                }
            }
        }

        if (empty($class))
        {
            return '';
        }

        return $namespace . '\\' . $class;
    }
}
