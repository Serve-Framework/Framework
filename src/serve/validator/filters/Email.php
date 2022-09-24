<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

/**
 * Filter email.
 *
 * @author Joe J. Howard
 */
class Email extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return filter_var($value, FILTER_UNSAFE_RAW);
	}
}
