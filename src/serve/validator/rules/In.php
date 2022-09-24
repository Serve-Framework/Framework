<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\WithParametersTrait;

use function in_array;
use function json_encode;
use function sprintf;

/**
 * In rule.
 *
 * @author Joe J. Howard
 */
class In extends Rule implements RuleInterface, WithParametersInterface
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
		return in_array($value, $this->getParameter('values'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field must contain one of available options: %2$s', $field, json_encode($this->getParameter('values')));
	}
}
