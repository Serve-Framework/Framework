<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\wrappers\managers;

use serve\database\wrappers\providers\Provider;

/**
 * Provider manager base class.
 *
 * @author Joe J. Howard
 */
abstract class Manager
{
    /**
     * Provider.
     *
     * @var mixed
     */
    protected $provider;

    /**
     * Default constructor.
     *
     * @param \serve\database\wrappers\providers\Provider $provider Provider manager
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the provider.
     *
     * @return mixed
     */
    abstract public function provider();
}
