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
	 * @param int|null       $code     Exception code
	 * @param string|null    $message  Exception message
	 * @param Throwable|null $previous Previous exception
	 */
	public function __construct(?int $code = 500, ?string $message = 'Aw, snap! An error has occurred while processing your request.', ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
