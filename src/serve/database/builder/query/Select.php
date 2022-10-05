<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "SELECT" statement wrapper.
 *
 */
class Select
{
	/**
	 * Columns.
	 *
	 * @var array
	 */
	protected $columns = [];

	/**
	 * Constructor.
	 *
	 * @param string|array  $columns  Column or columns to select by
	 */
	public function __construct(string|array $columns)
	{
		$this->columns = $columns;
	}

	/**
	 * Returns the SQL
	 *
	 * @param  string|null  $table Whether to output table name using "dot.notation" (optional) (default null)
	 * @return string
	 */
	public function sql(?string $table = null): string
	{
		$sql = '';

		if (is_array($this->columns))
		{
			$first = reset($this->columns);

			// ['name', 'email', 'slug']
			if (!$this->isMulti($this->columns))
			{
				if (count($this->columns) > 1)
				{
					// ['name', 'email', 'slug'] -> table(name, email, slug)
					if ($table)
					{
						$sql = $table . '(' . implode(', ', $this->columns) . ')';
					}

					// ['name', 'email', 'slug'] -> name, email, slug
					else
					{
						$sql = implode(', ', $this->columns);
					}
				}
				else
				{
					// ['name'] -> table.name
					if ($table)
					{
						$sql = $table . '.' . $first;
					}

					// ['name'] -> name
					else
					{
						$sql = $first;
					}
				}
			}
			//  [ 'table1' => ['name', 'email', 'slug'] ] OR  ['table2' => 'name']
			else
			{
				// $table is ignore here as if the syntax is used the correct table
				// shoul be specified
				foreach ($this->columns as $tname => $cols)
				{
					// [ 'table1' => ['id', 'name', 'slug'] ] -> table1(name, 'id', 'slug')
					if (is_array($cols))
					{
						$cols = array_values($cols);

						if (count($cols) > 1)
						{
							$sql .= ', ' . $tname . '(' . implode(', ', $cols) . ')';
						}
						else
						{
							$sql .= ', ' . $tname . '.' .  $cols[0];
						}
					}
					// ['table2' => 'name'] -> table2.name
					else
					{
						$sql .= ', ' . $tname . '.' . $cols;
					}

					$sql = ltrim($sql, ', ');
				}
			}
		}
		// 'email' or 'foo.email'
		else
		{
			// Only apply the table name when it was not specified explicitly
			if ($table && !str_contains($this->columns, '.'))
			{
				$sql = $table . '.' . $this->columns;
			}
			else
			{
				$sql = $this->columns;
			}
		}
		
		return 'SELECT ' . trim($sql);
	}

	/**
	 * Returns TRUE if the array is multi-dimensional and FALSE if not.
	 *
	 * @param  array $array Array to check
	 * @return bool
	 */
	protected function isMulti(array $array): bool
    {
        foreach ($array as $key => $value)
        {
        	if (is_string($key) && is_array($value))
            {
            	return true;
            }
        }

        return false;
    }
}
