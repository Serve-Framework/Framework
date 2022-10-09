<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use function implode;
use function preg_replace;
use function str_replace;
use function trim;

/**
 * SQL "GROUP_CONCAT" statement wrapper.
 */
class GroupConcat
{
	/**
	 * Key to concat by.
	 *
	 * @var string
	 */
	protected $column;

	/**
	 * "as".
	 *
	 * @var string|null
	 */
	protected $as;

	/**
	 * "DISTINCT".
	 *
	 * @var bool|null
	 */
	protected $distinct;

	/**
	 * Constructor.
	 *
	 * @param string      $column   Concat column
	 * @param string|null $as       As value
	 * @param bool|null   $distinct "DISTINCT" (optional) (default null)
	 */
	public function __construct(string $column, ?string $as = null, ?bool $distinct = null)
	{
		$this->column = $column;

		$this->as = $as;

		$this->distinct = $distinct;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		$sql = ['GROUP_CONCAT('];

		if ($this->distinct)
		{
			$sql[] = 'DISTINCT ' . $this->column . ')';
		}
		else
		{
			$sql[] = $this->column . ')';
		}

		if ($this->as)
		{
			$sql[] = 'AS "' . $this->as . '"';
		}

		return preg_replace('/\s+/', ' ', trim(str_replace(' )', ')', str_replace('( ', '(', implode(' ', $sql)))));
	}
}
