<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "GROUP_CONCAT" statement wrapper.
 *
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
	 * "as"
	 *
	 * @var string|null
	 */
	protected $as;

	/**
	 * "DISTINCT"
	 *
	 * @var bool|null
	 */
	protected $distinct;

	/**
	 * "SEPARATOR"
	 *
	 * @var string|null
	 */
	protected $separator;

	/**
	 * "ORDER BY"
	 *
	 * @var string|array|null
	 */
	protected $orderby;

	/**
	 * Constructor.
	 *
	 * @param string             $column     Concat column
	 * @param string|null        $as         As value
	 * @param bool|null          $distinct   "DISTINCT" (optional) (default null)
	 * @param string|null        $separator  "SEPARATOR" (optional) (default null)
	 * @param string|array|null  $orderby    "ORDER BY" (optional) (default null)
	 */
	public function __construct(string $column, ?string $as = null, ?bool $distinct = null, ?string $separator = null, string|array|null $orderby = null)
	{
		$this->column = $column;

		$this->as = $as;

		$this->distinct = $distinct;

		$this->separator = $separator;

		$this->orderby = $orderby;
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
			$sql[] = 'DISTINCT';
		}

		if ($this->orderby)
		{
			if (is_array($this->orderby))
			{
				$sql[] = 'ORDER BY ' . implode(', ', $this->orderby);
			}
			else
			{
				$sql[] = 'ORDER BY ' . $this->orderby;
			}			
		}

		if ($this->separator)
		{
			$sql[] = 'SEPARATOR\'' . $this->separator . '\'';
		}

		$sql[] = $this->column .' ) ';

		if ($this->as)
		{
			$sql[] = 'AS "' . $this->as . '"';
		}

		return implode(' ', $sql);
	}
}
