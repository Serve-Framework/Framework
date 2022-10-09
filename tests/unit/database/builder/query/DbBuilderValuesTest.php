<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Values;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderValuesTest extends TestCase
{
	/**
     *
     */
    public function testSql(): void
    {
    	$values = new Values(['column1' => 'foo', 'column2' => 'bar']);

    	$bindings = $values->bindings();

    	$this->assertEquals('(column1, column2) VALUES(:'. implode(', :',  array_values(array_flip($bindings))).')', $values->sql());

    	$this->assertEquals(['foo', 'bar'], array_values($bindings));
    }
}