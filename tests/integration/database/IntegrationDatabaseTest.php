<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\integration\database;

use serve\tests\integration\DatabaseTestCase;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 */
class IntegrationDatabaseTest extends DatabaseTestCase
{
    /**
     *
     */
    public function testConnection(): void
    {
        $this->setup();

        $this->assertTrue($this->connection->isConnected());
    }
}
