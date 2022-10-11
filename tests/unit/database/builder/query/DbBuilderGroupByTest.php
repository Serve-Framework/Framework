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
    	$group = new GroupBy('foobar', 'prefix_');

    	$this->assertEquals('GROUP BY foobar', $group->sql());

        //

        $group = new GroupBy('foo, bar', 'prefix_');

        $this->assertEquals('GROUP BY foo, bar', $group->sql());

        //

        $group = new GroupBy('foo, bar', 'prefix_');

        $this->assertEquals('GROUP BY prefix_base_table.foo, prefix_base_table.bar', $group->sql('prefix_base_table'));

        //


        $group = new GroupBy('foo, base_table.bar', 'prefix_');

        $this->assertEquals('GROUP BY prefix_base_table.foo, prefix_base_table.bar', $group->sql('prefix_base_table'));

        //

        $group = new GroupBy('tabe_b.foo, table_a.bar', 'prefix_');

        $this->assertEquals('GROUP BY prefix_tabe_b.foo, prefix_table_a.bar', $group->sql('prefix_table_a'));
    }
}
