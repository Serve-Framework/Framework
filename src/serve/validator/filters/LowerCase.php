<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function strtolower;

/**
 * Lowercase.
 *
 * @author Joe J. Howard
 */
class LowerCase extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return strtolower($value);
	}
}
