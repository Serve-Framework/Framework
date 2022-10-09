<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder;

use serve\database\builder\query\Alter;
use serve\database\builder\query\Query;
use serve\database\connection\ConnectionHandler;

/**
 * Database SQL builder.
 *
 * @author Joe J. Howard
 */
class Builder
{
	/**
	 * Connection handler.
	 *
	 * @var \serve\database\connection\ConnectionHandler
	 */
	private $connectionHandler;

	/**
	 * Query.
	 *
	 * @var \serve\database\query\Query
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param \serve\database\connection\ConnectionHandler $connectionHandler Database connection handler
	 * @param \serve\database\builder\query\Query|null     $query             Builder Query (optional)
	 */
	public function __construct(ConnectionHandler $connectionHandler, ?Query $query = null)
	{
        // Save the database access instance locally
		$this->connectionHandler = $connectionHandler;

        // create a new query object
        $this->query = !$query ? new Query($connectionHandler) : $query;
	}

    /**
     * Get the database connection.
     *
     * @return \serve\database\connection\ConnectionHandler
     */
    public function connectionHandler(): ConnectionHandler
    {
        return $this->connectionHandler;
    }

    /**
     * Set query to "SELECT" with columns.
     *
     * @param  array|string $columns $columns to select
     * @return $this
     */
    public function SELECT(string|array $columns): Builder
    {
        $this->query->select($columns);

        return $this;
    }

    /**
     * Set table to select from.
     *
     * @param  string $table Table
     * @return $this
     */
    public function FROM(string $table): Builder
    {
        $this->query->from($table);

        return $this;
    }

    /**
     * Set a "WHERE" clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Logical operator
     * @param  mixed  $value    Value to compare to
     * @return $this
     */
    public function WHERE(string $column, string $operator, mixed $value): Builder
    {
        $this->query->where($column, $operator, $value);

        return $this;
    }

    /**
     * Set a "AND" where clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Logical operator
     * @param  mixed  $value    Value to compare to
     * @return $this
     */
    public function AND_WHERE(string $column, string $operator, mixed $value): Builder
    {
        $this->query->andWhere($column, $operator, $value);

        return $this;
    }

    /**
     * Set a "OR" where clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Logical operator
     * @param  mixed  $value    Value to compare to
     * @return $this
     */
    public function OR_WHERE(string $column, string $operator, mixed $value): Builder
    {
        $this->query->orWhere($column, $operator, $value);

        return $this;
    }

    /**
     * Set a "JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->join($table, $comparisons);

        return $this;
    }

    /**
     * Set a "INNNER JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function INNER_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->innerJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set a "LEFT JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function LEFT_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->leftJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set a "RIGHT JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function RIGHT_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->rightJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set a "LEFT OUTER JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function LEFT_OUTER_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->leftOuterJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set a "RIGHT OUTER JOIN ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function RIGHT_OUTER_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->rightOuterJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set a "FULL OUTER ON" clause.
     *
     * @param  string       $table       Table name
     * @param  array|string $comparisons Join columns by string or array of multiple
     * @return $this
     */
    public function FULL_OUTER_JOIN_ON(string $table, string|array $comparisons): Builder
    {
        $this->query->fullOuterJoin($table, $comparisons);

        return $this;
    }

    /**
     * Set an "ORDER BY" clause.
     *
     * @param  array|string $columns   Single column or array of multiple column names
     * @param  string       $direction Optional direction (DESC|ASC) (optional) (default DESC)
     * @return $this
     */
    public function ORDER_BY(string|array $columns, string $direction = 'DESC'): Builder
    {
        $this->query->orderBy($columns, $direction);

        return $this;
    }

    /**
     * Set a "GROUP BY" clause.
     *
     * @param  string $column Column to group
     * @return $this
     */
    public function GROUP_BY(string $column): Builder
    {
        $this->query->groupBy($column);

        return $this;
    }

