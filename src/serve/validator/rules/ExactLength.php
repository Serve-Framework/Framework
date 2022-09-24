<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use serve\validator\rules\traits\WithParametersTrait;

use function intval;
use function mb_strlen;
use function sprintf;

/**
 * Exact length rule.
 *
 * @author Joe J. Howard
 */
class ExactLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['length'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) === intval($this->getParameter('length'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of The "%1$s" field must be exactly %2$s characters long.', $field, $this->getParameter('length'));
	}
}
