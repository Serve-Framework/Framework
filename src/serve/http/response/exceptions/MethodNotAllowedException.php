<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response\exceptions;

use Throwable;

/**
 * 405 Exception.
 *
 * @author Joe J. Howard
 */
class MethodNotAllowedException extends RequestException
{
	/**
	 * Allowed methods.
	 *
	 * @var array
	 */
	protected $allowedMethods;

	/**
	 * Constructor.
	 *
	 * @param array           $allowedMethods Allowed methods
	 * @param string|null     $message        Exception message
	 * @param \Throwable|null $previous       Previous exception
	 */
	public function __construct(array $allowedMethods = [], string $message = null, Throwable $previous = null)
	{
		$this->allowedMethods = $allowedMethods;

		parent::__construct(405, $message, $previous);
	}

	/**
	 * Returns the allowed methods.
	 *
	 * @return array
	 */
	public function getAllowedMethods(): array
	{
		return $this->allowedMethods;
	}
}
