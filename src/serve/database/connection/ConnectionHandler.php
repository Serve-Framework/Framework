<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\connection;

use PDO;
use PDOStatement;
use PDOException;

use function array_keys;
use function array_merge;
use function array_reverse;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_string;
use function microtime;
use function preg_replace;
use function str_replace;
use function strtolower;
use function trim;

/**
 * Database connection handler.
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
	 * PDO statement object returned from \PDO::prepare().
	 *
	 * @var PDO|PDOStatement
	 */
	protected $pdoStatement;

	/**
	 *  Database query cache.
	 *
	 * @var \serve\database\connection\Cache
	 */
	protected $cache;

	/**
	 *  Database connection.
	 *
	 * @var \serve\database\connection\Connection
	 */
	protected $connection;

	/**
	 * Constructor.
	 *
	 * @param \serve\database\connection\Connection $connection PDO connection
	 * @param \serve\database\connection\Cache      $cache      Connection cache
	 */
	public function __construct(Connection $connection, Cache $cache)
	{
		$this->connection = $connection;

		$this->cache = $cache;
	}

	/**
	 * Returns the cache.
	 *
	 * @return \serve\database\connection\Cache
	 */
	public function cache(): Cache
	{
		return $this->cache;
	}

	/**
	 *  Returns the last inserted id.
	 *
	 * @return false|int|string
	 */
	public function lastInsertId(): int|string|false
	{
		return $this->connection->pdo()->lastInsertId();
	}

	/**
	 * Returns the table prefix for the connection.
	 *
	 * @return string
	 */
	public function tablePrefix(): string
	{
		return $this->connection->tablePrefix();
	}

	/**
	 * Returns the cache.
	 *
	 * @return \serve\database\connection\Connection
	 */
	public function connection(): Connection
	{
		return $this->connection;
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
     * Safely format the query consistently.
     *
     * @param  string $sql SQL query statement
     * @return string
     */
    public function cleanQuery(string $sql): string
    {
       return trim(preg_replace('/\s+/', ' ', $sql));
    }

	/**
	 * Add the parameter to the parameter array.
	 *
	 * @param string $column Column key name
	 * @param string $value  Value to bind
	 */
	public function bind(string $column, $value): void
	{
		$this->bindings[] = [$column, $this->sanitizeValue($value)];
	}

	/**
	 * Bind multiple parameters.
	 *
	 * @param array $bindings Array of column => value
	 */
	public function bindMultiple(array $bindings = []): void
	{
		if (empty($this->bindings) && is_array($bindings) && !empty($bindings))
		{
			$columns = array_keys($bindings);

			foreach($columns as $i => &$column)
			{
				$this->bind($column, $bindings[$column]);
			}
		}
	}

	/**
	 * If the SQL query contains a SELECT or SHOW statement it
	 * returns an array containing all of the result set row.
	 * If the SQL statement is a DELETE, INSERT, or UPDATE statement
	 * it returns the number of affected rows.
	 *
	 * @param  string     $query     The query to execute
	 * @param  array|null $bindings  Assoc array of parameters to bind (optional) (default [])
	 * @param  int        $fetchmode PHP PDO::ATTR_DEFAULT_FETCH_MODE constant or integer
	 * @return mixed
	 */
	public function query(string $query, ?array $bindings = null, int $fetchmode = PDO::FETCH_ASSOC)
	{
		$start = microtime(true);

		$fromCache = false;

		$bindings = !$bindings ? [] : $bindings;

		// Query is either SELECT or SHOW
		if ($this->queryIsCachable($query))
		{
			$cacheParams = array_merge($this->bindings, $bindings);

			// Load from cache
			if ($this->cache->has($query, $cacheParams))
			{
				$fromCache = true;

				$result = $this->cache->get($query, $cacheParams);
			}
			// Execute query and cache the result
			else
			{
				$this->parseQuery($query, $bindings);

				$result = $this->pdoStatement->fetchAll($fetchmode);

				$this->cache->put($query, $cacheParams, $result);
			}
		}
		// Other queries e.g UPDATE, DELETE FROM, CREATE TABLE etc..
		else
		{
			$this->parseQuery($query, $bindings);

			$queryType = $this->getQueryType($query);

			$result = $queryType === 'select' || $queryType === 'show' || $queryType === 'pragma' ? $this->pdoStatement->fetchAll($fetchmode) : $this->pdoStatement->rowCount();

			if ($queryType === 'delete' || $queryType === 'update')
			{
				$this->cache->clear($query);
			}
		}

		// Log query
		$this->log($query, array_merge($this->bindings, $bindings), $start, $fromCache);

		// Reset parameters incase "parseQuery" was not called
		$this->bindings = [];

		return $result;
	}

	/**
	 * All SQL queries pass through this method.
	 *
	 * @param string $query    SQL query statement
	 * @param array  $bindings Array of parameters to bind (optional) (default [])
	 */
	protected function parseQuery(string $query, array $bindings = []): void
	{
		// Prepare query
		try
		{
    		$this->pdoStatement = $this->connection->pdo()->prepare($query);
		}
		catch (PDOException $e)
		{
			$msg = [$e->getMessage(), 'SQL Query: [' . $query . ']', 'Bindings: [' . var_export($bindings, true) . ']' ];

    		throw new PDOException(implode("\n\n", $msg));
		}

		// Add parameters to the parameter array
		$this->bindMultiple($bindings);

		// Bind parameters
		if (!empty($this->bindings))
		{
			foreach($this->bindings as $_params)
			{
				$this->pdoStatement->bindParam(':' . $_params[0], $_params[1]);
			}
		}

		// Execute SQL
		$this->pdoStatement->execute();

		// Reset the parameters
		$this->bindings = [];
	}

	/**
	 * Tries to load the current query from the cache.
	 *
	 * @param  string $query The type of query being executed e.g 'select'|'delete'|'update'
	 * @return bool
	 */
	protected function queryIsCachable(string $query): bool
	{
		$queryType = $this->getQueryType($query);

		return $queryType === 'select' || $queryType === 'show';
	}

	/**
	 * Gets the query type from the query string.
	 *
	 * @param  string $query SQL query
	 * @return string
	 */
	protected function getQueryType(string $query): string
	{
		return strtolower(explode(' ', trim($query))[0]);
	}

	/**
	 * Sanitize a value.
	 *
	 * @param  mixed $value A query value to sanitize
	 * @return mixed
	 */
	protected function sanitizeValue($value)
	{
		if (is_int($value))
		{
			return $value;
		}
		elseif (is_bool($value))
		{
			return !$value ? 0 : 1;
		}
		elseif (is_string($value) && trim($value) === '' || is_null($value))
		{
			return null;
		}
		elseif (is_string($value))
		{
			return $value;
		}

		return $value;
	}

	/**
	 * Adds a query to the query log.
	 *
	 * @param string $query     SQL query
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
	 * @param  string $query    SQL query
	 * @param  array  $bindings Query paramaters
	 * @return string
	 */
	protected function prepareQueryForLog(string $query, array $bindings): string
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

		return str_replace('`', '"', $query);
	}
}
