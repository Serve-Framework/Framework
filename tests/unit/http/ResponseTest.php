<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\http\response;

use serve\http\cookie\Cookie;
use serve\http\request\Headers as RequestHeaders;
use serve\http\request\Request;
use serve\http\response\Body;
use serve\http\response\CDN;
use serve\http\response\Format;
use serve\http\response\Headers as ResponseHeaders;
use serve\http\response\Protocol;
use serve\http\response\Response;
use serve\http\response\Status;
use serve\http\session\Session;
use serve\mvc\view\View;
use serve\tests\TestCase;

use function extract;
use function hash;
use function ob_end_clean;
use function ob_start;

/**
 * @group unit
 */
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	private function mockResponse()
	{
		$protocol = $this->mock(Protocol::class);
		$format   = $this->mock(Format::class);
		$body     = $this->mock(Body::class);
		$status   = $this->mock(Status::class);
		$headers  = $this->mock(ResponseHeaders::class);
		$cookie   = $this->mock(Cookie::class);
		$session  = $this->mock(Session::class);
		$cdn      = $this->mock(CDN::class);
		$view     = $this->mock(View::class);
		$request  = $this->mock(Request::class);
		$rHeaders = $this->mock(RequestHeaders::class);

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);

		$response = new Response($protocol, $format, $body, $status, $headers, $cookie, $session, $cdn, $view, $request, false);

		return
		[
			'protocol' => $protocol,
			'format'   => $format,
			'body'     => $body,
			'status'   => $status,
			'headers'  => $headers,
			'cookie'   => $cookie,
			'session'  => $session,
			'cdn'      => $cdn,
			'view'     => $view,
			'request'  => $request,
			'response' => $response,
			'rHeaders' => $rHeaders,
		];
	}

	/**
	 *
	 */
	public function testSend(): void
	{
		$this->expectNotToPerformAssertions();

		$responseArr = $this->mockResponse();

		extract($responseArr);

		$request->shouldReceive('getMethod')->andReturn('GET');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);
		$format->shouldReceive('get')->andReturn('text/html');
		$format->shouldReceive('getEncoding')->andReturn('utf-8');

		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('message')->andReturn('OK');
		$status->shouldReceive('isRedirect')->andReturn(false);
		$status->shouldReceive('isEmpty')->andReturn(false);
		$status->shouldReceive('isNotModified')->andReturn(false);

		$headers->shouldReceive('set')->withArgs(['Status', 200]);
		$headers->shouldReceive('set')->withArgs(['Content-length', 0]);
		$headers->shouldReceive('set')->withArgs(['HTTP', '200 OK']);
		$headers->shouldReceive('set')->withArgs(['Content-Type', 'text/html;utf-8']);
		$headers->shouldReceive('set')->withArgs(['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0']);
		$headers->shouldReceive('send');

		$body->shouldReceive('length')->andReturn(0);
		$body->shouldReceive('set')->withArgs(['']);
		$body->shouldReceive('get')->andReturn('');
		$body->shouldReceive('get')->andReturn('');

		$session->shouldReceive('save');
		$cookie->shouldReceive('send');
		$cdn->shouldReceive('filter')->withArgs([''])->andReturn('');

		$response->send();
	}

	/**
	 *
	 */
	public function testSendCacheEnabled(): void
	{
		$this->expectNotToPerformAssertions();

		ob_start();

		$hash = '"' . hash('sha256', 'foobar') . '"';

		$responseArr = $this->mockResponse();

		extract($responseArr);

		$request->shouldReceive('getMethod')->andReturn('GET');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);
		$format->shouldReceive('get')->andReturn('text/html');
		$format->shouldReceive('getEncoding')->andReturn('utf-8');

		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('message')->andReturn('OK');
		$status->shouldReceive('isRedirect')->andReturn(false);
		$status->shouldReceive('isEmpty')->andReturn(false);
		$status->shouldReceive('isNotModified')->andReturn(false);

		$headers->shouldReceive('get')->withArgs(['Cache-Control'])->andReturn(false);
		$headers->shouldReceive('set')->withArgs(['Status', 200]);
		$headers->shouldReceive('set')->withArgs(['Content-length', 6]);
		$headers->shouldReceive('set')->withArgs(['HTTP', '200 OK']);
		$headers->shouldReceive('set')->withArgs(['Content-Type', 'text/html;utf-8']);
		$headers->shouldReceive('set')->withArgs(['Cache-Control', 'private, max-age=3600']);
		$headers->shouldReceive('set')->withArgs(['ETag', $hash]);
		$headers->shouldReceive('send');

		$request->shouldReceive('headers')->andReturn($rHeaders);
		$rHeaders->HTTP_IF_NONE_MATCH = '3434r23rrfjf';

		$body->shouldReceive('length')->andReturn(6);
		$body->shouldReceive('set')->withArgs(['foobar']);
		$body->shouldReceive('get')->andReturn('foobar');

		$session->shouldReceive('save');
		$cookie->shouldReceive('send');
		$cdn->shouldReceive('filter')->withArgs(['foobar'])->andReturn('foobar');

		$response->enableCaching();
		$response->send();

		ob_end_clean();
	}
}
