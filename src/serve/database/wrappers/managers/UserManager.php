<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\wrappers\managers;

use serve\database\wrappers\providers\UserProvider;
use serve\security\Crypto;
use serve\utility\Str;
use serve\utility\UUID;
use function filter_var;
use function is_int;
use function utf8_encode;

/**
 * User manager.
 *
 * @author Joe J. Howard
 */
class UserManager extends Manager
{
    /**
     * Status code for users that already exists by email.
     *
     * @var int
     */
    public const EMAIL_EXISTS = 104;

    /**
     * Status code for users that already exists by username.
     *
     * @var int
     */
    public const USERNAME_EXISTS = 105;

    /**
     * Status code for users that already exists by username.
     *
     * @var int
     */
    public const SLUG_EXISTS = 106;

    /**
     * Encryption manager.
     *
     * @var \serve\security\Crypto
     */
    private $crypto;

    /**
     * Override inherited constructor.
     *
     * @param \serve\database\wrappers\providers\UserProvider $provider User provider instance
     * @param \serve\security\Crypto                          $crypto   Encryption manager
     */
    public function __construct(UserProvider $provider, Crypto $crypto)
    {
    	$this->provider = $provider;

        $this->crypto = $crypto;
    }

    /**
     * Create a new user.
     *
     * @param  string $email    Valid email address
     * @param  string $password Password string (optional) (default '')
     * @param  string $name     Users name  (optional) (default '')
     * @param  string $username Username (optional) (default '')
     * @param  string $role     User role  (optional) (default 'guest')
     * @param  bool   $activate Activate the user straight away (optional) (default false)
     * @return mixed
     */
    public function create(string $email, string $password = '', string $name = '', string $username = '', string $role = 'guest', bool $activate = false)
    {
        // Sanitize email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Create a unique username based on the email if one
        // wasnt provided
        if (empty($username))
        {
            $username = $this->uniqueUserName(Str::slug(Str::getBeforeFirstChar($email, '@')));
        }
        else
        {
            $username = $this->uniqueUserName(Str::alphaNumDash($username));
        }

        // Hash/generate password
        if (empty($password))
        {
            $password = utf8_encode($this->crypto->password()->hash(Str::random(10)));
        }
        else
        {
            $password = utf8_encode($this->crypto->password()->hash($password));
        }

        // Generate other required fields
        $slug   = Str::slug($username);
        $status = !$activate ? 'pending' : 'confirmed';
        $token  = UUID::v4();
        $key    = !$activate ? UUID::v4() : null;

        // Make sure username is unique
        $byUsername = $this->provider->byKey('username', $username, true);

        if ($byUsername && $byUsername->status === 'confirmed')
        {
            return self::USERNAME_EXISTS;
        }

        // Make sure slug is unique
        $bySlug = $this->provider->byKey('slug', $slug, true);
        if ($bySlug && $bySlug->status === 'confirmed')
        {
            return self::SLUG_EXISTS;
        }

        // Make sure email is unique
        $byEmail = $this->provider->byKey('email', $email, true);
        if ($byEmail && $byEmail->status === 'confirmed')
        {
            return self::EMAIL_EXISTS;
        }

        return $this->provider->create([
            'email'              => $email,
            'username'           => $username,
            'hashed_pass'        => $password,
            'slug'               => $slug,
            'status'             => $status,
            'role'               => $role,
            'access_token'       => $token,
            'serve_register_key' => $key,
        ]);
    }

    /**
     * Registers a new admin user for the CMS.
     *
     * @param  string $email Valid email address
     * @param  string $role  'administrator' or 'writer'
     * @return mixed
     */
    public function createAdmin(string $email, string $role = 'administrator', $activate = false)
    {
        return $this->create($email, '', '', '', $role, $activate);
    }

    /**
     * Activate an existing user.
     *
     * @param  string $token Verification token from DB
     * @return bool
     */
    public function activate(string $token): bool
    {
        // Validate the user exists
        $user = $this->provider->byKey('serve_register_key', $token, true);

        if ($user)
        {
            $user->serve_register_key = null;

            $user->status = 'confirmed';

            if ($user->save())
            {
                return true;
            }
        }

        return false;
    }

	/**
	 * Deletes an existing user.
	 *
	 * @param  mixed $usernameIdorEmail Username, id or email
	 * @return bool
	 */
	public function delete($usernameIdorEmail): bool
	{
		$user = false;

		if (is_int($usernameIdorEmail))
		{
			$user = $this->provider->byKey('id', $usernameIdorEmail, true);
		}
		elseif (filter_var($usernameIdorEmail, FILTER_VALIDATE_EMAIL))
		{
			$user = $this->provider->byKey('email', $usernameIdorEmail, true);
		}
		else
		{
			$user = $this->provider->byKey('username', $usernameIdorEmail, true);
		}

		if ($user)
		{
			return $user->delete() ? true : false;
		}

		return false;
	}

    /**
     * {@inheritDoc}
     */
    public function provider(): UserProvider
	{
        return $this->provider;
	}

    /**
     * Create a unique username.
     *
     * @param  string $username The username
     * @return string
     */
    private function uniqueUserName(string $username): string
    {
        $baseName = $username;
        $count    = 1;
        $exists   = $this->provider->byKey('username', $username, true);

        while (!empty($exists))
        {
            $username = $baseName . $count;
            $exists   = $this->provider->byKey('username', $username, true);
            $count++;
        }

        return $username;
    }
}
