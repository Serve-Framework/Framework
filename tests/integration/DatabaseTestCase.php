<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration;

use serve\database\connection\Connection;
use serve\tests\TestCase;
use PDO;

/**
 * Builder test case.
 */
abstract class DatabaseTestCase extends TestCase
{
	/**
	 * @var \serve\database\connection\Connection
	 */
	protected $connection;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var array
	 */
	protected $exampleData =
	[
		[
			'id'           => 1,
			'username'     => 'foo',
			'email'        => 'foo@example.org',
			'hashed_pass'  => NULL,
			'name'         => NULL,
			'slug'         => 'foo',
			'role'         => NULL,
			'status'       => NULL,
			'access_token' => NULL,
			'register_key' => NULL,
			'password_key' => NULL,
		],
		[
			'id'           => 1,
			'username'     => 'bar',
			'email'        => 'bar@example.org',
			'hashed_pass'  => NULL,
			'name'         => NULL,
			'slug'         => 'bar',
			'role'         => NULL,
			'status'       => NULL,
			'access_token' => NULL,
			'register_key' => NULL,
			'password_key' => NULL,

		],
		[
			'id'           => 1,
			'username'     => 'baz',
			'email'        => 'baz@example.org',
			'hashed_pass'  => NULL,
			'name'         => NULL,
			'slug'         => 'baz',
			'role'         => NULL,
			'status'       => NULL,
			'access_token' => NULL,
			'register_key' => NULL,
			'password_key' => NULL,
		],
	];

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
					'dsn'          => 'sqlite::memory:',
					'table_prefix' => 'serve_',
					'options'      =>
					[
						'ATTR_STRINGIFY_FETCHES' => true,
					],
				],
			],
		];

		$this->connection = new Connection($config['configurations']['serve'], 'sqlite');

		// Load test info

		$sql = file_get_contents(__DIR__ . '/resources/database.sql');

		$this->connection->pdo()->exec($sql);
	}
}