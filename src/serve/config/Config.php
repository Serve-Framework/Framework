<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\config;

use serve\utility\Str;
use serve\utility\Arr;

/**
 * Serve framework configuration manager.
 *
 * @author Joe J. Howard
 */
class Config
{
	/**
	 * File loader.
	 *
	 * @var \serve\config\Loader
	 */
	protected $loader;

	/**
	 * Configuration.
	 *
	 * @var string|null
	 */
	protected $environment;

	/**
	 * Configuration.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Constructor.
	 *
	 * @param \serve\config\Loader $loader Config file reader
	 */
	public function __construct(Loader $loader, string $environment = null)
	{
		$this->loader = $loader;

		$this->environment = $environment;
	}


	/**
	 * Sets the environment.
	 *
	 * @param string $environment Environment name
	 */
	public function setEnvironment(string $environment): void
	{
		$this->environment = $environment;
	}

	/**
	 * Get settings using dot notation
	 *
	 * @param  string $key Config key
	 * @return mixed
	 */
	public function get(string $key = null)
	{
		// Return loaded config
		if (!$key)
		{
			return $this->data;
		}

		// Find the file
		if (Str::contains($key, '.'))
        {
            $file = Str::getBeforeFirstChar($key, '.');
        }
        else
        {
           	$file = $key;
        }

        // Load the file if not loaded already
		if (!isset($this->data[$file]))
		{
			$this->data[$file] = $this->loader->load($file, $this->environment);
		}

		return Arr::get($this->data, $key);
	}

	/**
	 * Get a default setting - bypass the environment.
	 *
	 * @param  string     $key Config key
	 * @return mixed
	 */
	public function getDefault(string $key)
	{
		// Already on default environment
        if ($this->environment === null)
        {
        	return $this->get($key);
		}

		// Find the file
		if (Str::contains($key, '.'))
        {
            $file = Str::getBeforeFirstChar($key, '.');
        }
        else
        {
           	$file = $key;
        }

        $data =
        [
        	$file => $this->loader->load($file, null)
        ];

		return Arr::get($data, $key);
	}

	/**
     * Set a key value using dot notation
     *
     * @param string $key   Key to use
     * @param mixed  $value Value to save
     */
    public function set(string $key, $value): void
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * Set an array of key values using dot notation.
     *
     * @param array $data Associative array to add
     */
    public function setMultiple(array $data): void
    {
        foreach ($data as $key => $value)
        {
            if (Str::contains($key, '.'))
            {
                Arr::set($this->data, $key, $value);
            }
            else
            {
                $this->data[$key] = $value;
            }
        }
    }

	/**
     * Remove a key/value from the internal array using dot notation.
     *
     * @param string $key Key to use
     */
    public function remove(string $key): void
    {
        Arr::delete($this->data, $key);
    }

	/**
	 * Save the configuration.
	 *
	 */
	public function save(): void
	{
		if (count($this->data) > 0 && $this->environment !== 'defaults')
		{
			$this->loader->save($this->data, $this->environment);
		}
	}
}
