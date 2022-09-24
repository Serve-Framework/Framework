<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator;

use serve\ioc\Container;

/**
 * Validator factory.
 *
 * @author Joe J. Howard
 */
class ValidatorFactory
{
	/**
	 * Container.
	 *
	 * @var \serve\ioc\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \serve\ioc\Container $container Container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Creates and returns a validator instance.
	 *
	 * @param  array                      $input Array to validate
	 * @param  array                      $rules Array of validation rules
	 * @return \serve\validator\Validator
	 */
	public function create(array $input, array $rules, array $filters = []): Validator
	{
		return new Validator($input, $rules, $filters, $this->container);
	}
}
