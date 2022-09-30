<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\http\response;

use RuntimeException;
use serve\http\response\exceptions\ForbiddenException;
use serve\http\response\exceptions\InvalidTokenException;
use serve\http\response\exceptions\MethodNotAllowedException;
use serve\http\response\exceptions\NotFoundException;
use serve\http\response\exceptions\RequestException;
use serve\http\response\exceptions\Stop;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testRequest(): void
	{
		$this->expectException(RequestException::class);

		throw new RequestException(500, 'foobar message');
	}

	/**
	 *
	 */
	public function testToken(): void
	{
		$this->expectException(InvalidTokenException::class);

		throw new InvalidTokenException;
	}

	/**
	 *
	 */
	public function testNotFound(): void
	{
		$this->expectException(NotFoundException::class);

		throw new NotFoundException;
	}

	/**
	 *
	 */
	public function testMethod(): void
	{
		$this->expectException(MethodNotAllowedException::class);

		throw new MethodNotAllowedException(['POST', 'GET'], 'foobar message');
	}

	/**
	 *
	 */
	public function testForbidden(): void
	{
		$this->expectException(RuntimeException::class);

		throw new ForbiddenException;
	}

	/**
	 *
	 */
	public function testStop(): void
	{
		$this->expectException(Stop::class);

		throw new Stop;
	}
}
