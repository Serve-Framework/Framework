<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function htmlentities;

/**
 * Html encode.
 *
 * @author Joe J. Howard
 */
class HtmlEncode extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return htmlentities($value, ENT_QUOTES);
	}
}
