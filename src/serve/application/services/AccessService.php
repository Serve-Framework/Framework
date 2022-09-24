<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\access\Access;
use serve\application\services\Service;

/**
 * Access service.
 *
 * @author Joe J. Howard
 */
class AccessService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Access', function($container)
		{
			return new Access($container->Request, $container->Response, $container->Filesystem, $container->Config->get('cms.security.ip_blocked'), $container->Config->get('cms.security.ip_whitelist'));
		});
	}
}
