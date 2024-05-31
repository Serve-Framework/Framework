<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use serve\utility\Str;

use function array_map;
use function explode;
use function rtrim;
use function str_contains;
use function trim;

/**
 * SQL "GROUP BY" statement wrapper.
 */
class GroupBy
{
	/**
	 * Columns.
	 *
	 * @var array
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
	 * @param string $column Column name
	 */
	public function __construct(string $column, string $tablePrefix)
	{
		$this->columns = array_map('trim', explode(',', $column));

		$this->prefix = $tablePrefix;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(?string $baseTable = null): string
	{
		$columns = '';

		foreach($this->columns as $column)
		{
			if (str_contains($column, '.'))
			{
				$table  = trim(Str::getBeforeFirstChar($column, '.'));
				$column = trim(Str::getAfterFirstChar($column, '.'));

				if ($baseTable)
				{
					// Prefix an existing table
					if (!str_contains($baseTable, $table))
					{
						$columns .= $this->prefix . $table . '.' . $column . ', ';
					}
					// Use the base table
					else
					{
						$columns .= $baseTable . '.' . $column . ', ';
					}
				}
				// No base table but prefix this anyway
				else
				{
					$columns .= $this->prefix . $table . '.' . $column . ', ';
				}
			}
			// No table specified
			else
			{
				// Add the base table
				if ($baseTable)
				{
					$columns .= $baseTable . '.' . $column . ', ';
				}
				// No base table but prefix this anyway
				else
				{
					$columns .= $column . ', ';
				}
			}
		}

		return 'GROUP BY ' . rtrim($columns, ', ');
	}
}
