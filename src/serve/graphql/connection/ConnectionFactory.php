<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\connection;

use serve\cache\Cache as FileCache;
use serve\exception\ErrorHandler;
use serve\graphql\client\Client;

/**
 * Graphql connection factory.
 *
 * @author Joe J. Howard
 */
class ConnectionFactory
{
	/**
	 * File\DB Cache instance.
	 *
	 * @var \serve\cache\Cache
	 */
	protected $fileCache;

	/**
	 * Error handler instance.
	 *
	 * @var \serve\exception\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * Constructor.
	 *
	 * @param \serve\cache\Cache            $fileCache    File cache
	 * @param \serve\exception\ErrorHandler $errorHandler Error handler
	 */
	public function __construct(FileCache $fileCache, ErrorHandler $errorHandler)
	{
		$this->errorHandler = $errorHandler;

		$this->fileCache = $fileCache;
	}

	/**
	 * Creates connection.
	 *
	 * @param  string                               $connectionName Connection name
	 * @param  array                                $config         Connection configurations
	 * @return \serve\graphql\connection\Connection
	 */
	public function connection(string $connectionName, array $config): Connection
	{
		$config['throttle']['key'] = 'graphql.' . $connectionName;

		$throttle = new Throttle(new ThrottleStorage($this->fileCache));

		$cache = new Cache($config['cache']);

		$client = new Client($config['domain'], $config['path'], $config['auth']);

		$handler = new ConnectionHandler($client, $cache, $throttle, $this->errorHandler, $config['throttle']);

		return new Connection($connectionName, $client, $handler, $config['pre_connect']);
	}

}
