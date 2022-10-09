<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use InvalidArgumentException;
use serve\database\connection\ConnectionHandler;
use function array_filter;
use function array_map;
use function count;
use function explode;
use function implode;
use function is_array;
use function ltrim;
use function preg_replace;
use function rtrim;
use function str_contains;
use function str_replace;
use function strpos;
use function strtolower;
use function trim;

/**
 * SQL Query builder manager.
 */
class Query
{
	/**
	 * Wheres.
	 *
	 * @var array
	 */
	protected $wheres = [];

	/**
	 * Joins.
	 *
	 * @var array
	 */
	protected $joins = [];

	/**
	 * Select.
	 *
	 * @var \serve\database\query\Select|null
	 */
	protected $select;

	/**
	 * Limit.
	 *
	 * @var \serve\database\query\Limit|null
	 */
	protected $limit;

	/**
	 * Order by.
	 *
	 * @var \serve\database\query\OrderBy|null
	 */
	protected $orderBy;

	/**
	 * Group concat.
	 *
	 * @var \serve\database\query\GroupBy|null
	 */
	protected $groupBy;

	/**
	 * Concat.
	 *
	 * @var \serve\database\query\Concat|null
	 */
	protected $concat;

	/**
	 * Group concat.
	 *
	 * @var \serve\database\query\GroupConcat|null
	 */
	protected $groupConcat;

	/**
	 * Values to insert.
	 *
	 * @var \serve\database\query\Values|null
	 */
	protected $values;

	/**
	 * Values to update.
	 *
	 * @var \serve\database\query\Set|null
	 */
	protected $set;

	/**
	 * Values to update.
	 *
	 * @var \serve\database\query\Update|null
	 */
	protected $update;

	/**
	 * Current operation to run - SET | DELETE | SELECT FROM | INSERT | QUERY.
	 *
	 * @var string
	 */
	protected $operation;

	/**
	 * Bindings.
	 *
	 * @var array|null
	 */
	protected $bindings;

	/**
	 * Current table to run query on.
	 *
	 * @var string
	 */
	protected $operationTable;

	/**
	 * Database connection.
	 *
	 * @var \serve\database\connection\ConnectionHandler
	 */
	protected $connectionHandler;

	/**
	 * Database connection.
	 *
	 * @var string|null
	 */
	protected $queryStr;

	/**
	 * Constructor.
	 *
	 * @param \serve\database\connection\ConnectionHandler $connectionHandler Connection handler
	 * @param string|null                                  $table             Table name (optional) (default null)
	 */
	public function __construct(ConnectionHandler $connectionHandler, ?string $table = null)
	{
		$this->connectionHandler = $connectionHandler;

		if ($table)
		{
			$this->operationTable = $this->tableNamePrefix($table);
		}
	}

	/**
	 * Set query to "SELECT" with columns.
	 *
	 * @param array|string $columns $columns to select
	 */
	public function select(string|array $columns): void
	{
		$this->operation = 'SELECT';

		$this->select = new Select($columns, $this->connectionHandler->tablePrefix());
	}

    /**
     * Set table to select from.
     *
     * @param string $table Table
     */
    public function from(string $table): void
    {
    	$this->operationTable = $this->tableNamePrefix($table);
    }

	/**
	 * Set a "WHERE" clause.
	 *
	 * @param string $column   Column name
	 * @param string $operator Logical operator
	 * @param mixed  $value    Value to compare to
	 */
	public function where(string $column, string $operator, mixed $value): void
	{
		$this->whereFactory($column, $operator, $value, Where::WHERE);
	}

	/**
	 * Set a "AND" where clause.
	 *
	 * @param string $column   Column name
	 * @param string $operator Logical operator
	 * @param mixed  $value    Value to compare to
	 */
	public function andWhere(string $column, string $operator, mixed $value): void
	{
		$this->whereFactory($column, $operator, $value, Where::AND_WHERE);
	}

	/**
	 * Set a "OR" where clause.
	 *
	 * @param string $column   Column name
	 * @param string $operator Logical operator
	 * @param mixed  $value    Value to compare to
	 */
	public function orWhere(string $column, string $operator, mixed $value): void
	{
		$this->whereFactory($column, $operator, $value, Where::OR_WHERE);
	}

