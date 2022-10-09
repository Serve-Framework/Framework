<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\wrappers;

use serve\common\MagicObjectArray;
use serve\database\builder\Builder;

/**
 * Database wrapper base class.
 *
 * @author Joe J. Howard
 */
abstract class Wrapper extends MagicObjectArray
{
    /**
     * SQL query builder.
     *
     * @var \serve\database\builder\Builder
     */
    protected $SQL;

    /**
     * Constructor.
     *
     * @param \serve\database\builder\Builder $SQL  SQL query builder
     * @param array                           $data Array row from Database
     */
    public function __construct(Builder $SQL, array $data = [])
    {
        $this->SQL = $SQL;

        $this->data = !empty($data) ? $data : [];
    }

	/**
	 * Saves the row item.
	 *
	 * @return bool
	 */
	abstract public function save(): bool;

	/**
	 * Deletes the row item.
	 *
	 * @return bool
	 */
	abstract public function delete(): bool;
}
