<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response\exceptions;

use Throwable;

/**
 * 404 Exception.
 *
 * @author Joe J. Howard
 */
class NotFoundException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null    $message  Exception message
	 * @param Throwable|null $previous Previous exception
	 */
	public function __construct(?string $message = 'The resource you requested could not be found. It may have been moved or deleted.', ?Throwable $previous = null)
	{
		parent::__construct(404, $message, $previous);
	}
}
