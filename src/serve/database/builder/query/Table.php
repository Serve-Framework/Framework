<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL statement wrapper for "DROP TABLE", "TRUNCATE TABLE", "CREATE TABLE"
 *
 */
class Table
{
    /**
	 * Table name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Table column schema.
	 *
	 * @var array|null
	 */
	protected $schema;

	/**
	 * Constructor.
	 *
	 * @param string     $name   Table name
	 * @param array|null $schema Table schema when creating a table (optional) (default null)
	 */
	public function __construct(string $name, ?array $schema = null)
	{
		$this->name = $name;

		$this->schema = !$schema ? null : $schema;
	}

	/**
	 * Return SQL for "CREATE TABLE"
	 *
	 * @return string
	 */
	public function create(): string
	{
		// Build the SQL
        $sql = [ 'CREATE TABLE `' . $this->name . '` ('];

        $this->schema['id'] = 'INT | UNSIGNED | UNIQUE | AUTO_INCREMENT';

        // Loop the columns
        foreach ($this->schema as $name => $params)
        {
            $name  = strtolower(str_replace(' ', '_', $name));

            $sql[] = '`' . $name  . '` ' . str_replace('|', '', $params) . ',';
        }

        // Set default table configuration
        $sql[] = "PRIMARY KEY (id)\n) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        return implode(' ', $sql);
	}

	/**
	 * Return SQL for "TRUNCATE TABLE"
	 *
	 * @return string
	 */
	public function truncate(): string
	{
		return 'TRUNCATE TABLE `' . $this->name . '`';
	}

	/**
	 * Return SQL for "DROP TABLE"
	 *
	 * @return string
	 */
	public function drop(): string
	{
		return 'DROP TABLE `' . $this->name . '`';
	}
}
