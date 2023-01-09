<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use serve\utility\Str;
use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function ltrim;
use function rtrim;
use function str_contains;

/**
 * SQL "SELECT" statement wrapper.
 */
class Select
{
	/**
	 * Columns.
	 *
	 * @var array|string
	 */
	protected $columns;

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Constructor.
	 *
	 * @param array|string $columns Column or columns to select by
	 */
	public function __construct(string|array $columns, string $prefix)
	{
		$this->columns = $this->normalizeSatement($columns, $prefix);

		$this->prefix = $prefix;
	}

	/**
	 * Returns the SQL.
	 *
	 * @param  string|null $baseTable Whether the default table needs to use "dot.notation" (optional) (default null)
	 * @return string
	 */
	public function sql(?string $baseTable = null): string
	{
		$sql = '';

		if ($baseTable && !empty($this->prefix) && !str_contains($baseTable, $this->prefix))
		{
			$baseTable = $this->prefix . $baseTable;
		}

		foreach($this->columns as $table => $columns)
		{
			if ($table === 'COUNT' || $table === 'SUM')
			{
				if ($baseTable)
				{
					foreach($columns as $i => $column)
					{
						if (!str_contains($column, '.') && strtolower($column) !== 'distinct')
						{
							$columns[$i] =  $baseTable . '.' . $column;
						}
					}
				}

				$sql .= $table . '(' . trim(implode(' ', $columns)) . ')';
			}
			elseif ($table === 'default')
			{
				if ($baseTable)
				{
					$sql .= $baseTable . '.' . implode(', ' . $baseTable . '.', $columns) . ', ';
				}
				else
				{
					$sql .= implode(', ', $columns);
				}
			}
			else
			{
				$sql .= $table . '.' . implode(', ' . $table . '.', $columns) . ', ';
			}
		}

		$sql = rtrim($sql, ', ');

		return 'SELECT ' . $sql;
	}

    /**
     * Normalizes statement into an array.
     *
     * @param  array|string $statement "SELECT" statement
     * @param  string       $prefix    Database table prefix
     * @return array
     */
    protected function normalizeSatement(string|array $statement, string $prefix): array
    {
    	$results = [];

    	// Special case for COUNT and SUM
    	if ((is_array($statement) && (isset($statement['COUNT']) || isset($statement['SUM']))) || (is_string($statement) && (str_contains($statement, 'COUNT') || str_contains($statement, 'SUM'))))
    	{
    		// [ 'COUNT' => ['name', 'email', 'slug'] ] OR  ['COUNT' => 'name']
    		if (is_array($statement))
			{
				$function = isset($statement['COUNT']) ? 'COUNT' : 'SUM';

				if (is_array($statement[$function]))
				{
					$results[$function] = array_map('trim', array_values($statement[$function]));
				}
				else
				{
					$results[$function] = array_filter(array_map('trim', explode(' ', $statement[$function])));
				}
			}
			// count(distinct col1) etc...
			else
			{
				$function = trim(Str::getBeforeFirstChar($statement, '('));

				$cols = array_filter(array_map('trim', explode(' ', Str::getAfterFirstChar(Str::getBeforeFirstChar($statement, ')'), '('))));

				$results[$function] = $cols;
			}

			$function = isset($results['COUNT']) ? 'COUNT' : 'SUM';

			foreach($results[$function] as $i => $column)
			{
				if (str_contains($column, '.'))
				{
					$results[$function][$i] =  $prefix . $column;
				}
			}

    		return $results;
    	}

    	if (is_array($statement))
    	{
	    	// ['name', 'email', 'slug'] | ['table.bar']
			if (!$this->isMulti($statement))
			{
				foreach($statement as $column)
				{
					// ['table.bar'] Not supported but check anyway
					if (str_contains($column, '.'))
					{
						$table  = $prefix . trim(Str::getBeforeFirstChar($column, '.'));
						$column = trim(Str::getAfterLastChar($column, '.'));

						if (!isset($results[$table]))
						{
							$results[$table] = [];
						}

						$results[$table][] = $column;
					}
					else
					{
						if (!isset($results['default']))
						{
							$results['default'] = [];
						}

						$results['default'][] = trim($column);
					}
				}
			}
			//  [ 'table1' => ['name', 'email', 'slug'] ] OR  ['table2' => 'name']
			else
			{
				foreach($statement as $table => $columns)
				{
					$table = $prefix . $table;

					// [ 'table1' => ['id', 'name', 'slug'] ]
					if (is_array($columns))
					{
						$results[$table] = array_map('trim', array_values($columns));

					}
					// ['table2' => 'name'] -> table2.name
					else
					{
						$results[$table] = [trim($columns)];
					}
				}
			}
		}
		else
		{
			// table1_name(column1, column2), table2_name(column1)
			if (str_contains($statement, ')'))
			{
				$statements = array_filter(array_map('trim', explode(')', $statement)));

				foreach ($statements as $_statement)
				{
					$table   = $prefix . trim(ltrim(Str::getBeforeFirstChar($_statement, '('), ','));
					$columns = array_filter(array_map('trim', explode(',', Str::getAfterFirstChar($_statement, '('))));

					$results[$table] = $columns;
				}
			}
			// e.g column1, column2 | table.col1, table.col2 | column1, column2 | column
			else
			{
				$statements = array_filter(array_map('trim', explode(',', $statement)));

				foreach ($statements as $_statement)
				{
					// table.col1
					if (str_contains($_statement, '.'))
					{
						$table  = $prefix . trim(Str::getBeforeFirstChar($_statement, '.'));
						$column = trim(Str::getAfterLastChar($_statement, '.'));

						if (!isset($results[$table]))
						{
							$results[$table] = [];
						}

						$results[$table][] = $column;
					}
					else
					{
						if (!isset($results['default']))
						{
							$results['default'] = [];
						}

						$results['default'][] = $_statement;
					}
				}
			}
		}

		return $results;
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
