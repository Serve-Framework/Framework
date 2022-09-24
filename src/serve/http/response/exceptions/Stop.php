<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response\exceptions;

use ErrorException;

/**
 * Stop the application gracefully.
 *
 * @author Joe J. Howard
 */
class Stop extends ErrorException
{
}
