<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\database\Database;

/**
 * Database services.
 *
 * @author Joe J. Howard
 */
class DatabaseService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Database', function($container)
		{
			return new Database($container->Config->get('database'));
		});
	}
}
