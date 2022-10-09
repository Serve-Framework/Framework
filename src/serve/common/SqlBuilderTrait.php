<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\common;

use serve\database\builder\Builder;
use serve\ioc\Container;

use function is_null;

/**
 * SQL Builder Trait.
 *
 * @author Joe J. Howard
 */
trait SqlBuilderTrait
{
    /**
     * SQL query builder instance.
     *
     * @var \serve\database\builder\Builder|null
     */
    protected $sql = null;

    /**
     * Instantiate and/or return a query builder instance.
     *
     * @return \serve\database\builder\Builder
     */
    protected function sql(): Builder
    {
        if (is_null($this->sql))
        {
            $this->sql = Container::instance()->Database->connection()->builder();
        }

        return $this->sql;
    }
}
