<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function strtoupper;

/**
 * Uppercase.
 *
 * @author Joe J. Howard
 */
class UpperCase extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return strtoupper($value);
	}
}
