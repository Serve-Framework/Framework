<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "VALUES" statement wrapper 
 *
 */
class Values
{
	/**
	 * Bindings
	 *
	 * @var array
	 */
	protected $bindings;

	/**
	 * Limit/ offset results.
	 *
	 * @param int      $offset Offset to start at or limit if single argument provided
	 * @param int|null $value  Limit results (optional) (default null)
	 */
	public function __construct(array $values)
	{
		foreach($values as $column => $value)
		{
			$key = $this->uniqueKey($column, $value);

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
		$columns = '';
		$values  = '';

		foreach($this->bindings as $binding)
		{
			$columns .= $binding[0] . ', ';

			$values .=  ':' . $binding[1] . ', ';
		}

		$values = rtrim($values, ', ');
		$columns = rtrim($columns, ', ');

		return   '(' . $columns . ') ' . '(' . $values . ')';
	}

	/**
	 * Returns array of bindings
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

		$key = str_shuffle(preg_replace('/[^A-Za-z_-]/', '', $str)) . substr(str_shuffle(str_repeat($chars, ceil(10/strlen($chars)) )),1,10);

		while(isset($this->bindings[$key]))
		{
			$key = $key . substr(str_shuffle(str_repeat($chars, ceil(10/strlen($chars)) )),1,10);
		}

		return $key;
	}
}