    /**
     * Set a "GROUP_CONCAT" clause.
     *
     * @param  string      $column   Column to group
     * @param  string|null $as       as (optional) (default null)
     * @param  bool|null   $distinct Distinct
     * @return $this
     */
    public function GROUP_CONCAT(string $column, ?string $as = null, ?bool $distinct = null): Builder
    {
        $this->query->groupConcat($column, $as, $distinct);

        return $this;
    }

    /**
     * Set a "LIMIT" clause.
     *
     * @param  int      $offset Offset or limit if second parameter not provided
     * @param  int|null $value  Value when offset is provided (optional) (default null)
     * @return $this
     */
    public function LIMIT(int $offset, ?int $value = null): Builder
    {
        $this->query->limit($offset, $value);

        return $this;
    }

    /**
     * Create a new table with given schema.
     *
     * @param  string $table  Table name to create
     * @param  array  $schema Table parameters
     * @return $this
     */
    public function CREATE_TABLE(string $table, array $schema): Builder
    {
        $this->query->createTable($table, $schema);

        return $this;
    }

    /**
     * Drop an existing table.
     *
     * @param  string $table Table name to use
     * @return $this
     */
    public function DROP_TABLE(string $table): Builder
    {
        $this->query->dropTable($table);

        return $this;
    }

    /**
     * Truncate an existing table.
     *
     * @param  string $table Table name to use
     * @return $this
     */
    public function TRUNCATE_TABLE(string $table): Builder
    {
        $this->query->truncateTable($table);

        return $this;
    }

    /**
     * Initialize an "ALTER TABLE" statement.
     *
     * @param  string                              $table Table name to use
     * @return \serve\database\builder\query\Alter
     */
    public function ALTER_TABLE(string $table): Alter
    {
        return $this->query->alterTable($table);
    }

    /**
     * Set the query to "INSERT INTO" a given table.
     *
     * @param  string $table The table name to use
     * @return $this
     */
    public function INSERT_INTO(string $table): Builder
    {
        $this->query->insertInto($table);

        return $this;
    }

    /**
     * Add the "VALUES" to when running an "INSERT INTO" query.
     *
     * @param  array $values The values to apply
     * @return $this
     */
    public function VALUES(array $values): Builder
    {
        $this->query->values($values);

        return $this;
    }

    /**
     * Set the query to "UPDATE" a given table.
     *
     * @param  string $table The table name to use
     * @return $this
     */
    public function UPDATE(string $table): Builder
    {
        $this->query->update($table);

        return $this;
    }

    /**
     * Add the "SET" values when running an "UPDATE" query.
     *
     * @param  array $values The values to apply
     * @return $this
     */
    public function SET(array $values): Builder
    {
        $this->query->set($values);

        return $this;
    }

    /**
     * Set the query to "DELETE FROM" a given table.
     *
     * @param  string $table The table name to use
     * @return $this
     */
    public function DELETE_FROM(string $table): Builder
    {
        $this->query->deleteFrom($table);

        return $this;
    }

    /**
     * Execute a query and limit to single row
     * and/or find a single row by id.
     *
     * @param  int|null   $id Row id to find (optional) (default null)
     * @return array|null
     */
    public function FIND(?int $id = null): array|null
    {
        $result = $this->query->find($id);

        $this->queryFactory();

        return $result;
    }

    /**
     * Execute a query and limit to single row.
     *
     * @return array|null
     */
    public function ROW(): array|null
    {
        return $this->find();
    }

    /**
     * Execute a query and find all rows.
     *
     * @return array|null
     */
    public function FIND_ALL(): array|null
    {
        return $this->exec();
    }

    /**
     * Execute current query and return results.
     *
     * @return mixed
     */
    public function exec(): mixed
    {
        $result = $this->query->exec();

        $this->queryFactory();

        return $result;
    }

    /**
     * Creates a new Query instance once a query is executed.
     */
    protected function queryFactory(): void
    {
        $query = $this->query::class;

        $this->query = new $query($this->connectionHandler);
    }
}
