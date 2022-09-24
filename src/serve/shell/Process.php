<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\shell;

use function fclose;
use function intval;
use function is_resource;
use function microtime;
use function proc_close;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use function stream_get_contents;
use function stream_select;
use function stream_set_blocking;
use function time;
use function trim;

/**
 * Shell Process.
 *
 * @author Joe J. Howard
 */
class Process
{
    /**
     * The cmd to run.
     *
     * @var string|null
     */
    private $cmd = null;

    /**
     * Default timeout in seconds.
     *
     * @var int
     */
    private $timeout = 10;

    /**
     * Result from the command.
     *
     * @var bool
     */
    private $result = false;

    /**
     * Errors from the output.
     *
     * @var string
     */
    private $errors = '';

    /**
     * Output from command.
     *
     * @var string
     */
    private $output = '';

    /**
     * Constructor.
     *
     * @param array $options Array of options (optional)
     *                       $options = [
     *                       'cmd'     => (string) Command to run (optional) (default null)
     *                       'timeout' => (int) imout in seconds (optional) (default 10)
     *                       ];
     */
    public function __construct(array $options)
    {
        $this->cmd = $options['cmd'] ?? null;

        $this->timeout = $options['timeout'] ?? 10;
    }

    /**
     * Run the command.
     */
    public function run(): string
    {
        $descriptors =
        [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];

        // Start the process.
        $process = proc_open(trim($this->cmd), $descriptors, $pipes);

        if (!is_resource($process))
        {
            $this->result = false;

            $this->errors = 'Could not run command "' . $this->cmd . '"';

            return false;
        }

        // Set the stdout stream to non-blocking.
        stream_set_blocking($pipes[1], 0);

        // Set the stderr stream to non-blocking.
        stream_set_blocking($pipes[2], 0);

        // Turn the timeout into microseconds.
        $timeout = $this->timeout * 1000000;

        // Output buffer.
        $buffer = '';

        // While we have time to wait.
        while ($timeout > 0)
        {
            $start = microtime(true);

            // Wait until we have output or the timer expired.
            $read  = [$pipes[1]];
            $other = [];
            stream_select($read, $other, $other, 0, intval($timeout));

            // Get the status of the process.
            // Do this before we read from the stream,
            // this way we can't lose the last bit of output if the process dies between these functions.
            $status = proc_get_status($process);

            // Read the contents from the buffer.
            // This function will always return immediately as the stream is non-blocking.
            $buffer .= stream_get_contents($pipes[1]);

            if (!$status['running'])
            {
                // Break from this loop if the process exited before the timeout.
                break;
            }

            // Subtract the number of microseconds that we waited.
            $timeout -= (microtime(true) - $start) * 1000000;
        }

        // Check if there were any errors.
        $errors = stream_get_contents($pipes[2]);

        if (!empty($errors))
        {
            $this->result = false;

            $this->errors = $errors;

            return false;
        }

        // Kill the process in case the timeout expired and it's still running.
        // If the process already exited this won't do anything.
        proc_terminate($process, 9);

        // Close all streams.
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        $this->result = true;

        $this->output = $buffer;

        return $buffer;
    }

    /**
     * Set command timeout.
     *
     * @param int $timeout Timeout in seconds
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Return output from buffer.
     *
     * @param string $cmd_str Command
     */
    public function setCommandLine($cmd_str): void
    {
        $this->cmd = $cmd_str;
    }

    /**
     * Was the command successful?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
       return $this->result === true;
    }

    /**
     * Return output from buffer.
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Return error from buffer.
     *
     * @return string
     */
    public function getErrorOutput(): string
    {
        return $this->errors;
    }

    /**
     * Clear output.
     */
    public function clearOutput(): void
    {
        $this->output = '';
    }
}
