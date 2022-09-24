<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use function html_entity_decode;

/**
 * Html decode.
 *
 * @author Joe J. Howard
 */
class HtmlDecode extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		return html_entity_decode($value, ENT_QUOTES);
	}
}
