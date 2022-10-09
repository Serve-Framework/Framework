<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use function in_array;
use function trim;

/**
 * SQL "ORDER BY" statement wrapper.
 */
class OrderBy
{
	/**
	 * Offset if it exists.
	 *
	 * @var string
	 */
	protected $columns;

	/**
	 * Direction.
	 *
	 * @var string
	 */
	protected $direction;

	/**
	 * Constructor.
	 *
	 * @param string $columns   The column names to sort in order of
	 * @param string $direction 'DESC'|'ASC' (optional) (default 'DESC')
	 */
	public function __construct(string $columns, string $direction = 'DESC')
	{
		if (!in_array($direction, ['ASC', 'DESC']))
		{
			throw new InvalidArgumentException('Invalid sort direction [' . $direction . ']. Direction must be one of [ASC, DESC]');
		}

		$this->direction = $direction;

		$this->columns = $columns;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		return trim('ORDER BY ' . $this->columns . ' ' . $this->direction);
	}
}
