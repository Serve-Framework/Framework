<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\logger;

use PDOException;
use serve\exception\ExceptionParserTrait;
use serve\file\Filesystem;
use serve\http\request\Environment;
use Throwable;

use function date;
use function get_class;
use function implode;
use function ltrim;
use function strpos;
use function time;

/**
 * Error logger class.
 *
 * @author Joe J. Howard
 */
class Logger implements LoggerInterface
{
    use ExceptionParserTrait;

    /**
     * Directory where logs are stored.
     *
     * @var string
     */
    private $path;

    /**
     * HttpEnv instance.
     *
     * @var \serve\http\request\Environment
     */
    private $environment;

    /**
     * Filesystem instance.
     *
     * @var \serve\file\Filesystem
     */
    private $fileSystem;

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

        $this->fileSystem->appendContents($this->genericPath(), $msg);

        $this->fileSystem->appendContents($this->errnoPath($exception), $msg);
    }

    /**
     * {@inheritDoc}
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Build and return the log text.
     *
     * @param  Throwable $exception Exception
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
     * Get the path to generic error log file.
     *
     * @return string
     */
    private function genericPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . date('d_m_y') . '_all_errors.log';
    }

    /**
     * Get the path to the specific error log file for current error.
     *
     * @param  Throwable $exception Exception
     * @return string
     */
    private function errnoPath(Throwable $exception): string
    {
        return $this->path . DIRECTORY_SEPARATOR . date('d_m_y') . '_' . $this->errnoToFile($exception) . '.log';
    }

    /**
     * Convert the error code to the log file name.
     *
     * @param  Throwable $exception Exception
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
}
