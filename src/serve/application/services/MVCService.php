<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\mvc\view\View;

/**
 * MVC Service.
 *
 * @author Joe J. Howard
 */
class MVCService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('View', function()
		{
			return new View;
		});
	}
}