	/**
	 * Makes new "Where".
	 *
	 * @param string $column   Column name
	 * @param string $operator Logical operator
	 * @param mixed  $value    Value to compare to
	 * @param string $type     Type of where statement
	 */
	protected function whereFactory(string $column, string $operator, mixed $value, string $type): void
	{
		$this->wheres[] = new Where($this->columnTablePrefix($column), $operator, $value, $type);
	}

	/**
	 * Set a "JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function join(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_INNER);
	}

	/**
	 * Set a "INNNER JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function innerJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_INNER);
	}

	/**
	 * Set a "LEFT JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function leftJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_LEFT);
	}

	/**
	 * Set a "RIGHT JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function rightJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_RIGHT);
	}

	/**
	 * Set a "LEFT OUTER JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function leftOuterJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_LEFT_OUTER);
	}

	/**
	 * Set a "RIGHT OUTER JOIN ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function rightOuterJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_RIGHT_OUTER);
	}

	/**
	 * Set a "FULL OUTER ON" clause.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 */
	public function fullOuterJoin(string $table, string $comparisons): void
	{
		$this->joinFactory($table, $comparisons, Join::TYPE_FULL_OUTER);
	}

	/**
	 * Creates "Join" class.
	 *
	 * @param string       $table       Table name
	 * @param array|string $comparisons Join columns by string or array of multiple
	 * @param string       $type        Join type
	 */
	protected function joinFactory(string $tableName, string|array $comparisons, string $type): void
	{
		if (is_array($comparisons))
		{
			$comps = '';

			foreach($comparisons as $key => $value)
			{
				$comps .= 'AND ' . $this->columnTablePrefix($key) . ' = ' . $this->columnTablePrefix($value);
			}

			$comparisons = ltrim($comps, 'AND ');
		}
		else
		{
			$comps = '';

			$statements = array_filter(array_map('trim', explode('AND', $comparisons)));

			foreach($statements as $comparison)
			{
				$comp = array_filter(array_map('trim', explode('=', $comparison)));

				if (count($comp) !== 2)
				{
					throw new InvalidArgumentException('Invalid join comparison [' . $comparisons . ']. Comparison syntax should be: [table1.column_name = table2.column_name]');
				}

				$comps .= 'AND ' . $this->columnTablePrefix($comp[0]) . ' = ' . $this->columnTablePrefix($comp[1]);
			}

			$comparisons = ltrim($comps, 'AND ');
		}

		$this->joins[] = new Join($this->tableNamePrefix($tableName), $comparisons, $type);
	}

	/**
	 * Set an "ORDER BY" clause.
	 *
	 * @param array|string $columns   Single column or array of multiple column names
	 * @param string       $direction Optional direction (DESC|ASC) (optional) (default DESC)
	 */
	public function orderBy(string|array $columns, string $direction = 'DESC'): void
	{
		$cols = '';

		// [column_one, column_two]
		$columns = !is_array($columns) ? [$columns] : $columns;

		foreach($columns as $column)
		{
			// column_name
			if (!str_contains($column, '.'))
			{
				// column_name -> prefixed_table.column_name
				if (!empty($this->joins))
				{
					$cols .= $this->operationTable . ' . ' . $column . ', ';
				}
				else
				{
					$cols  .=  $column . ', ';
				}
			}
			// table.column_one -> prefixed_table.column_one
			else
			{
				$cols .= $this->columnTablePrefix($column) . ', ';
			}
		}

		$columns = rtrim($cols, ', ');

		$this->orderBy = new OrderBy($columns, $direction);
	}

	/**
	 * Set a "GROUP BY" clause.
	 *
	 * @param string $column Column to group
	 */
	public function groupBy(string $column): void
	{
		$this->groupBy = new GroupBy($column);
	}

