<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\cli\commands;

use serve\console\Command;

/**
 * Generate application secret.
 *
 * @author Joe J. Howard
 */
class ServicesList extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Lists available container services.';

	/**
	 * {@inheritDoc}
	 */
	public function execute(): void
	{
		$services = $this->container->keys();
		$cols     = ['<green>Service</green>', '<green>Application Access</green>'];
		$rows     = [];

		sort($services);

		foreach ($services as $service)
		{
			$rows[] = [$service, '<yellow>$serve->' . $service . '</yellow>'];
		}

		$this->write($this->container->count() . ' available services:');

		$this->table($cols, $rows);
	}
}
