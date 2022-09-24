<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\event\Events;
use serve\event\Filters;
use serve\application\services\Service;

/**
 * Event and Filter service.
 *
 * @author Joe J. Howard
 */
class EventService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Events', function($container)
		{
			return new Events;
		});

		$this->container->singleton('Filters', function($container)
		{
			return new Filters;
		});
	}
}
