<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use function end;
use function implode;
use function key;
use function rtrim;
use function str_replace;
use function strtolower;

/**
 * SQL statement wrapper for "DROP TABLE", "TRUNCATE TABLE", "CREATE TABLE".
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
	 * Return SQL for "CREATE TABLE".
	 *
	 * @param  string $connectionType Connection database type
	 * @return string
	 */
	public function create(string $connectionType): string
	{
		if ($connectionType === 'sqlite')
		{
			$this->schema['id'] = 'INTEGER | NOT NULL | UNIQUE | PRIMARY KEY | AUTOINCREMENT';
		}
		else
		{
			$this->schema['id'] = 'INT | UNSIGNED | UNIQUE | AUTO_INCREMENT';
		}

		// Build the SQL
        $sql = ['CREATE TABLE ' . $this->name . ' ('];

        // Loop the columns
        foreach ($this->schema as $name => $schema)
        {
            $name  = strtolower(str_replace(' ', '_', $name));

            $sql[] = '`' . $name . '` ' . str_replace('|', '', $schema) . ',';
        }

        $lastVal = end($sql);
		$lastkey = key($sql);

		$sql[$lastkey] = rtrim($lastVal, ', ');

        if ($connectionType === 'sqlite')
        {
        	$sql[] = ')';
        }
        else
		{
        	// Set default table configuration
        	$sql[] = "PRIMARY KEY (id)\n) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        }

        return implode(' ', $sql);
	}

	/**
	 * Return SQL for "TRUNCATE TABLE".
	 *
	 * @param  string $connectionType Connection database type
	 * @return string
	 */
	public function truncate(string $connectionType): string
	{
		if ($connectionType === 'sqlite')
		{
			return 'DELETE FROM ' . $this->name;
		}

		return 'TRUNCATE TABLE ' . $this->name;
	}

	/**
	 * Return SQL for "DROP TABLE".
	 *
	 * @return string
	 */
	public function drop(): string
	{
		return 'DROP TABLE IF EXISTS ' . $this->name;
	}
}
