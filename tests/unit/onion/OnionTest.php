<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\onion;

use Closure;
use serve\http\request\Request;
use serve\http\response\Response;
use serve\http\response\status;
use serve\onion\Onion;
use serve\tests\TestCase;

use function count;
use function ob_get_clean;
use function ob_start;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class OnionCallbackTest
{
	protected $value;

	public function __construct(Request $request, Response $response, Closure $next, $arg1, $arg2)
    {
    	$this->value = $arg1 . $arg2;
    }

    public function normalMethod(): void
    {
    	echo $this->value;
    }

	public static function staticFunc(Request $request, Response $response, Closure $next, $arg1, $arg2): void
	{
		echo $arg1 . $arg2;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class OnionTest extends TestCase
{
	/**
	 *
	 */
	public function testAddLayer(): void
	{
		$callback = '\directory\ClassName::method';

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$this->assertEquals(1, count($onion->layers()));

		$this->assertEquals($callback, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testAddLayerInner(): void
	{
		$callbackOne = '\directory\ClassName::method';

		$callbackTwo = '\directory\ClassName::methodTwo';

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar', true);

		$this->assertEquals($callbackTwo, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testStaticLayer(): void
	{
		ob_start();

		$callback = OnionCallbackTest::class . '@normalMethod';

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testNonStaticLayer(): void
	{
		ob_start();

		$callback = OnionCallbackTest::class . '::staticFunc';

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testClosure(): void
	{
		ob_start();

		$callback = function (Request $request, Response $response, $next, $foo): void
		{
			echo $foo;
		};

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$onion->peel();

		$this->assertEquals('foo', ob_get_clean());
	}

	/**
	 *
	 */
	public function testCallNext(): void
	{
		ob_start();

		$callbackOne = function (Request $request, Response $response, $next, $foo): void
		{
			echo $foo;

			$next();
		};

		$callbackTwo = function (Request $request, Response $response, $next, $bar): void
		{
			echo $bar;
		};

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar');

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testPeeledEmpty(): void
	{
		$this->expectNotToPerformAssertions();

		$callback = function (Request $request, Response $response, $next, $foo): void
		{
			$next();
		};

		$request = $this->mock(Request::class);

		$response = $this->mock(Response::class);

		$status = $this->mock(status::class);

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$response->shouldReceive('status')->andReturn($status);

		$status->shouldReceive('get')->andReturn(404);

		$response->shouldReceive('notFound');

		$onion->peel();
	}
}
