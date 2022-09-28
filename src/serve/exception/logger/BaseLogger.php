<?php

namespace serve\exception\logger;

use Throwable;
use function date;

/**
 * Exception logger base class.
 *
 * @author Joe J. Howard
 */
abstract class BaseLogger
{
	/**
	 * Log directory.
	 *
	 * @var string
	 */
	protected $path;

    /**
     * Filesystem instance.
     *
     * @var \serve\file\Filesystem
     */
    protected $fileSystem;

    /**
     * Constructor.
     *
     * @param \serve\file\Filesystem $filesystem Filesystem instance
     * @param string                 $path       Directory to store log files in
     */
    public function __construct(Filesystem $filesystem, string $path)
    {
        $this->fileSystem = $filesystem;

        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function writeException(Throwable $exception): void
    {
    	$e = $this->exception;

    	$msg = '[ ' . $e::class . "]  {$e->getMessage()} on line [ {$e->getLine()} ] in [ {$e->getFile()} ]" . PHP_EOL;

    	$this->fileSystem->appendContents($this->path . DIRECTORY_SEPARATOR . date('d_m_y') . '_errors.log', $msg);
    }

    /**
     * {@inheritDoc}
     */
    public function writeMessage(string $msg): void
    {
    	$msg = trim($msg) . PHP_EOL;

    	$this->fileSystem->appendContents($this->path . DIRECTORY_SEPARATOR . date('d_m_y') . '_errors.log', $msg);
    }

    /**
     * {@inheritDoc}
     */
    public function setPath(string $path): void
    {
    	$this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(string $path): string
    {
    	return $this->path;
    }
}
