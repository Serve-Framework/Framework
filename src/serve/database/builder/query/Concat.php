<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "CONCAT" statement wrapper.
 *
 */
class Concat
{
	/**
	 * Columns to concat
	 *
	 * @var array
	 */
	protected $columns;

	/**
	 * "as"
	 *
	 * @var string
	 */
	protected $as;

	/**
	 * Constructor
	 *
	 * @param array   $columns  column names
	 * @param string  $as       As value
	 * 
	 */
	public function __construct(array $columns, string $as)
	{
		$this->columns = $columns;

		$this->as = $as;
	}

	/**
	 * Returns the SQL statement.
	 * 
	 * @return string
	 */
	public function sql(): string
	{
		return 'CONCAT(' . implode(', ', $this->columns) . ') AS ' . $this->as;
	}
}
