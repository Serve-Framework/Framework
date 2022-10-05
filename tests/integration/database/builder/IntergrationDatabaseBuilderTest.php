<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\database\builder;

use serve\database\builder\Builder;
use serve\database\connection\ConnectionHandler;
use serve\tests\integration\DatabaseTestCase;

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

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[0]['query']);

        $this->assertEquals('SELECT id FROM serve_users WHERE id = 5', $this->connection->handler()->getLog()[1]['query']);

        $this->assertEquals('SELECT id, username, email FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[2]['query']);

        $this->assertEquals('SELECT id, username, email FROM serve_users WHERE id = 1', $this->connection->handler()->getLog()[3]['query']);
    }

    /**
     *
     */
   /* public function testSelectWithJoin(): void
    {
        
        
    }*/

    /**
     *
     */
   /* public function testWhere(): void
    {
        
        
    }*/

    /**
     *
     */
   /* public function testMultiWhere(): void
    {
        
        
    }*/

    /**
     *
     */
    /*public function testWhereJoin(): void
    {
        
        
    }*/

    /**
     *
     */
    /*public function testRow(): void
    {
        
        
    }*/

     /**
     *
     */
    /*public function testFind(): void
    {
        
        
    }*/

    /**
     *
     */
    /*public function testFindAll(): void
    {
        
        
    }*/
}
