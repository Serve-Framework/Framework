<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use function ceil;
use function is_array;
use function preg_replace;
use function rtrim;
use function serialize;
use function str_repeat;
use function str_shuffle;
use function strlen;
use function substr;

/**
 * SQL "SET" statement wrapper.
 */
class Set
{
	/**
	 * Limit number.
	 *
	 * @var int
	 */
	protected $bindings;

	/**
	 * Constructor.
	 *
	 * @param array $values Values to set
	 */
	public function __construct(array $values)
	{
		foreach($values as $column => $value)
		{
			$key = $this->uniqueKey($column);

			if (is_array($value))
			{
				$this->bindings[$key] = [$column, $key, serialize($value)];
			}
			else
			{
				$this->bindings[$key] = [$column, $key, $value];
			}
		}
	}

	/**
	 * Returns SQL.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		$values  = '';

		foreach($this->bindings as $binding)
		{
			$values .=  $binding[0] . ' = :' . $binding[1] . ', ';
		}

		return rtrim($values, ', ');
	}

	/**
	 * Returns array of bindings.
	 *
	 * @return array
	 */
	public function bindings(): array
	{
		$bindings = [];

		foreach($this->bindings as $binding)
		{
			$bindings[$binding[1]] = $binding[2];
		}

		return $bindings;
	}

	/**
	 * Generates a random unique key for the binding.
	 *
	 * @param  string $str Key to provide for binding
	 * @return string
	 */
	protected function uniqueKey($str): string
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		$key = str_shuffle(preg_replace('/[^A-Za-z_-]/', '', $str)) . substr(str_shuffle(str_repeat($chars, ceil(10/strlen($chars)))), 1, 10);

		while(isset($this->bindings[$key]))
		{
			$key = $key . substr(str_shuffle(str_repeat($chars, ceil(10/strlen($chars)))), 1, 10);
		}

		return $key;
	}
}
