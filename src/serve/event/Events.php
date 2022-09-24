<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\event;

use serve\utility\Callback;

/**
 * Events manager.
 *
 * @author Joe J. Howard
 */
class Events
{
    /**
     * List of callbacks.
     *
     * @var mixed Array of callbacks
     */
    protected $callbacks = [];

    /**
     * Private constructor.
     */
    public function __construct()
    {
    }

    /**
     * Hook into an filter.
     *
     * @param string $eventName The name of the filter
     * @param mixed  $callback  Callback to apply
     */
    public function on(string $eventName, $callback): void
    {
        $this->callbacks[$eventName][] = $callback;
    }

    /**
     * Apply a filter.
     *
     * @param string $eventName The name of the filter being fired
     * @param mixed  $args      The arguments to be sent to filter callback (optional) (default [])
     */
    public function fire(string $eventName, $args): void
    {
        // Is there a custom callback for the filter?
        if (isset($this->callbacks[$eventName]))
        {
            // Loop the filter callbacks
            foreach ($this->callbacks[$eventName] as $callback)
            {
                Callback::apply($callback, $args);
            }
        }
    }
}
