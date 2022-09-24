<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\mvc;

use serve\mvc\controller\Controller;
use serve\mvc\model\Model;
use serve\tests\TestCase;

class TestController extends Controller
{
    public function foobar()
    {
        return 'foobar';
    }
}

class TestControllerModel extends Model
{
}

/**
 * @group unit
 */
class ControllerTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $request = $this->mock('\serve\http\request\Request');

        $response = $this->mock('\serve\http\response\Response');

        $next = function(): void
        {

        };

        $controller = new TestController($request, $response, $next, 'serve\tests\unit\framework\mvc\TestControllerModel');

        $this->assertEquals('foobar', $controller->foobar());
    }
}
