<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\wrappers\providers;

use serve\database\wrappers\User;

/**
 * User provider.
 *
 * @author Joe J. Howard
 */
class UserProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $user = new User($this->SQL, $row);

        if ($user->save())
        {
            return $user;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function byId(int $id)
    {
    	return $this->byKey('id', $id, true);
    }

    /**
     * {@inheritdoc}
     */
    public function byKey(string $key, $value, bool $single = false)
    {
    	if ($single)
        {
    		$row = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new User($this->SQL, $row);
            }

            return null;
    	}
    	else
        {
            $users = [];

    		$rows = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $users[] = new User($this->SQL, $row);
            }

            return $users;
    	}
    }

    /**
     * Gets a user by email.
     *
     * @param  string $email User email
     * @return mixed
     */
    public function byEmail(string $email)
    {
        return $this->byKey('email', $email, true);
    }

    /**
     * Gets a user by username.
     *
     * @param  string $username Username
     * @return mixed
     */
    public function byUsername(string $username)
    {
        return $this->byKey('username', $username, true);
    }

    /**
     * Gets a user by access token.
     *
     * @param  string $token User access token
     * @return mixed
     */
    public function byToken(string $token)
    {
        return $this->byKey('access_token', $token, true);
    }
}
