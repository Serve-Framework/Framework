<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\event\Events;
use serve\event\Filters;

/**
 * Event and Filter service.
 *
 * @author Joe J. Howard
 */
class EventService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Events', function ($container)
		{
			return new Events;
		});

		$this->container->singleton('Filters', function ($container)
		{
			return new Filters;
		});
	}
}
