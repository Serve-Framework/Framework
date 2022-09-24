<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

/**
 * With parameters interface.
 *
 * @author Joe J. Howard
 */
interface WithParametersInterface
{
	/**
	 * Sets the validation rule parameters.
	 *
	 * @param array $parameters Parameters
	 */
	public function setParameters(array $parameters);
}
