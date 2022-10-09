<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

/**
 * SQL "LIMIT" statement wrapper.
 */
class Limit
{
	/**
	 * Offset if it exists.
	 *
	 * @var int|null
	 */
	protected $offset;

	/**
	 * Limit number.
	 *
	 * @var int
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @param int      $offset Offset to start at or limit if single argument provided
	 * @param int|null $value  Limit results (optional) (default null)
	 */
	public function __construct(int $offset, ?int $value = null)
	{
		if ($value)
		{
			$this->offset = $offset;

			$this->value = $value;
		}
		else
		{
			$this->value = $offset;
		}
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		if (isset($this->offset))
		{
			return 'LIMIT ' . $this->offset . ', ' . $this->value;
		}

		return 'LIMIT ' . $this->value;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return int
	 */
	public function getValue(): int
	{
		return $this->value;
	}
}
