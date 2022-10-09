<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\mvc;

use serve\mvc\model\Model;
use serve\tests\TestCase;

class TestModelCase extends Model
{
    public function foobar()
    {
        return 'foobar';
    }
}

/**
 * @group unit
 */
class ModelTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $model = new TestModelCase;

        $this->assertEquals('foobar', $model->foobar());
    }
}
