<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\WithParametersTrait;

use function in_array;
use function sprintf;

/**
 * Not in rule.
 *
 * @author Joe J. Howard
 */
class NotIn extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['values'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return !in_array($value, $this->getParameter('values'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field contains an invalid value.', $field);
	}
}
