<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\ValidatesWhenEmptyTrait;

use function in_array;
use function sprintf;

/**
 * Required rule.
 *
 * @author Joe J. Howard
 */
class Required extends Rule implements RuleInterface
{
	use ValidatesWhenEmptyTrait;

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return !in_array($value, ['', null, []], true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field is required.', $field);
	}
}
