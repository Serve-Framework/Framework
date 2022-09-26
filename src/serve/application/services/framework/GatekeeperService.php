<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\auth\Gatekeeper;
use serve\database\wrappers\providers\UserProvider;
use serve\database\wrappers\managers\UserManager;

/**
 * Gatekeeper Service.
 *
 * @author Joe J. Howard
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->registerGatekeeper();

		$this->registerProviders();

		$this->registerManagers();
	}

	/**
	 * Registers the Gatekeeper
	 */
	private function registerGatekeeper(): void
	{
		$this->container->singleton('Gatekeeper', function ($container)
		{
			return new Gatekeeper(
			    $container->Database->connection()->builder(),
			    $container->UserProvider,
			    $container->Crypto,
			    $container->Cookie,
			    $container->Session
			);
		});
	}

	/**
	 * Registers the wrapper providers.
	 */
	private function registerProviders(): void
	{
		$this->container->singleton('UserProvider', function($container)
		{
			return new UserProvider($container->Database->connection()->builder());
		});
	}

	/**
	 * Registers the managers.
	 */
	private function registerManagers(): void
	{
		$this->container->singleton('UserManager', function($container)
		{
			return new UserManager(
				$container->UserProvider,
				$container->Crypto
			);
		});
	}

}
