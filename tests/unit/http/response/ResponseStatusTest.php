<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\http\response;

use serve\http\response\Status;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ResponseStatusTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$status = new Status;

		$this->assertEquals(200, $status->get());

		$status->set(404);

		$this->assertEquals(404, $status->get());
	}

	/**
	 *
	 */
	public function testMessage(): void
	{
		$status = new Status;

		$this->assertEquals('OK', $status->message());

		$status->set(404);

		$this->assertEquals('Not Found', $status->message());
	}

	/**
	 *
	 */
	public function testHelpers(): void
	{
		$status = new Status;

		$this->assertTrue($status->isOk());

		$status->set(102);

		$this->assertTrue($status->isInformational());

		$status->set(204);

		$this->assertTrue($status->isEmpty());

		$status->set(206);

		$this->assertTrue($status->isSuccessful());

		$status->set(302);

		$this->assertTrue($status->isRedirect());

		$status->set(403);

		$this->assertTrue($status->isForbidden());

		$status->set(404);

		$this->assertTrue($status->isNotFound());

		$status->set(440);

		$this->assertTrue($status->isClientError());

		$status->set(500);

		$this->assertTrue($status->isServerError());
	}
}
