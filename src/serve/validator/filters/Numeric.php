<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function preg_replace;

/**
 * Numeric.
 *
 * @author Joe J. Howard
 */
class Numeric extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return preg_replace('/[^0-9]+/', '', $value);
	}
}
