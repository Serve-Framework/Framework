<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\console;

/**
 * Command interface.
 *
 * @author Joe J. Howard
 */
interface CommandInterface
{
	/**
	 * Returns the command description.
	 *
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * Returns the command arguments.
	 *
	 * @return array
	 */
	public function getArguments(): array;
}
