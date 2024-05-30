<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\connection;

use serve\exception\ErrorHandler;
use serve\graphql\client\Client;
use serve\graphql\client\Response;
use serve\graphql\exception\ResponseException;

use function array_shift;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_null;
use function is_string;
use function json_encode;
use function microtime;
use function str_contains;
use function str_replace;
use function strtolower;
use function trim;
use function var_export;

/**
 * Graphql connection handler.
 *
 * @author Joe J. Howard
 */
class ConnectionHandler
{
	/**
	 * Query log.
	 *
	 * @var array
	 */
	protected $log = [];

	/**
	 * Bindings for currently executing query statement.
	 *
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * Graphql query cache.
	 *
	 * @var \serve\graphql\connection\Cache
	 */
	protected $cache;

	/**
	 *  Graphql client.
	 *
	 * @var \serve\graphql\client\Client
	 */
	protected $client;

	/**
	 *  Graphql client.
	 *
	 * @var \serve\graphql\connection\Throttle
	 */
	protected $throttle;

	/**
	 * Error handler.
	 *
	 * @var \serve\exception\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * Throttle config.
	 *
	 * @var array
	 */
	protected $throttleConfig;

	/**
	 * Constructor.
	 *
	 * @param \serve\graphql\client\Client.      $client         Client isntance
	 * @param \serve\graphql\connection\Cache    $cache          Cache isntance
	 * @param \serve\graphql\connection\Throttle $throttle       Throttle isntance
	 * @param \serve\exception\ErrorHandler      $errorHandler   Error handler isntance
	 * @param array                              $throttleConfig Throttle configuration
	 */
	public function __construct(Client $client, Cache $cache, Throttle $throttle, ErrorHandler $errorHandler, array $throttleConfig)
	{
		$this->client = $client;

		$this->cache = $cache;

		$this->throttle = $throttle;

		$this->errorHandler = $errorHandler;

		$this->throttleConfig = $throttleConfig;
	}

	/**
	 * Returns connection client.
	 *
	 * @return \serve\graphql\client\Client
	 */
	public function client(): Client
	{
		return $this->client;
	}

	/**
	 * Returns the cache.
	 *
	 * @return \serve\graphql\connection\Cache
	 */
	public function cache(): Cache
	{
		return $this->cache;
	}

	/**
	 * Returns the throttle.
	 *
	 * @return \serve\graphql\connection\Throttle
	 */
	public function throttle(): Throttle
	{
		return $this->throttle;
	}

	/**
	 * Returns the connection query log.
	 *
	 * @return array
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * Bind a paramater to query.
	 *
	 * @param string $key   Binding key
	 * @param string $value Value to bind
	 */
	public function bind(string $key, $value): void
	{
		$this->bindings[] = [$key, $this->sanitizeValue($value)];
	}

	/**
	 * Bind multiple parameters to query.
	 *
	 * @param array $bindings Array of column => value
	 */
	public function bindMultiple(array $bindings): void
	{
		if (!empty($bindings))
		{
			foreach($bindings as $key => $val)
			{
				$this->bind($key, $val);
			}
		}
	}

	/**
	 * Execute a query and return the response.
	 *
	 * All graphql queries run through this method.
	 *
	 * @param  string                        $query    The query to execute
	 * @param  array|null                    $bindings Assoc array of parameters to bind (optional) (default [])
	 * @return serve\graphql\client\Response
	 */
	public function query(string $query, ?array $bindings = null): Response
	{
		$start = microtime(true);

		$fromCache = false;

		if (!empty($bindings))
		{
			$this->bindMultiple($bindings);
		}

		// Query is not a mutation
		if ($this->queryIsCachable($query))
		{
			// Load from cache
			if ($this->cache->has($query, $this->bindings))
			{
				$fromCache = true;

				$response = $this->cache->get($query, $this->bindings);
			}
			// Execute query and cache the result
			else
			{
				$response = $this->executeQuery($query);

				// Only cache if it was succesfull
				if ($response->isSuccessful())
				{
					$this->cache->put($query, $this->bindings, $response);
				}
			}
		}
		// Other query mutation
		else
		{
			$response = $this->executeQuery($query);

			$this->cache->clear();
		}

		// Save query to log
		$this->log($query, $this->bindings, $start, $fromCache);

		// Reset binding
		$this->bindings = [];

		return $response;
	}

	/**
	 * Execute graphql query.
	 *
	 * @param  string                        $query SQL query statement
	 * @return serve\graphql\client\Response
	 */
	protected function executeQuery(string $query): Response
	{
		if ($this->throttleConfig['enabled'])
		{
			$this->throttle->throttle($this->throttleConfig['key'], $this->throttleConfig['limit'], $this->throttleConfig['miliseconds']);
		}

		$response = $this->client->post($query, $this->bindings);

		if (!$response->isSuccessful())
		{
			$msg = json_encode($response->errors()) . "\n" . 'GRAPHQL : [' . $query . '] ' . "\n" . 'BINDINGS: [' . var_export($this->bindings, true) . ']';

			$exception = new ResponseException($msg);

			$this->errorHandler->logException($exception);
		}

        return $response;
	}

	/**
	 * Checks if query is cacheable.
	 *
	 * @param  string $query Query string
	 * @return bool
	 */
	protected function queryIsCachable(string $query): bool
	{
		return $this->getQueryType($query) !== 'mutation';
	}

    /**
     * Gets the query type from the query string.
     *
     * @param  string $query Graphql query
     * @return string
     */
    protected function getQueryType(string $query): string
    {
        $name = strtolower(trim(explode('{', trim($query))[0]));

        // Mutation or  Simple key
        if ($name === 'mutation' || !str_contains($name, '(')) return $name;

        // function(key: value)
        // name:function(key: value)
        $prefix = trim(explode('(', $name)[0]);

        if (str_contains($prefix, ':'))
        {
            $name = explode(':', $name);

            array_shift($name);

            $name = trim(implode(':', $name));
        }

        return $name;
    }

	/**
	 * Adds a query to the query log.
	 *
	 * @param string $query     Graphql query
	 * @param array  $bindings  Query parameters
	 * @param float  $start     Start time in microseconds
	 * @param bool   $fromCache Was the query loaded from the cache?
	 */
	protected function log(string $query, array $bindings, float $start, bool $fromCache = false): void
	{
		$time = microtime(true) - $start;

		$query = $this->prepareQueryForLog($query, $bindings);

		$this->log[] = ['query' => $query, 'time' => $time, 'from_cache' => $fromCache];
	}

	/**
	 * Prepares query for logging.
	 *
	 * @param  string $query    Graphql query
	 * @param  array  $bindings Query paramaters
	 * @return string
	 */
	protected function prepareQueryForLog(string $query, array $bindings): string
	{
		foreach ($bindings as $k => $v)
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
				$v = json_encode($v);
			}

			$query = str_replace('$' . $k, $parentesis . $v . $parentesis, $query);
		}

		return trim($query);
	}
}
