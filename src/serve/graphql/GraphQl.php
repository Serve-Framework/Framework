<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql;

use RuntimeException;
use serve\graphql\connection\Connection;
use serve\graphql\connection\ConnectionFactory;
use serve\graphql\query\Builder;

use function vsprintf;

/**
 * GraphQL Service.
 *
 * @author Joe J. Howard
 */
class GraphQl
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
     * Connection factory instance.
     *
     * @var \serve\graphql\connection\ConnectionFactory
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param serve\graphql\connection\ConnectionFactory $factory        Connection factory
     * @param array                                      $configurations Array of configuration options
     */
    public function __construct(ConnectionFactory $factory, array $configurations)
    {
        $this->configurations = $configurations['configurations'];

        $this->default = $configurations['default'];

        $this->factory = $factory;
    }

    /**
     * Get a graphql connection by name.
     *
     * @param  string|null                          $connectionName Name of the connection (optional) (default null)
     * @return \serve\graphql\connection\Connection
     */
    public function connection(?string $connectionName = null): Connection
    {
        $connectionName = !$connectionName ? $this->default : $connectionName;

        return $this->connect($connectionName);
    }

    /**
     * Connect to graphql endpoint by name.
     *
     * @param  string                               $connectionName Name of the connection
     * @throws RuntimeException
     * @return \serve\graphql\connection\Connection
     */
    protected function connect(string $connectionName): Connection
    {
        if(!isset($this->configurations[$connectionName]))
        {
            throw new RuntimeException(vsprintf('%s(): [ %s ] has not been defined in the graphql configuration.', [__METHOD__, $connectionName]));
        }

        if (isset($this->connections[$connectionName]))
        {
            return $this->connections[$connectionName];
        }

        $this->connections[$connectionName] = $this->factory->connection($connectionName, $this->configurations[$connectionName]);

        return $this->connections[$connectionName];
    }

    /**
     * Get a graphql query builder by connection name.
     *
     * @param  string|null                  $connectionName Name of the connection
     * @return \serve\graphql\query\Builder
     */
    public function builder(?string $connectionName = null): Builder
    {
        $connectionName = !$connectionName ? $this->default : $connectionName;

        return $this->connect($connectionName)->builder();
    }

    /**
     * Get all graphql connections.
     *
     * @return array
     */
    public function connections(): array
    {
        return $this->connections;
    }
}
