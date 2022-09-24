<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\security\password\hashers;

/**
 * Password hashing interface.
 *
 * @author Joe J. Howard
 */
interface HasherInterface
{
	/**
	 * Hashes a password.
	 *
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function hash(string $string): string;

	/**
	 * Verifies a hashed password with an unhashed one.
	 *
	 * @param  string $string String to decrypt
	 * @return bool
	 */
	public function verify(string $string, string $hashed): bool;
}
