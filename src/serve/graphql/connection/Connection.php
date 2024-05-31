<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\connection;

use Closure;
use Exception;
use serve\graphql\client\Client;
use serve\graphql\exception\GraphqlException;
use serve\graphql\query\Builder;

use function call_user_func;
use function is_callable;

/**
 * Graphql connection.
 *
 * @author Joe J. Howard
 */
class Connection
{
	/**
	 * Connection name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Are we connected.
	 *
	 * @var bool
	 */
	protected $connected = false;

	/**
	 * Graphql client.
	 *
	 * @var \serve\graphql\client\Client
	 */
	protected $client;

	/**
	 * Connection handler.
	 *
	 * @var \serve\graphql\connection\ConnectionHandler
	 */
	protected $connectionHandler;

	/**
	 * Pre connect callback.
	 *
	 * @var \Closure|null
	 */
	protected $preConnect;

	/**
	 * Constructor.
	 *
	 * @param string                                      $name              Connection name
	 * @param \serve\graphql\client\Client                $client            Client
	 * @param \serve\graphql\connection\ConnectionHandler $connectionHandler Connection handler
	 * @param \Closure|null                               $preConnect        Pre connection callback
	 */
	public function __construct(string $name, Client $client, ConnectionHandler $connectionHandler, ?Closure $preConnect = null)
	{
		$this->name = $name;

		$this->client = $client;

		$this->connectionHandler = $connectionHandler;

		$this->preConnect = $preConnect;

		$this->connect();
	}

	/**
	 * Checks is we are connected.
	 *
	 * @return bool
	 */
	public function connected(): bool
	{
		return $this->connected;
	}

	/**
	 * Re-connects the graphql client.
	 *
	 * @return \serve\graphql\client\Client
	 */
	public function reconnect(): Client
	{
		$this->connected = false;

		return $this->connect();
	}

 	/**
 	 * Close the current connection.
 	 */
 	public function close(): void
 	{
 		$this->connected = false;
 	}

	/**
	 * Returns the client instance.
	 *
	 * @return \serve\graphql\client\Client
	 */
	public function client(): Client
	{
		return $this->connect();
	}

	/**
	 * Returns a new query builder instance.
	 *
	 * @return \serve\graphql\query\Builder
	 */
	public function builder(): Builder
	{
		return new Builder($this->connectionHandler);
	}

	/**
	 * Return ConnectionHandler instance.
	 *
	 * @return \serve\graphql\connection\ConnectionHandler
	 */
	public function handler(): ConnectionHandler
	{
		return $this->connectionHandler;
	}

	/**
	 * Runs any pre-connection callbacks and returns the client.
	 *
	 * @return \serve\graphql\client\Client
	 */
	protected function connect(): Client
	{
		if (!$this->connected)
		{
			if ($this->preConnect && is_callable($this->preConnect))
			{
				$callback = $this->preConnect;

				try
				{
					call_user_func($callback, $this);
				}
				catch(Exception $e)
				{
					throw new GraphqlException('Failed to connect to Graphql connection [ ' . $this->name . ' ] : ' . $e->getMessage());
				}
			}

			$this->connected = true;
		}

		return $this->client;
	}
}
