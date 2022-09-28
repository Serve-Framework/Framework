<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\logger;

use Throwable;

/**
 * Error logging interface.
 *
 * @author Joe J. Howard
 */
interface LoggerInterface
{
    /**
     * Write an exception to file.
     * 
     * @param \Throwable $exception Exception to write
     */
    public function writeException(Throwable $exception): void;

    /**
     * Write a custom message to file.
     * 
     * @param string $msg Message to write
     */
    public function writeMessage(string $msg): void;

    /**
     * Set the error logs directory.
     * 
     * @param string $path Directory to save logs to
     */
    public function setPath(string $path): void;

    /**
     * Get the error log path.
     * 
     * @return string
     */
    public function getPath(): string;
}