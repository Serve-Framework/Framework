<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\connection;

use function ceil;
use function microtime;
use function usleep;

/**
 * This file is part of Stiphle.
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A 'leaky bucket' style rate limiter.
 *
 * @see    http://stackoverflow.com/questions/1375501/how-do-i-throttle-my-sites-api-users
 * @author Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Throttle
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param \serve\graphql\connection\ThrottleStorage $storage Storage implementation
     */
    public function __construct(ThrottleStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Throttle.
     *
     * @param  string                   $key          - A unique key for what we're throttling
     * @param  int                      $limit        - How many are allowed
     * @param  int                      $milliseconds - In this many milliseconds
     * @throws LockWaitTimeoutException
     * @return int
     */
    public function throttle($key, $limit, $milliseconds)
    {
        /**
         * Try and do our waiting without a lock.
         */
        $key = $this->getStorageKey($key, $limit, $milliseconds);
        $wait     = 0;
        $newRatio = $this->getNewRatio($key, $limit, $milliseconds);

        if ($newRatio > $milliseconds) {
            $wait = ceil($newRatio - $milliseconds);
        }
        usleep($wait * 1000);

        /*
         * Lock, record and release
         */
        $this->storage->lock($key);
        $newRatio = $this->getNewRatio($key, $limit, $milliseconds);
        $this->setLastRatio($key, $newRatio);
        $this->setLastRequest($key, microtime(1));
        $this->storage->unlock($key);
        return $wait;
    }

    /**
     * Get Estimate (doesn't require lock).
     *
     * How long would I have to wait to make a request?
     *
     * @param  string $key          - A unique key for what we're throttling
     * @param  int    $limit        - How many are allowed
     * @param  int    $milliseconds - In this many milliseconds
     * @return int    - the number of milliseconds before this request should be allowed
     *                             to pass
     */
    public function getEstimate($key, $limit, $milliseconds)
    {
        $key = $this->getStorageKey($key, $limit, $milliseconds);
        $newRatio = $this->getNewRatio($key, $limit, $milliseconds);
        $wait     = 0;
        if ($newRatio > $milliseconds) {
            $wait = ceil($newRatio - $milliseconds);
        }
        return $wait;
    }

    /**
     * Get new ratio.
     *
     * Assuming we're making a request, get the ratio of requests made to
     * requests allowed
     *
     * @param  string $key          - A unique key for what we're throttling
     * @param  int    $limit        - How many are allowed
     * @param  int    $milliseconds - In this many milliseconds
     * @return float
     */
    protected function getNewRatio($key, $limit, $milliseconds)
    {
        $lastRequest = $this->getLastRequest($key) ?: 0;
        $lastRatio   = $this->getLastRatio($key) ?: 0;

        $diff = (microtime(1) - $lastRequest) * 1000;

        $newRatio = $lastRatio - $diff;
        $newRatio = $newRatio < 0 ? 0 : $newRatio;
        $newRatio+= $milliseconds/$limit;

        return $newRatio;
    }

    /**
     * Get storage key.
     *
     * @param  string $key          - A unique key for what we're throttling
     * @param  int    $limit        - How many are allowed
     * @param  int    $milliseconds - In this many milliseconds
     * @return string
     */
    protected function getStorageKey($key, $limit, $milliseconds)
    {
        return $key . '::' . $limit . '::' . $milliseconds;
    }

    /**
     * Get Last Ratio.
     *
     * @param  string $key
     * @return float
     */
    protected function getLastRatio($key)
    {
        return $this->storage->get($key . '::LASTRATIO');
    }

    /**
     * Set Last Ratio.
     *
     * @param string $key
     * @param float  $ratio
     */
    protected function setLastRatio($key, $ratio): void
    {
        $this->storage->set($key . '::LASTRATIO', $ratio);
    }

    /**
     * Get Last Request.
     *
     * @param  string $key
     * @return float
     */
    protected function getLastRequest($key)
    {
        return $this->storage->get($key . '::LASTREQUEST');
    }

    /**
     * Set Last Request.
     *
     * @param string $key
     * @param float  $request
     */
    protected function setLastRequest($key, $request): void
    {
        $this->storage->set($key . '::LASTREQUEST', $request);
    }
}
