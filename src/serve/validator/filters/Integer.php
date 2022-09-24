<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use serve\validator\filters\traits\FiltersWhenUnset;

/**
 * Integer.
 *
 * @author Joe J. Howard
 */
class Integer extends FilterBase implements FilterInterface
{
	use FiltersWhenUnset;

	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return $value === '' ? null : intval($value);
	}
}
