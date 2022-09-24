<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\auth\Gatekeeper;

/**
 * CMS Gatekeeper.
 *
 * @author Joe J. Howard
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Gatekeeper', function($container)
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
}
