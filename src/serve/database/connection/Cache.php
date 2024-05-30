<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\connection;

use Exception;

use function array_key_exists;
use function array_reverse;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_null;
use function is_string;
use function md5;
use function preg_match;
use function str_replace;
use function strtolower;
use function trim;

/**
 * Database Connection Cache.
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Cached data by table.
     *
     * @var array
     */
    public $data = [];

    /**
     * Is the cache enabled?
     *
     * @var bool
     */
    private $enabled;

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
     * @param  string $queryStr SQL query string
     * @param  array  $bindings SQL query parameters
     * @return bool
     */
    public function has(string $queryStr, array $bindings): bool
    {
        $tableName = $this->getTableName($queryStr);
        $cacheKey  = $this->queryToKey($queryStr, $bindings);

        if (!$this->enabled)
        {
            return false;
        }
        elseif ($this->getQueryType($queryStr) !== 'select')
        {
            return false;
        }
        elseif (!isset($this->data[$tableName]))
        {
            return false;
        }

        return array_key_exists($cacheKey, $this->data[$tableName]);
    }

    /**
     * Get cached result.
     *
     * @param  string $queryStr SQL query string
     * @param  array  $bindings SQL query parameters
     * @return mixed
     */
    public function get(string $queryStr, array $bindings)
    {
        if ($this->has($queryStr, $bindings))
        {
            return $this->data[$this->getTableName($queryStr)][$this->queryToKey($queryStr, $bindings)];
        }

        return false;
    }

    /**
     * Save a cached result.
     *
     * @param string $queryStr SQL query string
     * @param array  $bindings SQL query parameters
     * @param mixed  $result   Data to cache
     */
    public function put(string $queryStr, array $bindings, $result): void
    {
        if ($this->enabled)
        {
            $this->data[$this->getTableName($queryStr)][$this->queryToKey($queryStr, $bindings)] = $result;
        }
    }

    /**
     * Clear current table from results.
     */
    public function clear(?string $queryStr = null): void
    {
        if (!$queryStr)
        {
            $this->data = [];

            return;
        }

        $tableName = $this->getTableName($queryStr);

        if (isset($this->data[$tableName]))
        {
            unset($this->data[$tableName]);
        }
    }

    /**
     * Returns the cache key based on query and params.
     *
     * @param  string $query    SQL query string
     * @param  array  $bindings SQL query parameters
     * @return string
     */
    private function queryToKey(string $query, array $bindings): string
    {
        foreach (array_reverse($bindings) as $k => $v)
        {
            $parentesis = '';

            if (is_null($v))
            {
                $v = 'NULL';
            }
            elseif (is_string($v))
            {
                $parentesis = '"';
            }
            elseif (is_bool($v))
            {
                $v = $v === true ? 'TRUE' : 'FALSE';
            }
            elseif (is_array($v))
            {
                $v = implode('', $v);
            }

            $query = str_replace(":$k", $parentesis . $v . $parentesis, $query);
        }

        return md5(str_replace('`', '"', $query));
    }

    /**
     * Gets the table name based on the query string.
     *
     * @param  string $query SQL query string
     * @return string
     */
    private function getTableName(string $query): string
    {
        if (in_array($this->getQueryType($query), ['drop', 'create', 'show', 'alter', 'start', 'stop']))
        {
            return 'NULL';
        }

        preg_match("/(FROM|INTO|UPDATE)(\s+)(\w+)/i", $query, $matches);

        if (!$matches || !isset($matches[3]))
        {
            throw new Exception('Error retrieving database query table name. Query: [' . $query . ']');
        }

        return trim($matches[3]);
    }

    /**
     * Gets the query type from the query string.
     *
     * @param  string $query SQL query
     * @return string
     */
    private function getQueryType(string $query): string
    {
        return strtolower(explode(' ', trim($query))[0]);
    }
}
