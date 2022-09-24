<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator\rules;

use RuntimeException;
use serve\validator\rules\traits\WithParametersTrait;

use function filter_var;
use function sprintf;
use function vsprintf;

/**
 * IP rule.
 *
 * @author Joe J. Howard
 */
class IP extends Rule implements RuleInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['version'];

	/**
	 * Returns the filter flags.
	 *
	 * @return int
	 */
	protected function getFlags(): int
	{
		if(($version = $this->getParameter('version', true)) === null)
		{
			return 0;
		}

		switch($version)
		{
			case 'v4':
				return FILTER_FLAG_IPV4;
			case 'v6':
				return FILTER_FLAG_IPV6;
			default:
				throw new RuntimeException(vsprintf('Invalid IP version [ %s ]. The accepted versions are v4 and v6.', [$version]));
		}
	}

	/**
	 * Returns the name of the IP version that we're validating.
	 *
	 * @return string
	 */
	protected function getVersion(): string
	{
		switch($this->getParameter('version', true))
		{
			case 'v4':
				return 'IPv4';
			case 'v6':
				return 'IPv6';
			default:
				return 'IP';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_IP, $this->getFlags()) !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field must contain a valid %2$s address.', $field, $this->getVersion());
	}
}
