<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\common;

use Iterator;
use serve\utility\Arr;

/**
 * Array access magic methods trait.
 *
 * @author Joe J. Howard
 */
abstract class MagicObjectArray implements Iterator
{
    /**
     * Array access.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param array $data Data to construct with (optional)
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v)
        {
            Arr::set($this->data, $k, $v);
        }
    }

    /**
     * Save a key to the array using dot notation.
     *
     * @param string $key   Key to use
     * @param mixed  $value Value to save
     */
    public function set(string $key, $value)
    {
        Arr::set($this->data, $key, $value);

        return $value;
    }

    /**
     * Alias for set.
     *
     * @param string $key   Key to use
     * @param mixed  $value Value to save
     */
    public function put(string $key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Save an array of key values using dot notation.
     *
     * @param array $data Associative array to add
     */
    public function setMultiple(array $data): void
    {
        foreach ($data as $key => $value)
        {
            if (strpos($key, '.') !== false)
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
     * Check if the internal array has a value using dot notation.
     *
     * @param  string $key Key to use
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Get a key/value from the internal array using dot notation.
     *
     * @param  string|null $key Key to use (optional) (default null)
     * @return mixed
     */
    public function get(string $key = null)
    {
        if (!$key)
        {
            return $this->data;
        }

        return Arr::get($this->data, $key);
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
     * Empty the internal array.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Overwrite the internal array with a new one.
     *
     * @param array $data Array to overwrite the internal array with
     */
    public function overwrite(array $data): void
    {
        $this->data = [];

        $this->setMultiple($data);
    }

    /**
     * Alias for get.
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $key)
    {
        return Arr::get($this->data, $key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function __set(string $key, $value): void
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Unset a property by key.
     */
    public function __unset(string $key): void
    {
        Arr::delete($this->data, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): string|int|null
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if ($this->valid())
        {
            next($this->data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        $key = key($this->data);

        $data = ($key !== null && $key !== false);

        return $data;
    }
}
