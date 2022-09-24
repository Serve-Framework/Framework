<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case.
 */
abstract class TestCase extends PHPUnitTestCase
{
	use MockeryPHPUnitIntegration;

    protected function mock(string $class, array $args = [])
    {
        if (!empty($args))
        {
            return Mockery::mock($class, $args);
        }

        return Mockery::mock($class);
    }
}
