<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\GroupConcat;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderGroupConcatTest extends TestCase
{
	 /**
     *
     */
    public function testSql(): void
    {
    	$concat = new GroupConcat('foobar');

    	$this->assertEquals('GROUP_CONCAT(foobar)', $concat->sql());

    	//

    	$concat = new GroupConcat('foobar', 'foobaz');

    	$this->assertEquals('GROUP_CONCAT(foobar) AS "foobaz"', $concat->sql());

    	// 
    	
    	$concat = new GroupConcat('foobar', 'foobaz', true);

    	$this->assertEquals('GROUP_CONCAT(DISTINCT foobar) AS "foobaz"', $concat->sql());

    }
}