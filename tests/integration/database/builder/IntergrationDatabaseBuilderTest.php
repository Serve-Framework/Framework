<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\database\builder;

use serve\database\builder\Builder;
use serve\database\connection\ConnectionHandler;
use serve\tests\integration\DatabaseTestCase;
use PDOException;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 */
class IntergrationDatabaseBuilderTest extends DatabaseTestCase
{
    /**
     *
     */
    public function builderFactory(): Builder
    {
        $this->setup();

        $cHandler = $this->connection->handler();

        $cHandler->cache()->disable();
        
        return new Builder($cHandler);
    }

    /**
     *
     */
    public function testSelect(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 5)->EXEC();

        $this->assertNull($result);

        // 

        $result = $builder->SELECT('id, username, email')->FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'username' => 'foo', 'email' => 'foo@example.org'] ], $result);

        //

        $result = $builder->SELECT(['id', 'username', 'email'])->FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'username' => 'foo', 'email' => 'foo@example.org'] ], $result);

        //

        $result = $builder->SELECT('*')->FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'group_id' => 1, 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'] ], $result);

        //

        $result = $builder->SELECT(['*'])->FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'group_id' => 1, 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'] ], $result);

        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 5', $this->connection->handler()->getLog()[1]['query']);

        $this->assertEquals('SELECT id, username, email FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[2]['query']);

        $this->assertEquals('SELECT id, username, email FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[3]['query']);
    }

    /**
     *
     */
    public function testSelectWithJoin(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->LEFT_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->LEFT_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);

        //

        $result = $builder->SELECT('id, email')->FROM('users')->INNER_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'email' => 'foo@example.org'], ['id' => 2, 'email' => 'bar@example.org'] ], $result);

        //

        $result = $builder->SELECT(['id', 'email'])->FROM('users')->INNER_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'email' => 'foo@example.org'], ['id' => 2, 'email' => 'bar@example.org'] ], $result);

        //

        $result = $builder->SELECT([ 'users' => ['id', 'email'] ])->FROM('users')->INNER_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'email' => 'foo@example.org'], ['id' => 2, 'email' => 'bar@example.org'] ], $result);

        //

        $result = $builder->SELECT([ 'users' => ['id', 'email'], 'groups' => ['name'] ])->FROM('users')->INNER_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'email' => 'foo@example.org', 'name' => 'admin'], ['id' => 2, 'email' => 'bar@example.org', 'name' => 'admin'] ], $result);

        //

        $result = $builder->SELECT([ 'users' => ['id', 'email'], 'groups' => ['name AS group_name'] ])->FROM('users')->INNER_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1, 'email' => 'foo@example.org', 'group_name' => 'admin'], ['id' => 2, 'email' => 'bar@example.org', 'group_name' => 'admin'] ], $result);

        //

        $this->assertEquals('SELECT serve_users.id FROM serve_users LEFT JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT serve_users.id FROM serve_users LEFT JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[1]['query']);

        $this->assertEquals('SELECT serve_users.id, serve_users.email FROM serve_users INNER JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[2]['query']);

        $this->assertEquals('SELECT serve_users.id, serve_users.email FROM serve_users INNER JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[3]['query']);

        $this->assertEquals('SELECT serve_users.id, serve_users.email FROM serve_users INNER JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[4]['query']);

        $this->assertEquals('SELECT serve_users.id, serve_users.email, serve_groups.name FROM serve_users INNER JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[5]['query']);

        $this->assertEquals('SELECT serve_users.id, serve_users.email, serve_groups.name AS group_name FROM serve_users INNER JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1', $this->connection->handler()->getLog()[6]['query']);
    }

    /**
     *
     */
    public function testWhere(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->EXEC();
         
        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('username', '=', 'foo')->EXEC();
         
        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('username', '=', 'does not exist')->EXEC();
         
        $this->assertNull($result);

        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE username = "foo"', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE username = "does not exist"', $this->connection->handler()->getLog()[2]['query']);

    }

    /**
     *
     */
    public function testMultiWhere(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->AND_WHERE('username', '=', 'foo')->EXEC();
         
        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->OR_WHERE('username', '=', 'foo')->EXEC();
         
        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->OR_WHERE('id', '=', 2)->EXEC();
         
        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->AND_WHERE('username', '=', 'foo')->OR_WHERE('id', '=', 2)->EXEC();
         
        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);
        
        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->AND_WHERE('username', '=', 'foo')->AND_WHERE('email', '=', 'foo@example.org')->OR_WHERE('id', '=', 2)->EXEC();
         
        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);
        
        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 AND username = "foo"', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 OR username = "foo"', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 OR id = 2', $this->connection->handler()->getLog()[2]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE (id = 1 AND username = "foo") OR id = 2', $this->connection->handler()->getLog()[3]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE (id = 1 AND username = "foo" AND email = "foo@example.org") OR id = 2', $this->connection->handler()->getLog()[4]['query']);
    }

    /**
     *
     */
    public function testWhereJoin(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->LEFT_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->AND_WHERE('users.id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1] ], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->LEFT_JOIN_ON('groups', 'users.group_id = groups.id')->WHERE('users.group_id', '=', 1)->OR_WHERE('users.id', '=', 1)->EXEC();

        $this->assertEquals([ ['id' => 1], ['id' => 2] ], $result);

        //

        $this->assertEquals('SELECT serve_users.id FROM serve_users LEFT JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1 AND serve_users.id = 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT serve_users.id FROM serve_users LEFT JOIN serve_groups ON serve_users.group_id = serve_groups.id WHERE serve_users.group_id = 1 OR serve_users.id = 1', $this->connection->handler()->getLog()[1]['query']);
    }

    /**
     *
     */
    public function testRow(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->ROW();

        $this->assertEquals(['id' => 1], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 5)->ROW();

        $this->assertNull($result);

        // 

        $result = $builder->SELECT('id, username, email')->FROM('users')->WHERE('id', '=', 1)->ROW();

        $this->assertEquals(['id' => 1, 'username' => 'foo', 'email' => 'foo@example.org'], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 2)->ROW();

        $this->assertEquals(['id' => 2], $result);

        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 LIMIT 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 5 LIMIT 1', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id, username, email FROM serve_users WHERE id = 1 LIMIT 1', $this->connection->handler()->getLog()[2]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 2 LIMIT 1', $this->connection->handler()->getLog()[3]['query']);
    }

    /**
     *
     */
    public function testFind(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->FIND();

        $this->assertEquals(['id' => 1], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->FIND(5);

        $this->assertNull($result);

        // 

        $result = $builder->SELECT('id')->FROM('users')->FIND(1);

        $this->assertEquals(['id' => 1], $result);

        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 LIMIT 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 5 LIMIT 1', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 LIMIT 1', $this->connection->handler()->getLog()[2]['query']);        
    }

    /**
     *
     */
    public function testFindAll(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->FIND_ALL();

        $this->assertEquals([['id' => 1]], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('group_id', '=', 1)->FIND_ALL();

        $this->assertEquals([['id' => 1], ['id' => 2]], $result);

        // 

        $result = $builder->SELECT('id')->FROM('users')->WHERE('group_id', '>', 1)->FIND_ALL();

        $this->assertEquals([['id' => 3]], $result);

        //
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE group_id = 1', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE group_id > 1', $this->connection->handler()->getLog()[2]['query']);
    }


    /**
     *
     */
    public function testLimit(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '=', 1)->LIMIT(1)->EXEC();

        $this->assertEquals(['id' => 1], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '>', 0)->LIMIT(1)->EXEC();

        $this->assertEquals(['id' => 1], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '>', 0)->LIMIT(3)->EXEC();

        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '>', 0)->LIMIT(5)->EXEC();

        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], $result);
       
        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '>', 0)->LIMIT(1, 1)->EXEC();

        $this->assertEquals(['id' => 2], $result);

        //

        $result = $builder->SELECT('id')->FROM('users')->WHERE('id', '>', 0)->LIMIT(2, 3)->EXEC();

        $this->assertEquals([['id' => 3]], $result);

        //

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1 LIMIT 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id > 0 LIMIT 1', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id > 0 LIMIT 3', $this->connection->handler()->getLog()[2]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id > 0 LIMIT 5', $this->connection->handler()->getLog()[3]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id > 0 LIMIT 1, 1', $this->connection->handler()->getLog()[4]['query']);
        
        $this->assertEquals('SELECT id FROM serve_users WHERE id > 0 LIMIT 2, 3', $this->connection->handler()->getLog()[5]['query']);
    }

    /**
     *
     */
    public function testOrderBy(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('username')->FROM('users')->ORDER_BY('username')->EXEC();

        $this->assertEquals([['username' => 'foo'], ['username' => 'baz'], ['username' => 'bar'] ], $result);

        //

        $result = $builder->SELECT('username')->FROM('users')->ORDER_BY('username', 'DESC')->EXEC();

        $this->assertEquals([['username' => 'foo'], ['username' => 'baz'], ['username' => 'bar'] ], $result);

        //

        $result = $builder->SELECT('username')->FROM('users')->ORDER_BY('username', 'ASC')->EXEC();

        $this->assertEquals([['username' => 'bar'], ['username' => 'baz'], ['username' => 'foo'] ], $result);

        //

        $this->assertEquals('SELECT username FROM serve_users ORDER BY username DESC', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT username FROM serve_users ORDER BY username DESC', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT username FROM serve_users ORDER BY username ASC', $this->connection->handler()->getLog()[2]['query']);
    }

    /**
     *
     */
    public function testGroup(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT(['group_id', 'COUNT(*) AS number'])->FROM('users')->GROUP_BY('group_id')->EXEC();

        $this->assertEquals([ ['group_id' => 1, 'number' => 2], ['group_id' => 2, 'number' => 1] ], $result);
            
        //  

        $this->assertEquals('SELECT group_id, COUNT(*) AS number FROM serve_users GROUP BY group_id', $this->connection->handler()->getLog()[0]['query']);
    }

    /**
     *
     */
    public function testGroupConcat(): void
    {
        $builder = $this->builderFactory();

        //

        $result = $builder->SELECT('id, first_name, last_name, dep_id')->GROUP_CONCAT('quality', 'qualities')->FROM('employees')->GROUP_BY('id')->EXEC();

        //

        $result = $builder->SELECT('dep_id')->GROUP_CONCAT('quality', 'Employee qualities', true)->FROM('employees')->GROUP_BY('dep_id')->EXEC();

        //

        $result = $builder->SELECT('dep_id')->GROUP_CONCAT('id', null, true)->FROM('employees')->EXEC();

        $this->assertEquals('SELECT id, first_name, last_name, dep_id , GROUP_CONCAT(quality) AS "qualities" FROM serve_employees GROUP BY id', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT dep_id , GROUP_CONCAT(DISTINCT quality) AS "Employee qualities" FROM serve_employees GROUP BY dep_id', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT dep_id , GROUP_CONCAT(DISTINCT id) FROM serve_employees', $this->connection->handler()->getLog()[2]['query']);
    }

    /**
     *
     */
    public function testCreateTable(): void
    {
        $builder = $this->builderFactory();

        $builder->DROP_TABLE('test');

        //

        $builder->CREATE_TABLE('test', ['created' => 'INTEGER | UNSIGNED']);

        $this->assertNull($builder->SELECT('*')->FROM('test')->EXEC());

        //

        $this->assertEquals('CREATE TABLE serve_test ( "created" INTEGER UNSIGNED, "id" INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT )', $this->connection->handler()->getLog()[1]['query']);
        
        $this->assertEquals('SELECT * FROM serve_test', $this->connection->handler()->getLog()[2]['query']);
    }

    /**
     *
     */
    public function testTruncateTable(): void
    {
        $builder = $this->builderFactory();
    
        //

        $builder->TRUNCATE_TABLE('users');

        $this->assertNull($builder->SELECT('*')->FROM('users')->EXEC());

        //

        $this->assertEquals('DELETE FROM serve_users', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT * FROM serve_users', $this->connection->handler()->getLog()[1]['query']);
    }

    /**
     *
     */
    public function testDropTable(): void
    {
        $this->expectException(PDOException::class);

        $builder = $this->builderFactory();
        
        //

        $builder->DROP_TABLE('users');

        $builder->SELECT('*')->FROM('users')->EXEC();

        //

        $this->assertEquals('DROP TABLE serve_users', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT * FROM serve_users', $this->connection->handler()->getLog()[1]['query']);
    }

    /**
     *
     */
    public function testInsert(): void
    {
        $builder = $this->builderFactory();

        //

        $values =
        [ 
            'group_id'   => 1,
            'username'   => 'foobar',
            'email'      => 'foobar@example.org',
            'created_at' => '2014-04-30 14:40:01',
        ];

        $insert = $builder->INSERT_INTO('users')->VALUES($values)->EXEC();

        $this->assertTrue($insert);

        $this->assertEquals(4, $this->connection->handler()->lastInsertId());

        $this->assertEquals(['username' => 'foobar'], $builder->SELECT('username')->FROM('users')->WHERE('id', '=', 4)->ROW());

        //

        $this->assertEquals('INSERT INTO serve_users (group_id, username, email, created_at) VALUES(1, "foobar", "foobar@example.org", "2014-04-30 14:40:01")', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('SELECT username FROM serve_users WHERE id = 4 LIMIT 1', $this->connection->handler()->getLog()[1]['query']);
    }

    /**
     *
     */
    public function testInsertFail(): void
    {
        $this->expectException(PDOException::class);

        // 
        
        $builder = $this->builderFactory();

        //

        $builder->INSERT_INTO('users')->VALUES([ 'email' => 'foobar@example.org' ])->EXEC();

        $logs = $this->connection->handler()->getLog();

        foreach($logs as $i => $log)
        {
            echo '$this->assertEquals("'. $log['query'] . '", $this->connection->handler()->getLog()['. $i .']["query"])' . "\n";
        }

        echo "\n\n";
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        $builder = $this->builderFactory();

        //

        $update = $builder->UPDATE('users')->SET([ 'username'   => 'changed' ])->WHERE('id', '=', 1)->EXEC();

        $this->assertEquals(1, $update);

        //

        $update = $builder->UPDATE('users')->SET([ 'username'   => 'changed' ])->WHERE('group_id', '=', 1)->EXEC();

        $this->assertEquals(2, $update);

        //

        $this->assertEquals('UPDATE serve_users SET username = "changed" WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);
        
        $this->assertEquals('UPDATE serve_users SET username = "changed" WHERE group_id = 1', $this->connection->handler()->getLog()[1]['query']);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $builder = $this->builderFactory();

        //

        $delete = $builder->DELETE_FROM('users')->WHERE('id', '=', 1)->EXEC();

        $this->assertNull($builder->SELECT('username')->FROM('users')->WHERE('id', '=', 4)->ROW());

        $this->assertEquals(1, $delete);

        //

        $this->assertEquals('DELETE FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT username FROM serve_users WHERE id = 4 LIMIT 1', $this->connection->handler()->getLog()[1]['query']);
    }
}
