<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\database\builder\query;

use InvalidArgumentException;

use function implode;
use function in_array;

/**
 * SQL "JOIN" statement wrapper.
 */
class Join
{
    /**
     * Constant for "INNER JOIN" statement.
     *
     * @var string
     */
    public const TYPE_INNER = 'INNER JOIN';

    /**
     * Constant for "LEFT JOIN" statement.
     *
     * @var string
     */
    public const TYPE_LEFT = 'LEFT JOIN';

    /**
     * Constant for "RIGHT JOIN" statement.
     *
     * @var string
     */
    public const TYPE_RIGHT = 'RIGHT JOIN';

    /**
     * Constant for "LEFT OUTER JOIN" statement.
     *
     * @var string
     */
    public const TYPE_LEFT_OUTER = 'LEFT OUTER JOIN';

    /**
     * Constant for "RIGHT OUTER JOIN" statement.
     *
     * @var string
     */
    public const TYPE_RIGHT_OUTER = 'RIGHT OUTER JOIN';

    /**
     * Constant for "FULL OUTER JOIN" statement.
     *
     * @var string
     */
    public const TYPE_FULL_OUTER = 'FULL OUTER JOIN';

    /**
     * Acceptable join types.
     *
     * @var array
     */
    protected const ACCEPTABLE_TYPES = ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'FULL OUTER JOIN'];

	/**
	 * Table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Columns comparison e.g "table1.column_name = table2.column_name".
	 *
	 * @var string
	 */
	protected $comparisons;

	/**
	 * Join type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Constructor.
	 *
	 * @param string $table       The table name to join
	 * @param string $comparisons Column comparison e.g "table1.column_name = table2.column_name"
	 * @param string $type        Join type
	 */
	public function __construct(string $table, string $comparisons, string $type = 'INNER JOIN')
	{
		if (!in_array($type, self::ACCEPTABLE_TYPES))
		{
			throw new InvalidArgumentException('Invalid join type [' . $type . ']. Join type must be one of [' . implode(', ', self::ACCEPTABLE_TYPES) . ']');
		}

		$this->table = $table;

		$this->comparisons = $comparisons;

		$this->type = $type;
	}

	/**
	 * Returns the SQL statement.
	 *
	 * @return string
	 */
	public function sql(): string
	{
		$sql = [$this->type];

		return $this->type . ' ' . $this->table . ' ON ' . $this->comparisons;
	}
}
