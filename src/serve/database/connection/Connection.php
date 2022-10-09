<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\connection;

use PDO;
use PDOException;
use RuntimeException;
use serve\database\builder\Builder;

use function implode;
use function in_array;
use function is_null;
use function vsprintf;

/**
 * Database connection.
 *
 * @author Joe J. Howard
 */
class Connection
{
	/**
	 * Database name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Database host.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Database username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Database username password.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	protected $tablePrefix;

	/**
	 * Connection DSN.
	 *
	 * @var string
	 */
	protected $dsn;

	/**
	 * PDO options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * PDO object.
	 *
	 * @var PDO|null
	 */
	protected $pdo;

	/**
	 * Connection handler.
	 *
	 * @var \serve\database\connection\ConnectionHandler
	 */
	protected $handler;

	/**
	 * Connection type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Accepted connection types.
	 *
	 * @var array
	 */
	protected $acceptedTypes = ['mysql', 'sqlite'];

	/**
	 * Constructor.
	 *
	 * @param  array            $config Connection configuration
	 * @throws RuntimeException If connection type is not supported
	 */
	public function __construct(array $config, ?string $type = null)
	{
		$type = !$type ? 'mysql' : $type;

		// Validate connection type
		if (!in_array($type, $this->acceptedTypes))
		{
			throw new RuntimeException('The provided database connection [' . $type . '] is not supported. Supported connections are [' . implode(', ', $this->acceptedTypes) . ']');
		}

		if (isset($config['dsn']))
		{
			$this->dsn = $config['dsn'];
		}
		elseif ($type === 'mysql')
		{
			$this->dsn = "mysql:dbname=$config[name];host=$config[host]";

			if (isset($config['port']))
			{
				$this->dsn .= ';port=' . $config['port'];
			}
		}
		elseif ($type === 'sqlite')
		{
			$this->dsn = "sqlite:sqlite:$config[path]";
		}

		$this->host = $config['host'] ?? null;

		$this->name = $config['name'] ?? null;

		$this->username = $config['username'] ?? null;

		$this->password = $config['password'] ?? null;

		$this->options = $config['options'] ?? null;

		$this->tablePrefix = $config['table_prefix'] ?? '';

		$this->type = $type;

		$this->pdo = $this->connect();
	}

	/**
	 * Creates a PDO instance.
	 *
	 * @return PDO
	 */
	protected function connect(): PDO
	{
		try
		{
			$this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->getConnectionOptions());
		}
		catch(PDOException $e)
		{
			throw new PDOException(vsprintf('%s(): Failed to connect to the [ %s ] database. %s', [__METHOD__, $this->name, $e->getMessage()]));
		}

		return $this->pdo;
	}

	/**
	 * Creates a new PDO instance.
	 *
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return !is_null($this->pdo);
	}

	/**
	 * Creates a new PDO instance.
	 *
	 * @return PDO
	 */
	public function reconnect(): PDO
	{
		$this->pdo = $this->connect();

		return $this->pdo;
	}

	/**
	 * Creates a new PDO instance.
	 *
	 * @return PDO
	 */
	public function pdo(): PDO
	{
		if (!$this->isConnected())
		{
			return $this->connect();
		}

		return $this->pdo;
	}

	/**
	 * Get the table prefix.
	 *
	 * @return string
	 */
	public function tablePrefix(): string
	{
		return $this->tablePrefix;
	}

	/**
	 * Checks if the connection is alive.
	 *
	 * @return bool
	 */
	public function isAlive(): bool
	{
		if (!$this->pdo)
		{
			return false;
		}

		try
		{
			$this->pdo->query('SELECT 1');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}

 	/**
 	 * Close the current connection.
 	 */
 	public function close(): void
 	{
 		$this->pdo = null;
 	}

	/**
	 * Return a new Query builder instance.
	 *
	 * @return \serve\database\query\Builder
	 */
	public function builder(): Builder
	{
		return new Builder($this->handler());
	}

	/**
	 * Return a new Query builder instance.
	 *
	 * @return \serve\database\connection\ConnectionHandler
	 */
	public function handler(): ConnectionHandler
	{
		if (!$this->handler)
		{
			$this->handler = new ConnectionHandler($this, new Cache);
		}

		return $this->handler;
	}

	/**
	 * Returns the connection type.
	 *
	 * @return string
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Returns the connection options.
	 *
	 * @return array
	 */
	protected function getConnectionOptions(): array
	{
		return
		[
			PDO::ATTR_PERSISTENT         => $this->options['ATTR_PERSISTENT'] ?? false,
			PDO::ATTR_ERRMODE            => $this->options['ATTR_ERRMODE'] ?? PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => $this->options['ATTR_DEFAULT_FETCH_MODE'] ?? PDO::FETCH_ASSOC,
			PDO::ATTR_STRINGIFY_FETCHES  => $this->options['ATTR_STRINGIFY_FETCHES'] ?? false,
			PDO::ATTR_EMULATE_PREPARES   => $this->options['ATTR_EMULATE_PREPARES'] ?? false,
		];
	}
}
