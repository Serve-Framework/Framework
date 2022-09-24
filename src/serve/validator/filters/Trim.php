<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

/**
 * Trim.
 *
 * @author Joe J. Howard
 */
class Trim extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return trim($value);
	}
}
