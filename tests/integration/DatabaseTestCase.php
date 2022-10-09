<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration;

use serve\database\Database;
use serve\tests\TestCase;
use function file_get_contents;

/**
 * Builder test case.
 */
abstract class DatabaseTestCase extends TestCase
{
	/**
	 * @var \serve\database\Database
	 */
	protected $database;

	/**
	 * @var \serve\database\connection\Connection
	 */
	protected $connection;

	/**
	 * {@inheritDoc}
	 */
	public function setup(): void
	{
		$config =
		[
			'default' => 'serve',
			'configurations' =>
			[
				'serve' =>
				[
					'dsn'          => 'sqlite::memory:serve',
					'name'         => 'serve',
					'table_prefix' => 'serve_',
					'type'         => 'sqlite',
					'options'      => [],
				],
			],
		];

		$this->database = new Database($config);

		$this->connection = $this->database->connection('serve');

		// Load test info

		$sql = file_get_contents(__DIR__ . '/resources/database.sql');

		$this->connection->pdo()->exec($sql);
	}
}
