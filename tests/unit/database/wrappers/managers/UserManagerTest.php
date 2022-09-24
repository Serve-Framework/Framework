<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\wrappers\managers;

use serve\database\wrappers\managers\UserManager;
use serve\database\wrappers\providers\UserProvider;
use serve\security\Crypto;
use serve\security\password\hashers\NativePHP;
use serve\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class User
{
    public $status;

    public $serve_register_key;

    public function __construct()
    {
    }

    public static function save()
    {
        return true;
    }

    public static function delete()
    {
        return true;
    }
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class UserManagerTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $this->expectNotToPerformAssertions();

        $crypto   = $this->mock(Crypto::class);
        $password = $this->mock(NativePHP::class);
        $user     = $this->mock(User::class);
        $provider = $this->mock(UserProvider::class);
        $manager  = new UserManager($provider, $crypto);

        $crypto->shouldReceive('password')->andReturn($password);
        $password->shouldReceive('hash')->andReturn('hashedpass');
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('create')->andReturn($user);

        $manager->create('foo@bar.com');
        $manager->create('foo@bar.com', 'password');
        $manager->create('foo@bar.com', 'password', 'name');
        $manager->create('foo@bar.com', 'password', 'name', 'username');
        $manager->create('foo@bar.com', 'password', 'name', 'username', 'guest');
        $manager->create('foo@bar.com', 'password', 'name', 'username', 'guest', false);
    }

    /**
     *
     */
    public function testCreateAdmin(): void
    {
        $this->expectNotToPerformAssertions();

        $crypto   = $this->mock(Crypto::class);
        $password = $this->mock(NativePHP::class);
        $user     = $this->mock(User::class);
        $provider = $this->mock(UserProvider::class);
        $manager  = new UserManager($provider, $crypto);

        $crypto->shouldReceive('password')->andReturn($password);
        $password->shouldReceive('hash')->andReturn('hashedpass');
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('byKey')->andReturn(null);
        $provider->shouldReceive('create')->andReturn($user);

        $manager->createAdmin('foo@bar.com');
        $manager->createAdmin('foo@bar.com', 'administrator', false);
    }

    /**
     *
     */
    public function testActivate(): void
    {
        $crypto   = $this->mock(Crypto::class);
        $user     = new User;
        $provider = $this->mock(UserProvider::class);
        $manager  = new UserManager($provider, $crypto);

        $provider->shouldReceive('byKey')->andReturn($user);

        $manager->activate('csrf_token');

        $this->assertTrue($user->serve_register_key === null);
        $this->assertTrue($user->status === 'confirmed');
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $crypto   = $this->mock(Crypto::class);
        $user     = new User;
        $provider = $this->mock(UserProvider::class);
        $manager  = new UserManager($provider, $crypto);
        $provider->shouldReceive('byKey')->andReturn($user);

        $this->assertTrue($manager->delete('foo@bar.com'));
    }

    /**
     *
     */
    public function testNoDelete(): void
    {
        $crypto   = $this->mock(Crypto::class);
        $user     = new User;
        $provider = $this->mock(UserProvider::class);
        $manager  = new UserManager($provider, $crypto);
        $provider->shouldReceive('byKey')->andReturn(0);

        $this->assertFalse($manager->delete('foo@bar.com'));
    }

}
