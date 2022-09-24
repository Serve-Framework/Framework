<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\cms\auth;

use serve\auth\Gatekeeper;
use serve\database\query\Builder;
use serve\database\wrappers\providers\UserProvider;
use serve\database\wrappers\User;
use serve\http\cookie\Cookie;
use serve\http\session\Session;
use serve\http\session\Token;
use serve\security\Crypto;
use serve\security\password\hashers\NativePHP;
use serve\tests\TestCase;
use function is_string;

/**
 * @group unit
 */
class GatekeeperTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructor(): void
	{
		$this->expectNotToPerformAssertions();

		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);
	}

	/**
	 *
	 */
	public function testConstructorLoggedIn(): void
	{
		$this->expectNotToPerformAssertions();

		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);
	}

	/**
	 *
	 */
	public function testConstructorExpiredCSRF(): void
	{
		$this->expectNotToPerformAssertions();

		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);
	}

	/**
	 *
	 */
	public function testIsLoggedInTrue(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertTrue($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testIsLoggedInFalse(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertFalse($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testGetUserTrue(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertEquals($user, $gatekeeper->getUser());
	}

	/**
	 *
	 */
	public function testGetUserFalse(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertNull($gatekeeper->getUser());
	}

	/**
	 *
	 */
	public function testRefreshUser(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$user->id = 1;

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar')->once();

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token']);

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$token->shouldReceive('get')->andReturn('foobar token')->once();

		$cookie->shouldReceive('setMultiple')->with([
            'user_id' => 1,
            'email'   => 'foobar@mail.com',
        ]);

        $session->shouldReceive('setMultiple')->with(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token']);

        $sql->shouldReceive('UPDATE')->with('users')->andReturn($sql);

		$sql->shouldReceive('SET')->with(['access_token' => 'foobar token'])->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('QUERY');

		$cookie->shouldReceive('login');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$gatekeeper->refreshUser();
	}

	/**
	 *
	 */
	public function testGetTokenLoggenIn(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertEquals('foobar', $gatekeeper->token());
	}

	/**
	 *
	 */
	public function testGetTokenLoggenOut(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertEquals('foobar', $gatekeeper->token());
	}

	/**
	 *
	 */
	public function testIsGuestTrue(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertTrue($gatekeeper->isGuest());
	}

	/**
	 *
	 */
	public function testIsGuestFalse(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$user->role = 'administrator';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertFalse($gatekeeper->isGuest());

		$user->role = 'customer';

		$this->assertTrue($gatekeeper->isGuest());
	}

	/**
	 *
	 */
	public function testIsAdmin(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$user->role = 'administrator';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertTrue($gatekeeper->isAdmin());

		$user->role = 'customer';

		$this->assertFalse($gatekeeper->isAdmin());
	}

	/**
	 *
	 */
	public function testverifyTokenLoggedIn(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$this->assertTrue($gatekeeper->verifyToken('foobar'));

	}

	/**
	 *
	 */
	public function testverifyTokenLoggedOut(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$this->assertTrue($gatekeeper->verifyToken('foobar'));

		$this->assertFalse($gatekeeper->verifyToken('foobaz'));
	}

	/**
	 *
	 */
	public function testLogin(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('verify')->with('raw password', 'hased password')->andReturn(true);

		$cookie->shouldReceive('destroy');

		$session->shouldReceive('destroy');

		$session->shouldReceive('start');

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar token')->once();

		$cookie->shouldReceive('setMultiple')->with([
            'user_id' => 1,
            'email'   => 'foobar@mail.com',
        ]);

        $session->shouldReceive('setMultiple')->with(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

        $sql->shouldReceive('UPDATE')->with('users')->andReturn($sql);

		$sql->shouldReceive('SET')->with(['access_token' => 'foobar token'])->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

		$sql->shouldReceive('QUERY');

		$cookie->shouldReceive('login');

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$this->assertTrue($gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginIncorrectPass(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'confirmed', 'hashed_pass' => 'hased password']);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('verify')->with('raw password', 'hased password')->andReturn(false);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginPending(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'pending', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_ACTIVATING, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginBanned(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'banned', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_BANNED, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginLocked(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn(['id' => 1, 'email' => 'foobar@mail.com', 'access_token' => 'foobar token', 'status' => 'locked', 'hashed_pass' => 'hased password']);

		$this->assertEquals(Gatekeeper::LOGIN_LOCKED, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginDoesntExist(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('email', '=', 'foo@bar.com')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn([]);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@bar.com', 'raw password'));
	}

	/**
	 *
	 */
	public function testLoginByUsername(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$sql->shouldReceive('SELECT')->with('*')->andReturn($sql);

		$sql->shouldReceive('FROM')->with('users')->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('username', '=', 'foobar')->andReturn($sql);

		$sql->shouldReceive('ROW')->andReturn([]);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foobar', 'raw password'));
	}

	/**
	 *
	 */
	public function testLogout(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);

		$user->access_token = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(true)->once();

		$cookie->shouldReceive('get')->with('user_id')->andReturn(1);

		$userProvider->shouldReceive('byId')->with(1)->andReturn($user);

		$session->shouldReceive('token')->andReturn($token);

		$token->shouldReceive('get')->andReturn('foobar');

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$cookie->shouldReceive('logout');

		$gatekeeper->logout();

		$cookie->shouldReceive('isLoggedIn')->andReturn(false)->once();

		$this->assertFalse($gatekeeper->isLoggedIn());
	}

	/**
	 *
	 */
	public function testForgotPassowrd(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$userProvider->shouldReceive('byKey')->with('email', 'foo@bar.com', true)->andReturn($user);

		$user->shouldReceive('save')->once();

		$this->assertTrue(is_string($gatekeeper->forgotPassword('foo@bar.com')));

		$this->assertTrue($user->serve_password_key !== null);
	}

	/**
	 *
	 */
	public function testForgotPassowrdByUsername(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$userProvider->shouldReceive('byKey')->with('username', 'usernamefoo', true)->andReturn($user);

		$user->shouldReceive('save')->once();

		$this->assertTrue(is_string($gatekeeper->forgotPassword('usernamefoo')));

		$this->assertTrue($user->serve_password_key !== null);
	}

	/**
	 *
	 */
	public function testResetPassword(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);

		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$userProvider->shouldReceive('byKey')->with('serve_password_key', 'foobartoken', true)->andReturn($user);

		$crypto->shouldReceive('password')->andReturn($password);

		$password->shouldReceive('hash')->with('password')->andReturn('encrypted password');

		$user->shouldReceive('save')->once();

		$this->assertTrue($gatekeeper->resetPassword('password', 'foobartoken'));
	}

	/**
	 *
	 */
	public function testForgotUsername(): void
	{
		$sql          = $this->mock(Builder::class);
		$userProvider = $this->mock(UserProvider::class);
		$crypto       = $this->mock(Crypto::class);
		$cookie       = $this->mock(Cookie::class);
		$session      = $this->mock(Session::class);
		$token        = $this->mock(Token::class);
		$user         = $this->mock(User::class);
		$password     = $this->mock(NativePHP::class);

		$user->username = 'foobar';

		$cookie->shouldReceive('isLoggedIn')->andReturn(false);

		$cookie->shouldReceive('get')->with('user_id')->andReturn(null);

		$gatekeeper = new Gatekeeper($sql, $userProvider, $crypto, $cookie, $session);

		$userProvider->shouldReceive('byKey')->with('email', 'foo@bar.com', true)->andReturn($user);

		$this->assertEquals('foobar', $gatekeeper->forgotUsername('foo@bar.com'));
	}
}
