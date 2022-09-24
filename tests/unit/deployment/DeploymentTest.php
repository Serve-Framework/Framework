<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\deployment;

use serve\deployment\Deployment;
use serve\tests\TestCase;

/**
 * @group unit
 */
class DeploymentTest extends TestCase
{
    /**
     *
     */
    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();

        $webhook = $this->mock('\serve\deployment\webhooks\Github');

        $deployment = new Deployment($webhook);

        $webhook->shouldReceive('deploy');

        $deployment->update();
    }
}
