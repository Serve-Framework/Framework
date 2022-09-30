<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\logger;

use Throwable;

/**
 * Error logger interface.
 *
 * @author Joe J. Howard
 */
interface LoggerInterface
{
    /**
     * Write an exception to log.
     *
     * @param Throwable $exception Exception
     */
    public function writeException(Throwable $exception): void;

    /**
     * Set the error logs directory.
     *
     * @param string $path Directory to log to
     */
    public function setPath(string $path): void;
}
