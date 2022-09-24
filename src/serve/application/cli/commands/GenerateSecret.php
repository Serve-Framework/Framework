<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\cli\commands;

use serve\console\Command;
use serve\security\crypto\Key;

/**
 * Generate application secret.
 *
 * @author Joe J. Howard
 */
class GenerateSecret extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generates a new application secret.';

	/**
	 * {@inheritDoc}
	 */
	public function execute(): void
	{
		$this->container->Config->set('application.secret', Key::generateEncoded());

		$this->container->Config->save();

		$this->write('Success: A new application secret has been generated.', 'green');
	}
}
