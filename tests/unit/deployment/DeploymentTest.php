<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\deployment;

use serve\deployment\Deployment;
use serve\deployment\webhooks\Github;
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

        $webhook = $this->mock(Github::class);

        $deployment = new Deployment($webhook);

        $webhook->shouldReceive('deploy');

        $deployment->update();
    }
}
