<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function strip_tags;

/**
 * Sanitize string.
 *
 * @author Joe J. Howard
 */
class SanitizeString extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value): string
	{
		return strip_tags($value);
	}
}
