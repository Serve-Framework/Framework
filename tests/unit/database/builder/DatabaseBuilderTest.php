<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\query;

use serve\database\builder\Builder;
use serve\database\builder\query\Query;
use serve\database\connection\Connection;
use serve\database\connection\ConnectionHandler;
use serve\tests\TestCase;

use function array_values;
use function count;
use function preg_replace;
use function reset;
use function str_contains;
use function trim;

/**
 * @group unit
 */
class DatabaseBuilderTest extends TestCase
{
    /**
     *
     */
    public function testCreateTable(): void
    {
        $this->expectNotToPerformAssertions();

        $tableConfig =
        [
            'id'            => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
            'description'   => 'VARCHAR(255)',
            'thumbnail_id'  => 'INTEGER | UNSIGNED',
            'notifications' => 'BOOLEAN | DEFAULT TRUE',
        ];

        $expectedSQL = 'CREATE TABLE prefixed_my_table_name ( `id` INT UNSIGNED UNIQUE AUTO_INCREMENT, `description` VARCHAR(255), `thumbnail_id` INTEGER UNSIGNED, `notifications` BOOLEAN DEFAULT TRUE PRIMARY KEY (id) ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->with($expectedSQL);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->CREATE_TABLE('my_table_name', $tableConfig);
    }

    /**
     *
     */
    public function testDropTable(): void
    {
        $this->expectNotToPerformAssertions();

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->with('DROP TABLE IF EXISTS prefixed_my_table_name');

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->DROP_TABLE('my_table_name');
    }

    /**
     *
     */
    public function testTruncateTable(): void
    {
        $this->expectNotToPerformAssertions();

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(['TRUNCATE TABLE prefixed_my_table_name']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->TRUNCATE_TABLE('my_table_name');
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'DELETE FROM prefixed_my_table_name WHERE foo = :';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar'];

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL) && count($bindings) === 1 && reset($bindings) === 'bar';
        });

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->DELETE_FROM('my_table_name')->WHERE('foo', '=', 'bar')->EXEC();
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'UPDATE prefixed_my_table_name SET column = :';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar', 'column' => 'value'];

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 2 && $bindings[0] === 'value' && $bindings[1] === 'bar';
        });

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->UPDATE('my_table_name')->SET(['column' => 'value'])->WHERE('foo', '=', 'bar')->EXEC();
    }

    /**
     *
     */
    public function testInsertInto(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'INSERT INTO prefixed_my_table_name';

        $bindings = ['column1' => 'value1', 'column2' => 'value2'];

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 2 && $bindings[0] === 'value1' && $bindings[1] === 'value2';
        });

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->INSERT_INTO('my_table_name')->values(['column1' => 'value1', 'column2' => 'value2'])->EXEC();
    }

    /**
     *
     */
    public function testSelectAll(): void
    {
        $this->expectNotToPerformAssertions();

        $query = 'SELECT * FROM prefixed_my_table_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->with($query, [])->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testSelectColumns(): void
    {
        $this->expectNotToPerformAssertions();

        $query = 'SELECT id, name FROM prefixed_my_table_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->with($query, null)->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('id, name')->FROM('my_table_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testSelectColumnsArray(): void
    {
        $this->expectNotToPerformAssertions();

        $query = 'SELECT id, name FROM prefixed_my_table_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->with($query, null)->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT(['id', 'name'])->FROM('my_table_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testSelectRow(): void
    {
        $this->expectNotToPerformAssertions();

        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs([$query, []])->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ROW();
    }

    /**
     *
     */
    public function testSelectFind(): void
    {
        $this->expectNotToPerformAssertions();

        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs([$query, []])->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->FIND();
    }

    /**
     *
     */
    public function testWhere(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name WHERE foo = :';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 1 && $bindings[0] === 'bar';
        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrWhere(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name WHERE foo = :';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 2 && $bindings[0] === 'bar' && $bindings[1] === 'foo';
        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->OR_WHERE('bar', '=', 'foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testAndWhere(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name WHERE foo = :';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 2 && $bindings[0] === 'bar' && $bindings[1] === 'foo';

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->AND_WHERE('bar', '=', 'foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testNestedOrWhere(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name WHERE (foo = :';

            $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            $bindings = array_values($bindings);

            return str_contains($sql, $expectedSQL) && count($bindings) === 3 && $bindings[0] === 'foo' && $bindings[1] === 'bar' && $bindings[2] === 'foobaz';

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', ['foo', 'bar', 'foobaz'])->FIND_ALL();
    }

    /**
     *
     */
    public function testJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name INNER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testInnerJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name INNER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->INNER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testLeftJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name LEFT JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LEFT_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testRightJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name RIGHT JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->RIGHT_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testLeftOutJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name LEFT OUTER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LEFT_OUTER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testRightOutJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name RIGHT OUTER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->RIGHT_OUTER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testFullOutJoinOn(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT prefixed_my_table_name.* FROM prefixed_my_table_name FULL OUTER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->FULL_OUTER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrder(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name ORDER BY foo DESC';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ORDER_BY('foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrderAsc(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name ORDER BY foo ASC';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ORDER_BY('foo', 'ASC')->FIND_ALL();
    }

    /**
     *
     */
    public function testGroupBy(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name GROUP BY foo';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->GROUP_BY('foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testGroupConcatBasic(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * , GROUP_CONCAT(foo) AS "bar" FROM prefixed_my_table_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->GROUP_CONCAT('foo', 'bar')->FIND_ALL();
    }

    /**
     *
     */
    public function testGroupConcatAll(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * , GROUP_CONCAT(DISTINCT foo) AS "bar" FROM prefixed_my_table_name';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->GROUP_CONCAT('foo', 'bar', true)->FIND_ALL();
    }

    /**
     *
     */
    public function testLimit(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LIMIT(1)->FIND_ALL();
    }

    /**
     *
     */
    public function testOffset(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedSQL = 'SELECT * FROM prefixed_my_table_name LIMIT 0, 3';

        $connection = $this->mock(Connection::class);

        $connectionHandler = $this->mock(ConnectionHandler::class);

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function ($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('connection')->andReturn($connection);

        $connection->shouldReceive('type')->andReturn('mysql');

        $connectionHandler->shouldReceive('query')->withArgs(function ($sql, $bindings) use ($expectedSQL)
        {
            return str_contains($sql, $expectedSQL);

        })->andReturn([]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LIMIT(0, 3)->FIND_ALL();
    }
}
