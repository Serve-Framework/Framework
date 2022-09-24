<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules\traits;

/**
 * Doesn't validate when empty trait.
 *
 * @author Joe J. Howard
 */
trait DoesntValidateWhenEmptyTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function validateWhenEmpty(): bool
	{
		return false;
	}
}
