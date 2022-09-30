<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\exception\handlers;

use ErrorException;
use serve\exception\handlers\WebHandler;
use serve\http\request\Environment;
use serve\http\request\Request;
use serve\http\response\Body;
use serve\http\response\exceptions\ForbiddenException;
use serve\http\response\exceptions\InvalidTokenException;
use serve\http\response\exceptions\MethodNotAllowedException;
use serve\http\response\exceptions\NotFoundException;
use serve\http\response\exceptions\RequestException;
use serve\http\response\Format;
use serve\http\response\Headers;
use serve\http\response\Response;
use serve\http\response\Status;
use serve\mvc\view\View;
use serve\tests\TestCase;

/**
 * @group unit
 */
class WebHandlerTest extends TestCase
{
    /**
     *
     */
    protected function getServerData(): array
    {
        return
        [
            'REQUEST_METHOD'  => 'GET',
            'SCRIPT_NAME'     => '/foobar/index.php',
            'SERVER_NAME'     => 'localhost',
            'SERVER_PORT'     => '8888',
            'HTTP_PROTOCOL'   => 'http',
            'DOCUMENT_ROOT'   => '/usr/name/httpdocs',
            'HTTP_HOST'       => 'http://localhost:8888',
            'DOMAIN_NAME'     => 'localhost:8888',
            'REQUEST_URI'     => '/foobar?foo=bar',
            'REMOTE_ADDR'     => '192.168.1.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17',
        ];
    }

    /**
     *
     */
    public function testDefaults(): void
    {
        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('text/html');
        $format->shouldReceive('set')->with('text/html');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->with('output from view');

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('message')->andReturn('Internal Server Error');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $view->shouldReceive('display')->once()->withArgs(function ($path, $vars)
        {
            return str_contains($path, 'debug.php') && is_array($vars) && count($vars) > 0;

        })->andReturn('output from view');

        $response->shouldReceive('send');

        $handler->handle(new ErrorException, true);
    }

    /**
     *
     */
    public function testNoDebug(): void
    {
        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('text/html');
        $format->shouldReceive('set')->with('text/html');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->with('output from view');

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('get')->andReturn(500);
        $status->shouldReceive('message')->andReturn('Internal Server Error');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $view->shouldReceive('display')->once()->withArgs(function ($path, $vars)
        {
            return str_contains($path, 'generic.php') && $vars['code'] === 500;

        })->andReturn('output from view');

        $response->shouldReceive('send');

        $handler->handle(new ErrorException, false);
    }

    /**
     *
     */
    public function testRequestExceptionsDebug(): void
    {
        $this->expectNotToPerformAssertions();

        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('text/html');
        $format->shouldReceive('set')->with('text/html');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->with('output from view');

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(403);
        $status->shouldReceive('set')->with(404);
        $status->shouldReceive('set')->with(405);
        $status->shouldReceive('set')->with(498);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('set')->with(507);
        $status->shouldReceive('get')->andReturn(404);

        $status->shouldReceive('message')->andReturn('Forbidden');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $response->shouldReceive('headers')->andReturn($headers);
        $headers->shouldReceive('set')->with('allows', 'POST');

        $view->shouldReceive('display')->withArgs(function ($path, $vars)
        {
            return str_contains($path, 'debug.php') && is_array($vars) && count($vars) > 0;

        })->andReturn('output from view');

        $response->shouldReceive('send');

        $handler->handle(new ForbiddenException, true);
        $handler->handle(new InvalidTokenException, true);
        $handler->handle(new MethodNotAllowedException(['POST']), true);
        $handler->handle(new NotFoundException, true);
        $handler->handle(new RequestException(500), true);
        $handler->handle(new RequestException(507), true);
    }

    /**
     *
     */
    public function testRequestExceptionsGeneric(): void
    {
        $this->expectNotToPerformAssertions();

        $expectedCodes = [403, 404, 405, 498, 500, 507];

        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('text/html');
        $format->shouldReceive('set')->with('text/html');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->with('output from view');

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(403);
        $status->shouldReceive('set')->with(404);
        $status->shouldReceive('set')->with(405);
        $status->shouldReceive('set')->with(498);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('set')->with(507);
        $status->shouldReceive('get')->andReturn(404);

        $status->shouldReceive('message')->andReturn('Forbidden');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $response->shouldReceive('headers')->andReturn($headers);
        $headers->shouldReceive('set')->with('allows', 'POST');

        $view->shouldReceive('display')->withArgs(function ($path, $vars) use ($expectedCodes)
        {
            return str_contains($path, 'generic.php') && in_array($vars['code'], $expectedCodes) && $vars['title'] === 'Forbidden';

        })->andReturn('output from view');

        $response->shouldReceive('send');

        $handler->handle(new ForbiddenException, false);
        $handler->handle(new InvalidTokenException, false);
        $handler->handle(new MethodNotAllowedException(['POST']), false);
        $handler->handle(new NotFoundException, false);
        $handler->handle(new RequestException(500), false);
        $handler->handle(new RequestException(507), false);
    }

    /**
     *
     */
    public function testReturnAsJson(): void
    {
        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('application/json');
        $format->shouldReceive('set')->with('application/json');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->withArgs(function ($json)
        {
            $decoded = json_decode($json);

            return true;

        });

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('message')->andReturn('Internal Server Error');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $view->shouldNotReceive('display');

        $response->shouldReceive('send');

        $handler->handle(new ErrorException, true);
    }

    /**
     *
     */
    public function testReturnAsJsonDebug(): void
    {
        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('application/json');
        $format->shouldReceive('set')->with('application/json');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->withArgs(function ($json)
        {
            $decoded = json_decode($json);

            return true;

        });

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('message')->andReturn('Internal Server Error');

        $request->shouldReceive('isBot')->andReturn(false);
        $request->shouldReceive('environment')->andReturn($environment);

        $view->shouldNotReceive('display');

        $response->shouldReceive('send');

        $handler->handle(new ErrorException, false);
    }

    /**
     *
     */
    public function testIsBot(): void
    {
        $request     = $this->mock(Request::class);
        $response    = $this->mock(Response::class);
        $view        = $this->mock(View::class);
        $environment = new Environment($this->getServerData());
        $format      = $this->mock(Format::class);
        $body        = $this->mock(Body::class);
        $headers     = $this->mock(Headers::class);
        $status      = $this->mock(Status::class);
        $handler     = new WebHandler($request, $response, $view);

        $request->shouldReceive('isAjax')->andReturn(false);
        $response->shouldReceive('format')->andReturn($format);

        $format->shouldReceive('get')->andReturn('text/html');
        $format->shouldReceive('set')->with('text/html');

        $response->shouldReceive('body')->andReturn($body);
        $body->shouldReceive('set')->with('Test message');

        $response->shouldReceive('status')->andReturn($status);
        $status->shouldReceive('set')->with(500);
        $status->shouldReceive('message')->andReturn('Internal Server Error');

        $request->shouldReceive('isBot')->andReturn(true);
        $request->shouldReceive('environment')->andReturn($environment);

        $view->shouldNotReceive('display');

        $response->shouldReceive('send');

        $handler->handle(new ErrorException('Test message'), true);
    }
}
