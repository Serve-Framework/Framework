<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Select;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderSelectTest extends TestCase
{
    /**
     *
     */
    public function testSql(): void
    {
    	$select = new Select('id', 'prefix_');

    	$this->assertEquals('SELECT id', $select->sql());

    	//

    	$select = new Select('id, username, email', 'prefix_');

    	$this->assertEquals('SELECT id, username, email', $select->sql());

    	//

    	$select = new Select(['id', 'username', 'email'], 'prefix_');

    	$this->assertEquals('SELECT id, username, email', $select->sql());

    	//

    	$select = new Select(['users' => ['id', 'email']], 'prefix_');

    	$this->assertEquals('SELECT prefix_users.id, prefix_users.email', $select->sql());

    	//

    	$select = new Select(['users' => ['id', 'email'], 'groups' => ['name']], 'prefix_');

    	$this->assertEquals('SELECT prefix_users.id, prefix_users.email, prefix_groups.name', $select->sql());

    	//

   		$select = new Select('id', 'prefix_');

    	$this->assertEquals('SELECT prefix_table.id', $select->sql('table'));

    	//

    	$select = new Select('id, username, email', 'prefix_');

    	$this->assertEquals('SELECT prefix_table.id, prefix_table.username, prefix_table.email', $select->sql('table'));

    	//

    	$select = new Select(['id', 'username', 'email'], 'prefix_');

    	$this->assertEquals('SELECT prefix_table.id, prefix_table.username, prefix_table.email', $select->sql('table'));

    	//

    	$select = new Select(['users' => ['id', 'email']], 'prefix_');

    	$this->assertEquals('SELECT prefix_users.id, prefix_users.email', $select->sql('table'));

    	//

    	$select = new Select(['users' => ['id', 'email'], 'groups' => ['name']], 'prefix_');

    	$this->assertEquals('SELECT prefix_users.id, prefix_users.email, prefix_groups.name', $select->sql('table'));

        //

        $select = new Select(['COUNT' => 'id'], 'prefix_');

        $this->assertEquals('SELECT COUNT(prefix_table.id)', $select->sql('table'));

        //

        $select = new Select(['COUNT' => ['DISTINCT', 'id']], 'prefix_');

        $this->assertEquals('SELECT COUNT(DISTINCT prefix_table.id)', $select->sql('table'));

        //

        $select = new Select('COUNT(id)', 'prefix_');

        $this->assertEquals('SELECT COUNT(prefix_table.id)', $select->sql('table'));

        //

        $select = new Select('COUNT(id)', 'prefix_');

        $this->assertEquals('SELECT COUNT(id)', $select->sql());
    }
}
