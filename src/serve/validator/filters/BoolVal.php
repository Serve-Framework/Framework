<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\filters;

use serve\validator\filters\traits\FiltersWhenUnset;
use function boolval;
use function floatval;
use function intval;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function strtolower;
use function trim;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
class BoolVal extends FilterBase implements FilterInterface
{
	use FiltersWhenUnset;

	/**
	 * {@inheritDoc}
	 */
	public function filter(string $value)
	{
		if (is_null($value) || $value === false)
		{
			return false;
		}
		elseif (is_bool($value))
		{
			return boolval($value);
		}
		elseif (is_int($value))
		{
			return boolval($value);
		}
		elseif (is_float($value))
		{
			return floatval($value) > 0;
		}
		elseif (is_numeric($value))
		{
			return intval($value) > 0;
		}
		elseif (is_string($value))
		{
			$value = trim(strtolower($value));

			if ($value === 'yes' || $value === 'on' || $value === 'true' || $value === '1')
			{
				return true;
			}
			elseif ($value === 'no' || $value === 'off' || $value === 'false' || $value === '0' || $value === '-1' || $value === '')
			{
				return false;
			}
		}

		return boolval($value);
	}
}