	/**
	 * Set a "GROUP_CONCAT" clause.
	 *
	 * @param string            $column    Column to group
	 * @param string|null       $as        as (optional) (default null)
	 * @param bool|null         $distinct  Distinct
	 * @param string|null       $separator Separator (optional) (default null)
	 * @param array|string|null $orderby   Order (optional) (default null)
	 */
	public function groupConcat(string $column, ?string $as = null, ?bool $distinct = null, ?string $separator = null, string|array|null $orderby = null): void
	{
		$this->groupConcat = new GroupConcat($column, $as, $distinct, $separator, $orderby);
	}

	/**
	 * Set a "LIMIT" clause.
	 *
	 * @param int      $offset Offset or limit if second parameter not provided
	 * @param int|null $value  Value when offset is provided (optional) (default null)
	 */
	public function limit(int $offset, ?int $value = null): void
	{
		$this->limit = new Limit($offset, $value);
	}

    /**
     * Create a new table with given schema.
     *
     * @param string $table  Table name to create
     * @param array  $schema Table parameters
     */
    public function createTable(string $table, array $schema): void
    {
    	$name = $this->tableNamePrefix($table);

    	$table = new Table($name, $schema);

        // Execute the query
        $this->connectionHandler->query($this->connectionHandler->cleanQuery($table->create($this->connectionHandler->connection()->type())));

        // Reset internal operation and table
        $this->operationTable = $name;

        $this->operation = null;
    }

    /**
     * Drop an existing table.
     *
     * @param string $table Table name to use
     */
    public function dropTable(string $table): void
    {
        $table = new Table($this->tableNamePrefix($table));

        // Execute the query
        $this->connectionHandler->query($this->connectionHandler->cleanQuery($table->drop()));

        // Reset internal operation and table
        $this->operationTable = null;

        $this->operation = null;
    }

    /**
     * Truncate an existing table.
     *
     * @param string $table Table name to use
     */
    public function truncateTable(string $table): void
    {
        $name = $this->tableNamePrefix($table);

        $table = new Table($name);

        // Execute the query
        $this->connectionHandler->query($this->connectionHandler->cleanQuery($table->truncate($this->connectionHandler->connection()->type())));

        // Reset internal operation and table
        $this->operationTable = $table;

        $this->operation = null;
    }

	/**
	 * Set the query to "INSERT INTO" a given table.
	 *
	 * @param string $table The table name to use
	 */
	public function insertInto(string $table): void
	{
		$this->operationTable = $this->tableNamePrefix($table);

		$this->operation = 'INSERT';
	}

    /**
     * Add the "VALUES" to when running an "INSERT INTO" query.
     *
     * @param array $values The values to apply
     */
    public function values(array $values): void
    {
        $this->values = new Values($values);
    }

	/**
	 * Set the query to "UPDATE" a given table.
	 *
	 * @param string $table The table name to use
	 */
	public function update(string $table): void
	{
		$this->operationTable = $this->tableNamePrefix($table);

		$this->operation = 'UPDATE';
	}

    /**
     * Add the "SET" values when running an "UPDATE" query.
     *
     * @param array $values The values to apply
     */
    public function set(array $values): void
    {
        $this->set = new Set($values);
    }

    /**
     * Set the query to "DELETE FROM" a given table.
     *
     * @param string $table The table name to use
     */
    public function deleteFrom(string $table): void
    {
        $this->operationTable = $this->tableNamePrefix($table);

		$this->operation = 'DELETE';
    }

    /**
     * Set the query to "DELETE FROM" a given table.
     *
     * @param string $table The table name to use
     * @return \serve\database\builder\query\Alter;
     */
    public function alterTable(string $table): Alter
    {
    	$table = $this->tableNamePrefix($table);

        $this->operationTable = null;

		$this->operation = null;

		return new Alter($this->connectionHandler, $table);
    }

    /**
     * Execute a query and limit to single row
     * and/or find a single row by id.
     *
     * @param  int|null   $id Row id to find (optional) (default null)
     * @return array|null
     */
    public function find(?int $id = null): array|null
    {
		// If id filter by id
		if ($id)
		{
			if (empty($this->wheres))
			{
				$this->where('id', '=', $id);
			}
			else
			{
				$this->andWhere('id', '=', $id);
			}
		}

		// limit results to 1 row
		$this->limit(1);

		return $this->exec();
    }

