<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\crawler\CrawlerDetect;
use serve\crawler\fixtures\Inclusions;

/**
 * UserAgent Crawler Service.
 *
 * @author Joe J. Howard
 */
class CrawlerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('UserAgent', function($container)
		{
			return new CrawlerDetect($container->Request->headers(), new Inclusions);
		});
	}
}
