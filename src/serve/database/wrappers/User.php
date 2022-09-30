<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\wrappers;

use InvalidArgumentException;
use serve\utility\Str;
use serve\utility\UUID;

use function intval;
use function vsprintf;

/**
 * User utility wrapper.
 *
 * @author Joe J. Howard
 *
 * @property int    $id
 * @property string $username
 * @property string $email
 * @property string $hashed_pass
 * @property string $name
 * @property string $slug
 * @property string $role
 * @property string $status
 * @property string $access_token
 * @property string $register_key
 * @property string $password_key
 */
class User extends Wrapper
{
    /**
     * Override the set method.
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     */
    public function __set(string $key, $value): void
    {
        if ($key === 'slug')
        {
            $this->data[$key] = Str::slug($value);
        }
        elseif ($key === 'username')
        {
            $this->data[$key] = Str::alphaNumDash($value);
        }
        else
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(): bool
	{
        $saved = false;

        if (isset($this->data['id']))
        {
            $saved = $this->SQL->UPDATE('users')->SET($this->data)->WHERE('id', '=', $this->data['id'])->QUERY();
        }
        else
        {
            if (!isset($this->data['access_token']) || empty($this->data['access_token']))
            {
                $this->generateAccessToken();
            }

            $saved = $this->SQL->INSERT_INTO('users')->VALUES($this->data)->QUERY();

            if ($saved)
            {
                $this->data['id'] = intval($this->SQL->connectionHandler()->lastInsertId());
            }
        }

        return !$saved ? false : true;
	}

    /**
     * {@inheritDoc}
     */
    public function delete(): bool
	{
        if (isset($this->data['id']))
        {
            if ($this->data['id'] === 1)
            {
                throw new InvalidArgumentException(vsprintf("%s(): The primary user with id '1' is not deletable.", [__METHOD__]));
            }

            if ($this->SQL->DELETE_FROM('users')->WHERE('id', '=', $this->data['id'])->QUERY())
            {

                return true;
            }
        }

        return false;
	}

    /**
     * Generate an access token for this user.
     *
     * @return \serve\database\wrappers\User
     */
    public function generateAccessToken(): User
    {
        $this->data['access_token'] = UUID::v4();

        return $this;
    }
}
