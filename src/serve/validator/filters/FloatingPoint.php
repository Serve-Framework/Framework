<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function floatval;

/**
 * Filter float.
 *
 * @author Joe J. Howard
 */
class FloatingPoint extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return floatval($value);
	}
}
