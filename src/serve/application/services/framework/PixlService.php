<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use RuntimeException;
use serve\application\services\Service;
use serve\pixl\Image;
use serve\pixl\processor\GD;
use serve\pixl\processor\ProcessorInterface;

/**
 * UserAgent Crawler Service.
 *
 * @author Joe J. Howard
 */
class PixlService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Pixl', function ($container)
		{
			return new Image($this->getImageProcessor($container->Config->get('pixl')), '');
		});
	}

	/**
	 * Returns the image processor.
	 *
	 * @param  array                                    $config Pixl configuration
	 * @return \serve\pixl\processor\ProcessorInterface
	 */
	private function getImageProcessor(array $config): ProcessorInterface
	{
		if ($config['processor'] === 'GD')
		{
			return new GD(null, $config['compression']);
		}

		throw new RuntimeException($config['processor'] . ' is unrecognized');
	}
}
