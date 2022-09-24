<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * MatchField rule.
 *
 * @author Joe J. Howard
 */
class MatchField extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['field'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return $this->getParameter('field') === $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value "%1$s" of the "%2$s" field must did not match', $this->getParameter('field'), $field);
	}
}
