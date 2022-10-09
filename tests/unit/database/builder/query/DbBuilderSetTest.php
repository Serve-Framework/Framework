<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Set;
use serve\tests\TestCase;
use function array_flip;
use function array_values;

/**
 * @group unit
 */
class DbBuilderSetTest extends TestCase
{
    /**
     *
     */
    public function testSql(): void
    {
    	$set = new Set(['column1' => 'foo', 'column2' => 'bar']);

    	$bindings = $set->bindings();

    	$this->assertEquals(['foo', 'bar'], array_values($bindings));

    	$flipped = array_values(array_flip($bindings));

    	$this->assertEquals('column1 = :' . $flipped[0] . ', column2 = :' . $flipped[1], $set->sql());
    }
}
