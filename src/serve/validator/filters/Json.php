<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function json_decode;

/**
 * Json decode.
 *
 * @author Joe J. Howard
 */
class Json extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return json_decode($value, true);
	}
}
