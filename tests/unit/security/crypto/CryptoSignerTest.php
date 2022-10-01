<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\security\crypto;

use serve\security\crypto\Signer;
use serve\tests\TestCase;

/**
 * @group unit
 */
class CryptoSignerTest extends TestCase
{
	/**
	 *
	 */
	public function testSigner(): void
	{
		$data = 'foobar!!$#$@::32342:#"$#@!$P:{';

		$signer = new Signer('secret-code');

		$signed = $signer->sign($data);

		$unsigned = $signer->validate($signed);

		$this->assertEquals($data, $unsigned);
	}
}
