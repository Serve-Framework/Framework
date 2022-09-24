<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\DoesntValidateWhenEmptyTrait;

/**
 * Base rule.
 *
 * @author Joe J. Howard
 */
abstract class Rule
{
	use DoesntValidateWhenEmptyTrait;
}
