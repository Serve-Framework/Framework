<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\session;

use serve\common\MagicObjectArray;
use serve\http\session\storage\StoreInterface;

use function is_array;

/**
 * Session Manager.
 *
 * @author Joe J. Howard
 */
class Session extends MagicObjectArray
{
    /**
     * The session storage implementation.
     *
     * @var \serve\http\session\storage\StoreInterface
     */
    private $store;

    /**
     * The session flash data.
     *
     * @var \serve\http\session\Flash
     */
    private $flash;

    /**
     * CSRF token.
     *
     * @var \serve\http\session\Token
     */
    private $token;

    /**
     * The key that is used to store flash data inside $_SESSION[$sessionKey].
     *
     * @var string
     */
    private $flashKey = 'serve_flash';

    /**
     * The key that is used to store the CSRF token inside $_SESSION[$sessionKey].
     *
     * @var string
     */
    private $tokenKey = 'serve_token';

    /**
     * The key that is used to store the key/values inside $_SESSION[$sessionKey].
     *
     * @var string
     */
    private $dataKey = 'serve_data';

    /**
     * Constructor.
     *
     * @param \serve\http\session\Token                  $token Token wrapper
     * @param \serve\http\session\Flash                  $flash Flash wrapper
     * @param \serve\http\session\storage\StoreInterface $store Store implementation
     */
    public function __construct(Token $token, Flash $flash, StoreInterface $store, array $configuration)
    {
        $this->token = $token;

        $this->flash = $flash;

        $this->store = $store;

        $this->configure($configuration);

        $this->initializeSession();
    }

    /**
     * Set cookie the configuration.
     *
     * @param $configuration array Array of configuration options
     */
    public function configure(array $configuration): void
    {
        $this->store->session_name($configuration['cookie_name']);

        $this->store->session_set_cookie_params($configuration);
    }

    /**
     * Save the session so PHP can send it.
     */
    public function save(): void
    {
        $data =
        [
            $this->dataKey => $this->get(),

            $this->flashKey => $this->flash->getRaw(),

            $this->tokenKey => $this->token->get(),
        ];

        $this->store->write($data);

        $this->store->send();
    }

    /**
     * Initialize the session.
     */
    private function initializeSession(): void
    {
        $this->store->session_start();

        $this->loadData();

        $this->flash->iterate();

        if (empty($this->token->get()))
        {
            $this->token->regenerate();
        }
    }

    /**
     * Load the data from the session.
     */
    private function loadData(): void
    {
        $data = $this->store->read();

        if ($data && is_array($data))
        {
            if (isset($data[$this->dataKey]))
            {
                $this->overwrite($data[$this->dataKey]);
            }

            if (isset($data[$this->flashKey]))
            {
                $this->flash->clear();

                $this->flash->putRaw($data[$this->flashKey]);
            }

            if (isset($data[$this->tokenKey]))
            {
                $this->token->set($data[$this->tokenKey]);
            }
        }
    }

    /**
     * Get the access token.
     *
     * @return \serve\http\session\Token
     */
    public function token(): Token
    {
        return $this->token;
    }

    /**
     * Get the access token.
     *
     * @return \serve\http\session\Flash
     */
    public function flash(): Flash
    {
        return $this->flash;
    }

    /**
     * Clear the session.
     */
    public function destroy(): void
    {
        // Clear the internal session data
        $this->clear();

        // Clear the flash data
        $this->flash->clear();

        // Generate a new access token
        $this->token->regenerate();
    }

    /**
     * Clear the session.
     */
    public function start(): void
    {
        $this->store->session_start();
    }
}
