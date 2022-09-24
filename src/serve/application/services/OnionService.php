<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\onion\Onion;

/**
 * Onion/Middleware service.
 *
 * @author Joe J. Howard
 */
class OnionService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Onion', function($container)
		{
			return new Onion($container->Request, $container->Response);
		});
	}
}
