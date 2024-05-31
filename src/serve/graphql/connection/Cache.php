<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\connection;

use function array_key_exists;
use function is_array;
use function is_bool;
use function is_null;
use function json_encode;
use function md5;
use function trim;

/**
 * GraphQL query cache (in process).
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Cached data by query.
     *
     * @var array
     */
    public $data = [];

    /**
     * Is the cache enabled?
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Constructor.
     *
     * @param bool $enabled Enable or disable the cahce
     */
    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Is the cache enabled?
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the cache.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable the cache.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Is the query cached ?
     *
     * @param  string $queryStr Graphql query string
     * @param  array  $bindings Graphql query parameters
     * @return bool
     */
    public function has(string $queryStr, array $bindings): bool
    {
        if (!$this->enabled) return false;

        $cacheKey = $this->queryToKey($queryStr, $bindings);

        if (!$this->enabled)
        {
            return false;
        }

        return array_key_exists($cacheKey, $this->data);
    }

    /**
     * Get cached result.
     *
     * @param  string $queryStr Graphql query string
     * @param  array  $bindings Graphql query parameters
     * @return mixed
     */
    public function get(string $queryStr, array $bindings)
    {
        if (!$this->enabled) return false;

        if ($this->has($queryStr, $bindings))
        {
            return $this->data[$this->queryToKey($queryStr, $bindings)];
        }

        return false;
    }

    /**
     * Save a cached result.
     *
     * @param string $queryStr Graphql query string
     * @param array  $bindings Graphql query parameters
     * @param mixed  $result   Data to cache
     */
    public function put(string $queryStr, array $bindings, $result): void
    {
        if ($this->enabled)
        {
            $this->data[$this->queryToKey($queryStr, $bindings)] = $result;
        }
    }

    /**
     * Clear current table from results.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Returns the cache key based on query and params.
     *
     * @param  string $query    Graphql query string
     * @param  array  $bindings Graphql query parameters
     * @return string
     */
    protected function queryToKey(string $query, array $bindings): string
    {
        foreach ($bindings as $k => $v)
        {
            if (is_null($v))
            {
                $v = 'NULL';
            }
            elseif (is_bool($v))
            {
                $v = $v === true ? 'TRUE' : 'FALSE';
            }
            elseif (is_array($v))
            {
                $v = json_encode($v);
            }

            $query .= "$k:$v";
        }

        return md5(trim($query));
    }
}
