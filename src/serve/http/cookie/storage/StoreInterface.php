<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\cookie\storage;

/**
 * Cookie encrypt write / read decrypt.
 *
 * @author Joe J. Howard
 */
interface StoreInterface
{
	/**
	 * Decrypts and reads a cookie by name.
	 *
	 * @param  string $key key to read from
	 * @return mixed
	 */
	public function read(string $key);

	/**
	 * Encypts and writes a cookie by name.
	 *
	 * @param string $key   key to read from
	 * @param mixed  $value Value to save
	 */
	public function write(string $key, $value);
}
