<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\cache;

use serve\cache\stores\StoreInterface;

/**
 * Cache storage.
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Unix timestamp of max cache lifetime.
     *
     * @var int
     */
    private $lifetime;

    /**
     * Storage implementation.
     *
     * @var \serve\cache\stores\StoreInterface
     */
    private $store;

    /**
     * Constructor.
     *
     * @param int                                $lifetime Date the cache will expire (unix timestamp)
     * @param \serve\cache\stores\StoreInterface $store    Storage impementation
     */
    public function __construct(int $lifetime, StoreInterface $store)
    {
        $this->store = $store;

        $this->lifetime = $lifetime;
    }

    /**
     * Load a key value.
     *
     * @param string $key Key to load
     */
    public function get(string $key)
    {
        return $this->store->get($key);
    }

    /**
     * Save a key value.
     *
     * @param string $key  Key to save the output
     * @param string $data Data to store
     */
    public function put(string $key, $data): void
    {
        $this->store->put($key, $data);
    }

    /**
     * Check if a key is stored.
     *
     * @param string $key Key to check
     */
    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    /**
     * Remove a key value.
     *
     * @param string $key Key to delete
     */
    public function delete(string $key): void
    {
        $this->store->delete($key);
    }

    /**
     * Checks is key value is expired.
     *
     * @param string $key Key to check
     */
    public function expired(string $key): bool
    {
        return $this->store->expired($key, $this->lifetime);
    }

    /**
     * Clear the entire cache.
     */
    public function clear(): void
    {
        $this->store->clear();
    }
}
