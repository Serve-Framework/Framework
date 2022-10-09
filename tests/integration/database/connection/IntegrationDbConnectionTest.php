<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\connection;

use PDO;
use serve\database\builder\Builder;
use serve\database\connection\ConnectionHandler;
use serve\tests\integration\DatabaseTestCase;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 */
class IntegrationDbConnectionTest extends DatabaseTestCase
{
    /**
     *
     */
    public function testConnected(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->isConnected());
    }

    /**
     *
     */
    public function testReconnect(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->reconnect() instanceof PDO);
    }

    /**
     *
     */
    public function testPdo(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->pdo() instanceof PDO);
    }

    /**
     *
     */
    public function testTablePrefix(): void
    {
        $this->setup();

        //

        $this->assertEquals('serve_', $this->connection->tablePrefix());
    }

    /**
     *
     */
    public function testIsAlive(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->isAlive());

        $this->connection->close();

        $this->assertFalse($this->connection->isAlive());
    }

    /**
     *
     */
    public function testclose(): void
    {
        $this->setup();

        //

        $this->connection->close();

        $this->assertFalse($this->connection->isConnected());

        $this->connection->close();

        $this->assertFalse($this->connection->isAlive());
    }

    /**
     *
     */
    public function testBuilder(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->builder() instanceof Builder);
    }

    /**
     *
     */
    public function testHandler(): void
    {
        $this->setup();

        //

        $this->assertTrue($this->connection->handler() instanceof ConnectionHandler);
    }

    /**
     *
     */
    public function testType(): void
    {
        $this->setup();

        //

        $this->assertEquals('sqlite', $this->connection->type());
    }
}