    /**
     * Execute current query and return results.
     *
     * @return array|bool|null
     */
    public function exec(): array|null|bool
    {
       	// Validate a table was loaded
		if (!$this->operationTable)
		{
			throw new InvalidArgumentException('A table has not been loaded into the Query Builder.');
		}

		// Validate a correct query is loaded
		if (!$this->operation)
		{
			throw new InvalidArgumentException('No SQL query operation to run.');
		}

		// Exec and build queries
		if ($this->operation === 'SELECT')
		{
			return $this->execSQL($this->buildSelectSql());
		}
		elseif ($this->operation === 'UPDATE')
		{
			return $this->execSQL($this->buildUpdateSql());
		}
		elseif ($this->operation === 'DELETE')
		{
			return $this->execSQL($this->buildDeleteSql());
		}
		elseif ($this->operation === 'INSERT')
		{
			return $this->execSQL($this->buildInsertSql());
		}

		throw new InvalidArgumentException('No SQL query operation to run.');
    }

	/**
	 * Execute current query and return results.
	 *
	 * @return array|bool|int|null
	 */
	private function execSQL(string $sql): array|null|bool|int
	{
		// Save the SQL query
		$this->queryStr = $sql;

		// Execute the SQL
		$results = $this->connectionHandler->query(trim($sql), $this->bindings);

		// If limited to one row return that row
		if (!empty($results) && !empty($this->limit) && $this->limit->getValue() === 1 && $this->operation === 'SELECT')
		{
			return $results[0];
		}

		// If operation was select and no result return null
		if (empty($results) && $this->operation === 'SELECT')
		{
			return null;
		}

		// If operation was insert return bool
		if ($this->operation === 'INSERT')
		{
			return $results > 0;
		}

		// If operation was update return row count
		if ($this->operation === 'UPDATE')
		{
			return $results;
		}

		return $results;
	}

	/**
	 * Builds SQL for "SELECT" queries.
	 *
	 * @return string
	 */
	protected function buildSelectSql(): string
	{
		// Start with "SELECT"
		$sql = [$this->select->sql(!empty($this->joins) ? $this->operationTable : null)];

		// Add "GROUP_CONCAT"
		if (!empty($this->groupConcat))
		{
			$sql[] = ', ' . $this->groupConcat->sql();
		}

		$sql[] = 'FROM ' . $this->operationTable;

		// Add "CONCAT"
		if (!empty($this->concat))
		{
			$sql[] =  ', ' . $this->concat->sql();
		}

		// Add joins
		if (!empty($this->joins))
		{
			foreach($this->joins as $join)
			{
				$sql[] = $join->sql();
			}
		}

		if (!empty($this->wheres))
		{
			$sql[] = $this->procesWhereClauses();
		}

		// Add Orderby
		if (!empty($this->orderBy))
		{
			$sql[] = $this->orderBy->sql();
		}

		// Add limit
		if (!empty($this->limit))
		{
			$sql[] = $this->limit->sql();
		}

		// Add group
		if (!empty($this->groupBy))
		{
			$sql[] = $this->groupBy->sql();
		}

		return implode(' ', $sql);
	}

	/**
	 * Builds and returns SQL query string for an "UPDATE" query.
	 *
	 * @return string
	 */
	protected function buildUpdateSql(): string
	{
		// Validate things are setup properly
		if (empty($this->wheres))
		{
			throw new ErrorException('Cannot run an [UPDATE] query when no [WHERE] clauses are provided.');
		}

		if (!$this->set)
		{
			throw new ErrorException('Cannot run an [UPDATE] query when no [SET] clauses are provided.');
		}

		// Create bindings
		$this->applyBindings($this->set->bindings());

		// And the "UPDATE" with table
		$sql = ['UPDATE ' . $this->operationTable];

		// Add the "SET"
		$sql[] = 'SET ' . $this->set->sql();

		// Add joins
		if (!empty($this->joins))
		{
			foreach($this->joins as $join)
			{
				$sql[] = $join->sql();
			}
		}

		// Add "WHERE"
		$sql[] = $this->procesWhereClauses();

		return implode(' ', $sql);
	}

