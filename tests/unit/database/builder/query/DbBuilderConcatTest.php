<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Concat;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderConcatTest extends TestCase
{
    /**
     *
     */
    public function testSql(): void
    {
    	$concat = new Concat(['foo', 'bar', 'baz'], 'foobar');

    	$this->assertEquals('CONCAT(foo, bar, baz) AS foobar', $concat->sql());
    }
}
