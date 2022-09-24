<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\security\crypto\encrypters;

/**
 * Encryption/Decryption interface.
 *
 * @author Joe J. Howard
 */
interface EncrypterInterface
{
	/**
	 * Encrypts string.
	 *
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function encrypt(string $string): string;

	/**
	 * Decrypts string.
	 *
	 * @param  string $string String to decrypt
	 * @return mixed
	 */
	public function decrypt(string $string);
}