	/**
	 * Builds and returns SQL query string for an "DELETE" query.
	 *
	 * @return string
	 */
	protected function buildDeleteSql(): string
	{
		// Validate things are setup properly
		if (empty($this->wheres))
		{
			throw new ErrorException('Cannot run an [DELETE FROM] query when no [WHERE] clauses are provided.');
		}

		// And the "UPDATE" with table
		$sql = ['DELETE FROM ' . $this->operationTable];

		// Add "WHERE"
		$sql[] = $this->procesWhereClauses();

		// Add joins
		if (!empty($this->joins))
		{
			foreach($this->joins as $join)
			{
				$sql[] = $join->sql();
			}
		}

		return implode(' ', $sql);
	}

	/**
	 * Builds and returns SQL query string for an "INSERT" query.
	 *
	 * @return string
	 */
	protected function buildInsertSql(): string
	{
		// Validate things are setup properly
		if (empty($this->values))
		{
			throw new ErrorException('Cannot run an [INSERT INTO] query when no [VALUES] clause is provided.');
		}

		// Create bindings
		$this->applyBindings($this->values->bindings());

		// And the "UPDATE" with table
		$sql = ['INSERT INTO ' . $this->operationTable];

		// Add values
		$sql[] = $this->values->sql();

		return implode(' ', $sql);
	}

	/**
	 * Applies bindings.
	 *
	 * @param array $bindings Bindings
	 */
	protected function applyBindings(array $bindings): void
	{
		foreach($bindings as $key => $value)
		{
			$this->bindings[$key] = $value;
		}
	}

	/**
	 * Processes "WHERE" clause logics and returns SQL.
	 *
	 * @return string
	 */
	protected function procesWhereClauses(): string
	{
		$wheresSql = [];

		foreach($this->wheres as $i => $where)
		{
			$currType = $where->type();

			// We only need to wrap the previous statement when OR / AND changes from AND -> OR / OR -> AND

			if ($i > 0 && $currType !== $prevType && isset($prevType) && $prevType !== Where::WHERE)
			{
				// Prefix bracket where previous clause changed
				for ($x = 0; $x < count($this->wheres); $x++)
				{
					$prevWhere = $this->wheres[$x];

					if ($prevWhere->type() !== $currType)
					{
						if ($prevWhere->type() === Where::WHERE)
						{
							$wheresSql[$x] = 'WHERE (' . ltrim($wheresSql[$x], 'WHERE ');
						}
						else
						{
							$wheresSql[$x] = '(' . $wheresSql[$x];
						}

						break;
					}
				}

				// Append bracket on end of previous clause
				$wheresSql[$i-1] = $wheresSql[$i-1] . ')';
			}

			$wheresSql[] = $currType . ' ' . $where->sql(!empty($this->joins) ? $this->operationTable : null);

			$this->applyBindings($where->bindings());

			$prevType = $currType;
		}

		return implode(' ', $wheresSql);
	}

    /**
     * Table prefixes tables when either "table(column)" or "table.column" syntax is used.
     *
     * @param  string $query Query string
     * @return string
     */
    protected function columnTablePrefix(string $query): string
    {
        // Check that the the query is using a dot notatation
        // on a column
        // e.g turn  posts.id -> serve_posts.id
        if (strpos($query, '.') !== false)
        {
            return preg_replace('/(\w+\.)/', $this->connectionHandler->tablePrefix() . '$1', $query);
        }

        // e.g turn  posts(id) -> serve_posts(id)
        if (strpos($query, '(') !== false)
        {
            return preg_replace('/(\w+\()/', $this->connectionHandler->tablePrefix() . '$1', $query);
        }

        return $query;
    }

    /**
     * Table prefixes tables when table name is provided.
     *
     * @param  string $table Table name
     * @return string
     */
    protected function tableNamePrefix(string $table): string
    {
        // append the table prefix
        return $this->connectionHandler->tablePrefix() . strtolower(str_replace(' ', '_', $table));
    }
}
