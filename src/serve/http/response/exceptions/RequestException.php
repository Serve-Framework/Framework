<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response\exceptions;

use RuntimeException;
use Throwable;

/**
 * Request exception.
 *
 * @author Joe J. Howard
 */
class RequestException extends RuntimeException
{
	/**
	 * Constructor.
	 *
	 * @param int             $code     Exception code
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(int $code, string $message = null, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
