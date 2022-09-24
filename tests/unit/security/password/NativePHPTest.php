<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\security\password;

use serve\security\password\hashers\NativePHP;
use serve\tests\TestCase;

/**
 * @group unit
 */
class NativePHPTest extends TestCase
{
	/**
	 *
	 */
	public function testHash(): void
	{
		$password = new NativePHP;

		$hashed = $password->hash('f43423o$#@$!!$!GEWPG{"__+)_)o');

		$this->assertTrue($password->verify('f43423o$#@$!!$!GEWPG{"__+)_)o', $hashed));
	}

	/**
	 *
	 */
	public function testAlgos(): void
	{
		$algos =
		[
			PASSWORD_BCRYPT,
			PASSWORD_DEFAULT,
		];

		foreach ($algos as $algo)
		{
			$password = new NativePHP($algo);

			$hashed = $password->hash('f43423o$#@$!!$!GEWPG{"__+)_)o');

			$this->assertTrue($password->verify('f43423o$#@$!!$!GEWPG{"__+)_)o', $hashed));
		}
	}
}
