<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use serve\validator\filters\traits\DoesntFilterWhenUnset;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
abstract class FilterBase
{
	use DoesntFilterWhenUnset;
}
