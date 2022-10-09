<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\wrappers\providers;

use serve\database\connection\ConnectionHandler;
use serve\database\builder\Builder;
use serve\database\wrappers\providers\UserProvider;
use serve\tests\TestCase;

/**
 * @group unit
 */
class UserProviderTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $cHandler = $this->mock(ConnectionHandler::class);
        $sql      = $this->mock(Builder::class);
        $provider = new UserProvider($sql);

        $sql->shouldReceive('INSERT_INTO')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['email' => 'foo@bar.com', 'access_token' => 'foobar'])->once()->andReturn($sql);

        $sql->shouldReceive('EXEC')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $user = $provider->create(['email' => 'foo@bar.com', 'access_token' => 'foobar']);

        $this->assertEquals(4, $user->id);
    }

    /**
     *
     */
    public function testById(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byId(32);
    }

    /**
     *
     */
    public function testByKey(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $this->assertEquals('foo', $provider->byKey('name', 'foo', true)->name);
    }

    /**
     *
     */
    public function testByKeys(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 32, 'name' => 'foo', 'slug' => 'bar']]);

        $this->assertEquals('foo', $provider->byKey('name', 'foo')[0]->name);
    }

    /**
     *
     */
    public function testByEmail(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byEmail('foo@bar.com');
    }

    /**
     *
     */
    public function TestByUsername(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('username', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byUsername('foo');
    }

    /**
     *
     */
    public function testByToken(): void
    {
        $sql = $this->mock(Builder::class);

        $provider = new UserProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('users')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('access_token', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byToken('foo');
    }
}
