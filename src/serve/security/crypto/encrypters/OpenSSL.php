<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\security\crypto\encrypters;

use function array_filter;

use function base64_decode;
use function base64_encode;
use function in_array;
use function mb_substr;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_get_cipher_methods;
use function openssl_random_pseudo_bytes;
use function strpos;
use function strtolower;

/**
 * Encryption/Decryption interface.
 *
 * @author Joe J. Howard
 */
class OpenSSL extends Encrypter implements EncrypterInterface
{
	/**
	 * Key used to encrypt/decrypt string.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The cipher method to use for encryption.
	 *
	 * @var string
	 */
	protected $cipher;

	/**
	 * Initialization vector size.
	 *
	 * @var int
	 */
	protected $ivSize;

	/**
	 * Cyphers we don't use.
	 *
	 * @var array
	 */
	protected $acceptedCyphers =
	[
		'aes-128-cfb1',
		'aes-128-cfb8',
		'aes-128-ctr',
		'aes-128-ofb',
		'aes-128-xts',
		'aes-192-cfb',
		'aes-192-cfb1',
		'aes-192-cfb8',
		'aes-192-ctr',
		'aes-192-ofb',
		'aes-256-cfb',
		'aes-256-cfb1',
		'aes-256-cfb8',
		'aes-256-ctr',
		'aes-256-ofb',
		'aes-256-xts',
		'aria-128-cfb',
		'aria-128-cfb1',
		'aria-128-cfb8',
		'aria-128-ctr',
		'aria-128-ofb',
		'aria-192-cfb',
		'aria-192-cfb1',
		'aria-192-cfb8',
		'aria-192-ctr',
		'aria-192-ofb',
		'aria-256-cfb',
		'aria-256-cfb1',
		'aria-256-cfb8',
		'aria-256-ctr',
		'aria-256-ofb',
	];

	/**
	 * Constructor.
	 *
	 * @param string $key    Encryption key
	 * @param string $cipher Cipher (optional) (default 'AES-256-CTR')
	 */
	public function __construct(string $key, string $cipher = 'AES-256-CTR')
	{
		$this->key = $key;

		$this->cipher = !in_array($cipher, $this->cyphers()) ? 'AES-256-CTR' : $cipher;

		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	/**
	 * {@inheritDoc}
	 */
	public function cyphers(): array
	{
		return array_filter(openssl_get_cipher_methods(), function ($cypher)
		{
			return in_array(strtolower($cypher), $this->acceptedCyphers);
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function encrypt(string $string): string
	{
		$iv = $this->ivSize === 0 ? '' : openssl_random_pseudo_bytes($this->ivSize);

		$key = $this->deriveKey($this->key, $iv, 32);

		return base64_encode($iv . openssl_encrypt($string, $this->cipher, $key, 0, $iv));
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrypt(string $string)
	{
		$string = base64_decode($string, true);

		if($string === false)
		{
			return false;
		}

		$iv = mb_substr($string, 0, $this->ivSize, '8bit');

		$string = mb_substr($string, $this->ivSize, null, '8bit');

		$key = $this->deriveKey($this->key, $iv, 32);

		return openssl_decrypt($string, $this->cipher, $key, 0, $iv);
	}
}
