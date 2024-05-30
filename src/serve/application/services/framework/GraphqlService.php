<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\graphql\connection\ConnectionFactory;
use serve\graphql\GraphQl;

/**
 * Graphql service.
 *
 * @author Joe J. Howard
 */
class GraphqlService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Graphql', function ($container)
		{
			$factory = new ConnectionFactory($container->Cache, $container->ErrorHandler);

			return new GraphQl($factory, $container->Config->get('graphql'));
		});
	}
}
