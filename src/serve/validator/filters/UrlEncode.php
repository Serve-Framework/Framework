<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

/**
 * Url encode.
 *
 * @author Joe J. Howard
 */
class UrlEncode extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return urlencode($value);
	}
}
