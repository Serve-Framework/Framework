<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\cache\stores;

/**
 * Cache database storage.
 *
 * @author Joe J. Howard
 */
class DatabaseStore implements StoreInterface
{
	/**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        
    }

    /**
     * {@inheritDoc}
     */
    public function put(string $key, $data): void
    {
 
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
    	return false;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        
    }

    /**
     * {@inheritDoc}
     */
    public function expired(string $key, int $maxAge): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        
    }
}
