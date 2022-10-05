<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\database\connection;

use serve\tests\integration\DatabaseTestCase;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 */
class IntegrationDbConnectionHandlerTest extends DatabaseTestCase
{
    /**
     *
     */
    public function testBind(): void
    {
        $this->setup();

    	$handler = $this->database->connection()->handler();

        $handler->bind('id', 1);

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id');

        $this->assertEquals($this->exampleData[0], $query[0]);

        $this->assertTrue(count($query) === 1);
    }

    /**
     *
     */
    public function testBindMore(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        $handler->bindMore(['id' => 1, 'username' => 'foo']);

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username');

        $this->assertEquals($this->exampleData[0], $query[0]);

        $this->assertTrue(count($query) === 1);
    }

        /**
     *
     */
    public function testBindFromQueryArgs(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertEquals($this->exampleData[0], $query[0]);

        $this->assertTrue(count($query) === 1);
    }

      /**
     *
     */
    public function testWithCaching(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        $handler->cache()->enable();

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertTrue($handler->getLog()[1]['from_cache']);
    }




}
