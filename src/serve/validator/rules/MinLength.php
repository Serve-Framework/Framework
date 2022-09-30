<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\WithParametersTrait;

use function mb_strlen;

use function sprintf;

/**
 * Min length rule.
 *
 * @author Joe J. Howard
 */
class MinLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['minLength'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) >= $this->getParameter('minLength');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of The "%1$s" field must be at least %2$s characters long.', $field, $this->getParameter('minLength'));
	}
}
