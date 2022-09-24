<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\session;

use function hash;
use function random_bytes;

/**
 * Session CSRF Token.
 *
 * @author Joe J. Howard
 */
class Token
{
    /**
     * The token.
     *
     * @var string
     */
    private $token = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Regenerate the token.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->token;
    }

    /**
     * Set the token.
     *
     * @return string
     */
    public function set(string $token)
    {
        return $this->token = $token;
    }

    /**
     * Regenerate the token.
     *
     * @return string
     */
    public function regenerate(): string
    {
        $this->token = hash('sha256', random_bytes(16));

        return $this->token;
    }

    /**
     * Verify a user's access token.
     *
     * @param  string $token A token to make the comparison with
     * @return bool
     */
    public function verify(string $token): bool
    {
        return $token === $this->token;
    }
}
