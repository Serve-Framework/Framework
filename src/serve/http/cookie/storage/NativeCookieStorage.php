<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\cookie\storage;

use serve\security\Crypto;
use function serialize;
use function setcookie;
use function unserialize;

/**
 * Cookie encrypt/decrypt.
 *
 * @author Joe J. Howard
 */
class NativeCookieStorage implements StoreInterface
{
    /**
     * Encryption service.
     *
     * @var \serve\security\Crypto
     */
    private $crypto;

    /**
     * Cookie configuration.
     *
     * @var array
     */
    private $configuration;

    /**
     * Constructor.
     *
     * @param \serve\security\Crypto $crypto        Encryption service
     * @param array                  $configuration Assoc array of cookie configurations
     */
    public function __construct(Crypto $crypto, array $configuration)
    {
        $this->crypto = $crypto;

        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $name)
    {
        if (!isset($_COOKIE[$name]))
        {
            return false;
        }

        return unserialize($this->crypto->decrypt($_COOKIE[$name]));
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $name, $data)
    {
        return setcookie($name, $this->crypto->encrypt(serialize($data)), $this->configuration['expire'], $this->configuration['path'], $this->configuration['domain'], $this->configuration['secure'], $this->configuration['httponly']);
    }
}
