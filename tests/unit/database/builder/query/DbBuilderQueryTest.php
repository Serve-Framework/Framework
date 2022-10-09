<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Where;
use serve\tests\TestCase;
use function array_flip;
use function array_values;

/**
 * @group unit
 */
class DbBuilderQueryTest extends TestCase
{
    /**
     *
     */
    public function testBasic(): void
    {
    	$where = new Where('foo', '=', 'bar');

    	$bindings = $where->bindings();

    	$this->assertEquals(['bar'], array_values($bindings));

    	$flipped = array_values(array_flip($bindings));

    	$this->assertEquals('foo = :' . $flipped[0], $where->sql());
    }

    /**
     *
     */
    public function testNested(): void
    {
    	$where = new Where('foo', '=', ['bar', 'baz']);

    	$bindings = $where->bindings();

    	$this->assertEquals(['bar', 'baz'], array_values($bindings));

    	$flipped = array_values(array_flip($bindings));

    	$this->assertEquals('(foo = :' . $flipped[0] . ' OR ' . 'foo = :' . $flipped[1] . ')', $where->sql());
    }

    /**
     *
     */
    public function testTable(): void
    {
    	$where = new Where('foo', '=', 'bar');

    	$bindings = $where->bindings();

    	$this->assertEquals(['bar'], array_values($bindings));

    	$flipped = array_values(array_flip($bindings));

    	$this->assertEquals('table.foo = :' . $flipped[0], $where->sql('table'));
    }
}
