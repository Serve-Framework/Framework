<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\database\builder\query;

use serve\database\builder\query\Table;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DbBuilderTableTest extends TestCase
{
    /**
     *
     */
    public function testSql(): void
    {
    	$table = new Table('table_name');

    	$this->assertEquals("CREATE TABLE table_name ( `id` INT  UNSIGNED  UNIQUE  AUTO_INCREMENT PRIMARY KEY (id)\n) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;", $table->create('mysql'));

    	$this->assertEquals('DROP TABLE IF EXISTS table_name', $table->drop('mysql'));

    	$this->assertEquals('TRUNCATE TABLE table_name', $table->truncate('mysql'));

    	//

    	$this->assertEquals('CREATE TABLE table_name ( `id` INTEGER  NOT NULL  UNIQUE  PRIMARY KEY  AUTOINCREMENT )', $table->create('sqlite'));

    	$this->assertEquals('DROP TABLE IF EXISTS table_name', $table->drop('sqlite'));

    	$this->assertEquals('DELETE FROM table_name', $table->truncate('sqlite'));
    }
}
