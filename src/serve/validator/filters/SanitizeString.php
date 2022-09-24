<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

/**
 * Sanitize string.
 *
 * @author Joe J. Howard
 */
class SanitizeString extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value): string
	{
		return strip_tags($value);
	}
}
