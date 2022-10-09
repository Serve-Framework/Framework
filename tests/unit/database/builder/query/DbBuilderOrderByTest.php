<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\OrderBy;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderOrderByTest extends TestCase
{
	/**
     *
     */
    public function testSql(): void
    {
    	$order = new OrderBy('column1, column2');

    	$this->assertEquals('ORDER BY column1, column2 DESC', $order->sql());

    	//

    	$order = new OrderBy('column1, column2', 'DESC');

    	$this->assertEquals('ORDER BY column1, column2 DESC', $order->sql());

    	//

    	$order = new OrderBy('column1, column2', 'ASC');

    	$this->assertEquals('ORDER BY column1, column2 ASC', $order->sql());
    }
}