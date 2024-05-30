<?php

namespace serve\graphql\connection;

use Exception;
use serve\cache\Cache;

use function microtime;
use function sprintf;
use function usleep;

/**
 * This file is part of Stiphle.
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ThrottleStorage
{
    /**
     * Cache storage.
     *
     * @var \serve\cache\Cache
     */
    protected $cache;

    /**
     * Lock wait timeout.
     *
     * @var int
     */
    protected $lockWaitTimeout;

    /**
     * Lock wait interval.
     *
     * @var int
     */
    protected $lockWaitInterval;

    /**
     * Constructor.
     *
     * @param \serve\cache\Cache $cache            Cache instance
     * @param int                $lockWaitTimeout  Timeout for lock
     * @param int                $lockWaitInterval Interval to check lock
     */
    public function __construct(Cache $cache, $lockWaitTimeout = 1000, $lockWaitInterval = 100)
    {
        $this->cache = $cache;

        $this->lockWaitTimeout = $lockWaitTimeout;

        $this->lockWaitInterval = $lockWaitInterval;
    }

    /**
     * Set lock timeout.
     *
     * @param int $lockWaitTimeout Timeout for lock
     */
    public function setLockWaitTimeout(int $lockWaitTimeout): void
    {
        $this->lockWaitTimeout = $lockWaitTimeout;
    }

    /**
     * Lock.
     *
     * We might have multiple requests coming in at once, so we lock the storage
     *
     * @param  string    $key Key to lock
     * @throws Exception
     */
    public function lock(string $key): void
    {
        $key = sprintf('%s::LOCK', $key);

        $start = microtime(true);

        while ($this->cache->has($key))
        {
            $passed = (microtime(true) - $start) * 1000;

            if ($passed > $this->lockWaitTimeout)
            {
                throw new Exception('Graphql throttle cache lock timeout.');
            }

            usleep($this->lockWaitInterval);
        }

        $this->cache->put($key, true);
    }

    /**
     * Unlock.
     *
     * @param string $key Key to lock
     */
    public function unlock(string $key): void
    {
        $key = sprintf('%s::LOCK', $key);

        $this->cache->delete($key);
    }

    /**
     * Get.
     *
     * @param  string $key
     * @return int
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Set last modified.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value): void
    {
        $this->cache->put($key, $value);
    }
}
