<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\ioc\Container;

/**
 * Service provider base class.
 *
 * @author Joe J. Howard
 */
abstract class Service
{
	/**
	 * IoC container instance.
	 *
	 * @var \serve\ioc\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \serve\ioc\Container $container IoC container instance
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Registers the service.
	 */
	abstract public function register();
}
