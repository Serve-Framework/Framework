<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters\traits;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
trait FiltersWhenUnset
{
	/**
	 * {@inheritdoc}
	 */
	public function filterWhenUnset(): bool
	{
		return true;
	}
}
