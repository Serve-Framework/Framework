<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database;

use RuntimeException;
use serve\database\builder\Builder;
use serve\database\connection\Connection;

use function vsprintf;

/**
 * Database manager.
 *
 * @author Joe J. Howard
 */
class Database
{
	/**
	 * Name of the default configuration to use.
	 *
	 * @var string
	 */
	protected $default;

	/**
	 * Array of configurations settings.
	 *
	 * @var array
	 */
	protected $configurations;

	/**
	 * Array of database connections.
	 *
	 * @var array
	 */
	protected $connections = [];

	/**
	 * Constructor.
	 *
	 * @param array $configurations Array of configuration options
	 */
	public function __construct(array $configurations)
	{
		$this->configurations = $configurations['configurations'];

		$this->default = $configurations['default'];
	}

	/**
	 * Create a new database from the config.
	 *
	 * @param  string|null                           $connectionName Name of the connection (optional) (default null)
	 * @throws RuntimeException
	 * @return \serve\database\connection\Connection
	 */
	public function create(?string $connectionName = null)
	{
		$connectionName = !$connectionName ? $this->default : $connectionName;

		if (!isset($this->configurations[$connectionName]['name']))
		{
			throw new RuntimeException(vsprintf('%s(): [ %s ] has not been defined in the database configuration.', [__METHOD__, $connectionName]));
		}

		$config = $this->configurations[$connectionName];

		if ($config['type'] === 'sqlite')
		{
			return $this->connect($connectionName);
		}

		$databaseName = $config['name'];

		if (!isset($config['dsn']))
		{
			$config['dsn'] = "mysql:host=$config[host]";
		}

		$connection = new Connection($config);

		$connection->handler()->query("DROP DATABASE IF EXISTS $databaseName");

		$connection->handler()->query("CREATE DATABASE $databaseName");

		return $this->connect($connectionName);
	}

	/**
	 * Get a database connection by name.
	 *
	 * @param  string|null                           $connectionName Name of the connection (optional) (default null)
	 * @return \serve\database\connection\Connection
	 */
	public function connection(?string $connectionName = null): Connection
	{
		$connectionName = !$connectionName ? $this->default : $connectionName;

		return $this->connect($connectionName);
	}

	/**
	 * Connect to a database by name.
	 *
	 * @param  string                                $connectionName Name of the connection
	 * @throws RuntimeException
	 * @return \serve\database\connection\Connection
	 */
	protected function connect(string $connectionName): Connection
	{
		if(!isset($this->configurations[$connectionName]))
		{
			throw new RuntimeException(vsprintf('%s(): [ %s ] has not been defined in the database configuration.', [__METHOD__, $connectionName]));
		}

		if (isset($this->connections[$connectionName]))
		{
			return $this->connections[$connectionName];
		}

		$this->connections[$connectionName] = new Connection($this->configurations[$connectionName], isset($this->configurations[$connectionName]['type']) ? $this->configurations[$connectionName]['type'] : null);

		return $this->connections[$connectionName];
	}

	/**
	 * Get a database builder by connection name.
	 *
	 * @param  string                          $connectionName Name of the connection
	 * @return \serve\database\builder\Builder
	 */
	public function builder(?string $connectionName = null): Builder
	{
		$connectionName = !$connectionName ? $this->default : $connectionName;

		return $this->connect($connectionName)->builder();
	}

	/**
	 * Get all database connection.
	 *
	 * @return array
	 */
	public function connections(): array
	{
		return $this->connections;
	}
}
