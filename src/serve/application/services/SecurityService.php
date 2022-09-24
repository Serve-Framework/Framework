<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services;

use serve\security\Crypto;
use serve\security\crypto\encrypters\OpenSSL;
use serve\security\crypto\Key;
use serve\security\crypto\Signer;
use serve\security\password\hashers\NativePHP;
use serve\security\spam\gibberish\Gibberish;
use serve\security\spam\SpamProtector;
use serve\validator\ValidatorFactory;

/**
 * Security service.
 *
 * @author Joe J. Howard
 */
class SecurityService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Crypto', function($container)
		{
			return new Crypto($this->getSinger(), $this->getEncrypter(), $this->getPassword());
		});

		$this->container->singleton('Spam', function($container)
		{
			return new SpamProtector($this->getGibberish(), $container->Config);
		});

		$this->container->singleton('Validator', function($container)
		{
			return new ValidatorFactory($container);
		});
	}

	/**
	 * Returns the encryption signer.
	 *
	 * @return \serve\security\crypto\Signer
	 */
	private function getSinger(): Signer
	{
		return new Signer($this->container->Config->get('application.secret'));
	}

	/**
	 * Returns the encryption library.
	 *
	 * @return mixed
	 */
	protected function getEncrypter()
	{
		$configuration = $this->container->Config->get('crypto.configurations.' . $this->container->Config->get('crypto.default'));

		$library = $configuration['library'];

		if ($library === 'openssl')
		{
			return $this->openSSLEncrypter($configuration);
		}
	}

	/**
	 * Returns the the Open SSL Encrypter/Decrypter implementation.
	 *
	 * @param  array                                               $configuration Encryption configuration
	 * @return \serve\security\crypto\encrypters\OpenSSL
	 */
	protected function openSSLEncrypter(array $configuration): OpenSSL
	{
		return new OpenSSL(Key::decode($configuration['key']), $configuration['cipher']);
	}

	/**
	 * Returns the password hashing library.
	 *
	 * @return mixed
	 */
	protected function getPassword()
	{
		$passwordConfiguration = $this->container->Config->get('password.configurations.' . $this->container->Config->get('password.default'));

		$library = $passwordConfiguration['library'];

		if ($library === 'nativePHP')
		{
			return $this->nativePasswordHasher($passwordConfiguration);
		}
	}

	/**
	 * Returns the the native PHP password hasher.
	 *
	 * @param  array                                                   $passwordConfiguration Configuration to pass to constructor
	 * @return \serve\security\password\hashers\NativePHP
	 */
	protected function nativePasswordHasher(array $passwordConfiguration): NativePHP
	{
		return new NativePHP($passwordConfiguration['algo']);
	}

	/**
	 * Returns the gibberish detector.
	 *
	 * @return \serve\security\spam\gibberish\Gibberish
	 */
	protected function getGibberish(): Gibberish
	{
		return new Gibberish($this->container->Config->get('spam.gibberish_lib'));
	}
}
