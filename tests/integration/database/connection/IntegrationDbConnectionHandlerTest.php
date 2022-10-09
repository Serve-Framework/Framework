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
     * Example data for testing.
     * 
     * @var array
     */
    protected $exampleData =
    [
        [
            'id'           => 1,
            'username'     => 'foo',
            'email'        => 'foo@example.org',
            'group_id'     => 1,
            'created_at'   => '2014-04-30 14:40:01',
        ],
        [
            'id'           => 1,
            'username'     => 'bar',
            'email'        => 'bar@example.org',
            'group_id'     => 1,
            'created_at'   => '2014-04-30 14:02:43',
        ],
        [
            'id'           => 1,
            'username'     => 'baz',
            'email'        => 'baz@example.org',
            'group_id'     => 2,
            'created_at'   => '2014-04-30 14:12:43'
        ],
    ];

    /**
     *
     */
    public function testBind(): void
    {
        $this->setup();

    	$handler = $this->database->connection()->handler();

        //

        $handler->bind('id', 1);

        $query = $handler->query('SELECT * FROM serve_users WHERE id = :id');

        $this->assertEquals($this->exampleData[0], $query[0]);

        $this->assertTrue(count($query) === 1);
    }

    /**
     *
     */
    public function testBindMultiple(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $handler->bindMultiple(['id' => 1, 'username' => 'foo']);

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

        //

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

        //

        $handler->cache()->enable();

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertTrue($handler->getLog()[1]['from_cache']);
    }

    /**
     *
     */
    public function testCacheClear(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $handler->cache()->enable();

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $handler->cache()->clear();

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertFalse($handler->getLog()[1]['from_cache']);

       
    }

    /**
     *
     */
    public function testCacheClearFromDelete(): void
    {
       $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $handler->cache()->enable();

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $handler->query('DELETE FROM serve_users');

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertFalse($handler->getLog()[2]['from_cache']);
    }

    /**
     *
     */
    public function testCacheClearFromUpdate(): void
    {
       $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $handler->cache()->enable();

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $handler->query('UPDATE serve_users SET username = :username WHERE id = :id', ['id' => 1, 'username' => 'foo']);

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertFalse($handler->getLog()[2]['from_cache']);
    }

    /**
     *
     */
    public function testLastInsertId(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $values =
        [ 
            'foo'   => 1,
            'bar'   => 'foobar',
            'baz'      => 'foobar@example.org',
            'bat' => '2014-04-30 14:40:01',
        ];

        $insert = $handler->query('INSERT INTO serve_users (group_id, username, email, created_at) VALUES(:foo, :bar, :baz, :bat)', $values);

        $this->assertEquals(4, $handler->lastInsertId());
    }

    /**
     *
     */
    public function testCleanQuery(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $this->assertEquals('FOO BAR', $handler->cleanQuery('FOO  BAR'));
    }

    /**
     *
     */
    public function testLog(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $this->assertEquals(0, count($handler->getLog()));

        $handler->query('SELECT * FROM serve_users WHERE id = :id OR username = :username', ['id' => 1, 'username' => 'foo']);

        $this->assertEquals(1, count($handler->getLog()));
    }

    /**
     *
     */
    public function testQueryReturns(): void
    {
        $this->setup();

        $handler = $this->database->connection()->handler();

        //

        $this->assertTrue(is_array($handler->query('SELECT * FROM serve_users WHERE id = :id', ['id' => 1])));

        $this->assertEquals([], $handler->query('SELECT * FROM serve_users WHERE id = :id', ['id' => 10]));

        $this->assertEquals(1, $handler->query('UPDATE serve_users SET username = :username WHERE id = :id', ['id' => 1, 'username' => 'foo']));

        $this->assertEquals(3, $handler->query('UPDATE serve_users SET username = :username WHERE id > :id', ['id' => 0, 'username' => 'foo']));

        $this->assertEquals(0, $handler->query('UPDATE serve_users SET username = :username WHERE id = :id', ['id' => 15, 'username' => 'foo']));

        $this->assertEquals(1, $handler->query('DELETE FROM serve_users WHERE id = :id', ['id' => 1]));

        $this->assertEquals(2, $handler->query('DELETE FROM serve_users WHERE id > :id', ['id' => 0]));

        $this->assertEquals(0, $handler->query('DELETE FROM serve_users WHERE id = :id', ['id' => 15]));
    }
}
