<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "GROUP BY" statement wrapper.
 */
class GroupBy
{
	/**
	 * Column.
	 *
	 * @var string
	 */
	protected $column;

	/**
	 * Constructor.
	 *
	 * @param string $column Column name
	 */
	public function __construct(string $column)
	{
		$this->column = $column;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		return 'GROUP BY ' . $this->column;
	}
}
