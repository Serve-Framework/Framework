<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Join;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderJoinTest extends TestCase
{
    /**
     *
     */
    public function testSql(): void
    {
    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_INNER);

    	$this->assertEquals('INNER JOIN table ON table1.foo = table2.bar', $join->sql());

    	//

    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_LEFT);

    	$this->assertEquals('LEFT JOIN table ON table1.foo = table2.bar', $join->sql());

    	//

    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_RIGHT);

    	$this->assertEquals('RIGHT JOIN table ON table1.foo = table2.bar', $join->sql());

    	//

    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_FULL_OUTER);

    	$this->assertEquals('FULL OUTER JOIN table ON table1.foo = table2.bar', $join->sql());

    	//

    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_LEFT_OUTER);

    	$this->assertEquals('LEFT OUTER JOIN table ON table1.foo = table2.bar', $join->sql());

    	//

    	$join = new Join('table', 'table1.foo = table2.bar', Join::TYPE_RIGHT_OUTER);

    	$this->assertEquals('RIGHT OUTER JOIN table ON table1.foo = table2.bar', $join->sql());
    }
}
