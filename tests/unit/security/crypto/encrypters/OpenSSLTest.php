<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\security\crypto\encrypters;

use serve\security\crypto\encrypters\OpenSSL;
use serve\tests\TestCase;

/**
 * @group unit
 */
class OpenSSLTest extends TestCase
{
	/**
	 *
	 */
	public function testEncryptDecrypt(): void
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		$encrypter = new OpenSSL('secret-code');

		$hashed = $encrypter->encrypt($data);

		$this->assertEquals($data, $encrypter->decrypt($hashed));
	}

	/**
	 *
	 */
	public function testCyphers(): void
	{
		$data = 'foobar!!$#$@#"$#@!$P:{';

		foreach (openssl_get_cipher_methods() as $cypher)
		{
			if (str_contains($cypher, 'gcm') || str_contains($cypher, 'ccm'))
			{
				continue;
			}

			$encrypter = new OpenSSL('secret-code', $cypher);

			$hashed = $encrypter->encrypt($data);

			$this->assertEquals($data, $encrypter->decrypt($hashed));
		}
	}
}
