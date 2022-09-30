<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\security\crypto;

use serve\security\Crypto;
use serve\security\crypto\encrypters\OpenSSL;
use serve\security\crypto\Signer;
use serve\security\password\hashers\NativePHP;
use serve\tests\TestCase;

/**
 * @group unit
 */
class CryptoTest extends TestCase
{
	/**
	 *
	 */
	public function testEncrypt(): void
	{
		$this->expectNotToPerformAssertions();

		$data = 'foobar!!$#$@#"$#@!$P:{';

		$signer = $this->mock(Signer::class);

		$encrypter = $this->mock(OpenSSL::class);

		$password = $this->mock(NativePHP::class);

		$crypto = new Crypto($signer, $encrypter, $password);

		$encrypter->shouldReceive('encrypt')->withArgs([$data])->andReturn('ENCRYPTED');

		$signer->shouldReceive('sign')->withArgs(['ENCRYPTED'])->andReturn('SIGNED AND ENCRYPTED');

		$crypto->encrypt($data);
	}

	/**
	 *
	 */
	public function testDecrypt(): void
	{
		$this->expectNotToPerformAssertions();

		$signer = $this->mock(Signer::class);

		$encrypter = $this->mock(OpenSSL::class);

		$password = $this->mock(NativePHP::class);

		$crypto = new Crypto($signer, $encrypter, $password);

		$signer->shouldReceive('validate')->withArgs(['SIGNED AND ENCRYPTED'])->andReturn('UNSIGNED ENCRYPTEDSTRING');

		$encrypter->shouldReceive('decrypt')->withArgs(['UNSIGNED ENCRYPTEDSTRING'])->andReturn('raw data');

		$crypto->decrypt('SIGNED AND ENCRYPTED');
	}
}
