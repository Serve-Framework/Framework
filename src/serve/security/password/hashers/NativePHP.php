<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\security\password\hashers;

/**
 * Native PHP hashing with polyfill fallback.
 *
 * @author Joe J. Howard
 */
class NativePHP extends Hasher implements HasherInterface
{
	/**
	 * PHP password hashing constant.
	 *
	 * @see http://php.net/manual/en/password.constants.php
	 * @var int|string
	 */
	protected $algo;

	/**
	 * Constructor.
	 *
	 * @param int|string $algo PHP password hashing constant (optional)
	 * @see   http://php.net/manual/en/password.constants.php
	 */
	public function __construct($algo = PASSWORD_DEFAULT)
	{
		$this->algo = $algo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hash(string $string): string
	{
		return password_hash($string, $this->algo, ['cost' => 8]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify(string $string, string $hashed): bool
	{
		return password_verify($string, $hashed);
	}
}
