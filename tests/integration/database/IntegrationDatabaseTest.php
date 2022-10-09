<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\database;

use RuntimeException;
use serve\database\builder\Builder;
use serve\database\connection\Connection;

use serve\tests\integration\DatabaseTestCase;
use function count;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 */
class IntegrationDatabaseTest extends DatabaseTestCase
{
    /**
     *
     */
    public function testConnection(): void
    {
        $this->setup();

        $this->expectException(RuntimeException::class);

        //

        $this->assertTrue($this->database->connection() instanceof Connection);

        $this->assertTrue($this->database->connection('serve') instanceof Connection);

        $this->database->connection('foobar');
    }

    /**
     *
     */
    public function testCreate(): void
    {
        $this->setup();

        $this->expectException(RuntimeException::class);

        //

        $this->assertTrue($this->database->create('serve') instanceof Connection);

        $this->assertTrue($this->database->create() instanceof Connection);

        $this->database->create('foo');
    }

    /**
     *
     */
    public function testBuilder(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->database->builder() instanceof builder);
    }

    /**
     *
     */
    public function testConnections(): void
    {
        $this->setup();

        //

        $this->assertEquals(1, count($this->database->connections()));
    }
}
