<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use InvalidArgumentException;

use function array_keys;
use function count;
use function implode;
use function in_array;
use function is_array;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function preg_replace;
use function random_int;
use function reset;
use function str_contains;
use function str_shuffle;
use function trim;

/**
 * SQL Where statement wrapper.
 */
class Where
{
    /**
     * Constant for "WHERE" statement.
     *
     * @var string
     */
    public const WHERE = 'WHERE';

    /**
     * Constant for "OR WHERE" statement.
     *
     * @var string
     */
    public const OR_WHERE = 'OR';

    /**
     * Constant for "AND WHERE" statement.
     *
     * @var string
     */
    public const AND_WHERE = 'AND';

    /**
     * Constant for acceptable types.
     *
     * @var array
     */
    protected const ACCEPTABLE_TYPES = ['WHERE', 'OR', 'AND'];

    /**
     * Constant for logical operators.
     *
     * @var array
     */
    protected const ACCEPTABLE_OPERATORS = ['=', '!=', '>', '<', '>=', '<=', '<>', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE', 'BETWEEN'];

	/**
	 * Type of where statement.
	 *
	 * @var string
	 */
	protected $type = 'WHERE';

	/**
	 * Associated column.
	 *
	 * @var string|null
	 */
	protected $column;

	/**
	 * Logical operator for statement.
	 *
	 * @var string
	 */
	protected $operator;

	/**
	 * Bindings for statement.
	 *
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * SQL WHERE Clause.
	 *
	 * @param string $column   Column name to use
	 * @param string $operator Logical operator
	 * @param mixed  $value    Comparison value
	 * @param string $type     Where type
	 */
	public function __construct(string $column, string $operator, mixed $value, string $type = 'WHERE')
	{
		// Validate the query type
		if (!in_array($type, self::ACCEPTABLE_TYPES))
		{
			throw new InvalidArgumentException('Invalid type [' . $type . ']. Type must be one of [' . implode(',', self::ACCEPTABLE_TYPES) . ']');
		}

		// Validate the operator
		if (!in_array($operator, self::ACCEPTABLE_OPERATORS))
		{
			throw new InvalidArgumentException('Invalid logic operator [' . $operator . ']. Logical operators must be one of [' . implode(',', self::ACCEPTABLE_OPERATORS) . ']');
		}

		// Set the type
		$this->type = $type;

		// Set the operator
		$this->operator = $operator;

		// Set the column
		$this->column = trim($column);

		// 'column_name', 'operator', ['value1', 'value2']
		if (is_array($value))
		{
			foreach ($value as $val)
			{
				$key = $this->uniqueKey($column . $val . $type);

				$this->bindings[$key] = [$key, $val];
			}
		}
		else
		{
			$key = $this->uniqueKey($column . $value . $type);

			$this->bindings[$key] = [$key, $value];
		}
	}

	/**
	 * Returns the clause as SQL.
	 *
	 * @return string
	 */
	public function sql(?string $table = null): string
	{
		$sql = '';

		// Nested OR when constructor $value is an array
		if (count($this->bindings) > 1)
		{
			// WHERE column IN ('value1', 'value2', 'value3')
			if (in_array($this->operator, ['IN', 'NOT IN', 'BETWEEN']))
			{
				if ($table && !str_contains($this->column, '.'))
				{
					$sql .=  $table . '.' . $this->column . ' ' . $this->operator;
				}
				else
				{
					$sql .= $this->column . ' ' . $this->operator;
				}

				$sql .= ' (:' . implode(', :', array_keys($this->bindings)) . ')';
			}
			// WHERE (column = 'value1' OR column = 'value1')
			else
			{
				foreach ($this->bindings as $binding)
				{
					if ($table && !str_contains($this->column, '.'))
					{
						$sql .=  ' OR ' . $table . '.' . $this->column . ' ' . $this->operator . ' :' . $binding[0];
					}
					else
					{
						$sql .=  ' OR ' . $this->column . ' ' . $this->operator . ' :' . $binding[0];
					}
				}

				$sql = '(' . trim(ltrim($sql, ' OR ')) . ')';
			}
		}
		// WHERE column = 'value1'
		else
		{
			// Get the first and only binding
			$binding = reset($this->bindings);

			if ($table && !str_contains($this->column, '.'))
			{
				$sql =  $table . '.' . $this->column . ' ' . $this->operator . ' :' . $binding[0];
			}
			else
			{
				$sql =  $this->column . ' ' . $this->operator . ' :' . $binding[0];
			}
		}

		return trim($sql);
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
			$bindings[$binding[0]] = $binding[1];
		}

		return $bindings;
	}

	/**
	 * Returns array of bindings.
	 *
	 * @return string
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Generates a random unique key for the binding.
	 *
	 * @param  string $str Key to provide for binding
	 * @return string
	 */
	protected function uniqueKey($str): string
	{
		$hex      = '';
		$pool     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length   = 5;
		$poolSize = mb_strlen($pool) - 1;

		for($i = 0; $i < $length; $i++)
		{
			$hex .= mb_substr($pool, random_int(0, $poolSize), 1);
		}

		$key = str_shuffle(preg_replace('/[^A-Za-z]/', '', $str) . $hex);

		while(isset($this->bindings[$key]))
		{
			$key = $key . mb_substr($pool, random_int(0, $poolSize), 1);
		}

		return $key;
	}
}
