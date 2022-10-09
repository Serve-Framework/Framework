<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\GroupBy;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderGroupByTest extends TestCase
{
	 /**
     *
     */
    public function testSql(): void
    {
    	$group = new GroupBy('foobar');

    	$this->assertEquals('GROUP BY foobar', $group->sql());
    }
}