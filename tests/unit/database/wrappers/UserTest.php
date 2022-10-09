<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\wrappers;

use serve\database\connection\ConnectionHandler;
use serve\database\builder\Builder;
use serve\database\wrappers\User;
use serve\tests\TestCase;

/**
 * @group unit
 */
class UserTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
    	$sql  = $this->mock(Builder::class);

		$user = new User($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $user->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
       	$sql  = $this->mock(Builder::class);

		$user = new User($sql);

        $user->name = 'baz';

        $this->assertEquals('baz', $user->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql);

        $user->name = 'baz';

        $this->assertTrue(isset($user->name));

        $this->assertFalse(isset($user->email));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql);

        $user->name = 'baz';

        unset($user->name);

        $this->assertEquals(null, $user->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $user->asArray());
    }

    /**
     *
     */
    public function testGenerateAccessToken(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql, ['name' => 'foo']);

		$user->generateAccessToken();

        $this->assertTrue(!empty($user->access_token));
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql, ['name' => 'foo']);

        $this->assertFalse($user->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql  = $this->mock(Builder::class);

		$user = new User($sql, ['id' => 2, 'name' => 'foo']);

		$sql->shouldReceive('DELETE_FROM')->with('users')->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

		$sql->shouldReceive('EXEC')->times(1)->andReturn(true);

        $this->assertTrue($user->delete());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
    	$cHandler = $this->mock(ConnectionHandler::class);

        $sql = $this->mock(Builder::class);

		$user = new User($sql, ['email' => 'foo@bar.com', 'access_token' => 'foobar']);

		$sql->shouldReceive('INSERT_INTO')->with('users')->once()->andReturn($sql);

		$sql->shouldReceive('VALUES')->with(['email' => 'foo@bar.com', 'access_token' => 'foobar'])->once()->andReturn($sql);

		$sql->shouldReceive('EXEC')->once()->andReturn(true);

		$sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

		$cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($user->save());

        $this->assertEquals(4, $user->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
    	$cHandler = $this->mock(ConnectionHandler::class);

        $sql = $this->mock(Builder::class);

		$user = new User($sql, ['id' => 3, 'email' => 'foo@bar.com', 'access_token' => 'foobar']);

		$sql->shouldReceive('UPDATE')->with('users')->once()->andReturn($sql);

		$sql->shouldReceive('SET')->with(['id' => 3, 'email' => 'foo@bar.com', 'access_token' => 'foobar'])->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 3)->once()->andReturn($sql);

		$sql->shouldReceive('EXEC')->once()->andReturn(true);

        $this->assertTrue($user->save());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteAdmin(): void
    {
        $sql  = $this->mock(Builder::class);

        $sql->shouldReceive('DELETE_FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('EXEC')->once()->andReturn(true);

		$user = new User($sql, ['id' => 2, 'name' => 'foo']);

        $user->delete();
    }
}
