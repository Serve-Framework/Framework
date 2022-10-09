<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Limit;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderLimitTest extends TestCase
{
	/**
     *
     */
    public function testSql(): void
    {
    	$limit = new Limit(3);

    	$this->assertEquals('LIMIT 3', $limit->sql());

    	//

    	$limit = new Limit(3, 5);

    	$this->assertEquals('LIMIT 3, 5', $limit->sql());
    }
}